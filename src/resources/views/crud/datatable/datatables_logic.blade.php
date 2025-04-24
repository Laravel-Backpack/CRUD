@php
// as it is possible that we can be redirected with persistent table we save the alerts in a variable
// and flush them from session, so we will get them later from localStorage.
$backpack_alerts = \Alert::getMessages();
\Alert::flush();

// Define the table ID - use the provided tableId or default to 'crudTable'
$tableId = $tableId ?? 'crudTable';
@endphp

{{-- DATA TABLES SCRIPT --}}
@basset('https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js')
@basset('https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js')
@basset('https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js')
@basset('https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css')
@basset('https://cdn.datatables.net/fixedheader/3.3.1/js/dataTables.fixedHeader.min.js')
@basset('https://cdn.datatables.net/fixedheader/3.3.1/css/fixedHeader.dataTables.min.css')
@basset(base_path('vendor/backpack/crud/src/resources/assets/css/responsive-modal.css'), false)

@basset(base_path('vendor/backpack/crud/src/resources/assets/img/spinner.svg'), false)

<script>
// here we will check if the cached dataTables paginator length is conformable with current paginator settings.
// datatables caches the ajax responses with pageLength in LocalStorage so when changing this
// settings in controller users get unexpected results. To avoid that we will reset
// the table cache when both lengths don't match.
let $dtCachedInfo = JSON.parse(localStorage.getItem('DataTables_{{$tableId}}_/{{$crud->getOperationSetting("datatablesUrl")}}'))
    ? JSON.parse(localStorage.getItem('DataTables_{{$tableId}}_/{{$crud->getOperationSetting("datatablesUrl")}}')) : [];
var $dtDefaultPageLength = {{ $crud->getDefaultPageLength() }};
let $pageLength = @json($crud->getPageLengthMenu());

let $dtStoredPageLength = parseInt(localStorage.getItem('DataTables_{{$tableId}}_/{{$crud->getOperationSetting("datatablesUrl")}}_pageLength'));

if(!$dtStoredPageLength && $dtCachedInfo.length !== 0 && $dtCachedInfo.length !== $dtDefaultPageLength) {
    localStorage.removeItem('DataTables_{{$tableId}}_/{{$crud->getOperationSetting("datatablesUrl")}}');
}

if($dtCachedInfo.length !== 0 && $pageLength[0].indexOf($dtCachedInfo.length) === -1) {
    localStorage.removeItem('DataTables_{{$tableId}}_/{{$crud->getRoute()}}');
}


// in this page we always pass the alerts to localStorage because we can be redirected with
// persistent table, and this way we guarantee non-duplicate alerts.
$oldAlerts = JSON.parse(localStorage.getItem('backpack_alerts'))
    ? JSON.parse(localStorage.getItem('backpack_alerts')) : {};

$newAlerts = @json($backpack_alerts);

Object.entries($newAlerts).forEach(function(type) {
    if(typeof $oldAlerts[type[0]] !== 'undefined') {
        type[1].forEach(function(msg) {
            $oldAlerts[type[0]].push(msg);
        });
    } else {
        $oldAlerts[type[0]] = type[1];
    }
});

// always store the alerts in localStorage for this page
localStorage.setItem('backpack_alerts', JSON.stringify($oldAlerts));

@if ($crud->getPersistentTable())

    var saved_list_url = localStorage.getItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url');

    //check if saved url has any parameter or is empty after clearing filters.
    if (saved_list_url && saved_list_url.indexOf('?') < 1) {
        var saved_list_url = false;
    } else {
        var persistentUrl = saved_list_url+'&persistent-table=true';
    }

var arr = window.location.href.split('?');
// check if url has parameters.
if (arr.length > 1 && arr[1] !== '') {
    // IT HAS! Check if it is our own persistence redirect.
    if (window.location.search.indexOf('persistent-table=true') < 1) {
        // IF NOT: we don't want to redirect the user.
        saved_list_url = false;
    }
}

@if($crud->getPersistentTableDuration())
    var saved_list_url_time = localStorage.getItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url_time');

    if (saved_list_url_time) {
        var $current_date = new Date();
        var $saved_time = new Date(parseInt(saved_list_url_time));
        $saved_time.setMinutes($saved_time.getMinutes() + {{$crud->getPersistentTableDuration()}});

        // if the save time is not expired we force the filter redirection.
        if($saved_time > $current_date) {
            if (saved_list_url && persistentUrl!=window.location.href) {
                window.location.href = persistentUrl;
            }
        } else {
            // persistent table expired, let's not redirect the user
            saved_list_url = false;
        }
    }
@endif

    if (saved_list_url && persistentUrl!=window.location.href) {
        // finally redirect the user.
        window.location.href = persistentUrl;
    }
@endif

// Initialize the global crud object if it doesn't exist
window.crud = window.crud || {};

// Initialize the tables object to store multiple table instances
window.crud.tables = window.crud.tables || {};

window.crud.defaultTableConfig = {
    functionsToRunOnDataTablesDrawEvent: [],
    addFunctionToDataTablesDrawEventQueue: function (functionName) {
        if (this.functionsToRunOnDataTablesDrawEvent.indexOf(functionName) == -1) {
            this.functionsToRunOnDataTablesDrawEvent.push(functionName);
        }
    },
    responsiveToggle: function(dt) {
        $(dt.table().header()).find('th').toggleClass('all');
        dt.responsive.rebuild();
        dt.responsive.recalc();
    },
    executeFunctionByName: function(str, args) {
        try {
            // First check if the function exists directly in the window object
            if (typeof window[str] === 'function') {
                window[str].apply(window, args || []);
                return;
            }
            
            // Check if the function name contains parentheses
            if (str.indexOf('(') !== -1) {
                // Extract the function name and arguments
                var funcNameMatch = str.match(/([^(]+)\((.*)\)$/);
                if (funcNameMatch) {
                    var funcName = funcNameMatch[1];
                    
                    // Handle direct function call
                    if (typeof window[funcName] === 'function') {
                        window[funcName]();
                        return;
                    }
                }
            }
            
            // Standard method - split by dots for namespaced functions
            var arr = str.split('.');
            var fn = window[ arr[0] ];

            for (var i = 1; i < arr.length; i++) { 
                fn = fn[ arr[i] ]; 
            }
            
            if (typeof fn === 'function') {
                fn.apply(window, args || []);
            } else {
            }
        } catch (e) {
        }
    },
    updateUrl: function (url) {
        if(!this.updatesUrl) {
            return;
        }
        let urlStart = this.urlStart;
        // compare if url and urlStart are the same, if they are not, just return
        let urlEnd = url.replace(urlStart, '');
        
        urlEnd = urlEnd.replace('/search', '');
        let newUrl = urlStart + urlEnd;
        let tmpUrl = newUrl.split("?")[0],
        params_arr = [],
        queryString = (newUrl.indexOf("?") !== -1) ? newUrl.split("?")[1] : false;

        if (urlStart !== tmpUrl) {
            return;
        }
        // exclude the persistent-table parameter from url
        if (queryString !== false) {
            params_arr = queryString.split("&");
            for (let i = params_arr.length - 1; i >= 0; i--) {
                let param = params_arr[i].split("=")[0];
                if (param === 'persistent-table') {
                    params_arr.splice(i, 1);
                }
            }
            newUrl = params_arr.length ? tmpUrl + "?" + params_arr.join("&") : tmpUrl;
        }
        window.history.pushState({}, '', newUrl);
        if (this.persistentTable) {
            localStorage.setItem(this.persistentTableSlug + '_list_url', newUrl);
        }
    }
};

// Create a table-specific configuration
window.crud.tableConfigs = window.crud.tableConfigs || {};

// Initialize the current table configuration
window.crud.tableConfigs['{{$tableId}}'] = Object.assign({}, window.crud.defaultTableConfig, {
    updatesUrl: {{ var_export($updatesUrl) }},
    exportButtons: JSON.parse('{!! json_encode($crud->get('list.export_buttons')) !!}'),
    functionsToRunOnDataTablesDrawEvent: [],
    urlStart: "{{ url($crud->getOperationSetting("datatablesUrl")) }}",
    persistentTable: {{ $crud->getPersistentTable() ? 'true' : 'false' }},
    persistentTableSlug: '{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}',
    persistentTableDuration: {{ $crud->getPersistentTableDuration() ?: 'null' }},
    subheading: {{ $crud->getSubheading() ? 'true' : 'false' }},
    resetButton: {{ ($crud->getOperationSetting('resetButton') ?? true) ? 'true' : 'false' }},
    responsiveTable: {{ $crud->getResponsiveTable() ? 'true' : 'false' }}
});

// For backward compatibility, maintain the global crud object
if (!window.crud.table) {
    window.crud.updatesUrl = window.crud.tableConfigs['{{$tableId}}'].updatesUrl;
    window.crud.exportButtons = window.crud.tableConfigs['{{$tableId}}'].exportButtons;
    window.crud.functionsToRunOnDataTablesDrawEvent = window.crud.tableConfigs['{{$tableId}}'].functionsToRunOnDataTablesDrawEvent;
    window.crud.addFunctionToDataTablesDrawEventQueue = window.crud.tableConfigs['{{$tableId}}'].addFunctionToDataTablesDrawEventQueue;
    window.crud.responsiveToggle = window.crud.tableConfigs['{{$tableId}}'].responsiveToggle;
    window.crud.executeFunctionByName = window.crud.tableConfigs['{{$tableId}}'].executeFunctionByName;
    window.crud.updateUrl = window.crud.tableConfigs['{{$tableId}}'].updateUrl;
}

// Create a table-specific datatable configuration
window.crud.tableConfigs['{{$tableId}}'].dataTableConfiguration = {
    bInfo: {{ var_export($crud->getOperationSetting('showEntryCount') ?? true) }},
    @if ($crud->getResponsiveTable())
    responsive: {
        details: {
            display: $.fn.dataTable.Responsive.display.modal( {
                header: function ( row ) {
                    return '';
                },
            }),
            type: 'none',
            target: '.dtr-control',
            renderer: function ( api, rowIdx, columns ) {
                var data = $.map( columns, function ( col, i ) {
                    // Use the table instance from the API
                    var table = api.table().context[0].oInstance;
                    var tableId = table.attr('id');
                    var columnHeading = window.crud.tables[tableId].columns().header()[col.columnIndex];
                    // hide columns that have VisibleInModal false
                    if ($(columnHeading).attr('data-visible-in-modal') == 'false') {
                        return '';
                    }

                    if (col.data.indexOf('crud_bulk_actions_checkbox') !== -1) {
                        col.data = col.data.replace('crud_bulk_actions_checkbox', 'crud_bulk_actions_checkbox d-none');
                    }

                    let colTitle = '';
                    if (col.title) {
                        let tempDiv = document.createElement('div');
                        tempDiv.innerHTML = col.title;
                        
                        let checkboxSpan = tempDiv.querySelector('.crud_bulk_actions_checkbox');
                        if (checkboxSpan) {
                            checkboxSpan.remove();
                        }
                        
                        colTitle = tempDiv.textContent.trim();
                    } else {
                        colTitle = '';
                    }

                    return '<tr data-dt-row="'+col.rowIndex+'" data-dt-column="'+col.columnIndex+'">'+
                            '<td style="vertical-align:top; border:none;"><strong>'+colTitle+':'+'<strong></td> '+
                            '<td style="padding-left:10px;padding-bottom:10px; border:none;">'+col.data+'</td>'+
                            '</tr>';
                }).join('');

                return data ?
                    $('<table class="table table-striped mb-0">').append( '<tbody>' + data + '</tbody>' ) :
                    false;
            },
        }
    },
    fixedHeader: true,
    @else
    responsive: false,
    scrollX: true,
    @endif

    @if ($crud->getPersistentTable())
    stateSave: true,
    stateSaveParams: function(settings, data) {
        localStorage.setItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url_time', data.time);

        // Get the table ID from the settings
        var tableId = settings.sTableId;
        var table = window.crud.tables[tableId];
        
        data.columns.forEach(function(item, index) {
            var columnHeading = table.columns().header()[index];
            if ($(columnHeading).attr('data-visible-in-table') == 'true') {
                return item.visible = true;
            }
        });
    },
    @if($crud->getPersistentTableDuration())
    stateLoadParams: function(settings, data) {
        var $saved_time = new Date(data.time);
        var $current_date = new Date();

        $saved_time.setMinutes($saved_time.getMinutes() + {{$crud->getPersistentTableDuration()}});

        //if the save time as expired we force datatabled to clear localStorage
        if($saved_time < $current_date) {
            if (localStorage.getItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl"))}}_list_url')) {
                localStorage.removeItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url');
            }
            if (localStorage.getItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl"))}}_list_url_time')) {
                localStorage.removeItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url_time');
            }
           return false;
        }
    },
    @endif
    @endif
    autoWidth: false,
    pageLength: $dtDefaultPageLength,
    lengthMenu: $pageLength,
    /* Disable initial sort */
    aaSorting: [],
    language: {
          "emptyTable":     "{{ trans('backpack::crud.emptyTable') }}",
          "info":           "{{ trans('backpack::crud.info') }}",
          "infoEmpty":      "{{ trans('backpack::crud.infoEmpty') }}",
          "infoFiltered":   "{{ trans('backpack::crud.infoFiltered') }}",
          "infoPostFix":    "{{ trans('backpack::crud.infoPostFix') }}",
          "thousands":      "{{ trans('backpack::crud.thousands') }}",
          "lengthMenu":     "{{ trans('backpack::crud.lengthMenu') }}",
          "loadingRecords": "{{ trans('backpack::crud.loadingRecords') }}",
          "processing":     "<img src='{{ Basset::getUrl('vendor/backpack/crud/src/resources/assets/img/spinner.svg') }}' alt='{{ trans('backpack::crud.processing') }}'>",
          "search": "_INPUT_",
          "searchPlaceholder": "{{ trans('backpack::crud.search') }}...",
          "zeroRecords":    "{{ trans('backpack::crud.zeroRecords') }}",
          "paginate": {
              "first":      "{{ trans('backpack::crud.paginate.first') }}",
              "last":       "{{ trans('backpack::crud.paginate.last') }}",
              "next":       '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M8 5l5 5l-5 5"></path></svg>',
              "previous":   '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-5 5l5 5"></path></svg>'
          },
          "aria": {
              "sortAscending":  "{{ trans('backpack::crud.aria.sortAscending') }}",
              "sortDescending": "{{ trans('backpack::crud.aria.sortDescending') }}"
          },
          "buttons": {
              "copy":   "{{ trans('backpack::crud.export.copy') }}",
              "excel":  "{{ trans('backpack::crud.export.excel') }}",
              "csv":    "{{ trans('backpack::crud.export.csv') }}",
              "pdf":    "{{ trans('backpack::crud.export.pdf') }}",
              "print":  "{{ trans('backpack::crud.export.print') }}",
              "colvis": "{{ trans('backpack::crud.export.column_visibility') }}"
          },
      },
      processing: true,
      serverSide: true,
      searchDelay: {{ $crud->getOperationSetting('searchDelay') }},
      @if($crud->getOperationSetting('showEntryCount') === false)
        pagingType: "simple",
      @endif
      searching: @json($crud->getOperationSetting('searchableTable') ?? true),
      ajax: {
          "url": "{!! url($crud->getOperationSetting("datatablesUrl").'/search').'?'.Request::getQueryString() !!}",
          "type": "POST",
          "data": {
            "totalEntryCount": "{{$crud->getOperationSetting('totalEntryCount') ?? false}}"
        },
      },
      dom:
        "<'row hidden'<'col-sm-6'i><'col-sm-6 d-print-none'f>>" +
        "<'table-content row'<'col-sm-12'tr>>" +
        "<'table-footer row mt-2 d-print-none align-items-center '<'col-sm-12 col-md-4'l><'col-sm-0 col-md-4 text-center'B><'col-sm-12 col-md-4 'p>>",
  };
</script> 
@include('crud::inc.export_buttons')

<script type="text/javascript">
window.crud.initializeTable = function(tableId, customConfig = {}) {   
    console.log(`Starting initialization of table ${tableId}`);
    
    if (!window.crud.tableConfigs[tableId]) {
        window.crud.tableConfigs[tableId] = {};
        
        for (let key in window.crud.defaultTableConfig) {
            if (typeof window.crud.defaultTableConfig[key] === 'function') {
                window.crud.tableConfigs[tableId][key] = window.crud.defaultTableConfig[key];
            } else if (typeof window.crud.defaultTableConfig[key] === 'object' && window.crud.defaultTableConfig[key] !== null) {
                window.crud.tableConfigs[tableId][key] = Array.isArray(window.crud.defaultTableConfig[key]) 
                    ? [...window.crud.defaultTableConfig[key]] 
                    : {...window.crud.defaultTableConfig[key]};
            } else {
                window.crud.tableConfigs[tableId][key] = window.crud.defaultTableConfig[key];
            }
        }
    }

    const tableElement = document.getElementById(tableId);
    if (tableElement) {
        const dataUrlStart = tableElement.getAttribute('data-url-start');
        if (dataUrlStart) {
            console.log(`Table ${tableId} found data-url-start: ${dataUrlStart}`);
            window.crud.tableConfigs[tableId].urlStart = dataUrlStart;
        } else {
            console.error(`Table ${tableId} is missing data-url-start attribute!`);
        }
    } else {
        console.error(`Table element ${tableId} not found in DOM!`);
    }
    
    // Apply any custom config
    if (customConfig && Object.keys(customConfig).length > 0) {
        console.log(`Applying custom config to table ${tableId}:`, customConfig);
        Object.assign(window.crud.tableConfigs[tableId], customConfig);
    }
    
    const config = window.crud.tableConfigs[tableId];
    
    // Create a completely new DataTable configuration
    let dataTableConfig = {
        bInfo: true,
        responsive: config.responsiveTable === true,
        fixedHeader: config.responsiveTable === true,
        scrollX: config.responsiveTable !== true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        searching: true,
        pageLength: {{ $crud->getDefaultPageLength() }},
        lengthMenu: @json($crud->getPageLengthMenu()),
        language: {
            processing: "<img src='{{ Basset::getUrl('vendor/backpack/crud/src/resources/assets/img/spinner.svg') }}' alt='{{ trans('backpack::crud.processing') }}'>"
        },
        dom: "<'row hidden'<'col-sm-6'i><'col-sm-6 d-print-none'f>>" +
             "<'table-content row'<'col-sm-12'tr>>" +
             "<'table-footer row mt-2 d-print-none align-items-center '<'col-sm-12 col-md-4'l><'col-sm-0 col-md-4 text-center'B><'col-sm-12 col-md-4 'p>>"
    };
    
    if (config.urlStart) {
        const currentParams = new URLSearchParams(window.location.search);
        const searchParams = currentParams.toString() ? '?' + currentParams.toString() : '';
        
        const ajaxUrl = config.urlStart + '/search' + searchParams;
        dataTableConfig.ajax = {
            "url": ajaxUrl,
            "type": "POST",
            "data": {
                "totalEntryCount": "{{$crud->getOperationSetting('totalEntryCount') ?? false}}"
            }
        };
        
        console.log(`Table ${tableId} initialized with URL: ${ajaxUrl}`);
    } else {
        console.error(`No urlStart found for table ${tableId}!`);
    }
    
    window.crud.tables[tableId] = $(`#${tableId}`).DataTable(dataTableConfig);
    
    if (!window.crud.table) {
        window.crud.table = window.crud.tables[tableId];
    }
    
    if (config.updateUrl) {
        config.updateUrl(location.href);
    }
    
    setupTableUI(tableId, config);
    setupTableEvents(tableId, config);
    
    return window.crud.tables[tableId];
};

// Document ready function to initialize all tables
jQuery(document).ready(function($) {
    // Initialize each table with its own data-url-start attribute
    $('.crud-table').each(function() {
        const tableId = $(this).attr('id');
        if (!tableId) return;
        
        if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
            return;
        }
        window.crud.initializeTable(tableId, {});
    });
});



function setupTableUI(tableId, config) {    
    const searchInput = $(`#datatable_search_stack_${tableId} input.datatable-search-input`);
    
    if (searchInput.length > 0) {
        searchInput.on('keyup', function() {
            window.crud.tables[tableId].search(this.value).draw();
        });
    } else {
        console.error(`Search input not found for table: ${tableId}`);
    }
    
    $(`#${tableId}_filter`).remove();

    $(`#${tableId}_wrapper .table-footer .btn-secondary`).removeClass('btn-secondary');

    $(".navbar.navbar-filters + div").css('overflow','initial');

    if (config.subheading) {
        $(`#${tableId}_info`).hide();
    } else {
        $("#datatable_info_stack").html($(`#${tableId}_info`)).css('display','inline-flex').addClass('animated fadeIn');
    }

    if (config.resetButton !== false) {
        var crudTableResetButton = `<a href="${config.urlStart}" class="ml-1 ms-1" id="${tableId}_reset_button">{{ trans('backpack::crud.reset') }}</a>`;
        $('#datatable_info_stack').append(crudTableResetButton);

        // when clicking in reset button we clear the localStorage for datatables
        $(`#${tableId}_reset_button`).on('click', function() {
            // Clear the filters
            if (localStorage.getItem(`${config.persistentTableSlug}_list_url`)) {
                localStorage.removeItem(`${config.persistentTableSlug}_list_url`);
            }
            if (localStorage.getItem(`${config.persistentTableSlug}_list_url_time`)) {
                localStorage.removeItem(`${config.persistentTableSlug}_list_url_time`);
            }

            // Clear the table sorting/ordering/visibility
            if(localStorage.getItem(`DataTables_${tableId}_/${config.urlStart}`)) {
                localStorage.removeItem(`DataTables_${tableId}_/${config.urlStart}`);
            }
        });
    }

    // move the bottom buttons before pagination
    $("#bottom_buttons").insertBefore($(`#${tableId}_wrapper .row:last-child`));
}

// Function to set up table event handlers
function setupTableEvents(tableId, config) {
    const table = window.crud.tables[tableId];
    
    // override ajax error message
    $.fn.dataTable.ext.errMode = 'none';
    $(`#${tableId}`).on('error.dt', function(e, settings, techNote, message) {
        new Noty({
            type: "error",
            text: "<strong>{{ trans('backpack::crud.ajax_error_title') }}</strong><br>{{ trans('backpack::crud.ajax_error_text') }}"
        }).show();
    });

    // when changing page length in datatables, save it into localStorage
    $(`#${tableId}`).on('length.dt', function(e, settings, len) {
        localStorage.setItem(`DataTables_${tableId}_/${config.urlStart}_pageLength`, len);
    });

    $(`#${tableId}`).on('page.dt', function() {
        localStorage.setItem('page_changed', true);
    });

    // on DataTable draw event run all functions in the queue
    $(`#${tableId}`).on('draw.dt', function() {
        window.crud.defaultTableConfig.functionsToRunOnDataTablesDrawEvent.forEach(function(functionName) {
            config.executeFunctionByName(functionName);
        });
        
        if ($(`#${tableId}`).data('has-line-buttons-as-dropdown')) {
            formatActionColumnAsDropdown(tableId);
        }

        if (!table.responsive.hasHidden()) {
            table.columns().header()[0].style.paddingLeft = '0.6rem';
        }

        if (table.responsive.hasHidden()) {           
            $('.dtr-control').removeClass('d-none');
            $('.dtr-control').addClass('d-inline');
            $(`#${tableId}`).removeClass('has-hidden-columns').addClass('has-hidden-columns');
        }
    }).dataTable();

    // when datatables-colvis (column visibility) is toggled
    $(`#${tableId}`).on('column-visibility.dt', function(event) {
        table.responsive.rebuild();
    }).dataTable();

    // Handle responsive table if enabled
    if (config.responsiveTable) {
        // when columns are hidden by responsive plugin
        table.on('responsive-resize', function(e, datatable, columns) {
            if (table.responsive.hasHidden()) {
                $('.dtr-control').each(function() {
                    var $this = $(this);
                    var $row = $this.closest('tr');
                    
                    var $firstVisibleColumn = $row.find('td').filter(function() {
                        return $(this).css('display') !== 'none';
                    }).first();
                    $this.prependTo($firstVisibleColumn);
                });

                $('.dtr-control').removeClass('d-none');
                $('.dtr-control').addClass('d-inline');
                $(`#${tableId}`).removeClass('has-hidden-columns').addClass('has-hidden-columns');
            } else {
                $('.dtr-control').removeClass('d-none').removeClass('d-inline').addClass('d-none');
                $(`#${tableId}`).removeClass('has-hidden-columns');
            }
        });
    } else {
        // make sure the column headings have the same width as the actual columns
        var resizeTimer;
        function resizeCrudTableColumnWidths() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                table.columns.adjust();
            }, 250);
        }
        $(window).on('resize', function(e) {
            resizeCrudTableColumnWidths();
        });
        $('.sidebar-toggler').click(function() {
            resizeCrudTableColumnWidths();
        });
    }
}

// Support for multiple tables with filters
document.addEventListener('backpack:filters:cleared', function (event) {       
    // Get the table ID from the event detail or default to the current table ID
    const tableId = event.detail && event.detail.tableId ? event.detail.tableId : '{{$tableId}}';
    const config = window.crud.tableConfigs[tableId];
    
    // behaviour for ajax table
    var new_url = `${config.urlStart}/search`;
    var ajax_table = window.crud.tables[tableId];

    // replace the datatables ajax url with new_url and reload it
    ajax_table.ajax.url(new_url).load();

    // remove filters from URL
    config.updateUrl(new_url);       
});

document.addEventListener('backpack:filter:changed', function (event) {
    let filterName = event.detail.filterName;
    let filterValue = event.detail.filterValue;
    let shouldUpdateUrl = event.detail.shouldUpdateUrl;
    let debounce = event.detail.debounce;
    let tableId = event.detail.tableId || '{{$tableId}}';
    
    updateDatatablesOnFilterChange(filterName, filterValue, filterValue || shouldUpdateUrl, debounce, tableId);
});

// Update the updateDatatablesOnFilterChange function to support multiple tables
function updateDatatablesOnFilterChange(filterName, filterValue, shouldUpdateUrl, debounce, tableId) {
    tableId = tableId || '{{$tableId}}';
    
    // Get the table instance and config
    const table = window.crud.tables[tableId];
    const tableConfig = window.crud.tableConfigs[tableId];
    
    if (!table) return;
    
    // Get the current URL from the table's ajax settings
    let currentUrl = table.ajax.url();
    
    // Update the URL with the new filter parameter
    let newUrl = addOrUpdateUriParameter(currentUrl, filterName, filterValue);
    
    // Set the new URL for the table
    table.ajax.url(newUrl);
    
    // Update the browser URL if needed
    if (shouldUpdateUrl) {
        tableConfig.updateUrl(newUrl);
    }
    
    // Reload the table with the new URL if needed
    if (shouldUpdateUrl) {
        callFunctionOnce(function() { 
            table.ajax.reload();
        }, debounce, 'refreshDatatablesOnFilterChange_' + tableId);
    }
    
    return newUrl;
}


function formatActionColumnAsDropdown(tableId) {
    // Use the provided tableId or default to 'crudTable' for backward compatibility
    tableId = tableId || 'crudTable';
    
    // Get action column
    const actionColumnIndex = $('#' + tableId).find('th[data-action-column=true]').index();
    if (actionColumnIndex === -1) return;

    const minimumButtonsToBuildDropdown = $('#' + tableId).data('line-buttons-as-dropdown-minimum');
    const buttonsToShowBeforeDropdown = $('#' + tableId).data('line-buttons-as-dropdown-show-before-dropdown');

    $('#' + tableId + ' tbody tr').each(function (i, tr) {
        const actionCell = $(tr).find('td').eq(actionColumnIndex);
        const actionButtons = actionCell.find('a.btn.btn-link');
        if (actionCell.find('.actions-buttons-column').length) return;
        if (actionButtons.length < minimumButtonsToBuildDropdown) return;

        // Prepare buttons as dropdown items
        const dropdownItems = actionButtons.slice(buttonsToShowBeforeDropdown).map((index, action) => {
            $(action).addClass('dropdown-item').removeClass('btn btn-sm btn-link');
            $(action).find('i').addClass('me-2 text-primary');
            return action;
        });

        // Only create dropdown if there are items to drop
        if (dropdownItems.length > 0) {
            // Wrap the cell with the component needed for the dropdown
            actionCell.wrapInner('<div class="nav-item dropdown"></div>');
            actionCell.wrapInner('<div class="dropdown-menu dropdown-menu-left"></div>');

            actionCell.prepend('<a class="btn btn-sm px-2 py-1 btn-outline-primary dropdown-toggle actions-buttons-column" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">{{ trans('backpack::crud.actions') }}</a>');
            
            const remainingButtons = actionButtons.slice(0, buttonsToShowBeforeDropdown);
            actionCell.prepend(remainingButtons);
        }
    });
}
</script>

@include('crud::inc.details_row_logic')
