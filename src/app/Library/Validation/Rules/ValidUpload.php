<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ValidUpload extends BackpackCustomRule
{
    /**
     * Run the validation rule and return the array of errors.
     */
    public function validateRules(string $attribute, mixed $value): array
    {
        $entry = CrudPanelFacade::getCurrentEntry();

        if (! Arr::has($this->data, $attribute)) {
            $requestAttribute = Arr::get($this->data, '_order_'.$attribute);

            if ($entry && Arr::get($entry->{Str::before($attribute, '.')}, Str::after($attribute, '.')) === $requestAttribute) {
                return [];
            }
            // set the empty attribute in data
            Arr::set($this->data, $attribute, null);
        }

        $fieldErrors = $this->validateFieldRules($attribute, $value);

        if (! empty($value) && ! empty($this->getFileRules())) {
            $fileErrors = $this->validateFileRules($attribute, $value);
        }

        return array_merge($fieldErrors, $fileErrors ?? []);
    }

    public static function field(string|array|ValidationRule|Rule $rules = []): self
    {
        return parent::field($rules);
    }
}
