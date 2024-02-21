@if ($crud->get('reorder.enabled') && $crud->hasAccess('reorder'))
    <a href="{{ url($crud->route.'/reorder') }}" class="btn btn-outline-primary" data-style="zoom-in">
        <span><i class="la la-arrows"></i> <span class="reorder-button-text-span">{{ trans('backpack::crud.reorder') }} {{ $crud->entity_name_plural }}</span></span>
    </a>
@endif