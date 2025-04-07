<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait FieldsProtectedMethods
{
    /**
     * If field has entity we want to get the relation type from it.
     *
     * @param  array  $field
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
     * @param  array  $field
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
     * @param  array  $field
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
     * @param  array  $field
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
     * @param  array  $field
     * @return array
     */
    public function overwriteFieldNameFromDotNotationToArray($field)
    {
        if (strpos($field['name'], '.') !== false) {
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
     * Run the field name overwrite in multiple fields.
     *
     * @param  array  $fields
     * @return array
     */
    public function overwriteFieldNamesFromDotNotationToArray($fields)
    {
        foreach ($fields as $key => $field) {
            $fields[$key] = $this->overwriteFieldNameFromDotNotationToArray($field);
        }

        return $fields;
    }

    /**
     * If the field_definition_array array is a string, it means the programmer was lazy
     * and has only passed the name of the field. Turn that into a proper array.
     *
     * @param  string|array  $field  The field definition array (or string).
     * @return array
     */
    protected function makeSureFieldHasName($field)
    {
        if (empty($field)) {
            abort(500, 'Field name can\'t be empty', ['developer-error-exception']);
        }

        if (is_string($field)) {
            return ['name' => Str::replace(' ', '', $field)];
        }

        if (is_array($field) && ! isset($field['name'])) {
            abort(500, 'All fields must have their name defined', ['developer-error-exception']);
        }

        if (is_array($field['name'])) {
            abort(500, 'Field name can\'t be an array. It should be a string. Error in field: '.json_encode($field['name']), ['developer-error-exception']);
        }

        $field['name'] = Str::replace(' ', '', $field['name']);

        return $field;
    }

    /**
     * If entity is not present, but it looks like the field SHOULD be a relationship field,
     * try to determine the method on the model that defines the relationship, and pass it to
     * the field as 'entity'.
     *
     * @param  array  $field
     * @return array
     */
    protected function makeSureFieldHasEntity($field)
    {
        $model = isset($field['baseModel']) ? (new $field['baseModel']) : $this->getModel();

        if (isset($field['entity'])) {
            return $field;
        }

        // by default, entity is false if we cannot link it with guessing functions to a relation
        $field['entity'] = false;

        //if the name is dot notation we are sure it's a relationship
        if (strpos($field['name'], '.') !== false) {
            $possibleMethodName = Str::of($field['name'])->before('.')->value();
            // check model method for possibility of being a relationship
            $field['entity'] = $this->modelMethodIsRelationship($model, $possibleMethodName) ? $field['name'] : false;

            return $field;
        }

        // if there's a method on the model with this name
        if (method_exists($model, $field['name']) || $model->isRelation($field['name'])) {
            // check model method for possibility of being a relationship
            $field['entity'] = $this->modelMethodIsRelationship($model, $field['name']);

            return $field;
        }

        // if the name ends with _id and that method exists,
        // we can probably use it as an entity
        if (Str::endsWith($field['name'], '_id')) {
            $possibleMethodName = Str::replaceLast('_id', '', $field['name']);

            if (method_exists($model, $possibleMethodName)) {
                // check model method for possibility of being a relationship
                $field['entity'] = $this->modelMethodIsRelationship($model, $possibleMethodName);

                return $field;
            }
        }

        return $field;
    }

    protected function makeSureFieldHasAttribute($field)
    {
        if (isset($field['entity']) && $field['entity']) {
            // if the user setup the attribute in relation string, we are not going to infer that attribute from model
            // instead we get the defined attribute by the user.
            if ($this->isAttributeInRelationString($field)) {
                $field['attribute'] = $field['attribute'] ?? Str::afterLast($field['entity'], '.');

                return $field;
            }
        }
        // if there's a model defined, but no attribute
        // guess an attribute using the identifiableAttribute functionality in CrudTrait
        if (isset($field['model']) && ! isset($field['attribute']) && method_exists($field['model'], 'identifiableAttribute')) {
            $field['attribute'] = (new $field['model']())->identifiableAttribute();
        }

        return $field;
    }

    /**
     * Set the label of a field, if it's missing, by capitalizing the name and replacing
     * underscores with spaces.
     *
     * @param  array  $field  Field definition array.
     * @return array Field definition array that contains label too.
     */
    protected function makeSureFieldHasLabel($field)
    {
        if (! isset($field['label'])) {
            $name = str_replace(',', ' ', $field['name']);
            $name = str_replace('_id', '', $name);
            $field['label'] = mb_ucfirst(str_replace('_', ' ', $name));
        }

        return $field;
    }

    /**
     * Set the type of a field, if it's missing, by inferring it from the
     * db column type.
     *
     * @param  array  $field  Field definition array.
     * @return array Field definition array that contains type too.
     */
    protected function makeSureFieldHasType($field)
    {
        if (! isset($field['type'])) {
            $field['type'] = isset($field['relation_type']) ? $this->inferFieldTypeFromRelationType($field['relation_type']) : $this->inferFieldTypeFromDbColumnType($field['name']);
        }

        return $field;
    }

    protected function inferFieldTypeFromRelationType($relationType)
    {
        if (backpack_pro()) {
            return 'relationship';
        }

        switch ($relationType) {
            case 'BelongsTo':
                return 'select';
            case 'BelongsToMany':
            case 'MorphToMany':
                return 'select_multiple';
            default:
                return 'text';
        }
    }

    /**
     * If a field has subfields, go through each subfield and guess
     * its attribute, filling in whatever is missing.
     *
     * @param  array  $field  Field definition array.
     * @return array The improved definition of that field (a better 'subfields' array)
     */
    protected function makeSureSubfieldsHaveNecessaryAttributes($field)
    {
        if (! isset($field['subfields']) || ! is_array($field['subfields'])) {
            return $field;
        }

        if (! is_multidimensional_array($field['subfields'], true)) {
            abort(500, 'Subfields of «'.$field['name'].'» are malformed. Make sure you provide an array of subfields.', ['developer-error-exception']);
        }

        foreach ($field['subfields'] as $key => $subfield) {
            if (empty($subfield) || ! isset($subfield['name'])) {
                abort(500, 'A subfield of «'.$field['name'].'» is malformed. Subfield attribute name can\'t be empty.', ['developer-error-exception']);
            }

            // make sure the field definition is an array
            if (is_string($subfield)) {
                $subfield = ['name' => $subfield];
            }

            $subfield['name'] = Str::replace(' ', '', $subfield['name']);

            $subfield['parentFieldName'] = $field['name'];
            $subfield['baseFieldName'] = is_array($subfield['name']) ? implode(',', $subfield['name']) : $subfield['name'];
            $subfield['baseFieldName'] = Str::afterLast($subfield['baseFieldName'], '.');

            if (! isset($field['model'])) {
                // we're inside a simple 'repeatable' with no model/relationship, so
                // we assume all subfields are supposed to be text fields
                $subfield['type'] = $subfield['type'] ?? 'text';
                $subfield['entity'] = $subfield['entity'] ?? false;
            } else {
                // we should use 'model' as the `baseModel` for all subfields, so that when
                // we look if `category()` relationship exists on the model, we look on
                // the model this repeatable represents, not the main CRUD model
                $currentEntity = $subfield['baseEntity'] ?? $field['entity'];
                $subfield['baseModel'] = $subfield['baseModel'] ?? $field['model'];
                $subfield['baseEntity'] = isset($field['baseEntity']) ? $field['baseEntity'].'.'.$currentEntity : $currentEntity;
            }

            $field['subfields'][$key] = $this->makeSureFieldHasNecessaryAttributes($subfield);
        }

        // when field has any of `many` relations we need to append either the pivot selector for the `ToMany` or the
        // local key for the `many` relations. Other relations don't need any special treatment when used as subfields.
        if (isset($field['relation_type'])) {
            switch ($field['relation_type']) {
                case 'MorphToMany':
                case 'BelongsToMany':
                    $pivotSelectorField = static::getPivotFieldStructure($field);

                    $pivot = Arr::where($field['subfields'], function ($item) use ($pivotSelectorField) {
                        return $item['name'] === $pivotSelectorField['name'];
                    });

                    if (! empty($pivot)) {
                        break;
                    }

                    if ($field['allow_duplicate_pivots'] ?? false) {
                        $pivotSelectorField['allow_duplicate_pivots'] = true;
                        $field['subfields'] = Arr::prepend($field['subfields'], [
                            'name' => $field['pivot_key_name'] ?? 'id',
                            'type' => 'hidden',
                            'wrapper' => [
                                'class' => 'd-none',
                            ],
                        ]);
                    }

                    $this->setupFieldValidation($pivotSelectorField, $field['name']);
                    $field['subfields'] = Arr::prepend($field['subfields'], $pivotSelectorField);

                    break;
                case 'MorphMany':
                case 'HasMany':
                    $entity = isset($field['baseEntity']) ? $field['baseEntity'].'.'.$field['entity'] : $field['entity'];
                    $relationInstance = $this->getRelationInstance(['entity' => $entity]);

                    $localKeyField = Arr::where($field['subfields'], function ($item) use ($relationInstance) {
                        return $item['name'] === $relationInstance->getRelated()->getKeyName();
                    });

                    if (! empty($localKeyField)) {
                        break;
                    }

                    $field['subfields'] = Arr::prepend($field['subfields'], [
                        'name' => $relationInstance->getRelated()->getKeyName(),
                        'type' => 'hidden',
                    ]);
                    break;
            }
        }

        return $field;
    }

    /**
     * Enable the tabs functionality, if a field has a tab defined.
     *
     * @param  array  $field  Field definition array.
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
     * @param  array  $field  Field definition array.
     */
    protected function addFieldToOperationSettings($field)
    {
        $allFields = $this->getOperationSetting('fields');
        $allFields = array_merge($this->getCleanStateFields(), [$field['name'] => $field]);

        $this->setOperationSetting('fields', $allFields);
    }

    /**
     * Get the string that should be used as an array key, for the attributive array
     * where the fields are stored for the current operation.
     *
     * @deprecated v6
     */
    protected function getFieldKey(array $field): string
    {
        return $field['name'];
    }
}
