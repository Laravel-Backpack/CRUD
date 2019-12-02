<!-- select multiple -->
@php
    if (!isset($field['options'])) {
        $options = $field['model']::all();
    } else {
        $options = call_user_func($field['options'], $field['model']::query());
    }
@endphp

<div @include('crud::inc.field_wrapper_attributes') >

    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')

    <select
    	class="form-control"
        name="{{ $field['name'] }}[]"
        @include('crud::inc.field_attributes')
    	multiple>

		@if (!isset($field['allows_null']) || $field['allows_null'])
			<option value="">-</option>
		@endif

    	@if (count($options))
    		@foreach ($options as $option)
				@if( (old(square_brackets_to_dots($field["name"])) && in_array($option->getRouteKey(), old(square_brackets_to_dots($field["name"])))) || (is_null(old(square_brackets_to_dots($field["name"]))) && isset($field['value']) && in_array($option->getRouteKey(), $field['value']->pluck($option->getRouteKeyName(), $option->getRouteKeyName())->toArray())))
					<option value="{{ $option->getRouteKey() }}" selected>{{ $option->{$field['attribute']} }}</option>
				@else
					<option value="{{ $option->getRouteKey() }}">{{ $option->{$field['attribute']} }}</option>
				@endif
    		@endforeach
    	@endif

	</select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>
