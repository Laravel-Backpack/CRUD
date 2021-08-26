<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudField;
use Illuminate\Support\Arr;

trait Fields
{
    use FieldsProtectedMethods;
    use FieldsPrivateMethods;

    // ------------
    // FIELDS
    // ------------

    /**
     * Get the CRUD fields for the current operation.
     *
     * @return array
     */
    public function fields()
    {
        return $this->getOperationSetting('fields') ?? [];
    }

    /**
     * The only REALLY MANDATORY attribute when defining a field is the 'name'.
     * Everything else Backpack can probably guess. This method makes sure  the
     * field definition array is complete, by guessing missing attributes.
     *
     * @param  string|array $field  The definition of a field (string or array).
     * @return array                The correct definition of that field.
     */
    public function makeSureFieldHasNecessaryAttributes($field)
    {
        $field = $this->makeSureFieldHasName($field);
        $field = $this->makeSureFieldHasEntity($field);
        $field = $this->makeSureFieldHasLabel($field);

        if (isset($field['entity']) && $field['entity'] !== false) {
            $field = $this->makeSureFieldHasRelationType($field);
            $field = $this->makeSureFieldHasModel($field);
            $field = $this->overwriteFieldNameFromEntity($field);
            $field = $this->makeSureFieldHasAttribute($field);
            $field = $this->makeSureFieldHasMultiple($field);
            $field = $this->makeSureFieldHasPivot($field);
        }

        $field = $this->makeSureFieldHasType($field);
        $field = $this->overwriteFieldNameFromDotNotationToArray($field);

        return $field;
    }

    /**
     * Add a field to the create/update form or both.
     *
     * @param string|array $field The new field.
     *
     * @return self
     */
    public function addField($field)
    {
        $field = $this->makeSureFieldHasNecessaryAttributes($field);

        $this->enableTabsIfFieldUsesThem($field);
        $this->addFieldToOperationSettings($field);

        return $this;
    }

    /**
     * Add multiple fields to the create/update form or both.
     *
     * @param array  $fields The new fields.
     */
    public function addFields($fields)
    {
        if (count($fields)) {
            foreach ($fields as $field) {
                $this->addField($field);
            }
        }
    }

    /**
     * Move the most recently added field after the given target field.
     *
     * @param string $targetFieldName The target field name.
     */
    public function afterField($targetFieldName)
    {
        $this->transformFields(function ($fields) use ($targetFieldName) {
            return $this->moveField($fields, $targetFieldName, false);
        });
    }

    /**
     * Move the most recently added field before the given target field.
     *
     * @param string $targetFieldName The target field name.
     */
    public function beforeField($targetFieldName)
    {
        $this->transformFields(function ($fields) use ($targetFieldName) {
            return $this->moveField($fields, $targetFieldName, true);
        });
    }

    /**
     * Move this field to be first in the fields list.
     *
     * @return bool|null
     */
    public function makeFirstField()
    {
        if (! $this->fields()) {
            return false;
        }

        $firstField = array_keys(array_slice($this->fields(), 0, 1))[0];
        $this->beforeField($firstField);
    }

    /**
     * Remove a certain field from the create/update/both forms by its name.
     *
     * @param string $name Field name (as defined with the addField() procedure)
     */
    public function removeField($name)
    {
        $this->transformFields(function ($fields) use ($name) {
            Arr::forget($fields, $name);

            return $fields;
        });
    }

    /**
     * Remove many fields from the create/update/both forms by their name.
     *
     * @param array $array_of_names A simple array of the names of the fields to be removed.
     */
    public function removeFields($array_of_names)
    {
        if (! empty($array_of_names)) {
            foreach ($array_of_names as $name) {
                $this->removeField($name);
            }
        }
    }

    /**
     * Remove all fields from the create/update/both forms.
     */
    public function removeAllFields()
    {
        $current_fields = $this->getCurrentFields();
        if (! empty($current_fields)) {
            foreach ($current_fields as $field) {
                $this->removeField($field['name']);
            }
        }
    }

    /**
     * Remove an attribute from one field's definition array.
     * @param  string $field     The name of the field.
     * @param  string $attribute The name of the attribute being removed.
     */
    public function removeFieldAttribute($field, $attribute)
    {
        $fields = $this->fields();

        unset($fields[$field][$attribute]);

        $this->setOperationSetting('fields', $fields);
    }

    /**
     * Update value of a given key for a current field.
     *
     * @param string $fieldName         The field name
     * @param array  $modifications An array of changes to be made.
     */
    public function modifyField($fieldName, $modifications)
    {
        $fieldsArray = $this->fields();
        $field = $this->firstFieldWhere('name', $fieldName);
        $fieldKey = $this->getFieldKey($field);

        foreach ($modifications as $attributeName => $attributeValue) {
            $fieldsArray[$fieldKey][$attributeName] = $attributeValue;
        }

        $this->enableTabsIfFieldUsesThem($modifications);

        $this->setOperationSetting('fields', $fieldsArray);
    }

    /**
     * Set label for a specific field.
     *
     * @param string $field
     * @param string $label
     */
    public function setFieldLabel($field, $label)
    {
        $this->modifyField($field, ['label' => $label]);
    }

    /**
     * Check if field is the first of its type in the given fields array.
     * It's used in each field_type.blade.php to determine wether to push the css and js content or not (we only need to push the js and css for a field the first time it's loaded in the form, not any subsequent times).
     *
     * @param array $field The current field being tested if it's the first of its type.
     *
     * @return bool true/false
     */
    public function checkIfFieldIsFirstOfItsType($field)
    {
        $fields_array = $this->getCurrentFields();
        $first_field = $this->getFirstOfItsTypeInArray($field['type'], $fields_array);

        if ($first_field && $field['name'] == $first_field['name']) {
            return true;
        }

        return false;
    }

    /**
     * Decode attributes that are casted as array/object/json in the model.
     * So that they are not json_encoded twice before they are stored in the db
     * (once by Backpack in front-end, once by Laravel Attribute Casting).
     */
    public function decodeJsonCastedAttributes($data)
    {
        $fields = $this->getFields();
        $casted_attributes = $this->model->getCastedAttributes();

        foreach ($fields as $field) {

            // Test the field is castable
            if (isset($field['name']) && is_string($field['name']) && array_key_exists($field['name'], $casted_attributes)) {

                // Handle JSON field types
                $jsonCastables = ['array', 'object', 'json'];
                $fieldCasting = $casted_attributes[$field['name']];

                if (in_array($fieldCasting, $jsonCastables) && isset($data[$field['name']]) && ! empty($data[$field['name']]) && ! is_array($data[$field['name']])) {
                    try {
                        $data[$field['name']] = json_decode($data[$field['name']]);
                    } catch (\Exception $e) {
                        $data[$field['name']] = [];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getCurrentFields()
    {
        return $this->fields();
    }

    /**
     * Order the CRUD fields. If certain fields are missing from the given order array, they will be
     * pushed to the new fields array in the original order.
     *
     * @param array $order An array of field names in the desired order.
     */
    public function orderFields($order)
    {
        $this->transformFields(function ($fields) use ($order) {
            return $this->applyOrderToFields($fields, $order);
        });
    }

    /**
     * Get the fields for the create or update forms.
     *
     * @return array all the fields that need to be shown and their information
     */
    public function getFields()
    {
        return $this->fields();
    }

    /**
     * Check if the create/update form has upload fields.
     * Upload fields are the ones that have "upload" => true defined on them.
     *
     * @param string   $form create/update/both - defaults to 'both'
     * @param bool|int $id   id of the entity - defaults to false
     *
     * @return bool
     */
    public function hasUploadFields()
    {
        $fields = $this->getFields();
        $upload_fields = Arr::where($fields, function ($value, $key) {
            return isset($value['upload']) && $value['upload'] == true;
        });

        return count($upload_fields) ? true : false;
    }

    // ----------------------
    // FIELD ASSET MANAGEMENT
    // ----------------------

    /**
     * Get all the field types whose resources (JS and CSS) have already been loaded on page.
     *
     * @return array Array with the names of the field types.
     */
    public function getLoadedFieldTypes()
    {
        return $this->getOperationSetting('loadedFieldTypes') ?? [];
    }

    /**
     * Set an array of field type names as already loaded for the current operation.
     *
     * @param array $fieldTypes
     */
    public function setLoadedFieldTypes($fieldTypes)
    {
        $this->setOperationSetting('loadedFieldTypes', $fieldTypes);
    }

    /**
     * Get a namespaced version of the field type name.
     * Appends the 'view_namespace' attribute of the field to the `type', using dot notation.
     *
     * @param  mixed $field
     * @return string Namespaced version of the field type name. Ex: 'text', 'custom.view.path.text'
     */
    public function getFieldTypeWithNamespace($field)
    {
        if (is_array($field)) {
            $fieldType = $field['type'];
            if (isset($field['view_namespace'])) {
                $fieldType = implode('.', [$field['view_namespace'], $field['type']]);
            }
        } else {
            $fieldType = $field;
        }

        return $fieldType;
    }

    /**
     * Add a new field type to the loadedFieldTypes array.
     *
     * @param string $field Field array
     * @return  bool Successful operation true/false.
     */
    public function addLoadedFieldType($field)
    {
        $alreadyLoaded = $this->getLoadedFieldTypes();
        $type = $this->getFieldTypeWithNamespace($field);

        if (! in_array($type, $this->getLoadedFieldTypes(), true)) {
            $alreadyLoaded[] = $type;
            $this->setLoadedFieldTypes($alreadyLoaded);

            return true;
        }

        return false;
    }

    /**
     * Alias of the addLoadedFieldType() method.
     * Adds a new field type to the loadedFieldTypes array.
     *
     * @param string $field Field array
     * @return  bool Successful operation true/false.
     */
    public function markFieldTypeAsLoaded($field)
    {
        return $this->addLoadedFieldType($field);
    }

    /**
     * Check if a field type's reasources (CSS and JS) have already been loaded.
     *
     * @param string $field Field array
     * @return  bool Whether the field type has been marked as loaded.
     */
    public function fieldTypeLoaded($field)
    {
        return in_array($this->getFieldTypeWithNamespace($field), $this->getLoadedFieldTypes());
    }

    /**
     * Check if a field type's reasources (CSS and JS) have NOT been loaded.
     *
     * @param string $field Field array
     * @return  bool Whether the field type has NOT been marked as loaded.
     */
    public function fieldTypeNotLoaded($field)
    {
        return ! in_array($this->getFieldTypeWithNamespace($field), $this->getLoadedFieldTypes());
    }

    /**
     * Get a list of all field names for the current operation.
     *
     * @return array
     */
    public function getAllFieldNames()
    {
        //we need to parse field names in relation fields so they get posted/stored correctly
        $fields = $this->parseRelationFieldNamesFromHtml($this->getCurrentFields());

        return Arr::flatten(Arr::pluck($fields, 'name'));
    }

    /**
     * Returns the request without anything that might have been maliciously inserted.
     * Only specific field names that have been introduced with addField() are kept in the request.
     */
    public function getStrippedSaveRequest($request)
    {
        $saveAllInputsExcept = $this->getOperationSetting('saveAllInputsExcept');

        if (is_array($saveAllInputsExcept)) {
            return $request->except($saveAllInputsExcept);
        }

        return $request->only(array_merge($this->getAllFieldNames(), $this->model->getFillable()));
    }

    /**
     * Check if a field exists, by any given attribute.
     *
     * @param  string  $attribute   Attribute name on that field definition array.
     * @param  string  $value       Value of that attribute on that field definition array.
     * @return bool
     */
    public function hasFieldWhere($attribute, $value)
    {
        $match = Arr::first($this->fields(), function ($field, $fieldKey) use ($attribute, $value) {
            return isset($field[$attribute]) && $field[$attribute] == $value;
        });

        return (bool) $match;
    }

    /**
     * Get the first field where a given attribute has the given value.
     *
     * @param  string  $attribute   Attribute name on that field definition array.
     * @param  string  $value       Value of that attribute on that field definition array.
     * @return bool
     */
    public function firstFieldWhere($attribute, $value)
    {
        return Arr::first($this->fields(), function ($field, $fieldKey) use ($attribute, $value) {
            return isset($field[$attribute]) && $field[$attribute] == $value;
        });
    }

    /**
     * Create and return a CrudField object for that field name.
     *
     * Enables developers to use a fluent syntax to declare their fields,
     * in addition to the existing options:
     * - CRUD::addField(['name' => 'price', 'type' => 'number']);
     * - CRUD::field('price')->type('number');
     *
     * And if the developer uses the CrudField object as Field in their CrudController:
     * - Field::name('price')->type('number');
     *
     * @param  string $name The name of the column in the db, or model attribute.
     * @return CrudField
     */
    public function field($name)
    {
        return new CrudField($name);
    }
}
