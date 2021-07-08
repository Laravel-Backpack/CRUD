<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

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

        $this->createRelations($item, $data);

        // omit the n-n relationships when updating the eloquent item
        $nn_relationships = Arr::pluck($this->getRelationFieldsWithPivot(), 'name');
        $repeatable_relationships = Arr::pluck($this->getRelationFieldsWithRepeatable(), 'name');
        $excluded_relationships = array_merge($nn_relationships, $repeatable_relationships);

        $data = Arr::except($data, $excluded_relationships);

        $updated = $item->update($data);
        // if there are any relationships available, also sync those
        $this->createRelations($item, $data);

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
        if (isset($field['entity'])) {
            $relational_entity = $this->parseRelationFieldNamesFromHtml([$field])[0]['name'];

            $relation_array = explode('.', $relational_entity);

            $relatedModel = $relatedModel = array_reduce(array_splice($relation_array, 0, -1), function ($obj, $method) {
                return $obj->{$method} ? $obj->{$method} : $obj;
            }, $model);

            $relationMethod = Arr::last($relation_array);

            if (method_exists($relatedModel, $relationMethod) && $relatedModel->{$relationMethod}() instanceof HasOne) {
                return $relatedModel->{$relationMethod}->{Arr::last(explode('.', $relational_entity))};
            } else {
                return $relatedModel->{$relationMethod};
            }
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
}
