@php
    // if field name is array we check if any of the arrayed fields is translatable
    $translatable = false;

    $translatableModel = isset($field['baseModel']) ? app($field['baseModel']) : $crud->model;

    $translatableFieldNames = isset($field['baseFieldName'])
        ? explode(',', $field['baseFieldName'])
        : (array) $field['name'];

    if (method_exists($translatableModel, 'translationEnabled') && $translatableModel->translationEnabled()) {
        foreach ($translatableFieldNames as $field_name) {
            if ($translatableModel->isTranslatableAttribute($field_name)) {
                $translatable = true;
            }
        }
        // if the field is a fake one (value is stored in a JSON column instead of a direct db column)
        // and that JSON column is translatable, then the field itself should be translatable
        if (isset($field['store_in']) && $translatableModel->isTranslatableAttribute($field['store_in'])) {
            $translatable = true;
        }
    }

@endphp
@if ($translatable && config('backpack.crud.show_translatable_field_icon'))
    <i class="la la-flag-checkered pull-{{ config('backpack.crud.translatable_field_icon_position') }}" style="margin-top: 3px;" title="This field is translatable."></i>
@endif
