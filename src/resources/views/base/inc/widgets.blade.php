@if (!empty($widgets))
	@foreach ($widgets as $currentWidget)
		@php
			if (!is_array($currentWidget)) {
				$currentWidget = $currentWidget->toArray();
			}
		@endphp

		@if (isset($currentWidget['viewNamespace']))
			@include($currentWidget['viewNamespace'].'.'.$currentWidget['type'], ['widget' => $currentWidget])
		@else
			@include(backpack_view('widgets.'.$currentWidget['type']), ['widget' => $currentWidget])
		@endif

	@endforeach
@endif
