<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait FieldsProtectedMethods
{
    /**
     * If field has entity we want to get the relation type from it.
     *
     * @param array $field
     * @return array
     */
    public function makeSureFieldHasRelationType($field)
    {
        $field['relation_type'] = $field['relation_type'] ?? $this->inferRelationTypeFromRelationship($field);

        return $field;
    }

    /**
     * If field has entity we want to make sure it also has a model for that relation.
     *
     * @param array $field
     * @return array
     */
    public function makeSureFieldHasModel($field)
    {
        $field['model'] = $field['model'] ?? $this->inferFieldModelFromRelationship($field);

        return $field;
    }

    /**
     * Based on relation type we can guess if pivot is set.
     *
     * @param array $field
     * @return array
     */
    public function makeSureFieldHasPivot($field)
    {
        $field['pivot'] = $field['pivot'] ?? $this->guessIfFieldHasPivotFromRelationType($field['relation_type']);

        return $field;
    }

    /**
     * Based on relation type we can try to guess if it is a multiple field.
     *
     * @param array $field
     * @return array
     */
    public function makeSureFieldHasMultiple($field)
    {
        if (isset($field['relation_type'])) {
            $field['multiple'] = $field['multiple'] ?? $this->guessIfFieldHasMultipleFromRelationType($field['relation_type']);
        }

        return $field;
    }

    /**
     * In case field name is dot notation we want to convert it to a valid HTML array field name for validation purposes.
     *
     * @param array $field
     * @return array
     */
    public function overwriteFieldNameFromDotNotationToArray($field)
    {
        if (! is_array($field['name']) && strpos($field['name'], '.') !== false) {
            $entity_array = explode('.', $field['name']);
            $name_string = '';

            foreach ($entity_array as $key => $array_entity) {
                $name_string .= ($key == 0) ? $array_entity : '['.$array_entity.']';
            }

            $field['name'] = $name_string;
        }

        return $field;
    }

    /**
     * If the field_definition_array array is a string, it means the programmer was lazy
     * and has only passed the name of the field. Turn that into a proper array.
     *
     * @param  string|array $field The field definition array (or string).
     * @return array
     */
    protected function makeSureFieldHasName($field)
    {
        if (is_string($field)) {
            return ['name' => $field];
        }

        if (is_array($field) && ! isset($field['name'])) {
            abort(500, 'All fields must have their name defined');
        }

        return $field;
    }

    /**
     * If entity is not present, but it looks like the field SHOULD be a relationship field,
     * try to determine the method on the model that defines the relationship, and pass it to
     * the field as 'entity'.
     *
     * @param  array $field
     * @return array
     */
    protected function makeSureFieldHasEntity($field)
    {
        if (isset($field['entity'])) {
            return $field;
        }

        // if the name is an array it's definitely not a relationship
        if (is_array($field['name'])) {
            return $field;
        }

        // if the name is dot notation we are sure it's a relationship
        if (strpos($field['name'], '.') !== false) {
            $field['entity'] = $field['name'];

            return $field;
        }

        // if there's a method on the model with this name
        if (method_exists($this->model, $field['name'])) {
            $field['entity'] = $field['name'];

            return $field;
        }

        // if the name ends with _id and that method exists,
        // we can probably use it as an entity
        if (Str::endsWith($field['name'], '_id')) {
            $possibleMethodName = Str::replaceLast('_id', '', $field['name']);

            if (method_exists($this->model, $possibleMethodName)) {
                $field['entity'] = $possibleMethodName;

                return $field;
            }
        }

        return $field;
    }

    protected function overwriteFieldNameFromEntity($field)
    {
        // if the entity doesn't have a dot, it means we don't need to overwrite the name
        if (! Str::contains($field['entity'], '.')) {
            return $field;
        }

        // only 1-1 relationships are supported, if it's anything else, abort
        if ($field['relation_type'] != 'HasOne' || $field['relation_type'] != 'MorphOne') {
            return $field;
        }

        if (count(explode('.', $field['entity'])) == count(explode('.', $this->getOnlyRelationEntity($field)))) {
            $field['name'] = implode('.', array_slice(explode('.', $field['entity']), 0, -1));
            $relation = $this->getRelationInstance($field);
            if (! empty($field['name'])) {
                $field['name'] .= '.';
            }
            $field['name'] .= $relation->getForeignKeyName();
        }

        return $field;
    }

    protected function makeSureFieldHasAttribute($field)
    {
        // if there's a model defined, but no attribute
        // guess an attribute using the identifiableAttribute functionality in CrudTrait
        if (isset($field['model']) && ! isset($field['attribute']) && method_exists($field['model'], 'identifiableAttribute')) {
            $field['attribute'] = call_user_func([(new $field['model']), 'identifiableAttribute']);
        }

        return $field;
    }

    /**
     * Set the label of a field, if it's missing, by capitalizing the name and replacing
     * underscores with spaces.
     *
     * @param  array $field Field definition array.
     * @return array        Field definition array that contains label too.
     */
    protected function makeSureFieldHasLabel($field)
    {
        if (! isset($field['label'])) {
            $name = is_array($field['name']) ? $field['name'][0] : $field['name'];
            $name = str_replace('_id', '', $name);
            $field['label'] = mb_ucfirst(str_replace('_', ' ', $name));
        }

        return $field;
    }

    /**
     * Set the type of a field, if it's missing, by inferring it from the
     * db column type.
     *
     * @param  array $field Field definition array.
     * @return array        Field definition array that contains type too.
     */
    protected function makeSureFieldHasType($field)
    {
        if (! isset($field['type'])) {
            $field['type'] = isset($field['relation_type']) ? $this->inferFieldTypeFromFieldRelation($field) : $this->inferFieldTypeFromDbColumnType($field['name']);
        }

        return $field;
    }

    /**
     * Enable the tabs functionality, if a field has a tab defined.
     *
     * @param  array $field Field definition array.
     * @return void
     */
    protected function enableTabsIfFieldUsesThem($field)
    {
        // if a tab was mentioned, we should enable it
        if (isset($field['tab'])) {
            if (! $this->tabsEnabled()) {
                $this->enableTabs();
            }
        }
    }

    /**
     * Add a field to the current operation, using the Settings API.
     *
     * @param  array $field Field definition array.
     */
    protected function addFieldToOperationSettings($field)
    {
        $fieldKey = $this->getFieldKey($field);

        $allFields = $this->getOperationSetting('fields');
        $allFields = Arr::add($this->fields(), $fieldKey, $field);

        $this->setOperationSetting('fields', $allFields);
    }

    /**
     * Get the string that should be used as an array key, for the attributive array
     * where the fields are stored for the current operation.
     *
     * The array key for the field should be:
     * - name (if the name is a string)
     * - name1_name2_name3 (if the name is an array)
     *
     * @param  array $field Field definition array.
     * @return string       The string that should be used as array key.
     */
    protected function getFieldKey($field)
    {
        if (is_array($field['name'])) {
            return implode('_', $field['name']);
        }

        return $field['name'];
    }
}
