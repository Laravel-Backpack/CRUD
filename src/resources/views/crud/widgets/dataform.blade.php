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
        <x-backpack::dataform 
        :controller="$widget['controller']" 
        :formOperation="$widget['formOperation']" 
        :entry="$widget['entry'] ?? null"
        :setup="$widget['setup'] ?? null"
        :formUrl="$widget['formUrl'] ?? null"
        :formAction="$widget['formAction'] ?? null"
        :formMethod="$widget['formMethod'] ?? null"
        :focusOnFirstField="$widget['focusOnFirstField'] ?? false"
        :hasUploadFields="$widget['hasUploadFields'] ?? false"
        />
      </div>

    </div>
  </div>
@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_end'))