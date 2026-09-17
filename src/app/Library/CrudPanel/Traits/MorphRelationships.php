<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

            // the submitted pair gets promoted to top-level columns that are mass-assigned on the
            // entry, so it has to be verified against what the field actually offered first
            [$morphType, $morphId] = $this->getVerifiedMorphToValues(
                $field,
                $morphTypeField,
                $morphIdField,
                Arr::get($input, $field['name'].'.'.$morphTypeField['name']),
                Arr::get($input, $field['name'].'.'.$morphIdField['name'])
            );

            Arr::set($input, $morphTypeField['name'], $morphType);
            Arr::set($input, $morphIdField['name'], $morphId);
        }

        return $input;
    }

    /**
     * Verify the (morph type, morph id) pair submitted for a MorphTo field against the options
     * the field declared as selectable, the same way BelongsTo foreign keys are verified in
     * getVerifiedBelongsToInputs(). An out-of-scope value aborts the save with a validation
     * error on the corresponding subfield, instead of being silently persisted.
     *
     * @param  array  $field  The MorphTo crud field.
     * @param  array  $morphTypeField  The morphable_type subfield.
     * @param  array  $morphIdField  The morphable_id subfield.
     * @param  mixed  $morphType  The submitted morph type.
     * @param  mixed  $morphId  The submitted morph id.
     * @return array The verified [morph type, morph id] pair.
     *
     * @throws \Illuminate\Validation\ValidationException When the submitted pair is out of scope.
     */
    private function getVerifiedMorphToValues(array $field, array $morphTypeField, array $morphIdField, $morphType, $morphId)
    {
        $nothingSubmitted = fn ($value) => $value === null || $value === '';

        // the admin cleared the relation, there is nothing to verify
        if ($nothingSubmitted($morphType) && $nothingSubmitted($morphId)) {
            return [$morphType, $morphId];
        }

        $optionKey = $this->getVerifiedMorphTypeOptionKey($field, $morphTypeField, $morphType);

        // the field declares no morph options, so it constrains nothing
        if ($optionKey === null) {
            return [$morphType, $morphId];
        }

        if ($nothingSubmitted($morphId)) {
            return [$optionKey, $morphId];
        }

        $allowedKeys = $this->getAllowedMorphKeys($morphTypeField, $optionKey, $morphIdField['morphOptions'][$optionKey] ?? []);

        if ($allowedKeys !== null && ! in_array((string) $morphId, $allowedKeys, true)) {
            throw ValidationException::withMessages([
                $field['name'].'.'.$morphIdField['name'] => 'The selected '.($field['label'] ?? $field['name']).' is invalid.',
            ]);
        }

        // store the option key the field declared, so the persisted type is always one of them
        return [$optionKey, $morphId];
    }

    /**
     * Match the submitted morph type against the keys of the options the field declared,
     * comparing the models they resolve to, so that a morphMap alias and the class it maps
     * to are treated as the same option.
     *
     * @param  array  $field  The MorphTo crud field.
     * @param  array  $morphTypeField  The morphable_type subfield.
     * @param  mixed  $morphType  The submitted morph type.
     * @return string|null The declared option key, or null when the field declares no options.
     *
     * @throws \Illuminate\Validation\ValidationException When the submitted type was never an option.
     */
    private function getVerifiedMorphTypeOptionKey(array $field, array $morphTypeField, $morphType)
    {
        $declaredOptions = $morphTypeField['options'] ?? [];

        if (empty($declaredOptions)) {
            return null;
        }

        $morphMap = $morphTypeField['morphMap'] ?? [];
        $submittedModel = $morphMap[$morphType] ?? $morphType;

        foreach (array_keys($declaredOptions) as $optionKey) {
            if ((string) $optionKey === (string) $morphType || ($morphMap[$optionKey] ?? $optionKey) === $submittedModel) {
                return $optionKey;
            }
        }

        throw ValidationException::withMessages([
            $field['name'].'.'.$morphTypeField['name'] => 'The selected '.($field['label'] ?? $field['name']).' type is invalid.',
        ]);
    }

    /**
     * Resolve the entry keys that were selectable for one morph option, or null when that
     * option puts no constraint on them (every key of the related model is allowed).
     *
     * @param  array  $morphTypeField  The morphable_type subfield (holds the morphMap).
     * @param  string  $optionKey  The declared option key (a model class or a morphMap alias).
     * @param  array  $morphOption  The options declared for that key.
     * @return array<string>|null
     */
    private function getAllowedMorphKeys(array $morphTypeField, $optionKey, array $morphOption)
    {
        if (isset($morphOption['options']) && is_array($morphOption['options'])) {
            return array_map('strval', array_keys($morphOption['options']));
        }

        $modelClass = $morphTypeField['morphMap'][$optionKey] ?? $optionKey;

        if (! is_a($modelClass, Model::class, true)) {
            return null;
        }

        $modelInstance = new $modelClass;

        if (is_callable($morphOption['query'] ?? null)) {
            // the closure gets the same base query builder the field's view hands it
            $result = ($morphOption['query'])($modelInstance->toBase());
        } elseif ($ajaxConstraint = $this->getMorphOptionAjaxConstraint($morphOption, $modelClass)) {
            $result = $ajaxConstraint($modelInstance->newQuery());
        } else {
            return null;
        }

        return array_map('strval', $this->getAllowedKeysFromOptionsResult($result, $modelInstance));
    }

    /**
     * Get the closure that constrains the entries an ajax morph option serves, so the pair
     * submitted for it can be verified against them like any other option.
     *
     * An ajax BelongsTo field names its fetch method after the field entity. A morph option
     * has no entity of its own, so it names it after the model it lists - the same name
     * FetchOperation builds the route from, so the two always agree: `App\Models\Article`
     * (or the `article` morphMap alias) is served by `fetchArticle()`. Set
     * `relation_options_query_source` on the option when the fetch method is named otherwise.
     *
     * Returns null when the controller exposes no query for it, leaving the option unconstrained.
     *
     * @param  array  $morphOption
     * @param  string  $modelClass  The model this option lists.
     * @return callable|null
     */
    private function getMorphOptionAjaxConstraint(array $morphOption, $modelClass)
    {
        if (! isset($morphOption['data_source']) && ! ($morphOption['ajax'] ?? false)) {
            return null;
        }

        return $this->getRelationOptionsConstraintFromFetchSource(
            $morphOption['relation_options_query_source'] ?? class_basename($modelClass)
        );
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
