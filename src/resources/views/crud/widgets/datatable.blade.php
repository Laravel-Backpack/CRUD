@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_start'))
  <div class="{{ $widget['class'] ?? 'card' }}">
    @if (isset($widget['content']['header']))
    <div class="card-header">
        <div class="card-title mb-0">{!! $widget['content']['header'] !!}</div>
    </div>
    @endif
    <div class="card-body">

      {!! $widget['content']['body'] ?? '' !!}

      <div class="card-wrapper datatable-widget-wrapper">
        <x-backpack::datatable :controller="$widget['controller']" :setup="$widget['setup'] ?? null" :modifiesUrl="false" :name="$widget['name']" />
      </div>

    </div>
  </div>
@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_end'))