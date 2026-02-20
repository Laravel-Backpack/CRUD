<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Input
{
    /**
     * ---------------
     * PRIVATE METHODS
     * ---------------.
     */

    /**
     * Returns the direct inputs parsed for model and relationship creation.
     *
     * @param  array  $inputs
     * @param  ?array  $relationDetails
     * @param  bool|string  $relationMethod
     * @return array
     */
    private function splitInputIntoDirectAndRelations($inputs, $relationDetails = null, $relationMethod = false)
    {
        $crudFields = $relationDetails['crudFields'] ?? [];
        $model = $relationDetails['model'] ?? false;

        $directInputs = $this->getDirectInputsFromInput($inputs, $model, $crudFields, $relationMethod);
        $relationInputs = $this->getRelationDetailsFromInput($inputs, $crudFields, $relationMethod);

        return [$directInputs, $relationInputs];
    }

    /**
     * Returns the attributes with relationships stripped out from the input.
     * BelongsTo relations are ensured to have the correct foreign key set.
     * ALL other relations are stripped from the input.
     *
     * @param  array  $input  - the input array
     * @param  mixed  $model  - the model of what we want to get the attributtes for
     * @param  array  $fields  - the fields used in this relation
     * @param  mixed  $relationMethod  - the relation method
     * @return array
     */
    private function getDirectInputsFromInput($input, $model = false, $fields = [], $relationMethod = false)
    {
        $model = $model ? (is_string($model) ? app($model) : $model) : $this->model;

        $input = $this->decodeJsonCastedAttributes($input, $model);
        $input = $this->compactFakeFields($input, $model, $fields);
        if (! $relationMethod) {
            $input = $this->includeMorphToInputsFromRelationship($input);
        }
        $input = $this->excludeRelationFieldsExceptBelongsTo($input, $fields, $relationMethod);
        $input = $this->changeBelongsToNamesFromRelationshipToForeignKey($input, $fields);

        return $input;
    }

    /**
     * Get a relation data array from the form data. For each relation defined in the fields
     * through the entity attribute, and set some relation details.
     *
     * We traverse this relation array later to create the relations, for example:
     * - Current model HasOne Address
     * - Address (line_1, country_id) BelongsTo Country through country_id in Address Model
     *
     * So when editing current model crud user have two fields
     * - address.line_1
     * - address.country
     * (we infer country_id from relation)
     *
     * Those will be nested accordingly in this relation array, so address relation
     * will have a nested relation with country.
     *
     * @param  array  $input  The form input.
     * @param  array  $crudFields  - present when getting the relation details for other relations.
     * @param  mixed  $relationMethod
     * @return array The formatted relation details.
     */
    private function getRelationDetailsFromInput($input, $crudFields = [], $relationMethod = false)
    {
        // main entity
        if (empty($crudFields)) {
            $relationFields = $this->getRelationFields();
        } else {
            // relations sends the fields that represent them so we can parse the input accordingly.
            $relationFields = $crudFields;

            foreach ($crudFields as $key => $crudField) {
                if (isset($crudField['subfields'])) {
                    foreach ($crudField['subfields'] as $crudSubField) {
                        if (isset($crudSubField['relation_type'])) {
                            $relationFields[] = $crudSubField;
                        }
                    }
                }
            }
        }

        //remove fields that are not in the submitted form input
        $relationFields = array_filter($relationFields, function ($field) use ($input) {
            return Arr::has($input, $field['name']) || isset($input[$field['name']]) || Arr::has($input, Str::afterLast($field['name'], '.'));
        });

        $relationDetails = [];

        foreach ($relationFields as $field) {
            // if relationMethod is set we strip it out of the fieldName that we use to create the relations array
            $fieldName = $relationMethod ? Str::after($field['name'], $relationMethod.'.') : $field['name'];

            $key = Str::before($this->getOnlyRelationEntity(['entity' => $fieldName]), '.');

            // if the field entity contains the attribute we want to add that attribute in the correct relation key.
            // eg: address.street, we want to add `street` as an attribute in `address` relation, `street` is not
            // a relation of `address`
            if ($this->getOnlyRelationEntity($field) !== $field['entity']) {
                if (Str::before($field['entity'], '.') === $relationMethod) {
                    $key = Str::before($this->getOnlyRelationEntity($field), '.');
                }
            }

            $attributeName = (string) Str::of($field['name'])->afterLast('.');

            switch ($field['relation_type']) {
                case 'BelongsTo':
                    // when it's a nested belongsTo relation we want to make sure
                    // the key used to store the values is the main relation key
                    $key = Str::beforeLast($this->getOnlyRelationEntity($field), '.');

                    if (! isset($field['parentFieldName']) && isset($field['entity'])) {
                        $mainField = $field;
                        $mainField['entity'] = Str::beforeLast($field['entity'], '.');

                        $inferredRelation = $this->inferRelationTypeFromRelationship($mainField);
                    }

                    break;
            }

            // we don't need to re-setup this relation method values, we just want the relations
            if ($key === $relationMethod) {
                unset($inferredRelation);
                continue;
            }

            $fieldDetails = Arr::get($relationDetails, $key, []);
            $fieldDetails['values'][$attributeName] = Arr::get($input, $fieldName);
            $fieldDetails['model'] = $fieldDetails['model'] ?? $field['model'];
            $fieldDetails['relation_type'] = $fieldDetails['relation_type'] ?? $inferredRelation ?? $field['relation_type'];
            $fieldDetails['crudFields'][] = $field;
            $fieldDetails['entity'] = $this->getOnlyRelationEntity($field);

            if (isset($field['fallback_id'])) {
                $fieldDetails['fallback_id'] = $field['fallback_id'];
            }
            if (isset($field['force_delete'])) {
                $fieldDetails['force_delete'] = $field['force_delete'];
            }

            Arr::set($relationDetails, $key, $fieldDetails);
            unset($inferredRelation);
        }

        return $relationDetails;
    }

    /**
     * Return the input without relations except BelongsTo that we are going to properly match
     * with the relation foreign_key in a later stage of the saving process.
     *
     * @param  array  $fields
     * @param  mixed  $relationMethod
     * @return array
     */
    private function excludeRelationFieldsExceptBelongsTo($input, $fields, $relationMethod)
    {
        // when fields are empty we are in the main entity, we get the regular crud relation fields
        if (empty($fields)) {
            $fields = $this->getRelationFields();
        }

        $excludedFields = [];
        foreach ($fields as $field) {
            $nameToExclude = $relationMethod ? Str::after($field['name'], $relationMethod.'.') : $field['name'];

            // when using dot notation if relationMethod is not set we are sure we want to exclude those relations.
            if ($this->getOnlyRelationEntity($field) !== $field['entity']) {
                if (! $relationMethod) {
                    $excludedFields[] = $nameToExclude;
                }

                continue;
            }

            if (isset($field['relation_type']) && $field['relation_type'] !== 'BelongsTo') {
                $excludedFields[] = $nameToExclude;

                continue;
            }
        }

        return Arr::where($input, function ($item, $key) use ($excludedFields) {
            return ! in_array($key, $excludedFields);
        });
    }

    /**
     * Decode attributes that are casted as array/object/json in the model.
     * So that they are not json_encoded twice before they are stored in the db
     * (once by Backpack in front-end, once by Laravel Attribute Casting).
     *
     * @param  array  $input
     * @param  mixed  $model
     * @return array
     */
    public function decodeJsonCastedAttributes($input, $model = false)
    {
        $model = $model ? $model : $this->model;
        $fields = $this->getCleanStateFields();
        $casted_attributes = $model->getCastedAttributes();
        $translatableAttributes = $model->translationEnabled() ? $model->getTranslatableAttributes() : [];

        foreach ($fields as $field) {
            // Test the field is castable
            if (isset($field['name']) && is_string($field['name']) && array_key_exists($field['name'], $casted_attributes)) {
                // Handle JSON field types
                $jsonCastables = ['array', 'object', 'json'];
                $fieldCasting = $casted_attributes[$field['name']];

                if (in_array($fieldCasting, $jsonCastables) && isset($input[$field['name']]) && ! empty($input[$field['name']]) && ! is_array($input[$field['name']]) && ! in_array($field['name'], $translatableAttributes)) {
                    try {
                        $input[$field['name']] = json_decode($input[$field['name']]);
                    } catch (\Exception $e) {
                        $input[$field['name']] = [];
                    }
                }
            }
        }

        return $input;
    }
}
