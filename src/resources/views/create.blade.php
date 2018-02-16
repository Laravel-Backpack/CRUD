@extends($request->ajax() ? 'crud::ajax_layout' : 'backpack::layout')
@section('title'){{ trans('backpack::crud.add_a_new') }} {{ $crud->entity_name }}@endsection

@section('header')
	<section class="content-header">
	  <h1>
        <span class="text-capitalize">{{ $crud->entity_name_plural }}</span>
        <small>{{ trans('backpack::crud.add').' '.$crud->entity_name }}.</small>
	  </h1>
	  <ol class="breadcrumb">
	    <li><a href="{{ url(config('backpack.base.route_prefix'), 'dashboard') }}">{{ trans('backpack::crud.admin') }}</a></li>
	    <li><a href="{{ url($crud->route) }}" class="text-capitalize">{{ $crud->entity_name_plural }}</a></li>
	    <li class="active">{{ trans('backpack::crud.add') }}</li>
	  </ol>
	</section>
@endsection

@section('content')
<div class="row">
	<div @if (!$request->ajax())class="col-md-8 col-md-offset-2"@endif>
		<!-- Default box -->
		@if ($crud->hasAccess('list') AND !$request->ajax())
			<a href="{{ url($crud->route) }}"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a><br><br>
		@endif

		@include('crud::inc.grouped_errors')

		  <form method="post"
		  		action="{{ url($crud->route) }}"
				@if ($crud->hasUploadFields('create'))
				enctype="multipart/form-data"
				@endif
		  		>
		  {!! csrf_field() !!}
		  <div class="box">

			@if (!$request->ajax())
		    <div class="box-header with-border">
		      <h3 class="box-title">@yield('title')</h3>
		    </div>
			@endif
		    <div class="box-body row">
		      <!-- load the view from the application if it exists, otherwise load the one in the package -->
		      @if(view()->exists('vendor.backpack.crud.form_content'))
		      	@include('vendor.backpack.crud.form_content', [ 'fields' => $crud->getFields('create'), 'action' => 'create' ])
		      @else
		      	@include('crud::form_content', [ 'fields' => $crud->getFields('create'), 'action' => 'create' ])
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
