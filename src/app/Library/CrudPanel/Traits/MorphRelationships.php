<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait MorphRelationships
{
    /**
     * MorphTo inputs (morphable_type and morphable_id) are used as subfields to represent the relation.
     * Here we add them to the direct input as we don't need to process any further this relationship.
     *
     * @param  array  $input
     * @return array
     */
    private function includeMorphToInputsFromRelationship($input)
    {
        $fields = $this->getFieldsWithRelationType('MorphTo');

        foreach ($fields as $field) {
            [$morphTypeField, $morphIdField] = $field['subfields'];
            Arr::set($input, $morphTypeField['name'], Arr::get($input, $field['name'].'.'.$morphTypeField['name']));
            Arr::set($input, $morphIdField['name'], Arr::get($input, $field['name'].'.'.$morphIdField['name']));
        }

        return $input;
    }

    /**
     * This function created the MorphTo relation fields in the CrudPanel.
     *
     * @param  array  $field
     * @return void
     */
    private function createMorphToRelationFields(array $field, $morphTypeFieldName, $morphIdFieldName)
    {
        $morphTypeField = static::getMorphTypeFieldStructure($field['name'], $morphTypeFieldName);
        $morphIdField = static::getMorphIdFieldStructure($field['name'], $morphIdFieldName, $morphTypeFieldName);
        $morphIdField['morphMap'] = $morphTypeField['morphMap'] = (new $this->model)->{$field['name']}()->morphMap();
        $field['subfields'] = [$morphTypeField, $morphIdField];

        return $field;
    }

    /**
     * Return the relation field names for a morphTo field.
     *
     * @param  string  $relationName  the morphto relation name
     * @return array
     */
    private function getMorphToFieldNames(string $relationName)
    {
        $relation = (new $this->model)->{$relationName}();

        return [$relation->getMorphType(), $relation->getForeignKeyName()];
    }

    /**
     * Make sure morph fields have the correct structure.
     *
     * @param  array  $field
     * @return array
     */
    private function makeSureMorphSubfieldsAreDefined(array $field)
    {
        if (isset($field['relation_type']) && $field['relation_type'] === 'MorphTo') {
            [$morphTypeFieldName, $morphIdFieldName] = $this->getMorphToFieldNames($field['name']);
            if (! $this->hasFieldWhere('name', $morphTypeFieldName) || ! $this->hasFieldWhere('name', $morphIdFieldName)) {
                // create the morph fields in the crud panel
                $field = $this->createMorphToRelationFields($field, $morphTypeFieldName, $morphIdFieldName);
                foreach ($field['morphOptions'] ?? [] as $morphOption) {
                    [$key, $label, $options] = $this->getMorphOptionStructured($morphOption);
                    $field = $this->addMorphOption($field, $key, $label, $options);
                }
            }
        }

        return $field;
    }

    /**
     * This function is responsible for setting up the morph fields structure.
     *
     * @param  string|array  $fieldOrName  - The field array or the field name
     * @param  string  $key  - the morph option key, usually a \Model\Class or a string for the morphMap
     * @param  ?string  $label  - the displayed text for this option
     * @param  array  $options  - options for the corresponding morphable_id field (usually ajax options)
     * @return void|array
     */
    public function addMorphOption($fieldOrName, string $key, $label = null, array $options = [])
    {
        $morphField = is_array($fieldOrName) ? $fieldOrName : $this->fields()[$fieldOrName];

        $fieldName = $morphField['name'];

        [$morphTypeFieldName, $morphIdFieldName] = $this->getMorphToFieldNames($fieldName);

        // check if the morph field where we are about to add the options have the proper fields setup
        if (! in_array($morphTypeFieldName, array_column($morphField['subfields'], 'name')) ||
            ! in_array($morphIdFieldName, array_column($morphField['subfields'], 'name'))) {
            throw new \Exception('Trying to add morphOptions to a non morph field. Check if field and relation name matches.');
        }
        // split the subfields into morphable_type and morphable_id fields.
        [$morphTypeField, $morphIdField] = $morphField['subfields'];

        // get the morphable_type field with the options set.
        [$morphTypeField, $key] = $this->getMorphTypeFieldWithOptions($morphTypeField, $key, $label);

        // set the morphable_id field options with the same key as morphable_type field above.
        $morphIdField['morphOptions'][$key] = $options;

        // merge additional options with the fields with setup above.
        $morphTypeField = isset($morphField['morphTypeField']) ? array_merge($morphTypeField, $morphField['morphTypeField']) : $morphTypeField;
        $morphIdField = isset($morphField['morphIdField']) ? array_merge($morphIdField, $morphField['morphIdField']) : $morphIdField;

        // set the complete setup fields as the subfields
        $morphField['subfields'] = [$morphTypeField, $morphIdField];

        // modify the field in case it exists or return it when creating it.
        if ($this->fields()[$fieldName] ?? false) {
            $this->modifyField($fieldName, $morphField);
        } else {
            return $morphField;
        }
    }

    /**
     * Return the provided morphable_type field with the options inferred from key.
     *
     * @param  array  $morphTypeField
     * @param  string  $key
     * @param  ?string  $label
     * @return array
     */
    private function getMorphTypeFieldWithOptions(array $morphTypeField, string $key, $label)
    {
        $morphMap = $morphTypeField['morphMap'];

        // in case developer provided a \Model\Class as the key
        if (is_a($key, 'Illuminate\Database\Eloquent\Model', true)) {
            // check if that key exists in the Laravel MorphMap and get it.
            if (in_array($key, $morphMap)) {
                $key = $morphMap[array_search($key, $morphMap)];
            }

            if (array_key_exists($key, $morphTypeField['options'] ?? [])) {
                throw new \Exception('Duplicate entry for «'.$key.'» key. That model is already part of another morphOption. Current options: '.json_encode($morphTypeField['options']));
            }

            // use the provided label or the Model name to display this option.
            $morphTypeField['options'][$key] = $label ?? Str::afterLast($key, '\\');
        } else {
            // in case it's not a model and is a string representing the model in the morphMap
            // check if that string exists in the morphMap, otherwise abort.
            if (! array_key_exists($key, $morphMap)) {
                throw new \Exception('Unknown morph type «'.$key.'», that name was not found in the morphMap.');
            }
            // check if the key already exists
            if (array_key_exists($key, $morphTypeField['options'] ?? [])) {
                throw new \Exception('Duplicate entry for «'.$key.'» key, That string is already part of another morphOption. Current options: '.json_encode($morphTypeField['options']));
            }
            // use the provided label or capitalize the provided key.
            $morphTypeField['options'][$key] = $label ?? ucfirst($key);
        }

        return [$morphTypeField, $key];
    }

    /**
     * Returns the morphable_id field structure for morphTo relations.
     *
     * @param  string  $relationName
     * @param  string  $morphIdFieldName
     * @return array
     */
    private static function getMorphidFieldStructure($relationName, $morphIdFieldName, $morphTypeFieldName)
    {
        return [
            'name' => $morphIdFieldName,
            'type' => 'relationship.morphTo_select',
            'entity' => false,
            'placeholder' => 'Select an entry',
            'allows_null' => true,
            'allow_multiple' => false,
            'morphTypeFieldName' => $morphTypeFieldName,
            'attributes' => [
                'data-morph-select' => $relationName.'-morph-select',
            ],
            'wrapper' => ['class' => 'form-group col-md-9'],
        ];
    }

    /**
     * Returns the morphable_type field structure for morphTo relations.
     *
     * @param  string  $relationName
     * @param  string  $morphTypeFieldName
     * @return array
     */
    private static function getMorphTypeFieldStructure($relationName, $morphTypeFieldName)
    {
        return [
            'name' => $morphTypeFieldName,
            'type' => 'relationship.morphTo_type_select',
            'placeholder' => 'Select an entry',
            'attributes' => [
                $relationName.'-morph-select' => true,
            ],
            'wrapper' => ['class' => 'form-group col-md-3'],
        ];
    }

    /**
     * return the array with defaults for a morphOption structure.
     *
     * @param  array  $morphOption
     * @return array
     */
    private function getMorphOptionStructured(array $morphOption)
    {
        return [$morphOption[0] ?? null, $morphOption[1] ?? null, $morphOption[2] ?? []];
    }
}
