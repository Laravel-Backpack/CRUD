@php
    $field['value'] = old_empty_or_null($field['name'], '') ?? $field['value'] ?? $field['default'] ?? '0';
    $field['allows_null'] = false;
    $field['options'][0] ??= Lang::has('backpack::crud.no') ? trans('backpack::crud.no') : 'No';
    $field['options'][1] ??= Lang::has('backpack::crud.yes') ? trans('backpack::crud.yes') : 'Yes';
@endphp

@include('crud::fields.select_from_array')
