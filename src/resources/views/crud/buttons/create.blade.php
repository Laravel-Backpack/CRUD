@if ($crud->hasAccess('create'))
    <a href="{{ url($crud->route.'/create') }}" class="btn btn-primary" data-style="zoom-in">
        <span><i class="la la-plus"></i> <span class="create-button-text-span">{{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span></span>
    </a>
@endif