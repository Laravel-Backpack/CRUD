@if ($crud->hasAccess('create'))
	<a href="{{ url($crud->route.'/create') . "?" . http_build_query($crud->request->query()) }}" class="btn btn-primary ladda-button" data-style="zoom-in"><span class="ladda-label"><i class="fa fa-plus"></i> {{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span></a>
@endif