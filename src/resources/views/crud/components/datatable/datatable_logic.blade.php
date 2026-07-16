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
    // FixedHeader monkey-patch: prevent "below" mode when headerOffset > 0
    (function() {
        if (typeof DataTable === 'undefined' || !DataTable.FixedHeader) {
            return;
        }

        const proto = DataTable.FixedHeader.prototype;
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
    })();

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
        dt.table().header().querySelectorAll('th').forEach(function(th) {
            th.classList.toggle('all');
        });
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
window.crud.updateUrl = function(url) {
    var tableEl = document.querySelector('table.crud-table[data-persistent-table-slug]')
        || document.querySelector('table[id^="crudTable"][data-persistent-table-slug]');
    
    if (tableEl) {
        // Temporarily build a context object with the properties updateUrl expects
        var ctx = {
            modifiesUrl: tableEl.getAttribute('data-modifies-url') === 'true',
            urlStart: tableEl.getAttribute('data-url-start') || '',
            persistentTable: tableEl.getAttribute('data-persistent-table') === 'true',
            persistentTableSlug: tableEl.getAttribute('data-persistent-table-slug') || ''
        };
        window.crud.defaultTableConfig.updateUrl.call(ctx, url);
    }
};

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
    config.spinnerUrl = tableElement.getAttribute('data-spinner-url') || '';
    
    // Parse language strings from data attribute
    try {
        config.language = JSON.parse(tableElement.getAttribute('data-language') || '{}');
    } catch (e) {
        console.error(`Error parsing language data attribute for table ${tableId}:`, e);
        config.language = {};
    }
    
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
            const isOurOwnPersistenceRedirect = window.location.search.indexOf('persistent-table=true') >= 1;
            const currentUrlHasParams = window.location.search.length > 1;

            if (isOurOwnPersistenceRedirect) {
                // This is the result of our own redirect, nothing to do
            } else if (currentUrlHasParams) {
                localStorage.setItem(`${config.persistentTableSlug}_list_url`, window.location.href);
            } else {
                // No params in current URL — restore the persistent state
                const persistentUrl = savedListUrl + '&persistent-table=true';
                
                if (config.persistentTableDuration) {
                    const savedListUrlTime = localStorage.getItem(`${config.persistentTableSlug}_list_url_time`);
                    
                    if (savedListUrlTime) {
                        const currentDate = new Date();
                        const savedTime = new Date(parseInt(savedListUrlTime));
                        savedTime.setMinutes(savedTime.getMinutes() + config.persistentTableDuration);
                        
                        if (savedTime > currentDate) {
                            window.location.href = persistentUrl;
                        }
                    }
                } else {
                    window.location.href = persistentUrl;
                }
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
              "emptyTable":     config.language.emptyTable || '',
              "info":           config.language.info || '',
              "infoEmpty":      config.language.infoEmpty || '',
              "infoFiltered":   config.language.infoFiltered || '',
              "infoPostFix":    config.language.infoPostFix || '',
              "thousands":      config.language.thousands || '',
              "lengthMenu":     config.language.lengthMenu || '',
              "loadingRecords": config.language.loadingRecords || '',
              "processing":     "<img src='" + config.spinnerUrl + "' alt='" + (config.language.processing || '') + "'>",
              "search": "_INPUT_",
              "searchPlaceholder": (config.language.search || '') + "...",
              "zeroRecords":    config.language.zeroRecords || '',
              "paginate": {
                  "first":      (config.language.paginate && config.language.paginate.first) || '',
                  "last":       (config.language.paginate && config.language.paginate.last) || '',
                  "next":       '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M8 5l5 5l-5 5"></path></svg>',
                  "previous":   '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-5 5l5 5"></path></svg>'
              },
              "aria": {
                  "sortAscending":  (config.language.aria && config.language.aria.sortAscending) || '',
                  "sortDescending": (config.language.aria && config.language.aria.sortDescending) || ''
              },
              "buttons": {
                  "copy":   (config.language.buttons && config.language.buttons.copy) || '',
                  "excel":  (config.language.buttons && config.language.buttons.excel) || '',
                  "csv":    (config.language.buttons && config.language.buttons.csv) || '',
                  "pdf":    (config.language.buttons && config.language.buttons.pdf) || '',
                  "print":  (config.language.buttons && config.language.buttons.print) || '',
                  "colvis": (config.language.buttons && config.language.buttons.colvis) || ''
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
                    var data = columns.map(function(col, i) {
                        // Safety check for column index
                        if (!col || col.columnIndex === undefined || col.columnIndex === null) {
                            return '';
                        }
                        
                        // Check if column is explicitly disabled for modal
                        var isModalDisabled = false;
                        
                        try {
                            var headerCell = table.column(col.columnIndex).header();
                            isModalDisabled = headerCell.getAttribute('data-visible-in-modal') === 'false';
                        } catch (e) {
                            // Column header not accessible - default to showing the column
                            isModalDisabled = false;
                        }
                        
                        // Skip columns that are explicitly disabled for modal
                        if (isModalDisabled) {
                            return '';
                        }
                        
                        // Use the table node from the API (native DOM element)
                        var tableNode = api.table().node();
                        var tableId = tableNode.id;
                        
                        // Check if we're in a modal context
                        if (tableNode.closest('.modal')) {
                            return '';
                        }
                        
                        var columnHeading;
                        if (window.crud?.tables?.[tableId]?.columns) {
                            columnHeading = window.crud.tables[tableId].columns().header()[col.columnIndex];
                        } else {
                            // Fallback: get column heading directly from table header
                            var headerCells = tableNode.querySelectorAll('thead th');
                            columnHeading = headerCells[col.columnIndex];
                        }
                        
                        if (columnHeading && columnHeading.getAttribute('data-visible-in-modal') === 'false') {
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

                    if (data) {
                        var tbl = document.createElement('table');
                        tbl.className = 'table table-striped mb-0';
                        tbl.innerHTML = '<tbody>' + data + '</tbody>';
                        return tbl;
                    }
                    return false;
                }
            }
        };
    }
    
    // Add persistent table settings if needed
    if (config.persistentTable) {
        dataTableConfig.stateSave = true;
        dataTableConfig.stateSaveParams = function(settings, data) {
            localStorage.setItem(`${config.persistentTableSlug}_list_url_time`, data.time);
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
                d.totalEntryCount = tableElement.getAttribute('data-total-entry-count') || false;
                d.datatable_id = tableId;
                // first-visible column index, so the server injects first-column buttons
                // into a cell that will survive the next DataTables draw
                if (window.crud
                    && window.crud.tables
                    && window.crud.tables[tableId]
                    && typeof window.crud.tables[tableId].__firstVisibleColumnHint === 'number'
                    && window.crud.tables[tableId].__firstVisibleColumnHint >= 0) {
                    d._bp_first_visible_column = window.crud.tables[tableId].__firstVisibleColumnHint;
                }
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
        
        if (typeof window.crud.initCompleteCallback === 'function') {
            window.crud.initCompleteCallback.call(this, settings, json);
        }
        try {
            if (window.crud.tables[tableId] && typeof window.crud.tables[tableId].__updateFirstColButtonsHint === 'function') {
                window.crud.tables[tableId].__updateFirstColButtonsHint();
            }
            if (window.crud.tables[tableId] && typeof window.crud.tables[tableId].__repositionFirstColButtons === 'function') {
                window.crud.tables[tableId].__repositionFirstColButtons();
            }
        } catch (e) { /* noop */ }
    };
    
    // Store the dataTableConfig in the config object for future reference
    config.dataTableConfig = dataTableConfig;

    // Seed __firstVisibleColumnHint before DT init so the first ajax call already carries it.
    // Baseline from <th data-visible>, then overlay persisted column visibility from localStorage
    // (DT restores it before init.dt fires).
    (function seedFirstVisibleColumnHint() {
        const placeholder = { __firstVisibleColumnHint: -1 };
        window.crud.tables[tableId] = placeholder;

        const headerCells = Array.from(tableElement.querySelectorAll(':scope > thead > tr > th'));
        const visibility = headerCells.map(function(th) {
            return th.getAttribute('data-visible') !== 'false';
        });

        try {
            let raw = localStorage.getItem(`DataTables_${tableId}_/${config.urlStart}`);
            if (!raw) {
                for (let i = 0; i < localStorage.length; i++) {
                    const k = localStorage.key(i);
                    if (k && k.startsWith(`DataTables_${tableId}_`)) {
                        raw = localStorage.getItem(k);
                        if (raw) break;
                    }
                }
            }
            if (raw) {
                const state = JSON.parse(raw);
                if (state && Array.isArray(state.columns)) {
                    state.columns.forEach(function(col, i) {
                        if (i < visibility.length && col && col.visible === false) {
                            visibility[i] = false;
                        }
                    });
                }
            }
        } catch (e) { /* noop */ }

        for (let i = 0; i < visibility.length; i++) {
            if (visibility[i]) {
                placeholder.__firstVisibleColumnHint = i;
                break;
            }
        }
    })();

    // Initialize the DataTable with the config
    const dtInstance = new DataTable('#' + tableId, dataTableConfig);
    const seededHint = (window.crud.tables[tableId] && typeof window.crud.tables[tableId].__firstVisibleColumnHint === 'number')
        ? window.crud.tables[tableId].__firstVisibleColumnHint
        : -1;
    dtInstance.__firstVisibleColumnHint = seededHint;
    window.crud.tables[tableId] = dtInstance;

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
document.addEventListener('DOMContentLoaded', function() {
    // Initialize each table with its own data-url-start attribute
    document.querySelectorAll('.crud-table').forEach(function(tableEl) {
        const tableId = tableEl.getAttribute('id');
        if (!tableId) return;
        
        // Skip tables inside modals
        if (tableEl.closest('.modal')) {
            return;
        }
        
        if (DataTable.isDataTable('#' + tableId)) {
            return;
        }
        window.crud.initializeTable(tableId, {});
    });
});

function setupTableUI(tableId, config) {
    const tableElement = document.getElementById(tableId);
    const searchInput = document.querySelector('#datatable_search_stack_' + tableId + ' input.datatable-search-input');
    const searchClear = document.querySelector('#datatable_search_stack_' + tableId + ' .datatable-search-clear');

    const toggleSearchClear = function(value) {
        if (value && value.length > 0) {
            searchClear.removeAttribute('hidden');
        } else {
            searchClear.setAttribute('hidden', 'hidden');
        }
    };

    if (searchInput) {
        searchInput.value = window.crud.tables[tableId].search();
        toggleSearchClear(searchInput.value);
        searchInput.addEventListener('keyup', function() {
            toggleSearchClear(this.value);
            window.crud.tables[tableId].search(this.value).draw();
        });
        searchInput.addEventListener('search', function() {
            toggleSearchClear(this.value);
        });

        if (searchClear) {
            searchClear.addEventListener('click', function(e) {
                e.preventDefault();
                searchInput.value = '';
                toggleSearchClear('');
                window.crud.tables[tableId].search('').draw();
                searchInput.focus();
            });
            searchClear.addEventListener('keydown', function(e) {
                if (e.key !== 'Enter' && e.key !== ' ') {
                    return;
                }
                e.preventDefault();
                searchInput.value = '';
                toggleSearchClear('');
                window.crud.tables[tableId].search('').draw();
                searchInput.focus();
            });
        }
    }
    
    // Remove old filter element
    var filterEl = document.getElementById(tableId + '_filter');
    if (filterEl) {
        filterEl.remove();
    }

    // Remove btn-secondary from footer buttons
    var footerBtns = document.querySelectorAll('#' + tableId + '_wrapper .table-footer .btn-secondary');
    footerBtns.forEach(function(btn) {
        btn.classList.remove('btn-secondary');
    });

    // Set overflow hidden on filters container
    var filtersNext = document.querySelector('.navbar.navbar-filters + div');
    if (filtersNext) {
        filtersNext.style.overflow = 'hidden';
    }

    if (config.subheading) {
        var infoEl = document.getElementById(tableId + '_info');
        if (infoEl) {
            infoEl.style.display = 'none';
        }
    } else {
        var infoStack = document.querySelector('#datatable_info_stack_' + tableId);
        var tableInfo = document.getElementById(tableId + '_info');
        if (infoStack && tableInfo) {
            infoStack.innerHTML = tableInfo.innerHTML;
            infoStack.style.display = 'inline-flex';
            infoStack.classList.add('animated', 'fadeIn');
        }
    }

    if (config.resetButton !== false) {
        var resetLabel = config.language.reset || 'Reset';
        var crudTableResetButton = '<a href="' + config.urlStart + '" class="ml-1 ms-1" id="' + tableId + '_reset_button">' + resetLabel + '</a>';
        var infoStackEl = document.querySelector('#datatable_info_stack_' + tableId);
        if (infoStackEl) {
            infoStackEl.insertAdjacentHTML('beforeend', crudTableResetButton);
        }

        // when clicking in reset button we clear the localStorage for datatables
        var resetBtn = document.getElementById(tableId + '_reset_button');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                // Clear the filters
                if (localStorage.getItem(config.persistentTableSlug + '_list_url')) {
                    localStorage.removeItem(config.persistentTableSlug + '_list_url');
                }
                if (localStorage.getItem(config.persistentTableSlug + '_list_url_time')) {
                    localStorage.removeItem(config.persistentTableSlug + '_list_url_time');
                }

                // Clear ALL DataTables localStorage keys for this table
                // Fixes key mismatch where DataTables 2.x uses pathname-based keys
                Object.keys(localStorage)
                  .filter(function(key) { return key.startsWith('DataTables_' + tableId); })
                  .forEach(function(key) { localStorage.removeItem(key); });
            });
        }
    }

    if (config.exportButtons && window.crud.exportButtonsConfig) {
        // Add the export buttons to the DataTable configuration
        new DataTable.Buttons(window.crud.tables[tableId], {
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
    if (tableElement.getAttribute('data-has-line-buttons-as-dropdown') === 'true' ||
        tableElement.getAttribute('data-has-line-buttons-as-dropdown') === '1') {
        setTimeout(function() {
            initDatatableDropdowns(tableId);
        }, 100);
    }
}

// Function to set up table event handlers
function setupTableEvents(tableId, config) {
    const table = window.crud.tables[tableId];

    // First-column buttons (bulk-actions checkbox, details-row trigger, .dtr-control)
    // must follow the first VISIBLE column when the natural first column is hidden
    // (visibleInTable=false, colvis, responsive, or persisted state).
    const FIRST_COL_BUTTONS_SELECTOR = '.dtr-control, .crud_bulk_actions_checkbox, span.details-control';
    const detachedFirstColButtons = new WeakMap();

    function getTableEl() {
        return document.getElementById(tableId);
    }

    function getDirectRowCells(row) {
        return Array.from(row.children).filter(function(c) {
            return c.tagName === 'TD' || c.tagName === 'TH';
        });
    }

    function getDirectFirstColButtons(row) {
        // skip nested tables (e.g. responsive-details modal)
        return Array.from(row.querySelectorAll(FIRST_COL_BUTTONS_SELECTOR)).filter(function(el) {
            const cell = el.closest('th, td');
            return cell && cell.parentElement === row;
        });
    }

    function moveFirstColButtonsToCell(row, targetCell) {
        if (!row || !targetCell) return;
        getDirectFirstColButtons(row).forEach(function(el) {
            const parentCell = el.closest('th, td');
            if (!parentCell || parentCell === targetCell) return;
            targetCell.insertBefore(el, targetCell.firstChild);
            // keep .details-control on whichever cell hosts the trigger
            if (el.matches('span.details-control') && parentCell.classList.contains('details-control')) {
                parentCell.classList.remove('details-control');
                targetCell.classList.add('details-control');
            }
        });
    }

    function getFirstVisibleColumnIndex() {
        let firstVisibleIdx = -1;
        try {
            table.columns().every(function (i) {
                if (firstVisibleIdx !== -1) return;
                if (!this.visible()) return;
                const headerCell = this.header();
                if (headerCell && headerCell.classList && headerCell.classList.contains('dtr-hidden')) return;
                firstVisibleIdx = i;
            });
        } catch (e) { /* noop */ }
        return firstVisibleIdx;
    }

    function repositionFirstColButtons() {
        const tableEl = getTableEl();
        if (!tableEl) return;
        const firstVisibleIdx = getFirstVisibleColumnIndex();
        if (firstVisibleIdx < 0) return;

        try {
            const headerNode = table.column(firstVisibleIdx).header();
            if (headerNode && headerNode.parentElement) {
                moveFirstColButtonsToCell(headerNode.parentElement, headerNode);
            }
        } catch (e) { /* noop */ }

        try {
            const footerNode = table.column(firstVisibleIdx).footer();
            if (footerNode && footerNode.parentElement) {
                moveFirstColButtonsToCell(footerNode.parentElement, footerNode);
            }
        } catch (e) { /* noop */ }

        try {
            table.rows({ page: 'current' }).every(function () {
                const rowNode = this.node();
                if (!rowNode) return;
                let cellNode = null;
                try { cellNode = table.cell(this.index(), firstVisibleIdx).node(); } catch (e) { /* noop */ }
                if (cellNode) moveFirstColButtonsToCell(rowNode, cellNode);
            });
        } catch (e) { /* noop */ }
    }
    window.crud.tables[tableId].__repositionFirstColButtons = repositionFirstColButtons;

    // colvis with redraw removes the toggled <td> before column-visibility.dt fires,
    // so detach the buttons on mousedown (capture phase) and reattach after the toggle.
    function onColvisMousedown(e) {
        const trigger = e.target.closest('.buttons-columnVisibility');
        if (!trigger) return;
        const tableEl = getTableEl();
        if (!tableEl) return;
        if (!tableEl.querySelector(FIRST_COL_BUTTONS_SELECTOR)) return;

        ['thead', 'tbody', 'tfoot'].forEach(function(section) {
            const sectionEl = tableEl.querySelector(':scope > ' + section);
            if (!sectionEl) return;
            Array.from(sectionEl.children).forEach(function(row) {
                if (row.tagName !== 'TR') return;
                const buttons = getDirectFirstColButtons(row);
                if (buttons.length) {
                    buttons.forEach(function(el) {
                        if (el.parentNode) el.parentNode.removeChild(el);
                    });
                    detachedFirstColButtons.set(row, buttons);
                }
            });
        });
    }
    document.addEventListener('mousedown', onColvisMousedown, true);

    function reattachDetachedFirstColButtons() {
        const tableEl = getTableEl();
        if (!tableEl) return;
        ['thead', 'tbody', 'tfoot'].forEach(function(section) {
            const sectionEl = tableEl.querySelector(':scope > ' + section);
            if (!sectionEl) return;
            Array.from(sectionEl.children).forEach(function(row) {
                if (row.tagName !== 'TR') return;
                const stash = detachedFirstColButtons.get(row);
                if (!stash || !stash.length) return;
                detachedFirstColButtons.delete(row);

                const cells = getDirectRowCells(row);
                let target = null;
                for (const c of cells) {
                    if (target) break;
                    if (c.classList.contains('dtr-hidden')) continue;
                    if (getComputedStyle(c).display === 'none') continue;
                    target = c;
                }
                if (!target) target = cells[0];
                if (!target) return;

                for (let i = stash.length - 1; i >= 0; i--) {
                    target.insertBefore(stash[i], target.firstChild);
                }
                if (stash.some(function(el) { return el.matches && el.matches('span.details-control'); })) {
                    target.classList.add('details-control');
                }
            });
        });
    }
    window.crud.tables[tableId].__reattachDetachedFirstColButtons = reattachDetachedFirstColButtons;

    // refresh the hint sent to the server on the next ajax draw
    function updateFirstColButtonsHint() {
        const idx = getFirstVisibleColumnIndex();
        if (window.crud.tables[tableId] && idx >= 0) {
            window.crud.tables[tableId].__firstVisibleColumnHint = idx;
        }
    }
    window.crud.tables[tableId].__updateFirstColButtonsHint = updateFirstColButtonsHint;

    // override ajax error message
    DataTable.ext.errMode = 'none';

    const tableElement = document.getElementById(tableId);
    if (tableElement) {
        table.on('error.dt', function(e, settings, techNote, message) {
            var errorTitle = config.language.ajax_error_title || 'Error';
            var errorText = config.language.ajax_error_text || 'Something went wrong with the AJAX request.';
            new Noty({
                type: "error",
                text: "<strong>" + errorTitle + "</strong><br>" + errorText
            }).show();
        });

        // when changing page length in datatables, save it into localStorage
        table.on('length.dt', function(e, settings, len) {
            localStorage.setItem('DataTables_' + tableId + '_/' + config.urlStart + '_pageLength', len);
        });

        table.on('page.dt', function() {
            localStorage.setItem('page_changed', true);
        });

        // on DataTable draw event run all functions in the queue
        table.on('draw.dt', function() {
            
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
                const tableEl = document.getElementById(tableId);
                if (tableEl) {
                    tableEl.querySelectorAll('script').forEach(function(script) {
                        if (script.parentNode) {
                            script.parentNode.removeChild(script);
                        }

                        if (script.src) {
                            // For external scripts with src attribute
                            const srcUrl = script.src;

                            // Only load the script if it's not already loaded in <head>
                            if (!document.querySelector('script[src="' + srcUrl + '"]')) {
                                const newScript = document.createElement('script');

                                // Copy all attributes from the original script
                                Array.from(script.attributes).forEach(function(attr) {
                                    newScript.setAttribute(attr.name, attr.value);
                                });

                                newScript.onerror = function(e) {
                                    console.warn('Error loading script:', srcUrl, e);
                                };

                                try {
                                    document.head.appendChild(newScript);
                                } catch (e) {
                                    console.warn('Error appending external script:', e);
                                }
                            }
                        } else {
                            // For inline scripts
                            const newScript = document.createElement('script');

                            // Copy all attributes from the original script
                            Array.from(script.attributes).forEach(function(attr) {
                                newScript.setAttribute(attr.name, attr.value);
                            });

                            // Copy the content
                            newScript.textContent = script.textContent;

                            try {
                                document.head.appendChild(newScript);
                            } catch (e) {
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
            
            if (tableElement.getAttribute('data-has-line-buttons-as-dropdown') === 'true' ||
                tableElement.getAttribute('data-has-line-buttons-as-dropdown') === '1') {
                formatActionColumnAsDropdown(tableId);
            }

            if (table.responsive && !table.responsive.hasHidden()) {
                table.columns().header()[0].style.paddingLeft = '0.6rem';
            }

            if (table.responsive && table.responsive.hasHidden()) {           
                document.querySelectorAll('.dtr-control').forEach(function(el) {
                    el.classList.remove('d-none');
                    el.classList.add('d-inline');
                });
                tableElement.classList.remove('has-hidden-columns');
                tableElement.classList.add('has-hidden-columns');
            }

            // move first-column buttons to the first visible cell
            repositionFirstColButtons();
        });

        table.on('processing.dt', function(e, settings, processing) {
            if (processing) {
                setTimeout(function() {
                    const tableWrapper = document.querySelector('#' + tableId + '_wrapper');
                    const processingIndicator = document.querySelector('.dataTables_processing, .dt-processing');
                    
                    if (tableWrapper && processingIndicator) {
                        if (!tableWrapper.contains(processingIndicator)) {
                            tableWrapper.appendChild(processingIndicator);
                        }
                        
                        processingIndicator.style.cssText = 
                            'position: absolute !important;' +
                            'top: 0 !important;' +
                            'left: 0 !important;' +
                            'right: 0 !important;' +
                            'bottom: 60px !important;' +
                            'width: 100% !important;' +
                            'height: calc(100% - 60px) !important;' +
                            'z-index: 1000 !important;' +
                            'transform: none !important;' +
                            'margin: 0 !important;' +
                            'padding: 0 !important;' +
                            'display: flex !important;' +
                            'justify-content: center !important;' +
                            'align-items: center !important;' +
                            'background: var(--bp-processing-bg, rgba(255, 255, 255, 0.8)) !important;' +
                            'font-size: 0 !important;' +
                            'color: transparent !important;' +
                            'text-indent: -9999px !important;' +
                            'overflow: hidden !important;';
                        
                        tableWrapper.style.position = 'relative';
                        
                        const allChildren = processingIndicator.querySelectorAll('*:not(img)');
                        allChildren.forEach(function(child) {
                            child.style.cssText = 'display: none !important; visibility: hidden !important; opacity: 0 !important;';
                        });
                        
                        const images = processingIndicator.querySelectorAll('img');
                        images.forEach(function(img) {
                            img.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important; width: 40px !important; height: 40px !important; margin: 0 auto !important;';
                        });
                    }
                }, 10);
            }
        });

        table.on('column-visibility.dt', function(event) {
            // defer past DataTables/Responsive's own listeners
            setTimeout(function () {
                reattachDetachedFirstColButtons();
                if (table.responsive) {
                    try { table.responsive.rebuild(); } catch (e) { /* noop */ }
                    try { table.responsive.recalc(); } catch (e) { /* noop */ }
                }
                repositionFirstColButtons();
                updateFirstColButtonsHint();
            }, 0);
        });
    }

    // Handle responsive table if enabled
    if (config.responsiveTable && table.responsive) {
        // when columns are hidden by responsive plugin
        table.on('responsive-resize', function(e, datatable, columns) {
            if (table.responsive.hasHidden()) {
                document.querySelectorAll('.dtr-control').forEach(function(el) {
                    var row = el.closest('tr');
                    
                    // Find the first visible column cell in this row
                    var firstVisibleColumn = null;
                    if (row) {
                        var cells = row.querySelectorAll('td');
                        for (var i = 0; i < cells.length; i++) {
                            if (getComputedStyle(cells[i]).display !== 'none') {
                                firstVisibleColumn = cells[i];
                                break;
                            }
                        }
                    }
                    if (firstVisibleColumn) {
                        firstVisibleColumn.prepend(el);
                    }
                });

                document.querySelectorAll('.dtr-control').forEach(function(el) {
                    el.classList.remove('d-none');
                    el.classList.add('d-inline');
                });
                var tbl = document.getElementById(tableId);
                if (tbl) {
                    tbl.classList.remove('has-hidden-columns');
                    tbl.classList.add('has-hidden-columns');
                }
            } else {
                document.querySelectorAll('.dtr-control').forEach(function(el) {
                    el.classList.remove('d-none', 'd-inline');
                    el.classList.add('d-none');
                });
                var tbl2 = document.getElementById(tableId);
                if (tbl2) {
                    tbl2.classList.remove('has-hidden-columns');
                }
            }

            // bulk checkbox and details-row trigger follow the first visible cell
            repositionFirstColButtons();
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
        window.addEventListener('resize', function(e) {
            resizeCrudTableColumnWidths();
        });
        var sidebarToggler = document.querySelector('.sidebar-toggler');
        if (sidebarToggler) {
            sidebarToggler.addEventListener('click', function() {
                resizeCrudTableColumnWidths();
            });
        }
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

    // Abort previous fixedHeader event listeners if any
    if (config._fixedHeaderController) {
        config._fixedHeaderController.abort();
    }
    config._fixedHeaderController = new AbortController();
    const fhSignal = config._fixedHeaderController.signal;

    const ensureActivation = function(explicitOffset) {
        const offsetValue = resolveFixedHeaderOffset(fixedHeader, explicitOffset);
        const rect = tableElement.getBoundingClientRect();
        const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
        const currentlyEnabled = fixedHeader.enabled();
        const headerHeight = measureFixedHeaderHeight(fixedHeader, headerElement);
        const margins = deriveFixedHeaderMargins(headerHeight);

        const withinViewport = rect.top < viewportHeight - 1 && rect.bottom > offsetValue + 1;
        if (!withinViewport) {
            if (currentlyEnabled) {
                fixedHeader.disable();
            }
            return false;
        }

        const headerBottom = rect.top + headerHeight;
        const clearanceThreshold = currentlyEnabled ? offsetValue + margins.disableMargin : offsetValue - margins.enableMargin;
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

    const recalculate = function(reason) {
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

    const scheduleRecalculation = function(reason) {
        if (state.timer) {
            return;
        }

        state.timer = setTimeout(function() {
            state.timer = null;
            recalculate(reason || 'timer');
        }, 75);
    };

    const addListener = function(target, eventName, handler) {
        if (!target || !target.addEventListener) {
            return;
        }
        target.addEventListener(eventName, handler, false);
        state.listeners.push(function() { target.removeEventListener(eventName, handler, false); });
    };

    recalculate('initial');
    setTimeout(function() { recalculate('delayed-initial'); }, 150);

    addListener(window, 'resize', function() { scheduleRecalculation('window:resize'); });
    addListener(window, 'orientationchange', function() { scheduleRecalculation('window:orientationchange'); });
    addListener(window, 'scroll', function() { scheduleRecalculation('window:scroll'); });

    // DataTable events for fixedHeader recalculation
    apiInstance.on('column-visibility.dt', function(evt) {
        scheduleRecalculation('dt:' + evt.type);
    });
    apiInstance.on('length.dt', function(evt) {
        scheduleRecalculation('dt:' + evt.type);
    });
    apiInstance.on('responsive-resize', function(evt) {
        scheduleRecalculation('dt:' + evt.type);
    });
    apiInstance.on('draw.dt', function(evt) {
        scheduleRecalculation('dt:' + evt.type);
    });

    // destroy.dt: cleanup and abort the controller (removing all fixedHeader listeners)
    apiInstance.on('destroy.dt', function() {
        if (state.timer) {
            clearTimeout(state.timer);
            state.timer = null;
        }

        state.listeners.forEach(function(cleanup) {
            cleanup();
        });
        state.listeners.length = 0;

        config._fixedHeaderController.abort();
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

        elements.forEach(function(element) {
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
    
    // Update the browser URL if needed - use navbar's data-filter-params as source of truth
    // so that accumulated state from multiple events (e.g. select2_ajax) is included
    if (shouldUpdateUrl) {
        var browserUrl;
        var navbar = document.querySelector('.navbar-filters[data-component-id="' + tableId + '"]');
        if (navbar) {
            var accumulatedParams = new URLSearchParams(navbar.getAttribute('data-filter-params') || '');

            if (filterValue !== '' && filterValue != null) {
                accumulatedParams.set(filterName, filterValue);
            } else {
                accumulatedParams.delete(filterName);
                var filterElement = navbar.querySelector('li[filter-name="' + filterName + '"]');
                if (filterElement) {
                    var displayAttr = filterElement.getAttribute('data-display-filter-attribute-name');
                    if (displayAttr) {
                        accumulatedParams.delete(displayAttr);
                    }
                }
            }

            browserUrl = window.location.href;
            var allFilters = navbar.querySelectorAll('li[filter-name]');
            allFilters.forEach(function(filter) {
                var fName = filter.getAttribute('filter-name');
                browserUrl = addOrUpdateUriParameter(browserUrl, fName, null);
                var displayAttr = filter.getAttribute('data-display-filter-attribute-name');
                if (displayAttr) {
                    browserUrl = addOrUpdateUriParameter(browserUrl, displayAttr, null);
                }
            });

            // Now add back only what's currently in data-filter-params
            var paramsObj = {};
            accumulatedParams.forEach(function(value, key) { paramsObj[key] = value; });
            browserUrl = addOrUpdateUriParameter(browserUrl, paramsObj);
        } else {
            browserUrl = addOrUpdateUriParameter(window.location.href, filterName, filterValue);
        }
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
    
    // Get action column index
    const actionColumnTh = table.querySelector('th[data-action-column="true"]');
    if (!actionColumnTh) return;
    const actionColumnIndex = Array.from(actionColumnTh.parentElement.children).indexOf(actionColumnTh);
    if (actionColumnIndex === -1) return;

    table.querySelectorAll('tbody tr').forEach(function(tr) {
        const cells = tr.querySelectorAll('td');
        const actionCell = cells[actionColumnIndex];
        if (!actionCell) return;

        // If already processed, skip
        if (actionCell.querySelector('.actions-buttons-column')) return;

        const actionButtons = Array.from(actionCell.querySelectorAll('a.btn.btn-link, .btn-group')).filter(function(el) {
            return !el.closest('.btn-group');
        });

        if (actionButtons.length < minimumButtonsToBuildDropdown) return;

        // Prepare buttons as dropdown items (index-based; note vanilla .slice().map() uses (el, i) order)
        var buttonsForDropdown = actionButtons.slice(buttonsToShowBeforeDropdown);

        // If there are no buttons to go into the dropdown, skip
        if (buttonsForDropdown.length === 0) return;

        buttonsForDropdown.forEach(function(action) {
            if (action.classList.contains('btn-group')) {
                action.classList.add('nested-dropdown-item', 'd-flex', 'nested-dropdown', 'align-items-stretch', 'p-0');
                action.classList.remove('btn-group');
                
                var btns = action.querySelectorAll('a.btn');
                
                btns.forEach(function(btn) {
                    if (!btn.classList.contains('dropdown-toggle')) {
                        btn.classList.add('flex-grow-1', 'py-1', 'px-3');
                        btn.classList.remove('btn', 'btn-sm', 'btn-link', 'pr-0', 'pl-1', 'dropdown-item');
                        var icon = btn.querySelector('i');
                        if (icon) icon.classList.add('me-2', 'text-primary');
                    } else {
                        btn.classList.add('px-2', 'py-1', 'text-primary', 'dropdown-toggle-split');
                        btn.classList.remove('btn', 'btn-sm', 'btn-link', 'pr-0', 'pl-1', 'dropdown-item', 'dropdown-toggle');
                        btn.setAttribute('href', 'javascript:void(0)');
                        btn.removeAttribute('data-bs-toggle');
                        btn.removeAttribute('data-toggle');
                        Object.assign(btn.style, {
                            width: 'auto',
                            cursor: 'pointer',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center'
                        });
                    }
                });
            } else {
                action.classList.add('dropdown-item');
                action.classList.remove('btn', 'btn-sm', 'btn-link');
                var icon2 = action.querySelector('i');
                if (icon2) icon2.classList.add('me-2', 'text-primary');
            }
        });

        // Wrap the cell contents for the dropdown
        // Create outer dropdown div
        var dropdownDiv = document.createElement('div');
        dropdownDiv.className = 'dropdown';
        // Create dropdown-menu div
        var dropdownMenuDiv = document.createElement('div');
        dropdownMenuDiv.className = 'dropdown-menu';

        // Move all children of actionCell into dropdown-menu
        while (actionCell.firstChild) {
            dropdownMenuDiv.appendChild(actionCell.firstChild);
        }
        // Put dropdown-menu inside dropdown
        dropdownDiv.appendChild(dropdownMenuDiv);
        // Put dropdown inside actionCell
        actionCell.appendChild(dropdownDiv);

        // Prepend the toggle button
        var actionsLabel = (window.crud.tableConfigs[tableId] && window.crud.tableConfigs[tableId].language && window.crud.tableConfigs[tableId].language.actions) || 'Actions';
        var toggleBtn = document.createElement('button');
        toggleBtn.className = 'btn btn-sm px-2 py-1 btn-outline-primary dropdown-toggle actions-buttons-column';
        toggleBtn.setAttribute('type', 'button');
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.textContent = actionsLabel;
        actionCell.insertBefore(toggleBtn, actionCell.firstChild);

        // Prepend the remaining buttons (shown before the dropdown)
        var remainingButtons = actionButtons.slice(0, buttonsToShowBeforeDropdown);
        for (var i = remainingButtons.length - 1; i >= 0; i--) {
            actionCell.insertBefore(remainingButtons[i], actionCell.firstChild);
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

        // Abort previous controllers for this table
        const config = window.crud.tableConfigs[tableId];
        if (config._lineActionsController) config._lineActionsController.abort();
        if (config._lineActionsDocController) config._lineActionsDocController.abort();
        if (config._nestedActionsController) config._nestedActionsController.abort();

        config._lineActionsController = new AbortController();
        config._lineActionsDocController = new AbortController();
        config._nestedActionsController = new AbortController();
        
        // Ensure the DOM is ready (the enclosing code already ran on DOMContentLoaded,
        // and we are in a 500ms timeout, but guard just in case)
        function whenReady(fn) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fn);
            } else {
                fn();
            }
        }

        whenReady(function() {
            // Use event delegation for dynamically created elements (lineActions)
            table.addEventListener('click', function(e) {
                var target = e.target.closest('.actions-buttons-column.dropdown-toggle');
                if (!target) return;

                e.preventDefault();
                e.stopPropagation();
                
                var dropdown = target.nextElementSibling;
                // Only select the direct child dropdown-menu to avoid selecting nested dropdowns
                var menu = null;
                if (dropdown && dropdown.classList.contains('dropdown')) {
                    menu = dropdown.querySelector(':scope > .dropdown-menu');
                }
                
                // Check if the menu is already open
                var wasOpen = menu ? (menu.classList.contains('show')) : false;

                // close all dropdowns in this table
                var allToggleBtns = table.querySelectorAll('.actions-buttons-column');
                allToggleBtns.forEach(function(btn) {
                    var nextEl = btn.nextElementSibling;
                    if (nextEl && nextEl.classList.contains('dropdown')) {
                        var childMenu = nextEl.querySelector(':scope > .dropdown-menu');
                        if (childMenu) {
                            childMenu.classList.remove('show');
                            childMenu.style.display = 'none';
                        }
                    }
                });
                
                // if it was open, we just closed it, so we are done
                if (wasOpen) {
                    return;
                }

                // if no menu found, try to find it differently
                if (!menu) {
                    if (dropdown && dropdown.classList.contains('dropdown')) {
                        var ul = dropdown.querySelector('ul');
                        if (ul) {
                            ul.classList.add('dropdown-menu', 'show');
                            ul.style.display = 'block';
                            
                            // Position the UL
                            var buttonRect = target.getBoundingClientRect();
                            Object.assign(ul.style, {
                                position: 'fixed',
                                top: (buttonRect.bottom + 5) + 'px',
                                left: buttonRect.left + 'px',
                                zIndex: '999999',
                                display: 'block',
                                minWidth: '160px'
                            });
                            
                            return;
                        }
                    }
                    return;
                }
                
                // Show this dropdown
                menu.classList.add('show');
                menu.style.display = 'block';
                
                // Force positioning
                var buttonRect = target.getBoundingClientRect();
                var menuHeight = menu.getBoundingClientRect().height || 150;
                var menuWidth = menu.getBoundingClientRect().width || 160;
                var windowHeight = window.innerHeight;
                var windowWidth = window.innerWidth;
                
                var top = buttonRect.bottom + 5;
                var left = buttonRect.left;

                // check position if going off screen vertically
                if (buttonRect.bottom + menuHeight > windowHeight) {
                    top = buttonRect.top - menuHeight - 5;
                }

                // check position if going off screen horizontally
                if (left + menuWidth > windowWidth) {
                    left = buttonRect.right - menuWidth;
                }
                
                // apply positioning
                Object.assign(menu.style, {
                    position: 'fixed',
                    top: top + 'px',
                    left: left + 'px',
                    zIndex: '999999',
                    display: 'block',
                    minWidth: '160px'
                });
            }, { signal: config._lineActionsController.signal });
            
            // Close on outside click, but only for line action dropdowns in this table
            document.addEventListener('click', function(e) {
                // Only close line action dropdowns, not export button dropdowns
                var actionsColEl = e.target.closest('#' + tableId + ' .actions-buttons-column');
                var insideActionsBtn = !!actionsColEl;
                var insideNextDropdown = false;
                if (actionsColEl) {
                    var nextEl = actionsColEl.nextElementSibling;
                    if (nextEl && nextEl.classList.contains('dropdown')) {
                        insideNextDropdown = nextEl.contains(e.target);
                    }
                }
                
                if (!insideActionsBtn && !insideNextDropdown) {
                    var btns = document.querySelectorAll('#' + tableId + ' .actions-buttons-column');
                    btns.forEach(function(btn) {
                        var next = btn.nextElementSibling;
                        if (next && next.classList.contains('dropdown')) {
                            var m = next.querySelector('.dropdown-menu');
                            if (m) {
                                m.classList.remove('show');
                                m.style.display = 'none';
                            }
                        }
                    });
                }
            }, { signal: config._lineActionsDocController.signal });

            // Handle nested dropdown toggles
            table.addEventListener('click', function(e) {
                var target = e.target.closest('.nested-dropdown .dropdown-toggle-split, .nested-dropdown > a:not(.dropdown-toggle-split)');
                if (!target) return;

                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                var parent = target.closest('.nested-dropdown');
                if (!parent) return;
                var menu = parent.querySelector('.dropdown-menu');
                
                // Close other nested dropdowns in the same parent menu
                var siblings = Array.from(parent.parentElement.children).filter(function(c) {
                    return c !== parent;
                });
                siblings.forEach(function(sibling) {
                    var siblingMenus = sibling.querySelectorAll('.dropdown-menu');
                    siblingMenus.forEach(function(sm) {
                        sm.classList.remove('show');
                        sm.style.display = 'none';
                    });
                });
                
                // Toggle this one
                if (menu) {
                    if (menu.classList.contains('show')) {
                        menu.classList.remove('show');
                        menu.style.display = 'none';
                    } else {
                        menu.classList.add('show');
                        menu.style.display = 'block';
                        // Ensure positioning
                        Object.assign(menu.style, {
                            position: 'absolute',
                            top: '100%',
                            left: '0',
                            marginTop: '0',
                            marginLeft: '0',
                            zIndex: '1000'
                        });
                    }
                }
            }, { signal: config._nestedActionsController.signal });
        });
    }, 500);
}
</script>