@if ($crud->hasAccess('show'))
	@if (!$crud->model->translationEnabled())

	<!-- Single edit button -->
	<a href="{{ url($crud->route.'/'.$entry->getRouteKey().'/show') }}" class="btn btn-sm btn-link"><i class="fa fa-eye"></i> {{ trans('backpack::crud.preview') }}</a>

	@else

	<!-- Edit button group -->
	<div class="btn-group">
	  <a href="{{ url($crud->route.'/'.$entry->getRouteKey().'/show') }}" class="btn btn-sm btn-link pr-0"><i class="fa fa-eye"></i> {{ trans('backpack::crud.preview') }}</a>
	  <a class="btn btn-sm btn-link dropdown-toggle text-primary pl-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    <span class="caret"></span>
	  </a>
	  <ul class="dropdown-menu dropdown-menu-right">
  	    <li class="dropdown-header">{{ trans('backpack::crud.preview') }}:</li>
	  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
		  	<a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getRouteKey().'/show') }}?locale={{ $key }}">{{ $locale }}</a>
	  	@endforeach
	  </ul>
	</div>

	@endif
@endif