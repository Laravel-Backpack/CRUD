<!-- configurable color picker -->
{{-- https://farbelous.io/bootstrap-colorpicker/ --}}
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <div class="input-group colorpicker-component">

        <input
        	type="text"
        	name="{{ $field['name'] }}"
            value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
            data-init-function="bpFieldInitColorPickerElement"
            @include('crud::fields.inc.attributes')
        	>
        <div class="input-group-addon">
            <i class="color-preview-{{ $field['name'] }}"></i>
        </div>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <small class="text-muted d-block">{!! $field['hint'] !!}</small>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <link rel="stylesheet" href="{{ asset('packages/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css') }}" />
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script type="text/javascript" src="{{ asset('packages/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js') }}"></script>
    <script>
        function bpFieldInitColorPickerElement(element) {
            // https://itsjaviaguilar.com/bootstrap-colorpicker/
            var config = jQuery.extend({}, {!! isset($field['color_picker_options']) ? json_encode($field['color_picker_options']) : '{}' !!});
            var picker = element.parents('.colorpicker-component').colorpicker(config);

            element.on('focus', function(){
                picker.colorpicker('show');
            });
        }
    </script>
    @endpush

@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
