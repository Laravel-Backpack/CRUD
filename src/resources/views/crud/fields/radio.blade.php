<!-- radio -->
@php
    $optionValue = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';


    // check if attribute is casted, if it is, we get back un-casted values
    if(Arr::get($crud->model->getCasts(), $field['name']) === 'boolean') {
        $optionValue = (int) $optionValue;
    }

    // if the class isn't overwritten, use 'radio'
    if (!isset($field['attributes']['class'])) {
        $field['attributes']['class'] = 'radio';
    }

    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitRadioElement';
@endphp

@include('crud::fields.inc.wrapper_start')

    <div>
        <label>{!! $field['label'] !!}</label>
        @include('crud::fields.inc.translatable_icon')
    </div>

    <input type="hidden" value="{{ $optionValue }}" name="{{$field['name']}}" />

    @if( isset($field['options']) && $field['options'] = (array)$field['options'] )

        @foreach ($field['options'] as $value => $label )

            <div class="form-check {{ isset($field['inline']) && $field['inline'] ? 'form-check-inline' : '' }}">
                <input  type="radio"
                        class="form-check-input"
                        @if ($field['toggle'] ?? false)
                        data-field-name="{{$field['name']}}"
                        data-field-toggle="{{ json_encode($field['hide_when'][$value] ?? []) }}"
                        @endif
                        value="{{$value}}"
                        @include('crud::fields.inc.attributes')
                        >
                <label class="{{ isset($field['inline']) && $field['inline'] ? 'radio-inline' : '' }} form-check-label font-weight-normal">{!! $label !!}</label>
            </div>

        @endforeach

    @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

@include('crud::fields.inc.wrapper_end')

@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script>
        function bpFieldInitRadioElement(element) {
            var hiddenInput = element.find('input[type=hidden]');
            var value = hiddenInput.val();
            var id = 'radio_'+Math.floor(Math.random() * 1000000);

            // set unique IDs so that labels are correlated with inputs
            element.find('.form-check input[type=radio]').each(function(index, item) {
                $(this).attr('id', id+index);
                $(this).siblings('label').attr('for', id+index);
            });

            // when one radio input is selected
            element.find('input[type=radio]').change(function(event) {
                // the value gets updated in the hidden input
                hiddenInput.val($(this).val());
                // all other radios get unchecked
                element.find('input[type=radio]').not(this).prop('checked', false);
            });

            // select the right radios
            element.find('input[type=radio][value="'+value+'"]').prop('checked', true);

            // If this is a toggleable radio element, then we want to hide/show whatever should be hidden/shown
            window.hiddenFields = window.hiddenFields || {};
            var toggle = function( $radio ){
                let hideWhen = $radio.data('field-toggle'),
                fieldName = $radio.data('field-name');
                hiddenFields[ fieldName ] = hiddenFields[ fieldName ] || [];
                if( Object.keys(hiddenFields[ fieldName ]).length ){
                    $.each(hiddenFields[ fieldName ], function(idx, field){
                        field.data('hide_count', field.data('hide_count') - 1);
                        if (field.data('hide_count') == 0) {
                            field.show();
                        }
                    });
                    hiddenFields[ fieldName ] = [];
                }
                if( hideWhen.length ){
                    $.each(hideWhen, function(idx, name){
                        var f = $('[name="'+name+'"]').parents('.form-group');
                        if( f.length ){
                            if (f.data('hide_count')) {
                                f.data('hide_count', f.data('hide_count') + 1);
                            } else {
                                f.data('hide_count', 1);
                            }
                            hiddenFields[ fieldName ].push(f);
                            f.hide();
                        }
                    });
                }
            };
            $('input[data-field-toggle]').on('change', function(){
                return toggle( $(this) );
            });
            $('input[data-field-toggle]:checked').each(function(){
                return toggle( $(this) );
            });
        }
    </script>
    @endpush

@endif
