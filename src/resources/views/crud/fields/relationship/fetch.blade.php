@php

    //in case entity is superNews we want the url friendly super-news
    $entityWithoutAttribute = $crud->getOnlyRelationEntity($field);
    $routeEntity = Str::kebab($entityWithoutAttribute);

    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();

    // we need to re-ensure field type here because relationship is a `switchboard` and not actually
    // a crud field like this one.
    $field['type'] = 'fetch';

    $field['multiple'] = $field['multiple'] ?? $crud->guessIfFieldHasMultipleFromRelationType($field['relation_type']);
    $field['data_source'] = $field['data_source'] ?? url($crud->route.'/fetch/'.$routeEntity);
    $field['attribute'] = $field['attribute'] ?? $connected_entity->identifiableAttribute();
    $field['placeholder'] = $field['placeholder'] ?? ($field['multiple'] ? trans('backpack::crud.select_entries') : trans('backpack::crud.select_entry'));
    $field['include_all_form_fields'] = $field['include_all_form_fields'] ?? true;
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
    // Note: isColumnNullable returns true if column is nullable in database, also true if column does not exist.

    // make sure the $field['value'] takes the proper value
    // and format it to JSON, so that select2 can parse it
    $current_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';
    if ($current_value != false) {
        switch (gettype($current_value)) {
            case 'array':
                $current_value = $connected_entity
                                    ->whereIn($connected_entity_key_name, $current_value)
                                    ->get()
                                    ->pluck($field['attribute'], $connected_entity_key_name);
                break;

            case 'object':
            if (is_subclass_of(get_class($current_value), 'Illuminate\Database\Eloquent\Model') ) {
                    $current_value = [$current_value->{$connected_entity_key_name} => $current_value->{$field['attribute']}];
                }else{
                    if(! $current_value->isEmpty())  {
                    $current_value = $current_value
                                    ->pluck($field['attribute'], $connected_entity_key_name)
                                    ->toArray();
                    }
                }
                break;

            default:
                $current_value = $connected_entity
                                ->where($connected_entity_key_name, $current_value)
                                ->get()
                                ->pluck($field['attribute'], $connected_entity_key_name);
                break;
        }
    }
    $field['value'] = json_encode($current_value);
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>

    <select
        style="width:100%"
        name="{{ $field['name'].($field['multiple']?'[]':'') }}"
        data-init-function="bpFieldInitFetchElement"
        data-column-nullable="{{ var_export($field['allows_null']) }}"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(Arr::wrap($field['dependencies'])): json_encode([]) }}"
        data-model-local-key="{{$crud->model->getKeyName()}}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-minimum-input-length="{{ isset($field['minimum_input_length']) ? $field['minimum_input_length'] : 2 }}"
        data-method="{{ $field['method'] ?? 'POST' }}"
        data-data-source="{{ $field['data_source']}}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-include-all-form-fields="{{ var_export($field['include_all_form_fields']) }}"
        data-current-value="{{ $field['value'] }}"
        data-app-current-lang="{{ app()->getLocale() }}"

        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control'])

        @if($field['multiple'])
        multiple
        @endif
        >
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
        <!-- fetch field type css -->
        @loadCssOnce('packages/select2/dist/css/select2.min.css')
        @loadCssOnce('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css')
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- fetch field type js -->
        @loadJsOnce('packages/select2/dist/js/select2.full.min.js')

        @if (app()->getLocale() !== 'en')
            @loadJsOnce('packages/select2/dist/js/i18n/' . app()->getLocale() . '.js')
        @endif

        @loadOnce('bpFieldInitFetchElement')
        <script>
            // if nullable, make sure the Clear button uses the translated string
            document.styleSheets[0].addRule('.select2-selection__clear::after','content:  "{{ trans('backpack::crud.clear') }}";');

    // if this function is not already on page, for example in fetch_create we add it.
    // this function is responsible for query the ajax endpoint and fetch a default entry
    // in case the field does not allow null
    if (!window.fetchDefaultEntry) {
        var fetchDefaultEntry = function (element) {
            var $fetchUrl = element.attr('data-data-source');
            var $relatedAttribute = element.attr('data-field-attribute');
            var $relatedKeyName = element.attr('data-connected-entity-key-name');
            var $return = {};
            var $appLang = element.attr('data-app-current-lang');

            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: $fetchUrl,
                    data: {
                        'q': ''
                    },
                    type: 'POST',
                    success: function (result) {
                        // if data is available here it means a paginated collection has been returned.
                // we want only the first to be default.
                if (typeof result.data !== "undefined"){
                    $key = result.data[0][$relatedKeyName];
                    $value = processItemText(result.data[0], $relatedAttribute, $appLang);
                }else{
                    $key = result[0][$relatedKeyName];
                    $value = processItemText(result[0], $relatedAttribute, $appLang);
                }

                $pair = { [$relatedKeyName] : $key, [$relatedAttribute] : $value}
                $return = {...$return, ...$pair};

                $(element).attr('data-current-value', JSON.stringify($return));
                resolve($return);
                    },
                    error: function (result) {
                        reject(result);
                    }
                });
            });
        };
    }

    /**
     * Initialize Select2 on an element that wants the "Fetch" functionality.
     * This method gets called automatically by Backpack:
     * - after the Create/Update page loads
     * - after a Fetch is inserted with JS somewhere (ex: in a modal)
     *
     * @param  node element The jQuery-wrapped "select" element.
     * @return void
     */
    function bpFieldInitFetchElement(element) {
        var form = element.closest('form');
        var $placeholder = element.attr('data-placeholder');
        var $minimumInputLength = element.attr('data-minimum-input-length');
        var $dataSource = element.attr('data-data-source');
        var $modelKey = element.attr('data-model-local-key');
        var $method = element.attr('data-method');
        var $fieldAttribute = element.attr('data-field-attribute');
        var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
        var $includeAllFormFields = element.attr('data-include-all-form-fields') == 'false' ? false : true;
        var $dependencies = JSON.parse(element.attr('data-dependencies'));
        var $allows_null = element.attr('data-column-nullable') == 'true' ? true : false;
        var $appLang = element.attr('data-app-current-lang');
        var $selectedOptions = JSON.parse(element.attr('data-selected-options') ?? null);
        var $multiple = element.prop('multiple');

        var FetchAjaxFetchSelectedEntry = function (element) {
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

        if($allows_null && !$multiple) {
            $(element).append('<option value="">'+$placeholder+'</option>');
        }


        if (typeof $selectedOptions !== typeof undefined &&
            $selectedOptions !== false &&
            $selectedOptions != '' &&
            $selectedOptions != null &&
            $selectedOptions != [])
        {
            var optionsForSelect = [];
            FetchAjaxFetchSelectedEntry(element).then(result => {
                result.forEach(function(item) {
                    $itemText = processItemText(item, $fieldAttribute, $appLang);
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

        var $item = false;

        var $value = JSON.parse(element.attr('data-current-value'))

        if(Object.keys($value).length > 0) {
            $item = true;
        }

        var $currentValue = $item ? $value : '';

        //we reselect the previously selected options if any.
        var selectedOptions = [];

        for (const [key, value] of Object.entries($currentValue)) {
            selectedOptions.push(key);
            var $option = new Option(value, key);
            $(element).append($option);
        }

        $(element).val(selectedOptions);


        if (!$allows_null && $item === false && $selectedOptions == null) {
            fetchDefaultEntry(element).then(result => {
                var $item = JSON.parse(element.attr('data-current-value'));
                $(element).append('<option value="'+$item[$modelKey]+'">'+$item[$fieldAttribute]+'</option>');
                $(element).val($item[$modelKey]);
                $(element).trigger('change');
            });
        }


        var $select2Settings = {
                theme: 'bootstrap',
                multiple: $multiple,
                placeholder: $placeholder,
                minimumInputLength: $minimumInputLength,
                allowClear: $allows_null,
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
                        //if we have data.data here it means we returned a paginated instance from controller.
                        //otherwise we returned one or more entries unpaginated.
                        if(data.data) {
                        var result = {
                            results: $.map(data.data, function (item) {
                                var $itemText = processItemText(item, $fieldAttribute, $appLang);

                                return {
                                    text: $itemText,
                                    id: item[$connectedEntityKeyName]
                                }
                            }),
                           pagination: {
                                 more: data.current_page < data.last_page
                           }
                        };
                        }else {
                            var result = {
                                results: $.map(data, function (item) {
                                    var $itemText = processItemText(item, $fieldAttribute, $appLang);

                                    return {
                                        text: $itemText,
                                        id: item[$connectedEntityKeyName]
                                    }
                                }),
                                pagination: {
                                    more: false,
                                }
                            }
                        }

                        return result;
                    },
                    cache: true
                },
            };
        if (!$(element).hasClass("select2-hidden-accessible"))
        {
            $(element).select2($select2Settings);
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
    }

    if (typeof processItemText !== 'function') {
    function processItemText(item, $fieldAttribute, $appLang) {
        if(typeof item[$fieldAttribute] === 'object' && item[$fieldAttribute] !== null)  {
                        if(item[$fieldAttribute][$appLang] != 'undefined') {
                            return item[$fieldAttribute][$appLang];
                        }else{
                            return item[$fieldAttribute][0];
                        }
                    }else{
                        return item[$fieldAttribute];
                    }
    }
}

        </script>
        @endLoadOnce
    @endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
