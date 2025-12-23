@php
  // Define the table ID - use the provided tableId or default to 'crudTable'
  $tableId = $tableId ?? 'crudTable';
  $fixedHeader = $useFixedHeader ?? $crud->getOperationSetting('useFixedHeader') ?? true;
@endphp
<section class="header-operation datatable-header animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
          <h1 class="text-capitalize mb-0" bp-section="page-heading">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</h1>
          <p class="ms-2 ml-2 mb-0" id="datatable_info_stack_{{$tableId}}" bp-section="page-subheading">{!! $crud->getSubheading() ?? '' !!}</p>
        </section>
<div class="row mb-2 align-items-center">
  <div class="col-sm-9">
    @if ( $crud->buttons()->where('stack', 'top')->count() ||  $crud->exportButtons())
      <div class="d-print-none {{ $crud->hasAccess('create')?'with-border':'' }} top_buttons_{{$tableId}}">
        @include('crud::inc.button_stack', ['stack' => 'top', 'crudTableId' => $tableId])
      </div>
    @endif
  </div>
  @if($crud->getOperationSetting('searchableTable'))
  <div class="col-sm-3">
    <div id="datatable_search_stack_{{ $tableId }}" class="mt-sm-0 mt-2 d-print-none datatable_search_stack">
      <div class="input-icon">
        <span class="input-icon-addon">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>
        </span>
        <input type="search" class="form-control datatable-search-input" data-table-id="{{ $tableId }}" placeholder="{{ trans('backpack::crud.search') }}..."/>
      </div>
    </div>
  </div>
  @endif
</div>

{{-- Backpack List Filters --}}
@if ($crud->filtersEnabled())
  @include('crud::inc.filters_navbar', ['componentId' => $tableId])
@endif
<div class="{{ backpack_theme_config('classes.tableWrapper') }}">
  <table
      id="{{ $tableId }}"
      class="{{ backpack_theme_config('classes.table') ?? 'table table-striped table-hover nowrap rounded card-table table-vcenter card d-table shadow-xs border-xs' }} crud-table"
      data-use-fixed-header="{{ $fixedHeader ? 'true' : 'false' }}"
      data-responsive-table="{{ (int) $crud->getOperationSetting('responsiveTable') }}"
      data-has-details-row="{{ (int) $crud->getOperationSetting('detailsRow') }}"
      data-has-bulk-actions="{{ (int) $crud->getOperationSetting('bulkActions') }}"
      data-has-line-buttons-as-dropdown="{{ (int) $crud->getOperationSetting('lineButtonsAsDropdown') }}"
      data-line-buttons-as-dropdown-minimum="{{ (int) $crud->getOperationSetting('lineButtonsAsDropdownMinimum') }}"
      data-line-buttons-as-dropdown-show-before-dropdown="{{ (int) $crud->getOperationSetting('lineButtonsAsDropdownShowBefore') }}"
      data-url-start="{{ $datatablesUrl }}"
      data-responsive-table="{{ $crud->getResponsiveTable() ? 'true' : 'false' }}"
      data-persistent-table="{{ $crud->getPersistentTable() ? 'true' : 'false' }}"
      data-persistent-table-slug="{{ Str::slug($crud->getOperationSetting('datatablesUrl')) }}"
      data-persistent-table-duration="{{ $crud->getPersistentTableDuration() ?: '' }}"
      data-subheading="{{ $crud->getSubheading() ? 'true' : 'false' }}"
      data-reset-button="{{ ($crud->getOperationSetting('resetButton') ?? true) ? 'true' : 'false' }}"
      data-modifies-url="{{ ($modifiesUrl ?? false) ? 'true' : 'false' }}"
      data-has-export-buttons="{{ var_export($crud->get('list.exportButtons') ?? false) }}"
      data-default-page-length="{{ $crud->getDefaultPageLength() }}"
      data-page-length-menu="{{ json_encode($crud->getPageLengthMenu()) }}"
      data-show-entry-count="{{ var_export($crud->getOperationSetting('showEntryCount') ?? true) }}"
      data-searchable-table="{{ var_export($crud->getOperationSetting('searchableTable') ?? true) }}"
      data-search-delay="{{ $crud->getOperationSetting('searchDelay') ?? 500 }}"
      data-total-entry-count="{{ var_export($crud->getOperationSetting('totalEntryCount') ?? false) }}"
      cellspacing="0">
    <thead>
      <tr>
        {{-- Table columns --}}
        @foreach ($crud->columns() as $column)
          @php
          $exportOnlyColumn = $column['exportOnlyColumn'] ?? false;
          $visibleInTable = $column['visibleInTable'] ?? ($exportOnlyColumn ? false : true);
          $visibleInModal = $column['visibleInModal'] ?? ($exportOnlyColumn ? false : true);
          $visibleInExport = $column['visibleInExport'] ?? true;
          $forceExport = $column['forceExport'] ?? (isset($column['exportOnlyColumn']) ? true : false);
          @endphp
          <th
            data-orderable="{{ var_export($column['orderable'], true) }}"
            data-priority="{{ $column['priority'] }}"
            data-column-name="{{ $column['name'] }}"
            {{--
            data-visible-in-table => if developer forced column to be in the table with 'visibleInTable => true'
            data-visible => regular visibility of the column
            data-can-be-visible-in-table => prevents the column to be visible into the table (export-only)
            data-visible-in-modal => if column appears on responsive modal
            data-visible-in-export => if this column is exportable
            data-force-export => force export even if columns are hidden
            --}}

            data-visible="{{ $exportOnlyColumn ? 'false' : var_export($visibleInTable) }}"
            data-visible-in-table="{{ var_export($visibleInTable) }}"
            data-can-be-visible-in-table="{{ $exportOnlyColumn ? 'false' : 'true' }}"
            data-visible-in-modal="{{ var_export($visibleInModal) }}"
            data-visible-in-export="{{ $exportOnlyColumn ? 'true' : ($visibleInExport ? 'true' : 'false') }}"
            data-force-export="{{ var_export($forceExport) }}"
          >
            {{-- Bulk checkbox --}}
            @if($loop->first && $crud->getOperationSetting('bulkActions'))
                {!! View::make('crud::columns.inc.bulk_actions_checkbox')->render() !!}
            @endif
            {!! $column['label'] !!}
          </th>
        @endforeach

        @if ( $crud->buttons()->where('stack', 'line')->count() )
          <th data-orderable="false"
              data-priority="{{ $crud->getActionsColumnPriority() }}"
              data-visible-in-export="false"
              data-action-column="true"
              >{{ trans('backpack::crud.actions') }}</th>
        @endif
      </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
      <tr>
        {{-- Table columns --}}
        @foreach ($crud->columns() as $column)
          <th>
            {{-- Bulk checkbox --}}
            @if($loop->first && $crud->getOperationSetting('bulkActions'))
                {!! View::make('crud::columns.inc.bulk_actions_checkbox')->render() !!}
            @endif
            {!! $column['label'] !!}
          </th>
        @endforeach

        @if ( $crud->buttons()->where('stack', 'line')->count() )
          <th>{{ trans('backpack::crud.actions') }}</th>
        @endif
      </tr>
    </tfoot>
  </table>
</div>

@if ( $crud->buttons()->where('stack', 'bottom')->count() )
    <div id="bottom_buttons_{{$tableId}}" class="bottom_buttons d-print-none text-sm-left">
        @include('crud::inc.button_stack', ['stack' => 'bottom', 'crudTableId' => $tableId])
        <div id="datatable_button_stack_{{$tableId}}" class="datatable_button_stack float-right float-end text-right hidden-xs"></div>
    </div>
@endif

@section('after_styles')
  {{-- CRUD LIST CONTENT - crud_list_styles stack --}}
  @stack('crud_list_styles')
@endsection

@section('after_scripts')
  @include('crud::components.datatable.datatable_logic', ['tableId' => $tableId])
  @include('crud::inc.export_buttons')
  @include('crud::inc.details_row_logic')

  {{-- CRUD LIST CONTENT - crud_list_scripts stack --}}
  @stack('crud_list_scripts')
@endsection
