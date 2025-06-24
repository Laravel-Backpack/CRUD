@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_start'))
  <div class="{{ $widget['class'] ?? 'card' }}">
    @if (isset($widget['content']['header']))
    <div class="card-header">
        <div class="card-title mb-0">{!! $widget['content']['header'] !!}</div>
    </div>
    @endif
    <div class="card-body">

      {!! $widget['content']['body'] ?? '' !!}

      <div class="card-wrapper form-widget-wrapper">
        <x-backpack::dataform :controller="$widget['controller']" :operation="$widget['operation']" :entry="$widget['entry'] ?? null"  />
      </div>

    </div>
  </div>
@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_end'))