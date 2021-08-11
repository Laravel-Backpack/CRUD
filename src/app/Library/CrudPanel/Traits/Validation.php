<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Foundation\Http\FormRequest;

trait Validation
{
    /**
     * Adds the required rules from an array and allows validation of that array.
     *
     * @param array $requiredFields
     */
    public function setValidationFromArray(array $rules)
    {
        $requiredFields = [];

        if (count($rules)) {
            foreach ($rules as $key => $rule) {
                if (
                    (is_string($rule) && strpos($rule, 'required') !== false && strpos($rule, 'required_') === false) ||
                    (is_array($rule) && array_search('required', $rule) !== false && array_search('required_', $rule) === false)
                ) {
                    $requiredFields[] = $key;
                }
            }
        }
        $this->setOperationSetting('requiredFields', $requiredFields);
        $this->setOperationSetting('validationRules', $rules);
    }

    /**
     * Mark a FormRequest file as required for the current operation, in Settings.
     * Adds the required rules to an array for easy access.
     *
     * @param string $class Class that extends FormRequest
     */
    public function setValidation($class)
    {
        $this->setFormRequest($class);
        $this->setRequiredFields($class);
    }

    /**
     * Remove the current FormRequest from configuration, so it will no longer be validated.
     */
    public function unsetValidation()
    {
        $this->setOperationSetting('formRequest', false);
    }

    /**
     * Remove the current FormRequest from configuration, so it will no longer be validated.
     */
    public function disableValidation()
    {
        $this->unsetValidation();
    }

    /**
     * Mark a FormRequest file as required for the current operation, in Settings.
     *
     * @param string $class Class that extends FormRequest
     */
    public function setFormRequest($class)
    {
        $this->setOperationSetting('formRequest', $class);
    }

    /**
     * Get the current form request file, in any.
     * Returns null if no FormRequest is required for the current operation.
     *
     * @return string Class that extends FormRequest
     */
    public function getFormRequest()
    {
        return $this->getOperationSetting('formRequest');
    }

    /**
     * Run the authorization and validation the currently set FormRequest.
     *
     * @return \Illuminate\Http\Request
     */
    public function validateRequest()
    {
        $formRequest = $this->getFormRequest();

        if ($formRequest) {
            // authorize and validate the formRequest
            // this is done automatically by Laravel's FormRequestServiceProvider
            // because form requests implement ValidatesWhenResolved
            $request = app($formRequest);
        } else {
            $request = $this->getRequest();

            if ($this->hasOperationSetting('validationRules')) {
                $rules = $this->getOperationSetting('validationRules');
                $request->validate($rules);
            }
        }

        return $request;
    }

    /**
     * Parse a FormRequest class, figure out what inputs are required
     * and store this knowledge in the current object.
     *
     * @param string $class Class that extends FormRequest
     */
    public function setRequiredFields($class)
    {
        $formRequest = new $class();
        $rules = $formRequest->rules();
        $requiredFields = [];

        if (count($rules)) {
            foreach ($rules as $key => $rule) {
                if (
                    (is_string($rule) && strpos($rule, 'required') !== false && strpos($rule, 'required_') === false) ||
                    (is_array($rule) && array_search('required', $rule) !== false && array_search('required_', $rule) === false)
                ) {
                    if (strpos($key, '.') !== false) {
                        // Convert dot to array notation
                        $entity_array = explode('.', $key);
                        $name_string = '';

                        foreach ($entity_array as $arr_key => $array_entity) {
                            $name_string .= ($arr_key == 0) ? $array_entity : '['.$array_entity.']';
                        }

                        $key = $name_string;
                    }

                    $requiredFields[] = $key;
                }
            }
        }

        $this->setOperationSetting('requiredFields', $requiredFields);
    }

    /**
     * Check the current object to see if an input is required
     * for the given operation.
     *
     * @param string $inputKey  Field or input name.
     * @param string $operation create / update
     *
     * @return bool
     */
    public function isRequired($inputKey)
    {
        if (! $this->hasOperationSetting('requiredFields')) {
            return false;
        }

        return in_array($inputKey, $this->getOperationSetting('requiredFields'));
    }
}
