<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;

trait Update
{
    /*
    |--------------------------------------------------------------------------
    |                                   UPDATE
    |--------------------------------------------------------------------------
    */

    /**
     * Update a row in the database.
     *
     * @param int   $id   The entity's id
     * @param array $data All inputs to be updated.
     *
     * @return object
     */
    public function update($id, $data)
    {
        $data = $this->decodeJsonCastedAttributes($data);
        $data = $this->compactFakeFields($data);
        $item = $this->model->findOrFail($id);

        $data = $this->changeBelongsToNamesFromRelationshipToForeignKey($data);

        $this->createRelations($item, $data);

        // omit the n-n relationships when updating the eloquent item
        $nn_relationships = Arr::pluck($this->getRelationFieldsWithPivot(), 'name');

        $data = Arr::except($data, $nn_relationships);

        $updated = $item->update($data);

        return $item;
    }

    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param int $id The id of the entry that is being edited.
     *
     * @return array The fields with attributes, fake attributes and values.
     */
    public function getUpdateFields($id = false)
    {
        $fields = $this->fields();
        $entry = ($id != false) ? $this->getEntry($id) : $this->getCurrentEntry();

        foreach ($fields as &$field) {
            // set the value
            if (! isset($field['value'])) {
                if (isset($field['subfields'])) {
                    $field['value'] = [];
                    foreach ($field['subfields'] as $subfield) {
                        $field['value'][] = $entry->{$subfield['name']};
                    }
                } else {
                    $field['value'] = $this->getModelAttributeValue($entry, $field);
                }
            }
        }

        // always have a hidden input for the entry id
        if (! array_key_exists('id', $fields)) {
            $fields['id'] = [
                'name'  => $entry->getKeyName(),
                'value' => $entry->getKey(),
                'type'  => 'hidden',
            ];
        }

        return $fields;
    }

    /**
     * Get the value of the 'name' attribute from the declared relation model in the given field.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The current CRUD model.
     * @param array                               $field The CRUD field array.
     *
     * @return mixed The value of the 'name' attribute from the relation model.
     */
    private function getModelAttributeValue($model, $field)
    {
        if (isset($field['entity']) && $field['entity'] !== false) {
            $relational_entity = $this->parseRelationFieldNamesFromHtml([$field])[0]['name'];

            $relation_array = explode('.', $relational_entity);

            $relatedModel = array_reduce(array_splice($relation_array, 0, -1), function ($obj, $method) {
                return $obj->{$method} ? $obj->{$method} : $obj;
            }, $model);

            $relationMethod = Arr::last($relation_array);
            if (method_exists($relatedModel, $relationMethod)) {
                $relation = $relatedModel->{$relationMethod}();
                $relation_type = get_class($relation);

                switch ($relation_type) {
                    case HasOne::class:
                    case MorphOne::class:
                        return $relatedModel->{$relationMethod}->{Str::afterLast($relational_entity, '.')};
                        break;

                    case HasMany::class:
                    case MorphMany::class:
                    case BelongsToMany::class:
                    case MorphToMany::class:
                        $attribute_value = $this->getManyRelationAttributeValue($relatedModel, $relationMethod, $field, $relation_type);
                        // we only want to return the json_encoded values here
                        if (is_string($attribute_value)) {
                            return $attribute_value;
                        }
                        break;
                }
            }

            return $relatedModel->{$relationMethod};
        }

        if (is_string($field['name'])) {
            return $model->{$field['name']};
        }

        if (is_array($field['name'])) {
            $result = [];
            foreach ($field['name'] as $key => $value) {
                $result = $model->{$value};
            }

            return $result;
        }
    }

    /**
     * Returns the json encoded pivot fields from supported relations.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $relation_method
     * @param array $field
     * @param string $relation_type
     * @return bool|string
     */
    private function getManyRelationAttributeValue($model, $relation_method, $field, $relation_type)
    {
        if (! isset($field['pivotFields']) || ! is_array($field['pivotFields'])) {
            return false;
        }

        $pivot_fields = Arr::where($field['pivotFields'], function ($item) use ($field) {
            return $field['name'] != $item['name'];
        });
        $related_models = $model->{$relation_method};
        $result = [];

        // for any given model, we grab the attributes that belong to our pivot table.
        foreach ($related_models as $related_model) {
            $item = [];
            switch ($relation_type) {
                case HasMany::class:
                case MorphMany::class:
                    // for any given related model, we get the value from pivot fields
                    foreach ($pivot_fields as $pivot_field) {
                        $item[$pivot_field['name']] = $related_model->{$pivot_field['name']};
                    }
                    $item[$related_model->getKeyName()] = $related_model->getKey();
                    $result[] = $item;
                    break;

                case BelongsToMany::class:
                case MorphToMany::class:
                    // for any given related model, we get the pivot fields.
                    foreach ($pivot_fields as $pivot_field) {
                        $item[$pivot_field['name']] = $related_model->pivot->{$pivot_field['name']};
                    }
                    $item[$field['name']] = $related_model->getKey();
                    $result[] = $item;
                    break;
            }
        }

        // we return the json encoded result as expected by repeatable field.
        return json_encode($result);
    }
}
