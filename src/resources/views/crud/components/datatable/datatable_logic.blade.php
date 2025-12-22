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
/* eslint-disable */
    (function($) {
        if (!$ || !$.fn || !$.fn.dataTable || !$.fn.dataTable.FixedHeader) {
            return;
        }

        const proto = $.fn.dataTable.FixedHeader.prototype;
        if (!proto || proto._backpackNoBelowPatchApplied) {
            return;
        }

        const originalModeChange = proto._modeChange;
        proto._modeChange = function(mode, type) {
            const args = Array.prototype.slice.call(arguments);
            if (type === 'header' && args[0] === 'below' && this && this.c && this.c.headerOffset > 0) {
                args[0] = 'in-place';
            }

            const result = originalModeChange.apply(this, args);

            return result;
        };

        proto._backpackNoBelowPatchApplied = true;
    })(window.jQuery);

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
    // Create a table-specific configuration
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
    config.lineButtonsAsDropdownMinimum = parseInt(tableElement.getAttribute('data-line-buttons-as-dropdown-minimum')) ?? 3;
    config.lineButtonsAsDropdownShowBeforeDropdown = parseInt(tableElement.getAttribute('data-line-buttons-as-dropdown-show-before-dropdown')) ?? 1;
    config.responsiveTable = tableElement.getAttribute('data-responsive-table') === 'true' || tableElement.getAttribute('data-responsive-table') === '1';
    const useFixedHeaderAttr = tableElement.getAttribute('data-use-fixed-header');
    if (useFixedHeaderAttr === null || useFixedHeaderAttr === '') {
        config.useFixedHeader = config.responsiveTable;
    } else {
        config.useFixedHeader = useFixedHeaderAttr.toLowerCase() === 'true';
    }
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
    const initialFixedHeaderOffset = calculateStickyHeaderOffset(tableElement);
    const dataTableConfig = {
        bInfo: config.showEntryCount,
        responsive: config.responsiveTable,
        fixedHeader: config.useFixedHeader ? {
            header: true,
            headerOffset: initialFixedHeaderOffset
        } : false,
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
        layout: {
            topStart: null,
            topEnd: null,
            bottomEnd: null,
            bottomStart: 'info',
            bottom: config.exportButtons ? [
                'pageLength',
                {
                    buttons: window.crud.exportButtonsConfig
                },
                {
                    paging: {
                        firstLast: false,
                    }
                }
            ] : [
                'pageLength',
                {
                    paging: {
                        firstLast: false,
                    }
                }
            ]
        }
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
                        // Safety check for column index
                        if (!col || col.columnIndex === undefined || col.columnIndex === null) {
                            return '';
                        }
                        
                        // Check if column is explicitly disabled for modal
                        var isModalDisabled = false;
                        
                        try {
                            var headerCell = table.column(col.columnIndex).header();
                            isModalDisabled = $(headerCell).data('visible-in-modal') === false || $(headerCell).data('visible-in-modal') === 'false';
                        } catch (e) {
                            // Column header not accessible - default to showing the column
                            isModalDisabled = false;
                        }
                        
                        // Skip columns that are explicitly disabled for modal
                        if (isModalDisabled) {
                            return '';
                        }
                        
                        // Use the table instance from the API
                        var table = api.table().context[0].oInstance;
                        var tableId = table.attr('id');
                        
                        // Check if we're in a modal context
                        if (table.closest('.modal').length > 0) {
                            return '';
                        }
                        
                        var columnHeading;
                        if (window.crud?.tables?.[tableId]?.columns) {
                            columnHeading = window.crud.tables[tableId].columns().header()[col.columnIndex];
                        } else {
                            // Fallback: get column heading directly from table header
                            columnHeading = table.find('thead th').eq(col.columnIndex)[0];
                        }
                        
                        if ($(columnHeading).attr('data-visible-in-modal') == 'false') {
                            return '';
                        }

                        // Skip if col is null or doesn't have required properties
                        if (!col || col.columnIndex === undefined) {
                            return '';
                        }

                        if (col.data && typeof col.data === 'string' && col.data.indexOf('crud_bulk_actions_checkbox') !== -1) {
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
                                '<td style="vertical-align:top; border:none;"><strong>'+colTitle+':'+'</strong></td> '+
                                '<td style="padding-left:10px;padding-bottom:10px; border:none;">'+(col.data || '')+'</td>'+
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
            
            if (!table || typeof table.columns !== 'function') {
                return;
            }
            
            data.columns.forEach(function(item, index) {
                var columnHeading = table.columns().header()[index];
                if ($(columnHeading).attr('data-visible-in-table') == 'true') {
                    item.visible = true;
                    return true;
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
        dataTableConfig.layout.bottom.buttons = window.crud.exportButtonsConfig;
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
            },
            "dataSrc": function(json) {
                
                return json.data;
            }
        };
    }
    
    // Add initComplete callback to fix processing indicator positioning
    dataTableConfig.initComplete = function(settings, json) {
        // Move processing indicator into table wrapper if it exists outside
        const tableWrapper = document.querySelector('#' + tableId + '_wrapper');
        const processingIndicator = document.querySelector('.dataTables_processing, .dt-processing');
        
        if (tableWrapper && processingIndicator && !tableWrapper.contains(processingIndicator)) {
            // Move the processing indicator into the wrapper
            tableWrapper.appendChild(processingIndicator);
            
            // Ensure proper positioning
            processingIndicator.style.position = 'absolute';
            processingIndicator.style.top = '0';
            processingIndicator.style.left = '0';
            processingIndicator.style.right = '0';
            processingIndicator.style.bottom = '0';
            processingIndicator.style.width = 'auto';
            processingIndicator.style.height = 'auto';
            processingIndicator.style.zIndex = '1000';
        }
        
        // Call any existing initComplete function
        if (typeof window.crud.initCompleteCallback === 'function') {
            window.crud.initCompleteCallback.call(this, settings, json);
        }
    };
    
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
        
        // Skip tables inside modals
        if ($(this).closest('.modal').length > 0) {
            return;
        }
        
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

            // Clear ALL DataTables localStorage keys for this table
            // Fixes key mismatch where DataTables 2.x uses pathname-based keys
            Object.keys(localStorage)
              .filter(key => key.startsWith(`DataTables_${tableId}`))
              .forEach(key => localStorage.removeItem(key));
        });
    }

    if (config.exportButtons && window.crud.exportButtonsConfig) {
        // Add the export buttons to the DataTable configuration
        new $.fn.dataTable.Buttons(window.crud.tables[tableId], {
            buttons: window.crud.exportButtonsConfig
        });
        
        if (typeof window.crud.moveExportButtonsToTopRight === 'function') {
            config.addFunctionToDataTablesDrawEventQueue('moveExportButtonsToTopRight');
        }
        if (typeof window.crud.setupExportHandlers === 'function') {
            config.addFunctionToDataTablesDrawEventQueue('setupExportHandlers');
        }
        
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
    
    // Initialize dropdown positioning fix if table has dropdown buttons
    if ($(`#${tableId}`).data('has-line-buttons-as-dropdown')) {
        setTimeout(() => {
            initDatatableDropdowns(tableId);
        }, 100);
    }
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
        
        // Ensure initializeAllModals function is available before we try to call it
        if (typeof window.initializeAllModals === 'undefined') {
            window.initializeAllModals = function() {
                // This is a basic fallback that will be replaced by the full implementation
                // when the modal script loads
            };
        }
        
        const modalTemplatesInTable = document.getElementById(tableId).querySelectorAll('[id^="modalTemplate"]');
        
        modalTemplatesInTable.forEach(function(modal, index) {
            const newModal = modal.cloneNode(true);
            document.body.appendChild(newModal);
            modal.remove();
        });
        
        // After moving modals, check what's now in the DOM
        const allModalTemplates = document.querySelectorAll('[id^="modalTemplate"]');
        
        // After moving modals, trigger initialization if the function exists
        if (typeof window.initializeAllModals === 'function') {
            window.initializeAllModals();
        } else {
            console.warn('window.initializeAllModals function not found');
        }
        // in datatables 2.0.3 the implementation was changed to use `replaceChildren`, for that reason scripts 
        // that came with the response are no longer executed, like the delete button script or any other ajax 
        // button created by the developer. For that reason, we move them to the end of the body
        // ensuring they are re-evaluated on each draw event.
        try {
            const tableElement = document.getElementById(tableId);
            if (tableElement) {
                document.getElementById(tableId).querySelectorAll('script').forEach(function(script) {
                    const scriptsToLoad = [];
                            if (script.src) {
                                // For external scripts with src attribute
                                const srcUrl = script.src;

                                // Only load the script if it's not already loaded
                                if (!document.querySelector(`script[src="${srcUrl}"]`)) {
                                    scriptsToLoad.push(new Promise((resolve, reject) => {
                                        const newScript = document.createElement('script');

                                        // Copy all attributes from the original script
                                        Array.from(script.attributes).forEach(attr => {
                                            newScript.setAttribute(attr.name, attr.value);
                                        });

                                        // Set up load and error handlers
                                        newScript.onload = resolve;
                                        newScript.onerror = reject;

                                        // Append to document to start loading
                                        try {
                                            document.head.appendChild(newScript);
                                        } catch (e) {
                                            console.warn('Error appending external script:', e);
                                            reject(e);
                                        }
                                    }));
                                }

                                // Remove the original script tag
                                script.parentNode.removeChild(script);
                            } else {
                                // For inline scripts
                                const newScript = document.createElement('script');

                                // Copy all attributes from the original script
                                Array.from(script.attributes).forEach(attr => {
                                    newScript.setAttribute(attr.name, attr.value);
                                });

                                // Copy the content
                                newScript.textContent = script.textContent;

                                try {
                                    document.head.appendChild(newScript);
                                }catch (e) {
                                    console.warn('Error appending inline script:', e);
                                }
                            }
                        
                });
            } else {
                console.warn('Table element not found:', tableId);
            }
        } catch (e) {
            console.warn('Error processing scripts for table:', tableId, e);
        }

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

    $(`#${tableId}`).on('processing.dt', function(e, settings, processing) {
        if (processing) {
            setTimeout(function() {
                const tableWrapper = document.querySelector('#' + tableId + '_wrapper');
                const processingIndicator = document.querySelector('.dataTables_processing, .dt-processing');
                
                if (tableWrapper && processingIndicator) {
                    if (!tableWrapper.contains(processingIndicator)) {
                        tableWrapper.appendChild(processingIndicator);
                    }
                    
                    processingIndicator.style.cssText = `
                        position: absolute !important;
                        top: 0 !important;
                        left: 0 !important;
                        right: 0 !important;
                        bottom: 60px !important;
                        width: 100% !important;
                        height: calc(100% - 60px) !important;
                        z-index: 1000 !important;
                        transform: none !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        display: flex !important;
                        justify-content: center !important;
                        align-items: center !important;
                        background: rgba(255, 255, 255, 0.8) !important;
                        font-size: 0 !important;
                        color: transparent !important;
                        text-indent: -9999px !important;
                        overflow: hidden !important;
                    `;
                    
                    tableWrapper.style.position = 'relative';
                    
                    const allChildren = processingIndicator.querySelectorAll('*:not(img)');
                    allChildren.forEach(child => {
                        child.style.cssText = 'display: none !important; visibility: hidden !important; opacity: 0 !important;';
                    });
                    
                    const images = processingIndicator.querySelectorAll('img');
                    images.forEach(img => {
                        img.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important; width: 40px !important; height: 40px !important; margin: 0 auto !important;';
                    });
                }
            }, 10);
        }
    });

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

    registerFixedHeaderListeners(tableId, config);
}

function resolveFixedHeaderOffset(fixedHeader, explicitOffset) {
    if (typeof explicitOffset === 'number') {
        return explicitOffset;
    }

    if (!fixedHeader) {
        return 0;
    }

    if (typeof fixedHeader.headerOffset === 'function') {
        const value = fixedHeader.headerOffset();
        if (typeof value === 'number') {
            return value;
        }
    }

    if (fixedHeader.c && typeof fixedHeader.c.headerOffset === 'number') {
        return fixedHeader.c.headerOffset;
    }

    return 0;
}

function measureFixedHeaderHeight(fixedHeader, headerElement) {
    const storedHeight = fixedHeader && fixedHeader.s && typeof fixedHeader.s.headerHeight === 'number'
        ? Math.max(0, Math.round(fixedHeader.s.headerHeight))
        : 0;

    if (storedHeight > 0) {
        return storedHeight;
    }

    if (headerElement) {
        const rectHeight = Math.max(0, Math.round(headerElement.getBoundingClientRect().height));
        if (rectHeight > 0) {
            return rectHeight;
        }

        const offsetHeight = Math.max(0, Math.round(headerElement.offsetHeight || 0));
        if (offsetHeight > 0) {
            return offsetHeight;
        }
    }

    return 56;
}

function deriveFixedHeaderMargins(headerHeight) {
    const enableMargin = Math.max(10, headerHeight ? Math.round(Math.max(14, headerHeight * 0.35)) : 28);
    const disableMargin = Math.max(enableMargin + 14, headerHeight ? Math.round(Math.max(24, headerHeight * 0.6)) : 44);
    return { enableMargin, disableMargin };
}

function registerFixedHeaderListeners(tableId, config) {
    if (!config.useFixedHeader || config.fixedHeaderListenersRegistered) {
        return;
    }

    const tableElement = document.getElementById(tableId);
    const apiInstance = window.crud.tables[tableId];
    const fixedHeader = apiInstance && apiInstance.fixedHeader;

    if (!tableElement || !fixedHeader || typeof fixedHeader.headerOffset !== 'function' || typeof fixedHeader.enabled !== 'function') {
        return;
    }

    const headerElement = tableElement.querySelector('thead');
    const state = {
        timer: null,
        lastOffset: null,
        lastEnabled: null,
        listeners: []
    };

    const ensureActivation = (explicitOffset) => {
        const offsetValue = resolveFixedHeaderOffset(fixedHeader, explicitOffset);
        const rect = tableElement.getBoundingClientRect();
        const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
        const currentlyEnabled = fixedHeader.enabled();
        const headerHeight = measureFixedHeaderHeight(fixedHeader, headerElement);
        const { enableMargin, disableMargin } = deriveFixedHeaderMargins(headerHeight);

        const withinViewport = rect.top < viewportHeight - 1 && rect.bottom > offsetValue + 1;
        if (!withinViewport) {
            if (currentlyEnabled) {
                fixedHeader.disable();
            }
            return false;
        }

        const headerBottom = rect.top + headerHeight;
        const clearanceThreshold = currentlyEnabled ? offsetValue + disableMargin : offsetValue - enableMargin;
        const shouldEnable = headerBottom <= clearanceThreshold;

        if (shouldEnable === currentlyEnabled) {
            return shouldEnable;
        }

        if (shouldEnable) {
            fixedHeader.enable(true);
        } else {
            fixedHeader.disable();
        }

        return shouldEnable;
    };

    const recalculate = (reason) => {
        const offset = calculateStickyHeaderOffset(tableElement);
        const enabled = ensureActivation(offset);
        const offsetChanged = typeof state.lastOffset !== 'number' || state.lastOffset !== offset;
        const enabledChanged = typeof state.lastEnabled !== 'boolean' || state.lastEnabled !== enabled;

        if (offsetChanged) {
            fixedHeader.headerOffset(offset);
        }

        if (enabled && (offsetChanged || enabledChanged || /(?:dt:|window:resize|orientationchange)/.test(reason || ''))) {
            if (typeof fixedHeader.adjust === 'function') {
                fixedHeader.adjust();
            }
        }

        state.lastOffset = offset;
        state.lastEnabled = enabled;
    };

    const scheduleRecalculation = (reason) => {
        if (state.timer) {
            return;
        }

        state.timer = setTimeout(() => {
            state.timer = null;
            recalculate(reason || 'timer');
        }, 75);
    };

    const addListener = (target, eventName, handler) => {
        if (!target || !target.addEventListener) {
            return;
        }
        target.addEventListener(eventName, handler, false);
        state.listeners.push(() => target.removeEventListener(eventName, handler, false));
    };

    recalculate('initial');
    setTimeout(() => recalculate('delayed-initial'), 150);

    addListener(window, 'resize', () => scheduleRecalculation('window:resize'));
    addListener(window, 'orientationchange', () => scheduleRecalculation('window:orientationchange'));
    addListener(window, 'scroll', () => scheduleRecalculation('window:scroll'));

    const $table = $(`#${tableId}`);
    $table.on('column-visibility.dt.fixedHeader length.dt.fixedHeader responsive-resize.fixedHeader draw.dt.fixedHeader', function(evt) {
        const eventLabel = evt && evt.type ? 'dt:' + evt.type : 'dt:unknown';
        scheduleRecalculation(eventLabel);
    });

    $table.on('destroy.dt.fixedHeader', function() {
        if (state.timer) {
            clearTimeout(state.timer);
            state.timer = null;
        }

        state.listeners.forEach(function(cleanup) {
            cleanup();
        });
        state.listeners.length = 0;

        $table.off('.fixedHeader');
        config.fixedHeaderListenersRegistered = false;
    });

    config.fixedHeaderListenersRegistered = true;
}

function calculateStickyHeaderOffset(tableElement) {
    if (!tableElement || tableElement.closest('.modal')) {
        return 0;
    }

    if (typeof document.elementsFromPoint !== 'function') {
        return 0;
    }

    const viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;
    const sampleX = Math.max(0, Math.round(viewportWidth / 2));
    const maxScanDepth = Math.min(400, Math.max(200, (window.innerHeight || 0) / 2));
    const seenElements = new Set();
    let offset = 0;

    for (let y = 0; y <= maxScanDepth; y += 8) {
        const elements = document.elementsFromPoint(sampleX, y) || [];

        elements.forEach((element) => {
            if (!element || seenElements.has(element)) {
                return;
            }

            seenElements.add(element);

            if (element.closest('.dtfh-floatingparent')) {
                return;
            }

            const computedStyle = window.getComputedStyle(element);
            if (computedStyle.position !== 'sticky' && computedStyle.position !== 'fixed') {
                return;
            }

            const rect = element.getBoundingClientRect();
            if (rect.bottom <= 0) {
                return;
            }

            const topValue = parseFloat(computedStyle.top) || 0;
            if (topValue > y + 2) {
                return;
            }

            offset = Math.max(offset, rect.bottom);
        });

        if (offset > 0 && y > offset) {
            break;
        }
    }

    const finalOffset = Math.max(0, Math.round(offset));

    return finalOffset;
}

// Support for multiple tables with filters
document.addEventListener('backpack:filters:cleared', function (event) {       
    // Get the table ID from the event detail or default to the current table ID
    let tableId = event.detail && event.detail.tableId ? event.detail.tableId : 'crudTable';
    
    // If the specific table config doesn't exist, try to find the first available table
    if (!window.crud.tableConfigs[tableId]) {
        // Get the first available table config
        const availableTableIds = Object.keys(window.crud.tableConfigs);
        
        if (availableTableIds.length > 0) {
            tableId = availableTableIds[0];
        } else {
            return;
        }
    }
    
    const config = window.crud.tableConfigs[tableId];
    
    // Get the table instance first
    var ajax_table = window.crud.tables[tableId];
    if (!ajax_table) {
        // Try to get the first available table if the specific one doesn't exist
        const availableTableIds = Object.keys(window.crud.tables);
        if (availableTableIds.length > 0) {
            tableId = availableTableIds[0];
            ajax_table = window.crud.tables[tableId];
        } else {
            return;
        }
    }
    
    // behaviour for ajax table - get the current URL and remove query parameters
    let currentAjaxUrl = ajax_table.ajax.url();
    
    // Parse the URL and remove all query parameters except essential ones
    let urlObj = new URL(currentAjaxUrl);
    let new_url = urlObj.origin + urlObj.pathname;

    // replace the datatables ajax url with new_url and reload it
    ajax_table.ajax.url(new_url).load();

    // remove filters from URL
    if (config.modifiesUrl) {
        config.updateUrl(new_url);       
    }
});

document.addEventListener('backpack:filter:changed', function (event) {
    const tableId = event.detail.componentId || '';
    if (!tableId) {
        return;
    }

    if (!window.crud.tableConfigs[tableId]) return;

    let filterName = event.detail.filterName;
    let filterValue = event.detail.filterValue;
    let shouldUpdateUrl = event.detail.shouldUpdateUrl;
    let debounce = event.detail.debounce;
    
    updateDatatablesOnFilterChange(filterName, filterValue, shouldUpdateUrl, debounce, tableId);
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
    
    // Update the browser URL if needed - use browser URL, not AJAX URL
    if (shouldUpdateUrl) {
        let browserUrl = addOrUpdateUriParameter(window.location.href, filterName, filterValue);
        tableConfig.updateUrl(browserUrl);
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
    const minAttr = table.getAttribute('data-line-buttons-as-dropdown-minimum');
    const showBeforeAttr = table.getAttribute('data-line-buttons-as-dropdown-show-before-dropdown');
    const minimumButtonsToBuildDropdown = minAttr !== null ? parseInt(minAttr) : 3;
    const buttonsToShowBeforeDropdown = showBeforeAttr !== null ? parseInt(showBeforeAttr) : 1;
    
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
            actionCell.wrapInner('<div class="dropdown-menu"></div>');
            actionCell.wrapInner('<div class="dropdown"></div>');

            actionCell.prepend('<button class="btn btn-sm px-2 py-1 btn-outline-primary dropdown-toggle actions-buttons-column" type="button" aria-expanded="false">{{trans("backpack::crud.actions")}}</button>');
            
            const remainingButtons = actionButtons.slice(0, buttonsToShowBeforeDropdown);
            actionCell.prepend(remainingButtons);
        }
    });
}


function initDatatableDropdowns(tableId) {    
    // Wait for table to be ready
    setTimeout(function() {        
        const table = document.getElementById(tableId);
        if (!table) {
            return;
        }
        
        $(document).ready(function() {            
            // Use event delegation for dynamically created elements
            $('#' + tableId).off('click.lineActions').on('click.lineActions', '.actions-buttons-column.dropdown-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $this = $(this);
                const $dropdown = $this.next('.dropdown');
                const $menu = $dropdown.find('.dropdown-menu');
                
                // Check if the menu is already open
                const wasOpen = $menu.hasClass('show');

                // close all dropdowns in this table
                $('#' + tableId + ' .actions-buttons-column').next('.dropdown').find('.dropdown-menu').removeClass('show').hide();
                
                // if it was open, we just closed it, so we are done
                if (wasOpen) {
                    return;
                }

                // if no menu found, let's create one or find it differently
                if ($menu.length === 0) {                    
                    // Try different selectors
                    const $ul = $dropdown.find('ul');
                    
                    if ($ul.length > 0) {
                        $ul.addClass('dropdown-menu show').show();
                        
                        // Position the UL
                        const buttonRect = this.getBoundingClientRect();
                        $ul.css({
                            'position': 'fixed',
                            'top': (buttonRect.bottom + 5) + 'px',
                            'left': buttonRect.left + 'px',
                            'z-index': '999999',
                            'display': 'block',
                            'background': 'white',
                            'border': '1px solid #dee2e6',
                            'border-radius': '0.375rem',
                            'box-shadow': '0 0.5rem 1rem rgba(0, 0, 0, 0.15)',
                            'min-width': '160px',
                            'padding': '0.5rem 0'
                        });
                        
                        return;
                    }
                }
                
                // Show this dropdown
                $menu.addClass('show').show();
                
                // Force positioning
                const buttonRect = this.getBoundingClientRect();
                const menuHeight = $menu.outerHeight() || 150;
                const menuWidth = $menu.outerWidth() || 160;
                const windowHeight = $(window).height();
                const windowWidth = $(window).width();
                
                let top = buttonRect.bottom + 5;
                let left = buttonRect.left;

                // check position if going off screen vertically
                if (buttonRect.bottom + menuHeight > windowHeight) {
                    top = buttonRect.top - menuHeight - 5;
                }

                // check position if going off screen horizontally
                if (left + menuWidth > windowWidth) {
                    left = buttonRect.right - menuWidth;
                }
                
                // apply positioning
                const cssProps = {
                    'position': 'fixed',
                    'top': top + 'px',
                    'left': left + 'px',
                    'z-index': '999999',
                    'display': 'block',
                    'background': 'white',
                    'border': '1px solid #dee2e6',
                    'border-radius': '0.375rem',
                    'box-shadow': '0 0.5rem 1rem rgba(0, 0, 0, 0.15)',
                    'min-width': '160px',
                    'padding': '0.5rem 0'
                };
                
                $menu.css(cssProps);
            });
            
            // Close on outside click, but only for line action dropdowns in this table
            $(document).off('click.lineActions' + tableId).on('click.lineActions' + tableId, function(e) {
                // Only close line action dropdowns, not export button dropdowns
                if (!$(e.target).closest('#' + tableId + ' .actions-buttons-column').length && 
                    !$(e.target).closest('#' + tableId + ' .actions-buttons-column').next('.dropdown').length) {
                    $('#' + tableId + ' .actions-buttons-column').next('.dropdown').find('.dropdown-menu').removeClass('show').hide();
                }
            });
        });
    }, 500);
}
</script>
