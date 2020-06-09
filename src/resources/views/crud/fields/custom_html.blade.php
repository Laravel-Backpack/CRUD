<!-- used for heading, separators, etc -->
@php
    if(is_null(old(square_brackets_to_dots($field['name']))) && !empty(session()->getOldInput())) {
        $field['value'] = '';
    }
@endphp
@include('crud::fields.inc.wrapper_start')
	{!! $field['value'] !!}
@include('crud::fields.inc.wrapper_end')
