@php
    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();
    $field['multiple'] = $field['multiple'] ?? $crud->relationAllowsMultiple($field['relation_type']);
    $field['attribute'] = $field['attribute'] ?? $connected_entity->identifiableAttribute();
    $field['include_all_form_fields'] = $field['include_all_form_fields'] ?? true;
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
    // Note: isColumnNullable returns true if column is nullable in database, also true if column does not exist.

    if (!isset($field['options'])) {
            $field['options'] = $connected_entity::all()->pluck($field['attribute'],$connected_entity_key_name);
        } else {
            $field['options'] = call_user_func($field['options'], $field['model']::query())->pluck($field['attribute'],$connected_entity_key_name);
    }

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
                    $current_value = $current_value
                                    ->pluck($field['attribute'], $connected_entity_key_name);
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
        data-single-name="{{$field['name']}}"
        data-init-function="bpFieldInitRelationshipSelectElement"
        data-column-nullable="{{ var_export($field['allows_null']) }}"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(Arr::wrap($field['dependencies'])): json_encode([]) }}"
        data-model-local-key="{{$crud->model->getKeyName()}}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-include-all-form-fields="{{ var_export($field['include_all_form_fields']) }}"
        data-current-value="{{ $field['value'] }}"
        data-field-multiple="{{var_export($field['multiple'])}}"
        data-language="{{ str_replace('_', '-', app()->getLocale()) }}"

        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control'])

        @if($field['multiple'])
        multiple
        @endif
        >
        <option value=""></option>
        @if (count($field['options']))
            @foreach ($field['options'] as $key => $option)
                    <option value="{{ $key }}">{{ $option }}</option>
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
    @endpush



<!-- include field specific select2 js-->
@push('crud_fields_scripts')
<script>
    // if nullable, make sure the Clear button uses the translated string
    document.styleSheets[0].addRule('.select2-selection__clear::after','content:  "{{ trans('backpack::crud.clear') }}";');


    /**
     *
     * This method gets called automatically by Backpack:
     *
     * @param  node element The jQuery-wrapped "select" element.
     * @return void
     */
    function bpFieldInitRelationshipSelectElement(element) {
        var form = element.closest('form');
        var $placeholder = element.attr('data-placeholder');
        var $modelKey = element.attr('data-model-local-key');
        var $fieldAttribute = element.attr('data-field-attribute');
        var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
        var $includeAllFormFields = element.attr('data-include-all-form-fields') == 'false' ? false : true;
        var $dependencies = JSON.parse(element.attr('data-dependencies'));
        var $multiple = element.attr('data-field-multiple')  == 'false' ? false : true;
        var $selectedOptions = typeof element.attr('data-selected-options') === 'string' ? JSON.parse(element.attr('data-selected-options')) : JSON.parse(null);
        var $allows_null = (element.attr('data-column-nullable') == 'true') ? true : false;


        var $single_name = element.attr('data-single-name');
        var $multiple_name = $single_name+'[]';


        var $item = false;

        var $value = JSON.parse(element.attr('data-current-value'))

        if(Object.keys($value).length > 0) {
            $item = true;
        }

        var selectedOptions = [];
        var $currentValue = $item ? $value : '';

        for (const [key, value] of Object.entries($currentValue)) {
            selectedOptions.push(key);
            $(element).val(selectedOptions);
        }

        //this variable checks if there are any options selected in the multi-select field
        //if there is no options the field will initialize as a single select.
        var $multiple_init = (Array.isArray(element.val()) && element.val().length > 0 && $multiple) ? true : false;

        $(element).attr('data-current-value',$(element).val());

        $(element).trigger('change');

        var $select2Settings = {
                theme: 'bootstrap',
                multiple: $multiple_init,
                placeholder: $placeholder,
                allowClear: $multiple_init ? true : false,
        };

        if (!$(element).hasClass("select2-hidden-accessible"))
        {
            $(element).select2($select2Settings).on('select2:unselect', function (e) {
                if ($multiple && Array.isArray(element.val()) && element.val().length == 0) {
                    //if there are no options selected we make sure the field name is reverted to single selection
                    //this way browser will send the empty value, otherwise it will omit the multiple input when empty
                    if(typeof element.attr('name') !== typeof undefined) {
                        element.attr('name', $single_name);
                    }else{
                        element.attr('data-repeatable-input-name', $single_name)
                    }
                    //we also change the multiple attribute from field
                    element.attr('multiple',false);
                    //we destroy the current select
                    setTimeout(function() {
                        element.select2('destroy');
                    });

                    //we reinitialize the select as a single select
                    setTimeout(function() {
                        element.select2({
                            theme: "bootstrap",
                            placeholder: $placeholder,
                            allowClear: false,
                            multiple: false
                        })
                    });
                    element.append('<option value=""></option>');
                    element.val(null).trigger('change');
                }
            }).on('select2:unselecting', function(e) {
                //we set a variable in the field that indicates that an unselecting operation is running
                //we will read this variable in the opening event to determine if we should open the options
                element.data('unselecting',true);
                return true;
            }).on('select2:selecting', function(e) {
                //when we select an option, if the element does not have the multiple attribute
                //but is indeed a multiple field, we know that this happened because we setup a single select while there is no selection
                //and now that user selected atleast one option we will make it multiple again.
                //the reason for this is because multiple selects are not sent by browser in request when empty
                //making it a single select when empty, will, send the value empty in request.
                if(typeof element.attr('multiple') === typeof undefined && $multiple) {
                    //set the element attribute multiple back to true
                    element.attr('multiple',true);
                    //revert the name to array
                    if(typeof element.attr('name') !== typeof undefined) {
                        element.attr('name', $multiple_name);
                    }else{
                        element.attr('data-repeatable-input-name', $multiple_name)
                    }
                    setTimeout(function() {
                        element.select2('destroy');
                    });

                    //we remove the placeholder option
                    $(element.find('option[value=""]')).remove();

                    setTimeout(function() {
                        element.select2({
                            theme: "bootstrap",
                            placeholder: $placeholder,
                            allowClear: true,
                            multiple: true
                        });
                    });
                }
            }).on('select2:clear', function(e) {
                //when clearing the selection we revert the field back to a "select single" state if it's multiple.
                if($multiple) {
                    if(typeof element.attr('name') !== typeof undefined) {
                        element.attr('name', $single_name);
                    }else{
                        element.attr('data-repeatable-input-name', $single_name)
                    }
                    element.attr('multiple',false);

                    setTimeout(function() {
                        element.select2('destroy');
                    });

                    setTimeout(function() {
                        element.select2({
                            theme: "bootstrap",
                            placeholder: $placeholder,
                            allowClear: false,
                            multiple: false
                        });

                    });

                    element.append('<option value=""></option>');
                    element.val(null).trigger('change');
                }
            }).on('select2:opening', function() {
                //this prevents the selection from opening upon clearing the field
                if (element.data('unselecting') === true) {
                    element.data('unselecting', false);
                    return false;
                }
                return true;
            });
        }
    }
</script>
@endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
