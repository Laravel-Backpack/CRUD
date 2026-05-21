@if ($crud->hasAccess('show', $entry))
	@php
		$backToAllEntriesUrl = $crud->getOperationSetting('backToAllEntriesUrl');
		$showUrl = url($crud->route.'/'.$entry->getKey().'/show') . ($backToAllEntriesUrl ? '?_backToAllEntriesUrl='.urlencode($backToAllEntriesUrl) : '');
	@endphp
	@if (!$crud->model->translationEnabled() || $crud->getOperationSetting('showLanguagesDirectlyInShowButton') === false)

	{{-- Single edit button --}}
	<a href="{{ $showUrl }}" bp-button="show" class="btn btn-sm btn-link">
		<i class="la la-eye"></i> <span>{{ trans('backpack::crud.preview') }}</span>
	</a>

	@else

	{{-- show button group --}}
	<div class="btn-group">
	  <a href="{{ $showUrl }}" class="btn btn-sm btn-link pr-0">
	  	<span><i class="la la-eye"></i> {{ trans('backpack::crud.preview') }}</span>
	  </a>
	  <a class="btn btn-sm btn-link dropdown-toggle text-primary pl-1" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    <span class="caret"></span>
	  </a>
	  <ul class="dropdown-menu dropdown-menu-right">
  	    <li class="dropdown-header">{{ trans('backpack::crud.preview') }}:</li>
	  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
		  	<a class="dropdown-item" href="{{ $showUrl.(str_contains($showUrl, '?') ? '&' : '?').'_locale='.$key }}">{{ $locale }}</a>
	  	@endforeach
	  </ul>
	</div>

	@endif
@endif
