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
                // if pivot is true and there is `fields` array in this field we are trying to sync a pivot with
                // extra attributes on it. It's a Repeatable Field so its values are sent as json.
                if (isset($field['pivot']) && $field['pivot'] && isset($field['fields']) && is_array($field['fields'])) {
                    //we remove the first field from repeatable because it is our relation.
                    $pivot_fields = Arr::where($field['fields'], function ($item) use ($field) {
                        return $field['name'] != $item['name'];
                    });

                    //we grab the related models
                    $related_models = $relatedModel->{$relationMethod};
                    $return = [];

                    //for any given model, we grab the attributes that belong to our pivot table.
                    foreach ($related_models as $related_model) {
                        $item[$field['name']] = $related_model->getKey();
                        //for any given related model, we attach the pivot fields.
                        foreach ($pivot_fields as $pivot_field) {
                            $item[$pivot_field['name']] = $related_model->pivot->{$pivot_field['name']};
                        }
                        $return[] = $item;
                    }
                    //we return the json encoded result as expected by repeatable field.
                    return json_encode($return);
                }

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
