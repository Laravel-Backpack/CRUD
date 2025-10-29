<input type="hidden" name="_http_referrer" value="{{ old('_http_referrer') ?? \URL::previous() ?? url($crud->route) }}">
<input type="hidden" name="_form_id" value="{{ $formId ?? 'crudForm' }}">

{{-- See if we're using tabs --}}
@if ($crud->tabsEnabled() && count($crud->getTabs()))
    @include('crud::inc.show_tabbed_fields', ['fields' => $crud->fields()])
    <input type="hidden" name="_current_tab" value="{{ Str::slug($crud->getTabs()[0], '') }}" />
@else
  <div class="card">
    <div class="card-body row">
      @include('crud::inc.show_fields', ['fields' => $crud->fields()])
    </div>
  </div>
@endif

@foreach (app('widgets')->toArray() as $currentWidget)
@php
    $currentWidget = \Backpack\CRUD\app\Library\Widget::add($currentWidget);
@endphp
    @if($currentWidget->getAttribute('inline'))
        @include($currentWidget->getFinalViewPath(), ['widget' => $currentWidget->toArray()])
    @endif
@endforeach


@push('before_scripts')
  @include('crud::inc.form_fields_script')
@endpush
