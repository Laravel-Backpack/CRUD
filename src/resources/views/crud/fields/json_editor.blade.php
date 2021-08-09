{{-- json field based on: https://github.com/josdejong/jsoneditor --}}
@php
    $value = new stdClass();

    if (old($field['name'])) {
        $value = old($field['name']);
    } elseif (isset($field['value']) && isset($field['default'])) {
        $value = array_merge_recursive($field['default'], $field['value']);
    } elseif (isset($field['value'])) {
        $value = $field['value'];
    } elseif (isset($field['default'])) {
        $value = $field['default'];
    }

    // if attribute casting is used, convert to JSON
    if (is_array($value) || is_object($value) ) {
        $value = json_encode($value);
    }
@endphp

@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))
    @push('crud_fields_styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/7.0.5/jsoneditor.min.css" />
    @endpush

    @push('crud_fields_scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/7.0.5/jsoneditor.min.js"></script>
        <script>
            let container, jsonString, options, editor;
        </script>
    @endpush
@endif

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>

    <div id="jsoneditor-{{ $field['name'] }}" style="height: 400px;"></div>

    <input type="hidden" id="{{ $field['name'] }}"
           name="{{ $field['name'] }}"
        @include('crud::inc.field_attributes') />

    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

@push('crud_fields_scripts')
    <script>
        container = document.getElementById('jsoneditor-{{ $field['name'] }}');
        jsonString = @json($value);

        options = {
            onChange: function() {
                const hiddenField = document.getElementById('{{ $field['name'] }}');
                hiddenField.value = window['editor_{{ $field['name'] }}'].getText();
            },
            modes: @json($field['modes'] ?? ['form', 'tree', 'code']),
        };

        window['editor_{{ $field['name'] }}'] = new JSONEditor(container, options, JSON.parse(jsonString));
        document.getElementById('{{ $field['name'] }}').value = window['editor_{{ $field['name'] }}'].getText();
    </script>
@endpush
