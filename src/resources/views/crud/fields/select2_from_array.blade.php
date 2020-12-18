@php
    $field['placeholder'] = $field['placeholder'] ?? '-';
    $field['allows_null'] = $field['allows_null'] ?? false;
    $field['allows_multiple'] = $field['allows_multiple'] ?? false;

@endphp
<!-- select2 from array -->
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <select
        name="{{ $field['name'] }}@if (isset($field['allows_multiple']) && $field['allows_multiple']==true)[]@endif"
        style="width: 100%"
        data-real-name="{{ $field['name'] }}"
        data-field-placeholder="{{$field['placeholder']}}"
        data-allows-null="{{var_export($field['allows_null'])}}"
        data-init-function="bpFieldInitSelect2FromArrayElement"
        data-field-multiple="{{var_export($field['allows_multiple'])}}"
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control select2_from_array'])
        @if ($field['allows_multiple'])multiple @endif
        >

        @if($field['allows_null'] && !$field['allows_multiple'])
            <option value="">{{$field['placeholder']}}</option>
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
    <script src="{{ asset('packages/select2/dist/js/i18n/' . app()->getLocale() . '.js') }}"></script>
    @endif
    <script>
         // if nullable, make sure the Clear button uses the translated string
    document.styleSheets[0].addRule('.select2-selection__clear::after','content:  "{{ trans('backpack::crud.clear') }}";');

        function bpFieldInitSelect2FromArrayElement(element) {
            var $placeholder = element.attr('data-field-placeholder');
            var $allows_null = element.attr('data-allows-null') == 'true' ? true : false;
            var $multiple = element.attr('data-field-multiple') == 'true' ? true : false;
            var $real_name = element.attr('data-real-name');
            var $attr_multiple_name = $real_name+'[]';

            //this variable checks if there are any options selected in the multi-select field
            //if there is no options the field will initialize as a single select.
            var $multiple_init = (Array.isArray(element.val()) && element.val().length > 0 && $multiple) ? true : false;

            if (!element.hasClass("select2-hidden-accessible")) {
                    //if we determined that the field has no value, is multiple and allows null
                    //we create the placeholder option
                    if(!$multiple_init && $multiple && $allows_null) {
                        element.append('<option value="" selected></option>');
                    }

                    //if we are initing a single select and null is not allowed, the first option will be selected
                    //so we init this variable to know when selecting that this one was forced
                    if(!$multiple_init && $multiple && !$allows_null) {
                        element.data('force-select', true);
                    }

                    element.select2({
                        theme: "bootstrap",
                        placeholder: $placeholder,
                        allowClear: $allows_null,
                        multiple: $multiple_init
                    }).on('select2:unselect', function (e) {

                        if ($multiple && Array.isArray(element.val()) && element.val().length == 0) {
                            //if there are no options selected we make sure the field name is reverted to single selection
                            //this way browser will send the empty value, otherwise it will omit the multiple input when empty
                            if(typeof element.attr('name') !== typeof undefined && element.attr('name') !== false) {
                                element.attr('name', $real_name);
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
                                });
                                //if the field does not allow null
                                //we make sure that atleast one option is always selected.
                                if(!$allows_null) {
                                    //this variable is used to unselect this option in a multiple select because was "forced" by
                                    //developer not allowing null in the field
                                    element.data('force-select', true);
                                    element.val(element.find('option:eq(0)').val()).trigger('change');
                                }else{
                                    //otherwise we append the placeholder
                                    element.append('<option value="">'+$placeholder+'</option>');
                                    element.val('').trigger('change');
                                }
                            });
                        }
                    }).on('select2:opening', function() {
                            //this prevents the selection from opening upon clearing the field
                            if (element.data('unselecting') === true) {
                                element.data('unselecting', false);
                                return false;
                            }
                            return true;
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
                            if(typeof element.attr('name') !== typeof undefined && element.attr('name') !== false && element.attr('name') !== $attr_multiple_name) {
                                element.attr('name', $attr_multiple_name);
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

                        //when selecting an option, if the one that is selected was forced by developer not allowing null
                        //we remove it from the selection when the user chooses his first option
                        if(element.data('force-select') === true) {
                            element.data('force-select', false);
                            element.val('').trigger('change');
                        }
                    }).on('select2:clear', function(e) {
                        //when clearing the selection we revert the field back to a "select single" state if it's multiple.
                        if($multiple) {

                            if(typeof element.attr('name') !== typeof undefined && element.attr('name') !== false) {
                                element.attr('name', $real_name);
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

                                if($allows_null) {
                                    element.append('<option value="">'+$placeholder+'</option>');
                                    element.val('').trigger('change');
                                }else{
                                    element.data('force-select', true);
                                }

                            });

                        }
                    });


                }
        }
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}