@if ($crud->hasAccess('create'))
    {{-- Regular create button that redirects to create page --}}
    <a href="{{ url($crud->route.'/create') }}" class="btn btn-primary" bp-button="create" data-style="zoom-in">
        <i class="la la-plus"></i> <span>{{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span>
    </a>
@endif