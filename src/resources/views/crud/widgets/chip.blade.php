@php
	// preserve backwards compatibility with Widgets in Backpack 4.0
	$widget['wrapper']['class'] = $widget['wrapper']['class'] ?? $widget['wrapperClass'] ?? 'col';
@endphp

@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_start'))

    @if(!empty($widget['title']))
    <h4 class="mt-4 mb-2">{{ $widget['title'] }}</h4>
    @endif

	<div class="{{ $widget['class'] ?? 'card' }}">
        <div class="card-body">
            @include($widget['view'], ['entry' => $widget['entry']])
        </div>
	</div>
@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_end'))
