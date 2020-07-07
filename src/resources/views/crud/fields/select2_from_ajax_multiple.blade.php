<!-- select2 from ajax multiple -->
@php
    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();
    $old_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false;

    // by default set ajax query delay to 500ms
    // this is the time we wait before send the query to the search endpoint, after the user as stopped typing.
    $field['delay'] = $field['delay'] ?? 500;
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <select
        name="{{ $field['name'] }}[]"
        style="width: 100%"
        data-init-function="bpFieldInitSelect2FromAjaxMultipleElement"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(Arr::wrap($field['dependencies'])): json_encode([]) }}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-minimum-input-length="{{ $field['minimum_input_length'] }}"
        data-data-source="{{ $field['data_source'] }}"
        data-method="{{ $field['method'] ?? 'GET' }}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-include-all-form-fields="{{ isset($field['include_all_form_fields']) ? ($field['include_all_form_fields'] ? 'true' : 'false') : 'false' }}"
        data-ajax-delay="{{ $field['delay'] }}"
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control'])
        multiple>

        @if ($old_value)
            @foreach ($old_value as $item)
                @if (!is_object($item))
                    @php
                        $item = $connected_entity->find($item);
                    @endphp
                @endif
                <option value="{{ $item->getKey() }}" selected>
                    {{ $item->{$field['attribute']} }}
                </option>
            @endforeach
        @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- select2_from_ajax_multiple field type css -->
        @loadCssOnce('packages/select2/dist/css/select2.min.css')
        @loadCssOnce('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css')
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    @loadJsOnce('packages/select2/dist/js/select2.full.min.js')
        @if (app()->getLocale() !== 'en')
            @loadJsOnce('packages/select2/dist/js/i18n/' . app()->getLocale() . '.js')
        @endif
    @loadOnce('bpFieldInitSelect2FromAjaxMultipleElement')
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
            var $ajaxDelay = element.attr('data-ajax-delay');
            var $selectedOptions = JSON.parse(element.attr('data-selected-options') ?? '[]');

            var select2AjaxMultipleFetchSelectedEntries = function (element) {
                return new Promise(function (resolve, reject) {
                    $.ajax({
                        url: $dataSource,
                        data: {
                            'keys': $selectedOptions
                        },
                        type: $method,
                        success: function (result) {

                            resolve(result);
                        },
                        error: function (result) {
                            reject(result);
                        }
                    });
                });
            };

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
                        delay: $ajaxDelay,
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

            // if we have selected options here we are on a repeatable field, we need to fetch the options with the keys
            // we have stored from the field and append those options in the select.
            if (typeof $selectedOptions !== typeof undefined && $selectedOptions !== false && $selectedOptions != '') {
                var optionsForSelect = [];
                select2AjaxMultipleFetchSelectedEntries(element).then(result => {
                    result.forEach(function(item) {
                        $itemText = item[$fieldAttribute];
                        $itemValue = item[$connectedEntityKeyName];
                        //add current key to be selected later.
                        optionsForSelect.push($itemValue);

                        //create the option in the select
                        $(element).append('<option value="'+$itemValue+'">'+$itemText+'</option>');
                    });

                    // set the option keys as selected.
                    $(element).val(optionsForSelect);
                    $(element).trigger('change');
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
@endLoadOnce
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
