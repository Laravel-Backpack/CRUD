@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => backpack_url('dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.edit') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
        <small>{!! $crud->getSubheading() ?? trans('backpack::crud.edit').' '.$crud->entity_name !!}.</small>

        @if ($crud->hasAccess('list'))
          <small><a href="{{ url($crud->route) }}" class="d-print-none font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
        @endif
	  </h2>
	</section>
@endsection

@section('content')
<div class="row">
	<div class="{{ $crud->getEditContentClass() }}">
		{{-- Default box --}}

		@include('crud::inc.grouped_errors')

		  <form method="post"
		  		action="{{ url($crud->route.'/'.$entry->getKey()) }}"
				@if ($crud->hasUploadFields('update', $entry->getKey()))
				enctype="multipart/form-data"
				@endif
		  		>
		  {!! csrf_field() !!}
		  {!! method_field('PUT') !!}

		  	@if ($crud->model->translationEnabled())
				@php
					$editLocale = $crud->getRequest()->input('_locale', app()->getLocale());
					$fallbackLocale = app()->getFallbackLocale();
					$translatedAttributes = array_filter($entry->getTranslatableAttributes(), function($attribute) use ($entry, $editLocale) {
						return $entry->getTranslation($attribute, $editLocale, false) ?? false;
					});
					$showTranslationNotice = empty($translatedAttributes) && ! empty($entry->getTranslatableAttributes()) && ! $crud->getRequest()->input('_use_fallback');
		  		@endphp
			  	<div @if($showTranslationNotice) class="mb-2 row" @else class="mb-2 text-right" @endif>
				
				@if($showTranslationNotice)
					<div class="alert text-dark alert-secondary ml-0 col-md-8" style="">{{ trans('backpack::crud.no_attributes_translated') }} {{ $crud->model->getAvailableLocales()[$editLocale]}}. <a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $editLocale }}&_use_fallback=true" class="btn btn-primary btn-sm" role="button">{{trans('backpack::crud.no_attributes_translated_href_text') . $crud->model->getAvailableLocales()[$fallbackLocale]}}</a></div>
				@endif
				<!-- Single button -->
				<div class="btn-group @if($showTranslationNotice) col-md-4 text-right"  style="margin-top:0.8em; display:inline;" @else " @endif>
				  <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				    {{trans('backpack::crud.language')}}: {{ $crud->model->getAvailableLocales()[request()->input('_locale')?request()->input('_locale'):App::getLocale()] }} &nbsp; <span class="caret"></span>
				  </button>
				  <ul class="dropdown-menu">
				  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
					  	<a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $key }}">{{ $locale }}</a>
				  	@endforeach
				  </ul>
				</div>
		    </div>
		    @endif
		      {{-- load the view from the application if it exists, otherwise load the one in the package --}}
		      @if(view()->exists('vendor.backpack.crud.form_content'))
		      	@include('vendor.backpack.crud.form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
		      @else
		      	@include('crud::form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
              @endif
              {{-- This makes sure that all field assets are loaded. --}}
            <div class="d-none" id="parentLoadedAssets">{{ json_encode(Assets::loaded()) }}</div>
            @include('crud::inc.form_save_buttons')
		  </form>
	</div>
</div>
@endsection

