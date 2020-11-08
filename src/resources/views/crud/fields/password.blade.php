<!-- password -->

@php
    // autocomplete off, if not otherwise specified
    if (!isset($field['attributes']['autocomplete'])) {
        $field['attributes']['autocomplete'] = "off";
    }
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <input
    	type="password"
    	name="{{ $field['name'] }}"
        @include('crud::fields.inc.attributes')
    	>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <small class="text-muted d-block">{!! $field['hint'] !!}</small>
    @endif
@include('crud::fields.inc.wrapper_end')
