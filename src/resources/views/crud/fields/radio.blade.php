{{-- radio --}}
@php
    $optionValue = old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '';

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


        <label class="d-block">{!! $field['label'] !!}</label>
        @include('crud::fields.inc.translatable_icon')


    <input type="hidden" value="{{ $optionValue }}" name="{{$field['name']}}" />

    @if( isset($field['options']) && $field['options'] = (array)$field['options'] )

        @foreach ($field['options'] as $value => $label )

            <div class="form-check {{ isset($field['inline']) && $field['inline'] ? 'form-check-inline' : '' }}">
                <input  type="radio"
                        class="form-check-input"
                        value="{{$value}}"
                        @include('crud::fields.inc.attributes')
                        >
                <label class="{{ isset($field['inline']) && $field['inline'] ? 'radio-inline' : '' }} form-check-label font-weight-normal p-0">{!! $label !!}</label>
            </div>

        @endforeach

    @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}

{{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
    @bassetBlock('backpack/crud/fields/radio-field.js')
    <script>
        function bpFieldInitRadioElement(element) {
            var wrapper = element[0];
            var hiddenInput = wrapper.querySelector('input[type=hidden]');
            var value = hiddenInput.value;
            var id = 'radio_'+Math.floor(Math.random() * 1000000);
            var radioInputs = wrapper.querySelectorAll('.form-check input[type=radio]');

            // set unique IDs so that labels are correlated with inputs
            radioInputs.forEach(function(item, index) {
                item.setAttribute('id', id+index);
                var label = item.closest('.form-check')?.querySelector('label');
                if (label) label.setAttribute('for', id+index);
            });

            hiddenInput.addEventListener('CrudField:disable', function(e) {
                radioInputs.forEach(function(item) {
                    item.disabled = true;
                });
            });

            hiddenInput.addEventListener('CrudField:enable', function(e) {
                radioInputs.forEach(function(item) {
                    item.removeAttribute('disabled');
                });
            });

            // when one radio input is selected
            radioInputs.forEach(function(radio) {
                radio.addEventListener('change', function(event) {
                    // the value gets updated in the hidden input and the 'change' event is fired
                    hiddenInput.value = event.target.value;
                    hiddenInput.dispatchEvent(new Event('change'));
                    // all other radios get unchecked
                    radioInputs.forEach(function(r) {
                        if (r !== event.target) r.checked = false;
                    });
                });
            });

            // select the right radio
            radioInputs.forEach(function(r) {
                if (r.value === value) r.checked = true;
            });
        }
    </script>
    @endBassetBlock
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
