<!--  relationship  -->

@php

    //in case entity is superNews we want the url friendly super-news
    $entityWithoutAttribute = $crud->getOnlyRelationEntity($field);
    $routeEntity = Str::kebab($entityWithoutAttribute);
    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();

    // make sure the $field['value'] takes the proper value
    // and format it to JSON, so that select2 can parse it
    $current_value = old(square_brackets_to_dots($field['name'])) ?? old($field['name']) ?? $field['value'] ?? $field['default'] ?? '';

    if ($current_value !== false) {
        switch (gettype($current_value)) {
            case 'array':
                $current_value = $connected_entity
                                    ->whereIn($connected_entity_key_name, $current_value)
                                    ->get()
                                    ->pluck($field['attribute'], $connected_entity_key_name)
                                    ->toArray();
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
                                ->pluck($field['attribute'], $connected_entity_key_name)
                                ->toArray();

                break;
        }
    }
    $field['value'] = json_encode($current_value);


    $field['data_source'] = $field['data_source'] ?? url($crud->route.'/fetch/'.$routeEntity);
    $field['include_all_form_fields'] = $field['include_all_form_fields'] ?? true;

    // this is the time we wait before send the query to the search endpoint, after the user as stopped typing.
    $field['delay'] = $field['delay'] ?? 500;



$activeInlineCreate = !empty($field['inline_create']) ? true : false;

if($activeInlineCreate) {


    //we check if this field is not beeing requested in some InlineCreate operation.
    //this variable is setup by InlineCreate modal when loading the fields.
    if(!isset($inlineCreate)) {
        //by default, when creating an entity we want it to be selected/added to selection.
        $field['inline_create']['force_select'] = $field['inline_create']['force_select'] ?? true;

        $field['inline_create']['modal_class'] = $field['inline_create']['modal_class'] ?? 'modal-dialog';

        //if user don't specify a different entity in inline_create we assume it's the same from $field['entity'] kebabed
        $field['inline_create']['entity'] = $field['inline_create']['entity'] ?? $routeEntity;

        //route to create a new entity
        $field['inline_create']['create_route'] = route($field['inline_create']['entity']."-inline-create-save");

        //route to modal
        $field['inline_create']['modal_route'] = route($field['inline_create']['entity']."-inline-create");

        //include main form fields in the request when asking for modal data,
        //allow the developer to modify the inline create modal
        //based on some field on the main form
        $field['inline_create']['include_main_form_fields'] = $field['inline_create']['include_main_form_fields'] ?? false;

        if(!is_bool($field['inline_create']['include_main_form_fields'])) {
            if(is_array($field['inline_create']['include_main_form_fields'])) {
                $field['inline_create']['include_main_form_fields'] = json_encode($field['inline_create']['include_main_form_fields']);
            }else{
                //it is a string or treat it like
                $arrayed_field = array($field['inline_create']['include_main_form_fields']);
                $field['inline_create']['include_main_form_fields'] = json_encode($arrayed_field);
            }
        }
    }
}

@endphp

@include('crud::fields.inc.wrapper_start')

        <label>{!! $field['label'] !!}</label>
        @include('crud::fields.inc.translatable_icon')

        @if($activeInlineCreate)
            @include('crud::fields.relationship.inline_create_button', ['field' => $field])
        @endif
<select
        name="{{ $field['name'].($field['multiple']?'[]':'') }}"
        data-original-name="{{ $field['name'] }}"
        style="width: 100%"
        data-force-select="{{ var_export($field['inline_create']['force_select']) }}"
        data-init-function="bpFieldInitFetchOrCreateElement"
        data-is-inline="{{ $inlineCreate ?? 'false' }}"
        data-allows-null="{{var_export($field['allows_null'])}}"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(Arr::wrap($field['dependencies'])): json_encode([]) }}"
        data-model-local-key="{{$crud->model->getKeyName()}}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-data-source="{{ $field['data_source'] }}"
        data-method="{{ $field['method'] ?? 'POST' }}"
        data-minimum-input-length="{{ $field['minimum_input_length'] }}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-include-all-form-fields="{{ var_export($field['include_all_form_fields']) }}"
        data-current-value="{{ $field['value'] }}"
        data-field-ajax="{{var_export($field['ajax'])}}"
        data-inline-modal-class="{{ $field['inline_create']['modal_class'] }}"
        data-app-current-lang="{{ app()->getLocale() }}"
        data-include-main-form-fields="{{ is_bool($field['inline_create']['include_main_form_fields']) ? var_export($field['inline_create']['include_main_form_fields']) : $field['inline_create']['include_main_form_fields'] }}"
        data-ajax-delay="{{ $field['delay'] }}"
        data-language="{{ str_replace('_', '-', app()->getLocale()) }}"

        @if($activeInlineCreate)
            @include('crud::fields.relationship.field_attributes')
        @endif

        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control select2_field'])

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
            <script src="{{ asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js') }}"></script>
            @endif
            <script>

document.styleSheets[0].addRule('.select2-selection__clear::after','content:  "{{ trans('backpack::crud.clear') }}";');

// this is the function responsible for querying the ajax endpoint with our query string, emulating the select2
// ajax search mechanism.
var performAjaxSearch = function (element, $searchString) {
    var $includeAllFormFields = element.attr('data-include-all-form-fields')=='false' ? false : true;
    var $refreshUrl = element.attr('data-data-source');
    var $method = element.attr('data-method');
    var form = element.closest('form')

    return new Promise(function (resolve, reject) {
        $.ajax({
            url: $refreshUrl,
            data: (function() {
                if ($includeAllFormFields) {
                            return {
                                q: $searchString, // search term
                                form: form.serializeArray() // all other form inputs
                            };
                        } else {
                            return {
                                q: $searchString, // search term
                            };
                        }
            })(),
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


  // this function is responsible for fetching some default option when developer don't allow null on field
if (!window.fetchDefaultEntry) {
var fetchDefaultEntry = function (element) {
    var $relatedAttribute = element.attr('data-field-attribute');
    var $relatedKeyName = element.attr('data-connected-entity-key-name');
    var $fetchUrl = element.attr('data-data-source');
    var $return = {};
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
                    $value = processItemText(result.data[0], $relatedAttribute);
                }else{
                    $key = result[0][$relatedKeyName];
                    $value = processItemText(result[0], $relatedAttribute);
                }

                $return[$relatedKeyName] = $key;
                $return[$relatedAttribute] = $value;

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

//this setup the "+Add" button in page with corresponding click handler.
//when clicked, fetches the html for the modal to show

function setupInlineCreateButtons(element) {
    var $fieldEntity = element.attr('data-field-related-name');
    var $inlineCreateButtonElement = $(element).parent().find('.inline-create-button');
    var $inlineModalRoute = element.attr('data-inline-modal-route');
    var $inlineModalClass = element.attr('data-inline-modal-class');
    var $parentLoadedFields = element.attr('data-parent-loaded-fields');
    var $includeMainFormFields = element.attr('data-include-main-form-fields') == 'false' ? false : (element.attr('data-include-main-form-fields') == 'true' ? true : element.attr('data-include-main-form-fields'));

    var $form = element.closest('form');

    $inlineCreateButtonElement.on('click', function () {

        //we change button state so users know something is happening.
        var loadingText = '<span class="la la-spinner la-spin" style="font-size:18px;"></span>';
        if ($inlineCreateButtonElement.html() !== loadingText) {
            $inlineCreateButtonElement.data('original-text', $inlineCreateButtonElement.html());
            $inlineCreateButtonElement.html(loadingText);


        }

        //prepare main form fields to be submited in case there are some.
        if(typeof $includeMainFormFields === "boolean" && $includeMainFormFields === true) {
            var $toPass = $form.serializeArray();
        }else{
            if(typeof $includeMainFormFields !== "boolean") {
                var $fields = JSON.parse($includeMainFormFields);
                var $serializedForm = $form.serializeArray();
                var $toPass = [];

                $fields.forEach(function(value, index) {
                    $valueFromForm = $serializedForm.filter(function(field) {
                        return field.name === value
                    });
                    $toPass.push($valueFromForm[0]);

                });

                $includeMainFormFields = true;
            }
        }
        $.ajax({
            url: $inlineModalRoute,
            data: (function() {
                if($includeMainFormFields) {
                    return {
                        'entity': $fieldEntity,
                        'modal_class' : $inlineModalClass,
                        'parent_loaded_fields' : $parentLoadedFields,
                        'main_form_fields' : $toPass
                    };
                }else{
                    return {
                        'entity': $fieldEntity,
                        'modal_class' : $inlineModalClass,
                        'parent_loaded_fields' : $parentLoadedFields
                    };
                }
            })(),
            type: 'POST',
            success: function (result) {
                $('body').append(result);
                triggerModal(element);

            },
            error: function (result) {
                // Show an alert with the result
                swal({
                    title: "error",
                    text: "error",
                    icon: "error",
                    timer: 4000,
                    buttons: false,
                });
            }
        });

    });

}

// when an entity is created we query the ajax endpoint to check if the created option is returned.
function ajaxSearch(element, created) {
    var $relatedAttribute = element.attr('data-field-attribute');
    var $relatedKeyName = element.attr('data-connected-entity-key-name');
    var $searchString = created[$relatedAttribute];

    //we run the promise with ajax call to search endpoint to check if we got the created entity back
    //in case we do, we add it to the selected options.
    performAjaxSearch(element, $searchString).then(function(result) {
        var inCreated = $.map(result.data, function (item) {
            var $itemText = processItemText(item, $relatedAttribute);
            var $createdText = processItemText(created, $relatedAttribute);
            if($itemText == $createdText) {
                    return {
                        text: $itemText,
                        id: item[$relatedKeyName]
                    }
                }
        });

        if(inCreated.length) {
            selectOption(element, created);
        }
    });
}

//this is the function called when button to add is pressed,
//it triggers the modal on page and initialize the fields

function triggerModal(element) {
    var $fieldName = element.attr('data-field-related-name');
    var $modal = $('#inline-create-dialog');
    var $modalSaveButton = $modal.find('#saveButton');
    var $modalCancelButton = $modal.find('#cancelButton');
    var $form = $(document.getElementById($fieldName+"-inline-create-form"));
    var $inlineCreateRoute = element.attr('data-inline-create-route');
    var $ajax = element.attr('data-field-ajax') == 'true' ? true : false;
    var $force_select = (element.attr('data-force-select') == 'true') ? true : false;


    $modal.modal();

    initializeFieldsWithJavascript($form);

    $modalCancelButton.on('click', function () {
        $($modal).modal('hide');
    });

    //when you hit save on modal save button.
    $modalSaveButton.on('click', function () {

        $form = document.getElementById($fieldName+"-inline-create-form");

        //this is needed otherwise fields like ckeditor don't post their value.
        $($form).trigger('form-pre-serialize');

        var $formData = new FormData($form);

        //we change button state so users know something is happening.
        //we also disable it to prevent double form submition
        var loadingText = '<i class="la la-spinner la-spin"></i> saving...';
        if ($modalSaveButton.html() !== loadingText) {
            $modalSaveButton.data('original-text', $(this).html());
            $modalSaveButton.html(loadingText);
            $modalSaveButton.prop('disabled', true);
        }


        $.ajax({
            url: $inlineCreateRoute,
            data: $formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (result) {

                $createdEntity = result.data;

                if(!$force_select) {
                    //if developer did not force the created entity to be selected we first try to
                    //check if created is still available upon model re-search.
                    ajaxSearch(element, result.data);

                }else{
                    selectOption(element, result.data);
                }

                $modal.modal('hide');



                new Noty({
                    type: "info",
                    text: '{{ trans('backpack::crud.related_entry_created_success') }}',
                }).show();
            },
            error: function (result) {

                var $errors = result.responseJSON.errors;

                let message = '';
                for (var i in $errors) {
                    message += $errors[i] + ' \n';
                }

                new Noty({
                    type: "error",
                    text: '<strong>{{ trans('backpack::crud.related_entry_created_error') }}</strong><br> '+message,
                }).show();

                //revert save button back to normal
                $modalSaveButton.prop('disabled', false);
                $modalSaveButton.html($modalSaveButton.data('original-text'));
            }
        });
    });

    $modal.on('hidden.bs.modal', function (e) {
        $modal.remove();

        //when modal is closed (canceled or success submited) we revert the "+ Add" loading state back to normal.
        var $inlineCreateButtonElement = $(element).parent().find('.inline-create-button');
        $inlineCreateButtonElement.html($inlineCreateButtonElement.data('original-text'));
    });


    $modal.on('shown.bs.modal', function (e) {
        $modal.on('keyup',  function (e) {
        if($modal.is(':visible')) {
            var key = e.which;
                if (key == 13) { //This is an ENTER
                e.preventDefault();
                $modalSaveButton.click();
            }
        }
        return false;
    });
    });
}

//function responsible for adding an option to the select
//it parses any previous options in case of select multiple.
function selectOption(element, option) {
    var $relatedAttribute = element.attr('data-field-attribute');
    var $relatedKeyName = element.attr('data-connected-entity-key-name');
    var $multiple = element.prop('multiple');

    var $optionText = processItemText(option, $relatedAttribute);

    var $option = new Option($optionText, option[$relatedKeyName]);

        $(element).append($option);

        if($multiple) {
            //we get any options previously selected
            var selectedOptions = $(element).val();

            //we add the option to the already selected array.
            selectedOptions.push(option[$relatedKeyName]);
            $(element).val(selectedOptions);

        }else{
            $(element).val(option[$relatedKeyName]);
        }

        $(element).trigger('change');

}



function bpFieldInitFetchOrCreateElement(element) {
    var form = element.closest('form');
    var $inlineField = element.attr('data-is-inline');
    var $ajax = element.attr('data-field-ajax') == 'true' ? true : false;
    var $placeholder = element.attr('data-placeholder');
    var $minimumInputLength = element.attr('data-minimum-input-length');
    var $dataSource = element.attr('data-data-source');
    var $method = element.attr('data-method');
    var $fieldAttribute = element.attr('data-field-attribute');
    var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
    var $includeAllFormFields = element.attr('data-include-all-form-fields')=='false' ? false : true;
    var $dependencies = JSON.parse(element.attr('data-dependencies'));
    var $modelKey = element.attr('data-model-local-key');
    var $allows_null = (element.attr('data-allows-null') == 'true') ? true : false;
    var $selectedOptions = typeof element.attr('data-selected-options') === 'string' ? JSON.parse(element.attr('data-selected-options')) : JSON.parse(null);
    var $multiple = element.prop('multiple');
    var $ajaxDelay = element.attr('data-ajax-delay');

    var FetchOrCreateAjaxFetchSelectedEntry = function (element) {
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

        FetchOrCreateAjaxFetchSelectedEntry(element).then(function(result) {
            result.forEach(function(item) {
                $itemText = processItemText(item, $fieldAttribute);
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

    var selectedOptions = [];
    var $currentValue = $item ? $value : {};
    //we reselect the previously selected options if any.
    Object.entries($currentValue).forEach(function(option) {
        selectedOptions.push(option[0]);
        var $option = new Option(option[1], option[0]);
        $(element).append($option);
    });

    $(element).val(selectedOptions);

    //null is not allowed we fetch some default entry
    if(!$allows_null && !$item && $selectedOptions == null) {
        fetchDefaultEntry(element).then(function(result) {
            $(element).append('<option value="'+result[$modelKey]+'">'+result[$fieldAttribute]+'</option>');
            $(element).val(result[$modelKey]);
            $(element).trigger('change');
        });
    }

    //Checks if field is not beeing inserted in one inline create modal and setup buttons
    if($inlineField == "false") {
        setupInlineCreateButtons(element);
    }

    if (!element.hasClass("select2-hidden-accessible")) {

        element.select2({
        theme: "bootstrap",
        placeholder: $placeholder,
        minimumInputLength: $minimumInputLength,
        allowClear: $allows_null,
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
            //if we have data.data here it means we returned a paginated instance from controller.
            //otherwise we returned one or more entries unpaginated.
            if(data.data) {
                var result = {
                    results: $.map(data.data, function (item) {
                        var $itemText = processItemText(item, $fieldAttribute);

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
                        var $itemText = processItemText(item, $fieldAttribute);

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
        });

        // if any dependencies have been declared
        // when one of those dependencies changes value
        // reset the select2 value
        for (var i=0; i < $dependencies.length; i++) {
            var $dependency = $dependencies[i];
            //if element does not have a custom-selector attribute we use the name attribute
            if(typeof element.attr('data-custom-selector') === 'undefined') {
                form.find('[name="'+$dependency+'"], [name="'+$dependency+'[]"]').change(function(el) {
                        $(element.find('option:not([value=""])')).remove();
                        element.val(null).trigger("change");
                });
            }else{
                // we get the row number and custom selector from where element is called
                let rowNumber = element.attr('data-row-number');
                let selector = element.attr('data-custom-selector');

                // replace in the custom selector string the corresponding row and dependency name to match
                    selector = selector
                        .replaceAll('%DEPENDENCY%', $dependency)
                        .replaceAll('%ROW%', rowNumber);

                $(selector).change(function (el) {
                    $(element.find('option:not([value=""])')).remove();
                    element.val(null).trigger("change");
                });
            }
        }
    }
}


if (typeof processItemText !== 'function') {
    function processItemText(item, $fieldAttribute) {
        var $appLang = '{{ app()->getLocale() }}';
        var $appLangFallback = '{{ Lang::getFallback() }}';
        var $emptyTranslation = '{{ trans("backpack::crud.empty_translations") }}';
        var $itemField = item[$fieldAttribute];

        // try to retreive the item in app language; then fallback language; then first entry; if nothing found empty translation string
        return typeof $itemField === 'object' && $itemField !== null
        ? $itemField[$appLang] ? $itemField[$appLang] : $itemField[$appLangFallback] ? $itemField[$appLangFallback] : Object.values($itemField)[0] ? Object.values($itemField)[0] : $emptyTranslation
            : $itemField;
    }
}
            </script>
        @endpush

    @endif
    {{-- End of Extra CSS and JS --}}
    {{-- ########################################## --}}
