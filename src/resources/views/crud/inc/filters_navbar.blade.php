<nav class="navbar navbar-expand-lg navbar-filters mb-0 py-0 shadow-none" data-component-id="{{ $componentId ?? '' }}">
    {{-- Brand and toggle get grouped for better mobile display --}}
    <a class="nav-item d-none d-lg-block my-auto"><span class="la la-filter"></span></a>
    <button class="navbar-toggler ms-3"
            type="button"
            data-toggle="collapse"  {{-- for Bootstrap v4 --}}
            data-target="#bp-filters-navbar" {{-- for Bootstrap v4 --}}
            data-bs-toggle="collapse"   {{-- for Bootstrap v5 --}}
            data-bs-target="#bp-filters-navbar"   {{-- for Bootstrap v5 --}}
            aria-controls="bp-filters-navbar"
            aria-expanded="false"
            aria-label="{{ trans('backpack::crud.toggle_filters') }}">
        <span class="la la-filter"></span> {{ trans('backpack::crud.filters') }}
    </button>

    {{-- Collect the nav links, forms, and other content for toggling --}}
    <div class="collapse navbar-collapse" id="bp-filters-navbar">
        <ul class="nav navbar-nav">
        {{-- THE ACTUAL FILTERS --}}
                @foreach ($crud->filters() as $filter)
                    @includeFirst($filter->getNamespacedViewWithFallbacks())
                @endforeach
        <li class="nav-item"><a href="#" class="nav-link remove_filters_button {{ count(Request::input()) != 0 ? '' : 'invisible' }}"><i class="la la-eraser"></i> {{ trans('backpack::crud.remove_filters') }}</a></li>
        </ul>
    </div>{{-- /.navbar-collapse --}}
</nav>
  
@push('after_scripts')
    @basset('https://cdn.jsdelivr.net/npm/urijs@1.19.11/src/URI.min.js')
    <script>
    if(typeof addOrUpdateUriParameter !== 'function') {
        function addOrUpdateUriParameter(uri, parameter, value) {
            let new_url = URI(uri).normalizeQuery();

            // this param is only needed in datatables persistent url redirector
            // not when applying filters so we remove it.
            if (new_url.hasQuery('persistent-table')) {
                new_url.removeQuery('persistent-table');
            }

            if (new_url.hasQuery(parameter)) {
                new_url.removeQuery(parameter);
            }

            if (value !== '' && value != null) {
                new_url = new_url.addQuery(parameter, value);
            }

            // Update all remove filter buttons visibility
            document.querySelectorAll('.remove_filters_button').forEach(function(button) {
                button.classList.toggle('invisible', !new_url.query());
            });

            return new_url.normalizeQuery().toString();
        }
    }

    if(typeof updatePageUrl !== 'function') {
        function updatePageUrl(filterName, filterValue, currentUrl = null) {
            currentUrl = currentUrl || window.location.href;
            let newUrl = addOrUpdateUriParameter(currentUrl, filterName, filterValue);
            crud.updateUrl(newUrl);
            return newUrl;
        }
    }

    if(typeof updateDatatablesOnFilterChange !== 'function') {
        function updateDatatablesOnFilterChange(filterName, filterValue, update_url = false, debounce = 500, tableId = 'crudTable') {
            // Get the table instance based on the tableId
            let table = window.crud.tables[tableId] || window.crud.table;
            
            if (!table) {
                console.error('No table found for tableId:', tableId);
                return;
            }
            
            // behaviour for ajax tables
            let currentAjaxUrl = table.ajax.url();
            let new_ajax_url = addOrUpdateUriParameter(currentAjaxUrl, filterName, filterValue);
            
            // Update the table's ajax URL
            table.ajax.url(new_ajax_url);

            let browser_url = updatePageUrl(filterName, filterValue, window.location.href);

            // when we are clearing ALL filters, we would not update the table url here, because this is done PER filter
            // and we have a function that will do this update for us after all filters had been cleared.
            if(update_url) {
                // replace the datatables ajax url with new_ajax_url and reload it
                callFunctionOnce(function() { refreshDatatablesOnFilterChange(new_ajax_url, tableId) }, debounce, 'refreshDatatablesOnFilterChange_' + tableId);
            }

            return new_ajax_url;
        }
    }

    /**
     * calls the function func once within the within time window.
     * this is a debounce function which actually calls the func as
     * opposed to returning a function that would call func.
     * 
     * @param func    the function to call
     * @param within  the time window in milliseconds, defaults to 300
     * @param timerId an optional key, defaults to func
     * 
     * FROM: https://stackoverflow.com/questions/27787768/debounce-function-in-jquery
     */
    if(typeof callFunctionOnce !== 'function') {
        function callFunctionOnce(func, within = 300, timerId = null) {
            window.callOnceTimers = window.callOnceTimers || {};
            timerId = timerId || func;
            if (window.callOnceTimers[timerId]) {
                clearTimeout(window.callOnceTimers[timerId]);
            }
            window.callOnceTimers[timerId] = setTimeout(func, within);
        }
    }

    if(typeof refreshDatatablesOnFilterChange !== 'function') {
        function refreshDatatablesOnFilterChange(url, tableId = 'crudTable')
        {
            // Get the table instance based on the tableId
            let table = window.crud.tables[tableId] || window.crud.table;
            
            if (!table) {
                console.error('No table found for refresh, tableId:', tableId);
                return;
            }
            
            // replace the datatables ajax url with new_url and reload it
            table.ajax.url(url).load();
        }
    }   

    // button to remove all filters
    document.addEventListener('DOMContentLoaded', function () {

        // find all nav.navbar-filters
        let filtersNavbar = document.querySelectorAll('.navbar-filters');

        // if there are no navbars, return
        if (!filtersNavbar.length) {
            return;
        }

        // run the init function for each filter
        filtersNavbar.forEach(function(navbar) {   
            let filters = navbar.querySelectorAll('li[filter-init-function]');

            if(filters.length === 0) {
                return;
            }

            // Add event listener only once per navbar to avoid duplication
            if (!navbar.hasAttribute('data-filter-events-bound')) {
                navbar.setAttribute('data-filter-events-bound', 'true');
                
                document.addEventListener('backpack:filter:changed', function(event) {
                    // check if any of the filters are active
                    let anyActiveFilters = false;

                    filters.forEach(function(filter) {
                        if (filter.classList.contains('active')) {
                            anyActiveFilters = true;
                        }
                    });

                    if(anyActiveFilters === true) {
                        navbar.querySelector('.remove_filters_button').classList.remove('invisible');
                    }else{
                        navbar.querySelector('.remove_filters_button').classList.add('invisible');
                    }
                });
            }
            
            filters.forEach(function(filter) {
                let initFunction = filter.getAttribute('filter-init-function');
                if (window[initFunction]) {
                    window[initFunction](filter, navbar);
                }
            });

            if(filtersNavbar.length === 0) {
                return;
            }

            let removeFiltersButton = navbar.querySelector('.remove_filters_button');
            if (removeFiltersButton) {
                removeFiltersButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Find the closest datatable to this navbar
                    let closestTable = null;
                    let navbarParent = navbar.parentElement;
                    
                    // Look for the datatable in the DOM - search in the entire document if needed
                    if (navbarParent) {
                        // First try to find a table with class crud-table
                        closestTable = navbarParent.querySelector('table.crud-table');
                        
                        // If not found, try to find a table with an ID that starts with "crudTable"
                        if (!closestTable) {
                            closestTable = navbarParent.querySelector('table[id^="crudTable"]');
                        }
                        
                        // If not found, try to find any table that might be the datatable
                        if (!closestTable) {
                            closestTable = navbarParent.querySelector('table.dataTable');
                        }
                        
                        // If still not found, search in the whole document
                        if (!closestTable) {
                            closestTable = document.querySelector('table.crud-table');
                        }
                        
                        // Last resort - any crudTable in the document
                        if (!closestTable) {
                            closestTable = document.querySelector('table[id^="crudTable"]');
                        }
                    }
                    
                    // Get the table ID if found, otherwise use the default 'crudTable'
                    let tableId = 'crudTable'; // Default fallback
                    
                    if (closestTable) {
                        // Try to get the ID attribute first
                        tableId = closestTable.getAttribute('id') || '';
                        
                        // If no ID found, try to get it from the DataTable instance
                        if (!tableId && $.fn.DataTable.isDataTable(closestTable)) {
                            try {
                                const dt = $(closestTable).DataTable();
                                if (dt && dt.table && dt.table().node && dt.table().node().id) {
                                    tableId = dt.table().node().id;
                                }
                            } catch (e) {
                                // Silently continue if error getting ID from DataTable
                            }
                        }
                        
                        // If still no ID, check the navbar's data-component-id
                        if (!tableId) {
                            tableId = navbar.getAttribute('data-component-id') || '';
                        }
                        
                        // Last resort - use default
                        if (!tableId) {
                            tableId = 'crudTable';
                        }
                    }
                    
                    document.dispatchEvent(new CustomEvent('backpack:filters:cleared', {
                        detail: {
                            navbar: navbar,
                            filters: filters,
                            tableId: tableId
                        }
                    }));

                    filters.forEach(function(filter) {
                        filter.dispatchEvent(new CustomEvent('backpack:filter:clear', {
                            detail: {
                                clearAllFilters: true,
                            }
                        }));
                    });

                    // After clearing filters, re-initialize them to ensure proper state
                    setTimeout(function() {
                        filters.forEach(function(filter) {
                            let initFunction = filter.getAttribute('filter-init-function');
                            if (window[initFunction]) {
                                window[initFunction](filter, navbar);
                            }
                        });
                    }, 50);

                    // Force update the URL to remove all filter parameters after a short delay
                    // to ensure all filters have processed the clear event
                    setTimeout(function() {
                        let currentUrl = window.location.href;
                        let cleanUrl = URI(currentUrl).search('').toString();
                        if (window.crud && window.crud.updateUrl) {
                            window.crud.updateUrl(cleanUrl);
                        }
                    }, 100);
                });
            }

            filters.forEach(function(filter) {
                filter.addEventListener('backpack:filter:clear', function() {
                    let anyActiveFilters = false;
                    filters.forEach(function (filterInstance) {
                        if (filterInstance.classList.contains('active')) {
                            anyActiveFilters = true;
                        }
                    });

                    if (anyActiveFilters === false) {
                        removeFiltersButton?.classList.add('invisible');
                    }
                });
            });
        });
    });
    </script>
@endpush
