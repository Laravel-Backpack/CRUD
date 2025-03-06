<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
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

        // if the attribute is not set in the request, and an entry exists,
        // we will check if there is a previous value, as this field might not have changed.
        if (! Arr::has($this->data, $attribute) && $entry) {
            if (str_contains($attribute, '.') && get_class($entry) === get_class(CrudPanelFacade::getModel())) {
                $previousValue = Arr::get($this->data, '_order_'.Str::before($attribute, '.'));
                $previousValue = Arr::get($previousValue, Str::after($attribute, '.'));
            } else {
                $previousValue = Arr::get($entry, $attribute);
            }

            if ($previousValue && empty($value)) {
                return [];
            }

            Arr::set($this->data, $attribute, $previousValue ?? $value);
        }

        // if the value is an uploaded file, or the attribute is not
        // set in the request, we force fill the data with the value
        if ($value instanceof UploadedFile || ! Arr::has($this->data, $attribute)) {
            Arr::set($this->data, $attribute, $value);
        }

        // if there are no entry, and the new value it's not a file ... well we don't want it at all.
        if (! $entry && ! $value instanceof UploadedFile) {
            Arr::set($this->data, $attribute, null);
        }

        $fieldErrors = $this->validateFieldRules($attribute);

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
