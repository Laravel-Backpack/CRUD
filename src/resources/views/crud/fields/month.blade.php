<!-- html5 month input -->
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <input
        type="month"
        name="{{ $field['name'] }}"
        value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
        @include('crud::fields.inc.attributes')
        >

    {{-- HINT --}}
    @if (isset($field['hint']))
        <small class="text-muted d-block">{!! $field['hint'] !!}</small>
    @endif
@include('crud::fields.inc.wrapper_end')