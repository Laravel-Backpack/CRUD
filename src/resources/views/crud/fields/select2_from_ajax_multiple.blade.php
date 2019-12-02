<!-- select2 from ajax multiple -->
@php
    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getRouteKeyName();
    $old_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false;
@endphp

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <select
        name="{{ $field['name'] }}[]"
        style="width: 100%"
        id="select2_ajax_multiple_{{ $field['name'] }}"
        data-init-function="bpFieldInitSelect2FromAjaxMultipleElement"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(array_wrap($field['dependencies'])): json_encode([]) }}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-minimum-input-length="{{ $field['minimum_input_length'] }}"
        data-data-source="{{ $field['data_source'] }}"
        data-method="{{ $field['method'] ?? 'GET' }}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-include-all-form-fields="{{ $field['include_all_form_fields'] ?? 'true' }}"
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control'])
        multiple>

        @if ($old_value)
            @foreach ($old_value as $item)
                @if (!is_object($item))
                    @php
                        $item = $connected_entity->find($item);
                    @endphp
                @endif
                <option value="{{ $item->getRouteKey() }}" selected>
                    {{ $item->{$field['attribute']} }}
                </option>
            @endforeach
        @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>


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
    @endpush

@endif

<!-- include field specific select2 js-->
@push('crud_fields_scripts')
<script>
    function bpFieldInitSelect2FromAjaxMultipleElement(element) {
        var form = element.closest('form');
        var $placeholder = element.attr('data-placeholder');
        var $minimumInputLength = element.attr('data-minimum-input-length');
        var $dataSource = element.attr('data-data-source');
        var $method = element.attr('data-method');
        var $fieldAttribute = element.attr('data-field-attribute');
        var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
        var $includeAllFormFields = element.attr('data-include-all-form-fields')=='false' ? false : true;
        var $allowClear = element.attr('data-column-nullable') == 'true' ? true : false;
        var $dependencies = JSON.parse(element.attr('data-dependencies'));

        if (!$(element).hasClass("select2-hidden-accessible"))
        {
            $(element).select2({
                theme: 'bootstrap',
                multiple: true,
                placeholder: $placeholder,
                minimumInputLength: $minimumInputLength,
                ajax: {
                    url: $dataSource,
                    type: $method,
                    dataType: 'json',
                    quietMillis: 250,
                    data: function (params) {
                        if ($includeAllFormFields) {
                            return {
                                q: params.term, // search term
                                page: params.page, // pagination
                                form: form.serializeArray() // all other form inputs
                            };
                        } else {
                            return {
                                q: params.term, // search term
                                page: params.page, // pagination
                            };
                        }
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.data, function (item) {
                                return {
                                    text: item[$fieldAttribute],
                                    id: item[$connectedEntityKeyName]
                                }
                            }),
                            pagination: {
                                 more: data.current_page < data.last_page
                            }
                        };
                    },
                    cache: true
                },
            });
        }

        // if any dependencies have been declared
        // when one of those dependencies changes value
        // reset the select2 value
        for (var i=0; i < $dependencies.length; i++) {
            $dependency = $dependencies[i];
            $('input[name='+$dependency+'], select[name='+$dependency+'], checkbox[name='+$dependency+'], radio[name='+$dependency+'], textarea[name='+$dependency+']').change(function () {
                element.val(null).trigger("change");
            });
        }
    }
</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
