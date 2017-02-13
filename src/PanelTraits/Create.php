<?php

namespace Backpack\CRUD\PanelTraits;

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
     * @param  [Request] All input values to be inserted.
     *
     * @return [Eloquent Collection]
     */
    public function create($data)
    {
        $data = $this->decodeJsonCastedAttributes($data, 'create');
        $data = $this->compactFakeFields($data, 'create');

        $item = $this->model->create($data);

        // if there are any relationships available, also sync those
        $this->syncPivot($item, $data);

        return $item;
    }

    /**
     * Get all fields needed for the ADD NEW ENTRY form.
     *
     * @return [array] The fields with attributes and fake attributes.
     */
    public function getCreateFields()
    {
        return $this->create_fields;
    }

    /**
     * Get all fields with relation set (model key set on field).
     *
     * @param [string: create/update/both]
     *
     * @return [array] The fields with model key set.
     */
    public function getRelationFields($form = 'create')
    {
        if ($form == 'create') {
            $fields = $this->create_fields;
        } else {
            $fields = $this->update_fields;
        }

        $relationFields = [];

        foreach ($fields as $field) {
            if (isset($field['model'])) {
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

    public function syncPivot($model, $data, $form = 'create')
    {
        $fields_with_relationships = $this->getRelationFields($form);

        foreach ($fields_with_relationships as $key => $field) {
            if (isset($field['pivot']) && $field['pivot']) {
                $values = isset($data[$field['name']]) ? $data[$field['name']] : [];
                $model->{$field['name']}()->sync($values);

                if (isset($field['pivotFields'])) {
                    $pivotFields = array_keys($field['pivotFields']);

                    foreach ($pivotFields as $pivotField) {
                        foreach ($data[$pivotField] as $pivot_id => $pivotValue) {
                            $model->{$field['name']}()->updateExistingPivot($pivot_id, [$pivotField => $pivotValue]);
                        }
                    }
                }
            }

            if (isset($field['morph']) && $field['morph']) {
                $values = isset($data[$field['name']]) ? $data[$field['name']] : [];
                if ($model->{$field['name']}) {
                    $model->{$field['name']}()->update($values);
                } else {
                    $model->{$field['name']}()->create($values);
                }
            }
        }
    }
}
