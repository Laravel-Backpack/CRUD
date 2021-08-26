{{--
    This field is a switchboard for the "real" field that is a repeatable
    Based on developer preferences and the relation type we "guess" the best solution
    we can provide for the user and setup some defaults for them.

    One of the things that we take care, is adding the "pivot selector field", that is the link with
    the current crud and pivot entries, in this scenario is used with other pivot fields in a repeatable container.
--}}

@php
    $field['type'] = 'repeatable';
    $field['fields'] = $field['pivotFields'];
    $inline_create = !isset($inlineCreate) && isset($field['inline_create']) ? $field['inline_create'] : false;
    $pivotSelectorField = [
        'name' => $field['name'],
        'label' => $field['label'],
        'multiple' => false,
        'ajax' => $field['ajax'] ?? false,
        'data_source' => $field['data_source'] ?? isset($field['ajax']) && $field['ajax'] ? url($crud->route.'/fetch/'.$routeEntity) : 'false',
        'wrapper' => $field['pivot_wrapper'] ?? [],
        'minimum_input_length' => $field['minimum_input_length'] ?? 2,
        'delay' => $field['delay'] ?? 500,
        'placeholder' => $field['placeholder'] ?? trans('backpack::crud.select_entry'),
        'options' => $field['options'] ?? (function($query) { return $query; }),
    ];

    if($inline_create) {
        $field['inline_create'] = $inline_create;
    }

    switch ($field['relation_type']) {
        case 'MorphToMany':
        case 'BelongsToMany':
            $field['fields'] = Arr::prepend($field['fields'], $pivotSelectorField);
            break;

        case 'MorphMany':
        case 'HasMany':
            if(isset($entry)) {
                $field['fields'] = Arr::prepend($field['fields'], [
                    'name' => $entry->{$field['name']}()->getLocalKeyName(),
                    'type' => 'hidden',
                ]);
            }
            break;
    }
@endphp

@include('crud::fields.repeatable')
