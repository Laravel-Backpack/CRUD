<!--  relationship  -->

@php
    if(isset($field['inline_create']) && !is_array($field['inline_create'])) {
        $field['inline_create'] = [true];
    }
    $field['multiple'] = $field['multiple'] ?? $crud->relationAllowsMultiple($field['relation_type']);
    $field['ajax'] = $field['ajax'] ?? false;
    $field['placeholder'] = $field['placeholder'] ?? ($field['multiple'] ? trans('backpack::crud.select_entries') : trans('backpack::crud.select_entry'));
    $field['attribute'] = $field['attribute'] ?? (new $field['model'])->identifiableAttribute();
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
    // Note: isColumnNullable returns true if column is nullable in database, also true if column does not exist.

    // if field is not ajax but user wants to use InlineCreate
    // we make minimum_input_length = 0 so when user open we show the entries like a regular select
    $field['minimum_input_length'] = ($field['ajax'] !== true) ? 0 : ($field['minimum_input_length'] ?? 2);

    if(isset($field['inline_create'])) {
        // if the field is beeing inserted in an inline create modal
        // we don't allow modal over modal (for now ...) so we load fetch or select accordingly to field type.
        if(!isset($inlineCreate)) {
            $field['type'] = 'fetch_or_create';
        }else{
            if($field['ajax']) {
                $field['type'] = 'fetch';
            }else{
                $field['type'] = 'relationship_select';
            }
        }
    }else{
        if($field['ajax']) {
            $field['type'] = 'fetch';
        }else{
            $field['type'] = 'relationship_select';
        }
    }
@endphp

@include('crud::fields.relationship.'.$field['type'])

