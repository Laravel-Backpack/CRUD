@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => url($crud->route),
    'Report' => false,
  ];

  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <div class="container-fluid d-flex justify-content-between my-3">
        <section class="header-operation animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
            <h1 class="text-capitalize mb-0" bp-section="page-heading">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</h1>
            <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">{!! $crud->getSubheading() ?? $title !!}</p>
            @if ($crud->hasAccess('list'))
                <p class="ms-2 ml-2 mb-0" bp-section="page-subheading-back-button">
                    <small><a href="{{ url($crud->route) }}" class="font-sm"><i class="la la-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
                </p>
            @endif
        </section>
    </div>
@endsection

@section('content')
<div class="row" bp-section="crud-operation-report">
    <div class="{{ $crud->getOperationSetting('contentClass') ?? 'col-md-12' }}">

        {{-- Filters --}}
        @if ($crud->filtersEnabled())
            @include('crud::inc.filters_navbar')
        @endif

        {{-- Metrics grid --}}
        <div class="row" id="report-metrics" data-report-url="{{ url($crud->route.'/report/metric-data') }}">
            @foreach ($metrics as $metric)
                @php
                    $wrapper = $metric->getWrapper();
                    // Ensure sensible defaults
                    $wrapper['class'] = ($wrapper['class'] ?? 'col-md-6') . ' mb-3';
                    // Add metric group data attribute if grouped
                    if ($metric->group) {
                        $wrapper['data-metric-group'] = $metric->group;
                    }
                @endphp
                <div @foreach($wrapper as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>
                    @include('crud::metrics.'.$metric->type, ['metric' => $metric])
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection

@push('after_scripts')
    @include('crud::metrics.inc.report_scripts')
@endpush
