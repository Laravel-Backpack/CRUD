@if ($crud->reorder)
	@if ($crud->hasAccess('reorder'))
	  <a href="{{ url($crud->route.'/reorder') . "?" . http_build_query($crud->request->query()) }}" class="btn btn-default ladda-button" data-style="zoom-in"><span class="ladda-label"><i class="fa fa-arrows"></i> {{ trans('backpack::crud.reorder') }} {{ $crud->entity_name_plural }}</span></a>
	@endif
@endif