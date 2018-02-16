@if ($crud->hasAccess('create'))
	<a href="{{ url($crud->route.'/create') }}" class="btn btn-primary ladda-button" data-style="zoom-in" @if ($crud->ajax_crud)data-toggle="modal" data-target="#modal-ajax-crud"@endif><span class="ladda-label"><i class="fa fa-plus"></i> {{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span></a>
@endif