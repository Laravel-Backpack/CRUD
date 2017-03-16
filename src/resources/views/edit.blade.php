@extends('backpack::layout')

@section('header')
  <section class="content-header">
    <h1>
      {{ trans('backpack::crud.edit') }} <span class="text-lowercase">{{ $crud->entity_name }}</span>
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{ url(config('backpack.base.route_prefix'),'dashboard') }}">{{ trans('backpack::crud.admin') }}</a></li>
      <li><a href="{{ url($crud->route) }}" class="text-capitalize">{{ $crud->entity_name_plural }}</a></li>
      <li class="active">{{ trans('backpack::crud.edit') }}</li>
    </ol>
  </section>
@endsection

@section('content')

{!! Form::open(array('url' => $crud->route.'/'.$entry->getKey(), 'method' => 'put', 'files'=>$crud->hasUploadFields('update', $entry->getKey()))) !!}
  @if(view()->exists('vendor.backpack.crud.form_content'))
    @include('vendor.backpack.crud.form_content', ['fields' => $fields, 'entry' => $entry])
  @else
    @include('crud::form_content', ['fields' => $fields, 'entry' => $entry])
  @endif
{!! Form::close() !!}

@endsection

@section('after_styles')
  @stack('crud_fields_styles')
  <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/crud.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/form.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/edit.css') }}">
@endsection

@section('after_scripts')
  @stack('crud_fields_scripts')
  <script src="{{ asset('vendor/backpack/crud/js/crud.js') }}"></script>
  <script src="{{ asset('vendor/backpack/crud/js/form.js') }}"></script>
  <script src="{{ asset('vendor/backpack/crud/js/edit.js') }}"></script>
@endsection
