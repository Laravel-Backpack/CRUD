@if ($crud->hasAccess('show'))
	@if (!$crud->model->translationEnabled())
	{{-- Single show button --}}
	<a href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}" class="btn btn-sm btn-link" title="{{ trans('backpack::crud.preview') }}">
		<i class="la la-eye"></i>
		<span class="{{ config('backpack.crud.operations.list.defaultButtonTextClass.line', '') }}">{{ trans('backpack::crud.preview') }}</span>
	</a>
	@else

	{{-- Show button group --}}
	<div class="btn-group">
	  <a href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}" class="btn btn-sm btn-link pr-0" title="{{ trans('backpack::crud.preview') }}">
		<i class="la la-eye"></i>
		<span class="{{ config('backpack.crud.operations.list.defaultButtonTextClass.line', '') }}">{{ trans('backpack::crud.preview') }}</span>
	  </a>
	  <a class="btn btn-sm btn-link dropdown-toggle text-primary pl-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    <span class="caret"></span>
	  </a>
	  <ul class="dropdown-menu dropdown-menu-right">
  	    <li class="dropdown-header">{{ trans('backpack::crud.preview') }}:</li>
	  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
		  	<a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}?locale={{ $key }}">{{ $locale }}</a>
	  	@endforeach
	  </ul>
	</div>

	@endif
@endif