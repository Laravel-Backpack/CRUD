<!-- select2 from array -->
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <select
        name="{{ $field['name'] }}@if (isset($field['allows_multiple']) && $field['allows_multiple']==true)[]@endif"
        style="width: 100%"
        data-init-function="bpFieldInitSelect2FromArrayElement"
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control select2_from_array'])
        @if (isset($field['allows_multiple']) && $field['allows_multiple']==true)multiple @endif
        >

        @if (isset($field['allows_null']) && $field['allows_null']==true)
            <option value="">-</option>
        @endif

        @if (count($field['options']))
            @foreach ($field['options'] as $key => $value)
                @if((old(square_brackets_to_dots($field['name'])) && (
                        $key == old(square_brackets_to_dots($field['name'])) ||
                        (is_array(old(square_brackets_to_dots($field['name']))) &&
                        in_array($key, old(square_brackets_to_dots($field['name'])))))) ||
                        (null === old(square_brackets_to_dots($field['name'])) &&
                            ((isset($field['value']) && (
                                        $key == $field['value'] || (
                                                is_array($field['value']) &&
                                                in_array($key, $field['value'])
                                                )
                                        )) ||
                                (!isset($field['value']) && isset($field['default']) &&
                                ($key == $field['default'] || (
                                                is_array($field['default']) &&
                                                in_array($key, $field['default'])
                                            )
                                        )
                                ))
                        ))
                    <option value="{{ $key }}" selected>{{ $value }}</option>
                @else
                    <option value="{{ $key }}">{{ $value }}</option>
                @endif
            @endforeach
        @endif
    </select>

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
    <!-- include select2 css-->
    <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include select2 js-->
    <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
    @if (app()->getLocale() !== 'en')
    <script src="{{ asset('packages/select2/dist/js/i18n/' . app()->getLocale() . '.js') }}"></script>
    @endif
    <script>
        function bpFieldInitSelect2FromArrayElement(element) {
            if (!element.hasClass("select2-hidden-accessible"))
                {
                    element.select2({
                        theme: "bootstrap"
                    }).on('select2:unselect', function(e) {
                        if ($(this).attr('multiple') && $(this).val().length == 0) {
                            $(this).val(null).trigger('change');
                        }
                    });
                }
        }
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
