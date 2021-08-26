<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

        // if there are any relationships available, also sync those
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
        $this->syncPivot($item, $data);
        $this->createOneToOneRelations($item, $data);
    }

    /**
     * Sync the declared many-to-many associations through the pivot field.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The current CRUD model.
     * @param array                               $data  The form data.
     */
    public function syncPivot($model, $data)
    {
        $fields_with_relationships = $this->getRelationFields();
        foreach ($fields_with_relationships as $key => $field) {
            if (isset($field['pivot']) && $field['pivot']) {
                $values = isset($data[$field['name']]) ? $data[$field['name']] : [];

                // if a JSON was passed instead of an array, turn it into an array
                if (is_string($values)) {
                    $values = json_decode($values);
                }

                $relation_data = [];
                foreach ($values as $pivot_id) {
                    $pivot_data = [];

                    if (isset($field['pivotFields'])) {
                        foreach ($field['pivotFields'] as $pivot_field_name) {
                            $pivot_data[$pivot_field_name] = $data[$pivot_field_name][$pivot_id];
                        }
                    }
                    $relation_data[$pivot_id] = $pivot_data;
                }

                $model->{$field['name']}()->sync($relation_data);
            }

            if (isset($field['morph']) && $field['morph'] && isset($data[$field['name']])) {
                $values = $data[$field['name']];
                $model->{$field['name']}()->sync($values);
            }
        }
    }

    /**
     * Create any existing one to one relations for the current model from the form data.
     *
     * @param \Illuminate\Database\Eloquent\Model $item The current CRUD model.
     * @param array                               $data The form data.
     */
    private function createOneToOneRelations($item, $data)
    {
        $relationData = $this->getRelationDataFromFormData($data);
        $this->createRelationsForItem($item, $relationData);
    }

    /**
     * Create any existing one to one relations for the current model from the relation data.
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
            $model = $relationData['model'];
            $relation = $item->{$relationMethod}();

            if ($relation instanceof BelongsTo) {
                $modelInstance = $model::find($relationData['values'])->first();
                if ($modelInstance != null) {
                    $relation->associate($modelInstance)->save();
                } else {
                    $relation->dissociate()->save();
                }
            } elseif ($relation instanceof HasOne) {
                if ($item->{$relationMethod} != null) {
                    $item->{$relationMethod}->update($relationData['values']);
                    $modelInstance = $item->{$relationMethod};
                } else {
                    $modelInstance = new $model($relationData['values']);
                    $relation->save($modelInstance);
                }
            } elseif ($relation instanceof HasMany) {
                $relation_values = $relationData['values'][$relationMethod];
                $this->attachManyRelation($item, $relation, $relationMethod, $relationData, $relation_values);
            }

            if (isset($relationData['relations'])) {
                $this->createRelationsForItem($modelInstance, ['relations' => $relationData['relations']]);
            }
        }
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
        $force_delete = $relationData['force_delete'] ?? false;
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
        $relation_fields = $this->getRelationFields();

        $relation_fields = $this->parseRelationFieldNamesFromHtml($relation_fields);

        //remove fields that are not in the submitted form.
        $relation_fields = array_filter($relation_fields, function ($item) use ($data) {
            return Arr::has($data, $item['name']);
        });

        $relationData = [];
        foreach ($relation_fields as $relation_field) {
            $attributeKey = $relation_field['name'];

            if (! is_null(Arr::get($data, $attributeKey)) && isset($relation_field['pivot']) && $relation_field['pivot'] !== true) {
                $key = implode('.relations.', explode('.', $this->getOnlyRelationEntity($relation_field)));
                $fieldData = Arr::get($relationData, 'relations.'.$key, []);
                if (! array_key_exists('model', $fieldData)) {
                    $fieldData['model'] = $relation_field['model'];
                }
                if (! array_key_exists('parent', $fieldData)) {
                    $fieldData['parent'] = $this->getRelationModel($attributeKey, -1);
                }
                $relatedAttribute = Arr::last(explode('.', $attributeKey));
                $fieldData['values'][$relatedAttribute] = Arr::get($data, $attributeKey);

                Arr::set($relationData, 'relations.'.$key, $fieldData);
            }
        }

        return $relationData;
    }
}
