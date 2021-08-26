<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Arr;

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
     * @param array $data All input values to be inserted.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($data)
    {
        $data = $this->decodeJsonCastedAttributes($data);
        $data = $this->compactFakeFields($data);
        $data = $this->changeBelongsToNamesFromRelationshipToForeignKey($data);

        // omit the n-n relationships when updating the eloquent item
        $nn_relationships = Arr::pluck($this->getRelationFieldsWithPivot(), 'name');

        $item = $this->model->create(Arr::except($data, $nn_relationships));

        // if there are any other relations create them.
        $this->createRelations($item, $data);

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
     * @return array The fields with model key set.
     */
    public function getRelationFields()
    {
        $fields = $this->fields();
        $relationFields = [];

        foreach ($fields as $field) {
            if (isset($field['model']) && $field['model'] !== false) {
                array_push($relationFields, $field);
            }

            if (isset($field['subfields']) &&
                is_array($field['subfields']) &&
                count($field['subfields'])) {
                foreach ($field['subfields'] as $subfield) {
                    array_push($relationFields, $subfield);
                }
            }
        }

        return $relationFields;
    }

    /**
     * Get all fields with n-n relation set (pivot table is true).
     *
     * @return array The fields with n-n relationships.
     */
    public function getRelationFieldsWithPivot()
    {
        $all_relation_fields = $this->getRelationFields();

        return Arr::where($all_relation_fields, function ($value, $key) {
            return isset($value['pivot']) && $value['pivot'];
        });
    }

    /**
     * Create the relations for the current model.
     *
     * @param \Illuminate\Database\Eloquent\Model $item The current CRUD model.
     * @param array                               $data The form data.
     */
    public function createRelations($item, $data)
    {
        $relationData = $this->getRelationDataFromFormData($data);

        // handles 1-1 and 1-n relations (HasOne, MorphOne, HasMany, MorphMany)
        $this->createRelationsForItem($item, $relationData);

        // this specifically handles M-M relations that could sync additional information into pivot table
        $this->syncPivot($item, $data);
    }

    /**
     * Sync the declared many-to-many associations through the pivot field.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The current CRUD model.
     * @param array                               $data  The form data.
     */
    public function syncPivot($model, $data)
    {
        $fields_with_relationships = $this->getRelationFieldsWithPivot();
        foreach ($fields_with_relationships as $field) {
            $values = isset($data[$field['name']]) ? $data[$field['name']] : [];

            // if a JSON was passed instead of an array, turn it into an array
            if (is_string($values)) {
                $decoded_values = json_decode($values, true);
                $values = [];
                // array is not multidimensional
                if (count($decoded_values) != count($decoded_values, COUNT_RECURSIVE)) {
                    foreach ($decoded_values as $value) {
                        $values[] = $value[$field['name']];
                    }
                } else {
                    $values = $decoded_values;
                }
            }

            $relation_data = [];

            foreach ($values as $pivot_id) {
                if ($pivot_id != '') {
                    $pivot_data = [];

                    if (isset($field['pivotFields'])) {
                        // array is not multidimensional
                        if (count($field['pivotFields']) == count($field['pivotFields'], COUNT_RECURSIVE)) {
                            foreach ($field['pivotFields'] as $pivot_field_name) {
                                $pivot_data[$pivot_field_name] = $data[$pivot_field_name][$pivot_id];
                            }
                        } else {
                            $field_data = json_decode($data[$field['name']], true);

                            // we grab from the parsed data the specific values for this pivot
                            $pivot_data = Arr::first($field_data, function ($item) use ($pivot_id, $field) {
                                return $item[$field['name']] === $pivot_id;
                            });

                            // we remove the relation field from extra pivot data as we already have the relation.
                            unset($pivot_data[$field['name']]);
                        }
                    }

                    $relation_data[$pivot_id] = $pivot_data;
                }
            }

            $model->{$field['name']}()->sync($relation_data);

            if (isset($field['morph']) && $field['morph'] && isset($data[$field['name']])) {
                $values = $data[$field['name']];
                $model->{$field['name']}()->sync($values);
            }
        }
    }

    /**
     * Handles 1-1 and 1-n relations. In case 1-1 it handles subsequent relations in connected models
     * For example, a Monster > HasOne Address > BelongsTo a Country.
     *
     * @param \Illuminate\Database\Eloquent\Model $item          The current CRUD model.
     * @param array                               $formattedData The form data.
     *
     * @return bool|null
     */
    private function createRelationsForItem($item, $formattedData)
    {
        if (! isset($formattedData['relations'])) {
            return false;
        }

        foreach ($formattedData['relations'] as $relationMethod => $relationData) {
            if (! isset($relationData['model'])) {
                continue;
            }

            $relation = $item->{$relationMethod}();
            $relation_type = get_class($relation);

            switch ($relation_type) {
                case HasOne::class:
                case MorphOne::class:

                    // we first check if there are relations of the relation
                    if (isset($relationData['relations'])) {
                        // if there are nested relations, we first add the BelongsTo like in main entry
                        $belongsToRelations = Arr::where($relationData['relations'], function ($relation_data) {
                            return $relation_data['relation_type'] == 'BelongsTo';
                        });

                        // adds the values of the BelongsTo relations of this entity to the array of values that will
                        // be saved at the same time like we do in parent entity belongs to relations
                        $valuesWithRelations = $this->associateHasOneBelongsTo($belongsToRelations, $relationData['values'], $relation->getModel());

                        // remove previously added BelongsTo relations from relation data.
                        $relationData['relations'] = Arr::where($relationData['relations'], function ($item) {
                            return $item['relation_type'] != 'BelongsTo';
                        });

                        $modelInstance = $relation->updateOrCreate([], $valuesWithRelations);
                    } else {
                        $modelInstance = $relation->updateOrCreate([], $relationData['values']);
                    }
                    break;

                case HasMany::class:
                case MorphMany::class:
                    $relation_values = $relationData['values'][$relationMethod];

                    if (is_string($relation_values)) {
                        $relation_values = json_decode($relationData['values'][$relationMethod], true);
                    }

                    if ($relation_values === null || count($relation_values) == count($relation_values, COUNT_RECURSIVE)) {
                        $this->attachManyRelation($item, $relation, $relationMethod, $relationData, $relation_values);
                    } else {
                        $this->createManyEntries($item, $relation, $relationMethod, $relationData);
                    }
                    break;
            }

            if (isset($relationData['relations'])) {
                $this->createRelationsForItem($modelInstance, ['relations' => $relationData['relations']]);
            }
        }
    }

    /**
     * Associate the nested HasOne -> BelongsTo relations by adding the "connecting key"
     * to the array of values that is going to be saved with HasOne relation.
     *
     * @param array $belongsToRelations
     * @param array $modelValues
     * @param Model $relationInstance
     * @return array
     */
    private function associateHasOneBelongsTo($belongsToRelations, $modelValues, $modelInstance)
    {
        foreach ($belongsToRelations as $methodName => $values) {
            $relation = $modelInstance->{$methodName}();

            $modelValues[$relation->getForeignKeyName()] = $values['values'][$methodName];
        }

        return $modelValues;
    }

    /**
     * Get a relation data array from the form data.
     * For each relation defined in the fields through the entity attribute, set the model, the parent model and the
     * attribute values.
     *
     * We traverse this relation array later to create the relations, for example:
     *
     * Current model HasOne Address, this Address (line_1, country_id) BelongsTo Country through country_id in Address Model.
     *
     * So when editing current model crud user have two fields address.line_1 and address.country (we infer country_id from relation)
     *
     * Those will be nested accordingly in this relation array, so address relation will have a nested relation with country.
     *
     *
     * @param array $data The form data.
     *
     * @return array The formatted relation data.
     */
    private function getRelationDataFromFormData($data)
    {
        // exclude the already attached belongs to relations but include nested belongs to.
        $relation_fields = Arr::where($this->getRelationFields(), function ($field, $key) {
            return $field['relation_type'] !== 'BelongsTo' || $this->isNestedRelation($field);
        });

        $relationData = [];

        foreach ($relation_fields as $relation_field) {
            $attributeKey = $this->parseRelationFieldNamesFromHtml([$relation_field])[0]['name'];

            if ((! is_null(Arr::get($data, $attributeKey)) || $this->isNestedRelation($relation_field)) && isset($relation_field['pivot']) && $relation_field['pivot'] !== true) {
                $key = implode('.relations.', explode('.', $this->getOnlyRelationEntity($relation_field)));
                $fieldData = Arr::get($relationData, 'relations.'.$key, []);
                if (! array_key_exists('model', $fieldData)) {
                    $fieldData['model'] = $relation_field['model'];
                }
                if (! array_key_exists('parent', $fieldData)) {
                    $fieldData['parent'] = $this->getRelationModel($attributeKey, -1);
                }

                // when using HasMany/MorphMany if fallback_id is provided instead of deleting the models
                // from database we resort to this fallback provided by developer
                if (array_key_exists('fallback_id', $relation_field)) {
                    $fieldData['fallback_id'] = $relation_field['fallback_id'];
                }

                // when using HasMany/MorphMany and column is nullable, by default backpack sets the value to null.
                // this allow developers to override that behavior and force deletion from database
                $fieldData['force_delete'] = $relation_field['force_delete'] ?? false;

                if (! array_key_exists('relation_type', $fieldData)) {
                    $fieldData['relation_type'] = $relation_field['relation_type'];
                }
                $relatedAttribute = Arr::last(explode('.', $attributeKey));
                $fieldData['values'][$relatedAttribute] = Arr::get($data, $attributeKey);

                Arr::set($relationData, 'relations.'.$key, $fieldData);
            }
        }

        return $relationData;
    }

    /**
     * When using the HasMany/MorphMany relations as selectable elements we use this function to sync those relations.
     * Here we allow for different functionality than when creating. Developer could use this relation as a
     * selectable list of items that can belong to one/none entity at any given time.
     *
     * @return void
     */
    public function attachManyRelation($item, $relation, $relationMethod, $relationData, $relation_values)
    {
        $model_instance = $relation->getRelated();
        $force_delete = $relationData['force_delete'];
        $relation_foreign_key = $relation->getForeignKeyName();
        $relation_local_key = $relation->getLocalKeyName();

        $relation_column_is_nullable = $model_instance->isColumnNullable($relation_foreign_key);

        if ($relation_values !== null && $relationData['values'][$relationMethod][0] !== null) {
            // we add the new values into the relation
            $model_instance->whereIn($model_instance->getKeyName(), $relation_values)
                ->update([$relation_foreign_key => $item->{$relation_local_key}]);

            // we clear up any values that were removed from model relation.
            // if developer provided a fallback id, we use it
            // if column is nullable we set it to null if developer didn't specify `force_delete => true`
            // if none of the above we delete the model from database
            if (isset($relationData['fallback_id'])) {
                $model_instance->whereNotIn($model_instance->getKeyName(), $relation_values)
                    ->where($relation_foreign_key, $item->{$relation_local_key})
                    ->update([$relation_foreign_key => $relationData['fallback_id']]);
            } else {
                if (! $relation_column_is_nullable || $force_delete) {
                    $model_instance->whereNotIn($model_instance->getKeyName(), $relation_values)
                        ->where($relation_foreign_key, $item->{$relation_local_key})
                        ->delete();
                } else {
                    $model_instance->whereNotIn($model_instance->getKeyName(), $relation_values)
                        ->where($relation_foreign_key, $item->{$relation_local_key})
                        ->update([$relation_foreign_key => null]);
                }
            }
        } else {
            // the developer cleared the selection
            // we gonna clear all related values by setting up the value to the fallback id, to null or delete.
            if (isset($relationData['fallback_id'])) {
                $model_instance->where($relation_foreign_key, $item->{$relation_local_key})
                    ->update([$relation_foreign_key => $relationData['fallback_id']]);
            } else {
                if (! $relation_column_is_nullable || $force_delete) {
                    $model_instance->where($relation_foreign_key, $item->{$relation_local_key})->delete();
                } else {
                    $model_instance->where($relation_foreign_key, $item->{$relation_local_key})
                        ->update([$relation_foreign_key => null]);
                }
            }
        }
    }

    /**
     * Handle HasMany/MorphMany relations when used as creatable entries in the crud.
     * By using repeatable field, developer can allow the creation of such entries
     * in the crud forms.
     *
     * @return void
     */
    public function createManyEntries($entry, $relation, $relationMethod, $relationData)
    {
        $items = json_decode($relationData['values'][$relationMethod], true);

        $relation_local_key = $relation->getLocalKeyName();

        // if the collection is empty we clear all previous values in database if any.
        if (empty($items)) {
            $entry->{$relationMethod}()->sync([]);
        } else {
            $created_ids = [];

            foreach ($items as $item) {
                if (isset($item[$relation_local_key]) && ! empty($item[$relation_local_key])) {
                    $entry->{$relationMethod}()->updateOrCreate([$relation_local_key => $item[$relation_local_key]], $item);
                } else {
                    $created_ids[] = $entry->{$relationMethod}()->create($item)->{$relation_local_key};
                }
            }

            // get from $items the sent ids, and merge the ones created.
            $relatedItemsSent = array_merge(array_filter(Arr::pluck($items, $relation_local_key)), $created_ids);

            if (! empty($relatedItemsSent)) {
                // we perform the cleanup of removed database items
                $entry->{$relationMethod}()->whereNotIn($relation_local_key, $relatedItemsSent)->delete();
            }
        }
    }
}
