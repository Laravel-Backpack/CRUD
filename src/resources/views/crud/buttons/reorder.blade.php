@if ($crud->get('reorder.enabled') && $crud->hasAccess('reorder'))
  <a href="{{ url($crud->route.'/reorder') }}" class="btn btn-outline-primary" data-style="zoom-in">
    <i class="la la-arrows"></i>
    <span class="{{ config('backpack.crud.operations.list.defaultButtonTextClass.top', '') }}">{{ trans('backpack::crud.reorder') }} {{ $crud->entity_name_plural }}</span>
  </a>
@endif