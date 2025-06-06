@php
// as it is possible that we can be redirected with persistent table we save the alerts in a variable
// and flush them from session, so we will get them later from localStorage.
$backpack_alerts = \Alert::getMessages();
\Alert::flush();
@endphp

{{-- DATA TABLES SCRIPT --}}
@basset("https://cdn.datatables.net/2.1.8/js/dataTables.min.js")
@basset("https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js")
@basset("https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js")
@basset('https://cdn.datatables.net/fixedheader/4.0.1/js/dataTables.fixedHeader.min.js')
@basset(base_path('vendor/backpack/crud/src/resources/assets/img/spinner.svg'), false)

@push('before_styles')
    @basset('https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css')
    @basset("https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.min.css")
    @basset('https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css')
@endpush

<script>
// Store the alerts in localStorage for this page
let $oldAlerts = JSON.parse(localStorage.getItem('backpack_alerts'))
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
        if(!this.modifiesUrl) {
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

// For backward compatibility, maintain the global crud object
window.crud.addFunctionToDataTablesDrawEventQueue = function(functionName) {
    window.crud.defaultTableConfig.addFunctionToDataTablesDrawEventQueue(functionName);
};
window.crud.responsiveToggle = window.crud.defaultTableConfig.responsiveToggle;
window.crud.executeFunctionByName = window.crud.defaultTableConfig.executeFunctionByName;
window.crud.updateUrl = window.crud.defaultTableConfig.updateUrl;

window.crud.initializeTable = function(tableId, customConfig = {}) {       
    if (!window.crud.tableConfigs[tableId]) {
        window.crud.tableConfigs[tableId] = {};
        
        // Clone default config properties into the table-specific config
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

    // Get table element
    const tableElement = document.getElementById(tableId);
    if (!tableElement) {
        console.error(`Table element ${tableId} not found in DOM!`);
        return;
    }

    // Extract all configuration from data attributes
    const config = window.crud.tableConfigs[tableId];
    
    // Read all configuration from data attributes
    config.urlStart = tableElement.getAttribute('data-url-start') || '';
    config.responsiveTable = tableElement.getAttribute('data-responsive-table') === 'true';
    config.persistentTable = tableElement.getAttribute('data-persistent-table') === 'true';
    config.persistentTableSlug = tableElement.getAttribute('data-persistent-table-slug') || '';
    config.persistentTableDuration = parseInt(tableElement.getAttribute('data-persistent-table-duration')) || null;
    config.subheading = tableElement.getAttribute('data-subheading') === 'true';
    config.resetButton = tableElement.getAttribute('data-reset-button') !== 'false';
    config.modifiesUrl = tableElement.getAttribute('data-modifies-url') === 'true';
    config.searchDelay = parseInt(tableElement.getAttribute('data-search-delay')) || 500;
    config.defaultPageLength = parseInt(tableElement.getAttribute('data-default-page-length')) || 10;
    
    // Parse complex JSON structures from data attributes
    try {
        config.pageLengthMenu = JSON.parse(tableElement.getAttribute('data-page-length-menu') || '[[10, 25, 50, 100], [10, 25, 50, 100]]');
    } catch (e) {
        console.error(`Error parsing JSON data attributes for table ${tableId}:`, e);
        config.pageLengthMenu = [[10, 25, 50, 100], [10, 25, 50, 100]];
    }
    
    // Boolean attributes
    config.showEntryCount = tableElement.getAttribute('data-show-entry-count') !== 'false';
    config.searchableTable = tableElement.getAttribute('data-searchable-table') !== 'false';
    config.hasDetailsRow = tableElement.getAttribute('data-has-details-row') === 'true' || tableElement.getAttribute('data-has-details-row') === '1';
    config.hasBulkActions = tableElement.getAttribute('data-has-bulk-actions') === 'true' || tableElement.getAttribute('data-has-bulk-actions') === '1';
    config.hasLineButtonsAsDropdown = tableElement.getAttribute('data-has-line-buttons-as-dropdown') === 'true' || tableElement.getAttribute('data-has-line-buttons-as-dropdown') === '1';
    config.lineButtonsAsDropdownMinimum = parseInt(tableElement.getAttribute('data-line-buttons-as-dropdown-minimum')) || 3;
    config.lineButtonsAsDropdownShowBeforeDropdown = parseInt(tableElement.getAttribute('data-line-buttons-as-dropdown-show-before-dropdown')) || 1;
    config.responsiveTable = tableElement.getAttribute('data-responsive-table') === 'true' || tableElement.getAttribute('data-responsive-table') === '1';
    config.exportButtons = tableElement.getAttribute('data-has-export-buttons') === 'true';
    // Apply any custom config
    if (customConfig && Object.keys(customConfig).length > 0) {
        Object.assign(config, customConfig);
    }
    
    // Check for persistent table redirect
    if (config.persistentTable) {
        const savedListUrl = localStorage.getItem(`${config.persistentTableSlug}_list_url`);
        
        // Check if saved url has any parameter or is empty after clearing filters
        if (savedListUrl && savedListUrl.indexOf('?') >= 1) {
            const persistentUrl = savedListUrl + '&persistent-table=true';
            
            const arr = window.location.href.split('?');
            // Check if url has parameters
            if (arr.length > 1 && arr[1] !== '') {
                // Check if it is our own persistence redirect
                if (window.location.search.indexOf('persistent-table=true') < 1) {
                    // If not, we don't want to redirect the user
                    if (persistentUrl != window.location.href) {
                        // Check duration if specified
                        if (config.persistentTableDuration) {
                            const savedListUrlTime = localStorage.getItem(`${config.persistentTableSlug}_list_url_time`);
                            
                            if (savedListUrlTime) {
                                const currentDate = new Date();
                                const savedTime = new Date(parseInt(savedListUrlTime));
                                savedTime.setMinutes(savedTime.getMinutes() + config.persistentTableDuration);
                                
                                // If the save time is not expired, redirect
                                if (savedTime > currentDate) {
                                    window.location.href = persistentUrl;
                                }
                            }
                        } else {
                            // No duration specified, just redirect
                            window.location.href = persistentUrl;
                        }
                    }
                }
            } else {
                // No parameters in current URL, redirect
                window.location.href = persistentUrl;
            }
        }
    }
    
    // Check cached datatables info
    const dtCachedInfoKey = `DataTables_${tableId}_/${config.urlStart}`;
    const dtCachedInfo = JSON.parse(localStorage.getItem(dtCachedInfoKey)) || [];
    const dtStoredPageLength = parseInt(localStorage.getItem(`${dtCachedInfoKey}_pageLength`));
    
    // Clear cache if page lengths don't match
    if (!dtStoredPageLength && dtCachedInfo.length !== 0 && dtCachedInfo.length !== config.defaultPageLength) {
        localStorage.removeItem(dtCachedInfoKey);
    }
    
    if (dtCachedInfo.length !== 0 && config.pageLengthMenu[0].indexOf(dtCachedInfo.length) === -1) {
        localStorage.removeItem(dtCachedInfoKey);
    }
    
    // Create DataTable configuration
    const dataTableConfig = {
        bInfo: config.showEntryCount,
        responsive: config.responsiveTable,
        fixedHeader: config.responsiveTable,
        scrollX: !config.responsiveTable,
        autoWidth: false,
        processing: true,
        serverSide: true,
        searchDelay: config.searchDelay,
        searching: config.searchableTable,
        pageLength: config.defaultPageLength,
        lengthMenu: config.pageLengthMenu,
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
        dom:
            "<'row hidden'<'col-sm-6'i><'col-sm-6 d-print-none'f>>" +
            "<'table-content row'<'col-sm-12'tr>>" +
            "<'table-footer row mt-2 d-print-none align-items-center '<'col-sm-12 col-md-4'l><'col-sm-0 col-md-4 text-center'B><'col-sm-12 col-md-4 'p>>",
        buttons: []
    };
    
    // Add responsive details if needed
    if (config.responsiveTable) {
        dataTableConfig.responsive = {
            details: {
                display: DataTable.Responsive.display.modal({
                    header: function() { return ''; }
                }),
                type: 'none',
                target: '.dtr-control',
                renderer: function(api, rowIdx, columns) {
                    var data = $.map(columns, function(col, i) {
                        // Use the table instance from the API
                        var table = api.table().context[0].oInstance;
                        var tableId = table.attr('id');
                        var columnHeading = window.crud.tables[tableId].columns().header()[col.columnIndex];
                        
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
                        $('<table class="table table-striped mb-0">').append('<tbody>' + data + '</tbody>') :
                        false;
                }
            }
        };
    }
    
    // Add persistent table settings if needed
    if (config.persistentTable) {
        dataTableConfig.stateSave = true;
        dataTableConfig.stateSaveParams = function(settings, data) {
            localStorage.setItem(`${config.persistentTableSlug}_list_url_time`, data.time);

            // Get the table ID from the settings
            var tableId = settings.sTableId;
            var table = window.crud.tables[tableId];
            
            data.columns.forEach(function(item, index) {
                var columnHeading = table.columns().header()[index];
                if ($(columnHeading).attr('data-visible-in-table') == 'true') {
                    return item.visible = true;
                }
            });
        };
        
        if (config.persistentTableDuration) {
            dataTableConfig.stateLoadParams = function(settings, data) {
                var savedTime = new Date(data.time);
                var currentDate = new Date();

                savedTime.setMinutes(savedTime.getMinutes() + config.persistentTableDuration);

                // If the save time has expired, force datatables to clear localStorage
                if (savedTime < currentDate) {
                    if (localStorage.getItem(`${config.persistentTableSlug}_list_url`)) {
                        localStorage.removeItem(`${config.persistentTableSlug}_list_url`);
                    }
                    if (localStorage.getItem(`${config.persistentTableSlug}_list_url_time`)) {
                        localStorage.removeItem(`${config.persistentTableSlug}_list_url_time`);
                    }
                    return false;
                }
            };
        }
    }
    
    // Configure export buttons if present
    if (config.exportButtons) {
        dataTableConfig.buttons = window.crud.exportButtonsConfig;
    }
    
    
    // Configure ajax for server-side processing
    if (config.urlStart) {
        const currentParams = new URLSearchParams(window.location.search);
        const searchParams = currentParams.toString() ? '?' + currentParams.toString() : '';
        
        // Configure the ajax URL and data
        const ajaxUrl = config.urlStart + '/search' + searchParams;
        dataTableConfig.ajax = {
            "url": ajaxUrl,
            "type": "POST",
            "data": function(d) {
                // Add table-specific parameters
                d.totalEntryCount = tableElement.getAttribute('data-total-entry-count') || false;
                d.datatable_id = tableId;
                return d;
            }
        };
    }
    
    // Store the dataTableConfig in the config object for future reference
    config.dataTableConfig = dataTableConfig;
    
    // Initialize the DataTable with the config
    window.crud.tables[tableId] = $('#'+tableId).DataTable(dataTableConfig);
    
    // For backward compatibility
    if (!window.crud.table) {
        window.crud.table = window.crud.tables[tableId];
    }
    
    // Update URL if needed
    if (config.modifiesUrl) {
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
        
        if ($.fn.DataTable.isDataTable('#' + tableId)) {
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
    }
    
    $(`#${tableId}_filter`).remove();

    $(`#${tableId}_wrapper .table-footer .btn-secondary`).removeClass('btn-secondary');

    $(".navbar.navbar-filters + div").css('overflow','hidden');

    if (config.subheading) {
        $(`#${tableId}_info`).hide();
    } else {
        $(`#datatable_info_stack_${tableId}`).html($(`#${tableId}_info`)).css('display','inline-flex').addClass('animated fadeIn');
    }

    if (config.resetButton !== false) {
        var crudTableResetButton = `<a href="${config.urlStart}" class="ml-1 ms-1" id="${tableId}_reset_button">Reset</a>`;
        $(`#datatable_info_stack_${tableId}`).append(crudTableResetButton);

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

    if (config.exportButtons && window.crud.exportButtonsConfig) {
        // Add the export buttons to the DataTable configuration
        new $.fn.dataTable.Buttons(window.crud.tables[tableId], {
            buttons: window.crud.exportButtonsConfig
        });
        // Initialize the buttons and place them in the correct container
        if (typeof window.crud.moveExportButtonsToTopRight === 'function') {
            window.crud.moveExportButtonsToTopRight(tableId);
        }
    }

    // dispatch an event that the table has been initialized
    const event = new CustomEvent('backpack:table:initialized', {
        detail: {
            tableId: tableId,
            config: config
        }
    });
    window.dispatchEvent(event);
    // move the bottom buttons before pagination
    $(`#bottom_buttons_${tableId}`).insertBefore($(`#${tableId}_wrapper .row:last-child`));
}

// Function to set up table event handlers
function setupTableEvents(tableId, config) {
    const table = window.crud.tables[tableId];
    
    // override ajax error message
    $.fn.dataTable.ext.errMode = 'none';
    $(`#${tableId}`).on('error.dt', function(e, settings, techNote, message) {
        new Noty({
            type: "error",
            text: "<strong>Ajax Error</strong><br>Something went wrong with the AJAX request."
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
        // in datatables 2.0.3 the implementation was changed to use `replaceChildren`, for that reason scripts 
        // that came with the response are no longer executed, like the delete button script or any other ajax 
        // button created by the developer. For that reason, we move them to the end of the body
        // ensuring they are re-evaluated on each draw event.
        document.getElementById(tableId).querySelectorAll('script').forEach(function(script) {
            const newScript = document.createElement('script');
            newScript.text = script.text;
            document.body.appendChild(newScript);
        });

        // Run table-specific functions and pass the tableId
        // to the function
        if (config.functionsToRunOnDataTablesDrawEvent && config.functionsToRunOnDataTablesDrawEvent.length) {
            config.functionsToRunOnDataTablesDrawEvent.forEach(function(functionName) {
                config.executeFunctionByName(functionName, [tableId]);
            });
        }
        
        if ($(`#${tableId}`).data('has-line-buttons-as-dropdown')) {
            formatActionColumnAsDropdown(tableId);
        }

        if (table.responsive && !table.responsive.hasHidden()) {
            table.columns().header()[0].style.paddingLeft = '0.6rem';
        }

        if (table.responsive && table.responsive.hasHidden()) {           
            $('.dtr-control').removeClass('d-none');
            $('.dtr-control').addClass('d-inline');
            $(`#${tableId}`).removeClass('has-hidden-columns').addClass('has-hidden-columns');
        }
    }).dataTable();

    // when datatables-colvis (column visibility) is toggled
    $(`#${tableId}`).on('column-visibility.dt', function(event) {
        if (table.responsive) {
            table.responsive.rebuild();
        }
    }).dataTable();

    // Handle responsive table if enabled
    if (config.responsiveTable && table.responsive) {
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
    } else if (!config.responsiveTable) {
        // make sure the column headings have the same width as the actual columns
        var resizeTimer;
        function resizeCrudTableColumnWidths() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (table.columns) {
                    table.columns.adjust();
                }
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
    const tableId = event.detail && event.detail.tableId ? event.detail.tableId : 'crudTable';
    if (!window.crud.tableConfigs[tableId]) return;
    
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
    const tableId = event.detail.componentId || '';
    if (!tableId) {
        console.log('No componentId provided in event detail. Exiting.');
        return;
    }

    if (!window.crud.tableConfigs[tableId]) return;

    let filterName = event.detail.filterName;
    let filterValue = event.detail.filterValue;
    let shouldUpdateUrl = event.detail.shouldUpdateUrl;
    let debounce = event.detail.debounce;
    
    updateDatatablesOnFilterChange(filterName, filterValue, filterValue || shouldUpdateUrl, debounce, tableId);
});

// Update the updateDatatablesOnFilterChange function to support multiple tables
function updateDatatablesOnFilterChange(filterName, filterValue, shouldUpdateUrl, debounce, tableId) {
    tableId = tableId || 'crudTable';
    
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
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Get configuration
    const minimumButtonsToBuildDropdown = parseInt(table.getAttribute('data-line-buttons-as-dropdown-minimum')) || 3;
    const buttonsToShowBeforeDropdown = parseInt(table.getAttribute('data-line-buttons-as-dropdown-show-before-dropdown')) || 1;
    
    // Get action column
    const actionColumnIndex = $('#' + tableId).find('th[data-action-column=true]').index();
    if (actionColumnIndex === -1) return;

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

            actionCell.prepend('<a class="btn btn-sm px-2 py-1 btn-outline-primary dropdown-toggle actions-buttons-column" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Actions</a>');
            
            const remainingButtons = actionButtons.slice(0, buttonsToShowBeforeDropdown);
            actionCell.prepend(remainingButtons);
        }
    });
}
</script>