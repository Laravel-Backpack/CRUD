{{-- html5 color input --}}
@php
$value = old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '';
// HTML5 color input requires a valid hex color format (#rrggbb)
// If the value is empty or invalid, default to black (#000000)
if (empty($value) || !preg_match('/^#[0-9a-f]{6}$/i', $value)) {
    $value = $field['default'] ?? '#000000';
}
@endphp


@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    <div class="input-group">
        @if(isset($field['prefix'])) <span class="input-group-text">{!! $field['prefix'] !!}</span> @endif
        <input
            type="text"
            name="{{ $field['name'] }}"
            value="{{ $value }}"
            pattern="#[0-9a-f]{6}"
            maxlength="7"
            data-init-function="bpFieldInitColorElement"
            @include('crud::fields.inc.attributes')
        />
        <span class="input-group-text">
            <input
                type="color"
                value="{{ $value }}"
            />
        </span>
        @if(isset($field['suffix'])) <span class="input-group-text">{!! $field['suffix'] !!}</span> @endif
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')


@push('crud_fields_styles')
    @bassetBlock('backpack/crud/fields/color.css')
    <style>
        [bp-field-type="color"] input[type="color"] {
            background-color: unset;
            border: 0;
            width: 1.8rem;
            height: 1.8rem;
        }
        [bp-field-type="color"] .input-group-text {
            padding: 0 0.4rem;
        }
    </style>
    @endBassetBlock
@endpush


@push('crud_fields_scripts')
    @bassetBlock('backpack/crud/fields/color.js')
    <script>
        function bpFieldInitColorElement(element) {
            let inputText = element[0];
            let inputColor = inputText.nextElementSibling.querySelector('input');

            // Ensure color input has a valid value (HTML5 requires #rrggbb format)
            if (!inputColor.value || !inputColor.value.match(/^#[0-9a-f]{6}$/i)) {
                inputColor.value = '#000000';
            }
            
            // Ensure text input matches color input
            if (!inputText.value || !inputText.value.match(/^#[0-9a-f]{6}$/i)) {
                inputText.value = inputColor.value;
            }

            inputText.addEventListener('input', () => inputText.value = inputColor.value = '#' + inputText.value.replace(/[^\da-f]/gi, '').toLowerCase());
            inputColor.addEventListener('input', () => inputText.value = inputColor.value);
        }
    </script>
    @endBassetBlock
@endpush