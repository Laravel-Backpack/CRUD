<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

trait Create
{
    /*
    |--------------------------------------------------------------------------
    |                                   CREATE
    |--------------------------------------------------------------------------
    */

    /**
     * Insert a row in the database.
     *
     * @param  array  $input  All input values to be inserted.
     * @return Model
     */
    public function create($input)
    {
        [$directInputs, $relationInputs] = $this->splitInputIntoDirectAndRelations($input);

        if ($this->get('create.useDatabaseTransactions') ?? config('backpack.base.useDatabaseTransactions', false)) {
            return DB::transaction(fn () => $this->createModelAndRelations($directInputs, $relationInputs));
        }

        return $this->createModelAndRelations($directInputs, $relationInputs);
    }

    private function createModelAndRelations(array $directInputs, array $relationInputs): Model
    {
        $item = $this->model->create($directInputs);
        $this->createRelationsForItem($item, $relationInputs);

        return $item;
    }

    /**
     * Get all fields needed for the ADD NEW ENTRY form.
     *
     * @return array The fields with attributes and fake attributes.
     */
    public function getCreateFields()
    {
        return $this->fields();
    }

    /**
     * Get all fields with relation set (model key set on field).
     *
     * @param  array  $fields
     * @return array The fields with model key set.
     */
    public function getRelationFields($fields = [])
    {
        if (empty($fields)) {
            $fields = $this->getCleanStateFields();
        }

        $relationFields = [];

        foreach ($fields as $field) {
            if (isset($field['model']) && $field['model'] !== false && $field['entity'] !== false) {
                array_push($relationFields, $field);
            }

            // if a field has an array name AND subfields
            // then take those fields into account (check if they have relationships);
            // this is done in particular for the checklist_dependency field,
            // but other fields could use it too, in the future;
            if ($this->holdsMultipleInputs($field['name']) &&
                isset($field['subfields']) &&
                is_array($field['subfields'])) {
                foreach ($field['subfields'] as $subfield) {
                    if (isset($subfield['model']) && $subfield['model'] !== false) {
                        array_push($relationFields, $subfield);
                    }
                }
            }
        }

        return $relationFields;
    }

    /**
     * ---------------
     * PRIVATE METHODS
     * ---------------.
     */

    /**
     * Create relations for the provided model.
     *
     * @param  Model  $item  The current CRUD model.
     * @param  array  $formattedRelations  The form data.
     * @return ?bool
     */
    private function createRelationsForItem($item, $formattedRelations)
    {
        // no relations to create
        if (empty($formattedRelations)) {
            return false;
        }

        foreach ($formattedRelations as $relationMethod => $relationDetails) {
            $relation = $item->{$relationMethod}();
            $relationType = $relationDetails['relation_type'];

            switch ($relationType) {
                case 'HasOne':
                case 'MorphOne':
                    $this->createUpdateOrDeleteOneToOneRelation($relation, $relationMethod, $relationDetails);
                    break;
                case 'HasMany':
                case 'MorphMany':
                    $relationValues = $relationDetails['values'][$relationMethod];
                    // if relation values are null we can only attach, also we check if we sent
                    // - a single dimensional array: [1,2,3]
                    // - an array of arrays: [[1][2][3]]
                    // if is as single dimensional array we can only attach.
                    if ($relationValues === null || ! is_multidimensional_array($relationValues)) {
                        $this->attachManyRelation($item, $relation, $relationDetails, $relationValues);
                    } else {
                        $this->createManyEntries($item, $relation, $relationMethod, $relationDetails);
                    }
                    break;
                case 'BelongsToMany':
                case 'MorphToMany':
                    $values = $relationDetails['values'][$relationMethod] ?? [];
                    $values = is_string($values) ? (json_decode($values, true) ?? []) : $values;
                    $field = $relationDetails['crudFields'][0] ?? [];

                    // if the values are multidimensional, we have additional pivot data.
                    if (is_array($values) && is_multidimensional_array($values)) {
                        // if the field allow duplicated pivots, we can't use sync or attach from laravel, we need to manually handle the pivot data.
                        if ($field['allow_duplicate_pivots'] ?? false) {
                            $keyName = $field['pivot_key_name'] ?? 'id';
                            $sentIds = array_filter(array_column($values, $keyName));
                            $dbValues = $relation->newPivotQuery()->pluck($keyName)->toArray();

                            $toDelete = array_diff($dbValues, $sentIds);

                            if (! empty($toDelete)) {
                                foreach ($toDelete as $id) {
                                    $relation->newPivot()->where($keyName, $id)->delete();
                                }
                            }
                            foreach ($values as $value) {
                                // if it's an existing pivot, update it
                                $attributes = $this->preparePivotAttributesForSave($value, $relation, $item->getKey(), $keyName, $relationMethod);
                                if (isset($value[$keyName])) {
                                    $relation->newPivot()->where($keyName, $value[$keyName])->update($attributes);
                                } else {
                                    $relation->newPivot()->create($attributes);
                                }
                            }
                            break;
                        }

                        $relationToManyValues = [];

                        foreach ($values as $value) {
                            if (isset($value[$relationMethod])) {
                                $relationToManyValues[$value[$relationMethod]] = Arr::except($value, $relationMethod);
                            }
                        }

                        $item->{$relationMethod}()->sync($relationToManyValues);
                        unset($relationToManyValues);
                        break;
                    }

                    // if there is no relation data, and the values array is single dimensional we have
                    // an array of keys with no additional pivot data. sync those.
                    if (empty($relationToManyValues)) {
                        $relationToManyValues = array_values($values);
                    }
                    $item->{$relationMethod}()->sync($relationToManyValues);
                    unset($relationToManyValues);
                    break;
            }
        }
    }

    private function preparePivotAttributesForSave(array $attributes, BelongsToMany|MorphToMany $relation, string|int $relatedItemKey, $pivotKeyName, $relationMethod): array
    {
        $attributes[$relation->getForeignPivotKeyName()] = $relatedItemKey;
        $attributes[$relation->getRelatedPivotKeyName()] = $attributes[$relationMethod];

        if ($relation instanceof MorphToMany) {
            $attributes[$relation->getMorphType()] = $relation->getMorphClass();
        }

        return Arr::except($attributes, [$relationMethod, $pivotKeyName]);
    }

    /**
     * Save the attributes of a given HasOne or MorphOne relationship on the
     * related entry, create or delete it, depending on what was sent in the form.
     *
     * For HasOne and MorphOne relationships, the dev might want to a few different things:
     * (A) save an attribute on the related entry (eg. passport.number)
     * (B) set an attribute on the related entry to NULL (eg. slug.slug)
     * (C) save an entire related entry (eg. passport)
     * (D) delete the entire related entry (eg. passport)
     *
     * @param  \Illuminate\Database\Eloquent\Relations\HasOne|\Illuminate\Database\Eloquent\Relations\MorphOne  $relation
     * @param  string  $relationMethod  The name of the relationship method on the main Model.
     * @param  array  $relationDetails  Details about that relationship. For example:
     *                                  [
     *                                  'model' => 'App\Models\Passport',
     *                                  'parent' => 'App\Models\Pet',
     *                                  'entity' => 'passport',
     *                                  'attribute' => 'passport',
     *                                  'values' => **THE TRICKY BIT**,
     *                                  ]
     * @return ?Model
     */
    private function createUpdateOrDeleteOneToOneRelation($relation, $relationMethod, $relationDetails)
    {
        // Let's see which scenario we're treating, depending on the contents of $relationDetails:
        //      - (A) ['number' => 1315, 'name' => 'Something'] (if passed using a text/number/etc field)
        //      - (B) ['slug' => null] (if the 'slug' attribute on the 'slug' related entry needs to be cleared)
        //      - (C) ['passport' => [['number' => 1314, 'name' => 'Something']]] (if passed using a repeatable field)
        //      - (D) ['passport' => null] (if deleted from the repeatable field)

        // Scenario C or D
        if (array_key_exists($relationMethod, $relationDetails['values'])) {
            $relationMethodValue = $relationDetails['values'][$relationMethod];

            // Scenario D
            if (is_null($relationMethodValue) && $relationDetails['entity'] === $relationMethod) {
                $relation->first()?->delete();

                return null;
            }

            // Scenario C (when it's an array inside an array, because it's been added as one item inside a repeatable field)
            if (gettype($relationMethodValue) == 'array' && is_multidimensional_array($relationMethodValue)) {
                $relationMethodValue = $relationMethodValue[0];
            }
        }
        // saving process
        $input = $relationMethodValue ?? $relationDetails['values'];
        [$directInputs, $relationInputs] = $this->splitInputIntoDirectAndRelations($input, $relationDetails, $relationMethod);

        $item = $relation->updateOrCreate([], $directInputs);

        $this->createRelationsForItem($item, $relationInputs);

        return $item;
    }

    /**
     * When using the HasMany/MorphMany relations as selectable elements we use this function to "mimic-sync" in those relations.
     * Since HasMany/MorphMany does not have the `sync` method, we manually re-create it.
     * Here we add the entries that developer added and remove the ones that are not in the list.
     * This removal process happens with the following rules:
     * - by default Backpack will behave like a `sync` from M-M relations: it deletes previous entries and add only the current ones.
     * - `force_delete` is configurable in the field, it's `true` by default. When false, if connecting column is nullable instead of deleting the row we set the column to null.
     * - `fallback_id` could be provided. In this case instead of deleting we set the connecting key to whatever developer gives us.
     *
     * @return mixed
     */
    private function attachManyRelation($item, $relation, $relationDetails, $relationValues)
    {
        $modelInstance = $relation->getRelated();
        $relationForeignKey = $relation->getForeignKeyName();
        $relationLocalKey = $relation->getLocalKeyName();

        if (empty($relationValues)) {
            // the developer cleared the selection
            // we gonna clear all related values by setting up the value to the fallback id, to null or delete.
            return $this->handleManyRelationItemRemoval($modelInstance, $relation, $relationDetails, $relationForeignKey);
        }
        // we add the new values into the relation, if it is HasMany we only update the foreign_key,
        // otherwise (it's a MorphMany) we need to update the morphs keys too
        $toUpdate[$relationForeignKey] = $item->{$relationLocalKey};

        if ($relationDetails['relation_type'] === 'MorphMany') {
            $toUpdate[$relation->getQualifiedMorphType()] = $relation->getMorphClass();
        }

        $modelInstance->whereIn($modelInstance->getKeyName(), $relationValues)
            ->update($toUpdate);

        // we clear up any values that were removed from model relation.
        // if developer provided a fallback id, we use it
        // if column is nullable we set it to null if developer didn't specify `force_delete => true`
        // if none of the above we delete the model from database
        $removedEntries = $modelInstance->whereNotIn($modelInstance->getKeyName(), $relationValues)
                            ->where($relationForeignKey, $item->{$relationLocalKey});

        // if relation is MorphMany we also match by morph type.
        if ($relationDetails['relation_type'] === 'MorphMany') {
            $removedEntries->where($relation->getQualifiedMorphType(), $relation->getMorphClass());
        }

        return $this->handleManyRelationItemRemoval($modelInstance, $removedEntries, $relationDetails, $relationForeignKey);
    }

    private function handleManyRelationItemRemoval($modelInstance, $removedEntries, $relationDetails, $relationForeignKey)
    {
        $relationColumnIsNullable = $modelInstance->isColumnNullable($relationForeignKey);
        $forceDelete = $relationDetails['force_delete'] ?? false;
        $fallbackId = $relationDetails['fallback_id'] ?? false;

        // developer provided a fallback_id he knows what he's doing, just use it.
        if ($fallbackId) {
            return $removedEntries->update([$relationForeignKey => $fallbackId]);
        }

        // developer set force_delete => true, so we don't care if it's nullable or not,
        // we just follow developer's will
        if ($forceDelete) {
            return $removedEntries->lazy()->each->delete();
        }

        // get the default that could be set at database level.
        $dbColumnDefault = $modelInstance->getDbColumnDefault($relationForeignKey);

        // check if the relation foreign key is in casts, and cast it to the correct type
        if ($modelInstance->hasCast($relationForeignKey)) {
            $dbColumnDefault = match ($modelInstance->getCasts()[$relationForeignKey]) {
                'int', 'integer' => $dbColumnDefault = (int) $dbColumnDefault,
                default => $dbColumnDefault = $dbColumnDefault
            };
        }
        // if column is not nullable in database, and there is no column default (null),
        // we will delete the entry from the database, otherwise it will throw and ugly DB error.
        if (! $relationColumnIsNullable && $dbColumnDefault === null) {
            return $removedEntries->lazy()->each->delete();
        }

        // if column is nullable we just set it to the column default (null when it does exist, or the default value when it does).
        return $removedEntries->update([$relationForeignKey => $dbColumnDefault]);
    }

    /**
     * Handle HasMany/MorphMany relations when used as creatable entries in the crud.
     * By using repeatable field, developer can allow the creation of such entries
     * in the crud forms.
     *
     * @param  $entry  - eg: story
     * @param  $relation  - eg  story HasMany monsters
     * @param  $relationMethod  - eg: monsters
     * @param  $relationDetails  - eg: info about relation including submitted values
     * @return void
     */
    private function createManyEntries($entry, $relation, $relationMethod, $relationDetails)
    {
        $items = $relationDetails['values'][$relationMethod];

        $relatedModelLocalKey = $relation->getRelated()->getKeyName();

        $relatedItemsSent = [];

        foreach ($items as $item) {
            [$directInputs, $relationInputs] = $this->splitInputIntoDirectAndRelations($item, $relationDetails, $relationMethod);
            // for each item we get the inputs to create and the relations of it.
            $relatedModelLocalKeyValue = $item[$relatedModelLocalKey] ?? null;

            // we either find the matched entry by local_key (usually `id`)
            // and update the values from the input
            // or create a new item from input
            $item = $entry->{$relationMethod}()->updateOrCreate([$relatedModelLocalKey => $relatedModelLocalKeyValue], $directInputs);

            // we store the item local key so we can match them with database and check if any item was deleted
            $relatedItemsSent[] = $item->{$relatedModelLocalKey};

            // create the item relations if any.
            $this->createRelationsForItem($item, $relationInputs);
        }

        // use the collection of sent ids to match against database ids, delete the ones not found in the submitted ids.
        if (! empty($relatedItemsSent)) {
            // we perform the cleanup of removed database items
            $entry->{$relationMethod}()->whereNotIn($relatedModelLocalKey, $relatedItemsSent)->lazy()->each->delete();
        }
    }
}
