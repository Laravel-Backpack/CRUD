@extends($request->ajax() ? 'crud::ajax_layout' : 'backpack::layout')
@section('title'){{ trans('backpack::crud.edit') }}@endsection

@section('header')
	<section class="content-header">
	  <h1>
        <span class="text-capitalize">{{ $crud->entity_name_plural }}</span>
        <small>{{ trans('backpack::crud.edit').' '.$crud->entity_name }}.</small>
	  </h1>
	  <ol class="breadcrumb">
	    <li><a href="{{ url(config('backpack.base.route_prefix'),'dashboard') }}">{{ trans('backpack::crud.admin') }}</a></li>
	    <li><a href="{{ url($crud->route) }}" class="text-capitalize">{{ $crud->entity_name_plural }}</a></li>
	    <li class="active">{{ trans('backpack::crud.edit') }}</li>
	  </ol>
	</section>
@endsection

@section('content')
<div class="row">
	<div @if (!$request->ajax())class="col-md-8 col-md-offset-2"@endif>
		<!-- Default box -->
		@if ($crud->hasAccess('list') && !$request->ajax())
			<a href="{{ url($crud->route) }}"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a><br><br>
		@endif

		@include('crud::inc.grouped_errors')

		  <form method="post"
		  		action="{{ url($crud->route.'/'.$entry->getKey()) }}"
				@if ($crud->hasUploadFields('update', $entry->getKey()))
				enctype="multipart/form-data"
				@endif
		  		>
		  {!! csrf_field() !!}
		  {!! method_field('PUT') !!}
		  <div class="box">
			@if (!$request->ajax())
		    <div class="box-header with-border">
		    	@if ($crud->model->translationEnabled())
			    	<!-- Single button -->
					<div class="btn-group pull-right">
					  <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					    {{trans('backpack::crud.language')}}: {{ $crud->model->getAvailableLocales()[$crud->request->input('locale')?$crud->request->input('locale'):App::getLocale()] }} <span class="caret"></span>
					  </button>
					  <ul class="dropdown-menu">
					  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
						  	<li><a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?locale={{ $key }}">{{ $locale }}</a></li>
					  	@endforeach
					  </ul>
					</div>
					<h3 class="box-title" style="line-height: 30px;">@yield('title')</h3>
				@else
					<h3 class="box-title">@yield('title')</h3>
				@endif
		    </div>
		    @endif
		    <div class="box-body row">
		      <!-- load the view from the application if it exists, otherwise load the one in the package -->
		      @if(view()->exists('vendor.backpack.crud.form_content'))
		      	@include('vendor.backpack.crud.form_content', ['fields' => $fields, 'action' => 'edit'])
		      @else
		      	@include('crud::form_content', ['fields' => $fields, 'action' => 'edit'])
		      @endif
		    </div><!-- /.box-body -->

		    <div class="box-footer @if ($request->ajax()) text-right @endif">

                @include('crud::inc.form_save_buttons')

		    </div><!-- /.box-footer-->
		  </div><!-- /.box -->
		  </form>
	</div>
</div>
@endsection
