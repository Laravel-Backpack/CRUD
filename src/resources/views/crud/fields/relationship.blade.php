{{-- 
    This field is a switchboard for the "real" relation fields AKA selects and repeatable.
    Based on developer preferences and the relation type we "guess" the best solution
    we can provide for the user, and use those field types (select/repeatable) accordingly.

    As relationships are the only thing allowed to "InlineCreate", that functionality is also handled here.
    We have a dedicated file for the inline create functionality that is `fetch_or_create`, that is basically
    a select2 with ajax enabled that allow to create a new entity without leaving the crud
--}}

@php
    if(isset($field['inline_create']) && !is_array($field['inline_create'])) {
        $field['inline_create'] = [true];
    }
    $field['multiple'] = $field['multiple'] ?? $crud->relationAllowsMultiple($field['relation_type']);
    $field['ajax'] = $field['ajax'] ?? isset($field['data_source']);
    $field['placeholder'] = $field['placeholder'] ?? ($field['multiple'] ? trans('backpack::crud.select_entries') : trans('backpack::crud.select_entry'));
    $field['attribute'] = $field['attribute'] ?? (new $field['model'])->identifiableAttribute();
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
    // Note: isColumnNullable returns true if column is nullable in database, also true if column does not exist.

    // if field is not ajax but user wants to use InlineCreate
    // we make minimum_input_length = 0 so when user open we show the entries like a regular select
    $field['minimum_input_length'] = ($field['ajax'] !== true) ? 0 : ($field['minimum_input_length'] ?? 2);

    switch($field['relation_type']) {
        case 'BelongsTo':
        case 'BelongsToMany':
            // if there is pivot fields we show the repeatable field
            if(isset($field['pivotFields'])) {
                $field['type'] = 'repeatable_relation';
            } else {
                if(isset($field['inline_create'])) {
                    // if the field is beeing inserted in an inline create modal
                    // we don't allow modal over modal (for now ...) so we load fetch or select accordingly to field type.
                    if(! isset($inlineCreate)) {
                        $field['type'] = 'fetch_or_create';
                    } else {
                        $field['type'] = $field['ajax'] ? 'fetch' : 'relationship_select';
                    }
                } else {
                    $field['type'] = $field['ajax'] ? 'fetch' : 'relationship_select';
                }
            }
        break;
        case 'MorphMany':
        case 'MorphToMany':
        case 'HasMany':
            // if there is pivot fields we show the repeatable field
            if(isset($field['pivotFields'])) {
                $field['type'] = 'repeatable_relation';
            } else {
                // we show a regular/ajax select
                $field['type'] = $field['ajax'] ? 'fetch' : 'relationship_select';
            }
        break;
    }
@endphp

@include('crud::fields.relationship.'.$field['type'])
