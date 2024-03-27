<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Backpack\CRUD\app\Library\Validation\Rules\Support\ValidateArrayContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ValidUploadMultiple extends BackpackCustomRule implements ValidateArrayContract
{
    public function validateRules(string $attribute, mixed $value): array
    {
        $entry = CrudPanelFacade::getCurrentEntry() !== false ? CrudPanelFacade::getCurrentEntry() : null;
        $data = $this->data;
        // `upload_multiple` sends [[0 => null]] when user doesn't upload anything
        // assume that nothing changed on field so nothing is sent on the request.
        if (count($value) === 1 && empty($value[0])) {
            Arr::set($data, $attribute, []);
            $value = [];
        }

        $previousValues = str_contains($attribute, '.') ?
                            (Arr::get($entry?->{Str::before($attribute, '.')} ?? [], Str::after($attribute, '.')) ?? []) :
                            ($entry?->{$attribute} ?? []);

        if (is_string($previousValues)) {
            $previousValues = json_decode($previousValues, true) ?? [];
        }

        Arr::set($data, $attribute, array_merge($previousValues, $value));

        if ($entry) {
            $filesDeleted = CrudPanelFacade::getRequest()->input('clear_'.$attribute) ?? [];
            Arr::set($data, $attribute, array_diff(Arr::get($data, $attribute), $filesDeleted));

            return $this->validateFieldAndFile($attribute, $data);
        }

        return $this->validateFieldAndFile($attribute, $data);
    }
}
