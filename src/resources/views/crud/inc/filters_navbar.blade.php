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

@php
    $showFilterValues = $showFilterValues ?? config('backpack.operations.list.showFilterValues', false);
@endphp

<div class="active-filter-badges d-flex flex-wrap align-items-center gap-1 px-3 pb-2"
     id="filter-badges-{{ $componentId ?? '' }}"
     data-show-filter-values-default="{{ var_export($showFilterValues, true) }}"
     style="display:none;">
</div>
  
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

            // When parameter is an object, treat it as {key: value} pairs to add/update all at once.
            // Otherwise fall back to the original single-parameter behavior.
            if (typeof parameter === 'object' && parameter !== null) {
                for (var key in parameter) {
                    if (!parameter.hasOwnProperty(key)) continue;
                    var val = parameter[key];
                    if (new_url.hasQuery(key)) {
                        new_url.removeQuery(key);
                    }
                    if (val !== '' && val != null) {
                        new_url = new_url.addQuery(key, val);
                    }
                }
            } else {
                if (new_url.hasQuery(parameter)) {
                    new_url.removeQuery(parameter);
                }
                if (value !== '' && value != null) {
                    new_url = new_url.addQuery(parameter, value);
                }
            }

            // Update all remove filter buttons visibility
            document.querySelectorAll('.remove_filters_button').forEach(function(button) {
                button.classList.toggle('invisible', !new_url.query());
            });

            return new_url.normalizeQuery().toString();
        }
    }

    /**
     * Get a human-readable display value for a filter's current value.
     */
    if(typeof getFilterDisplayValue !== 'function') {
        function getFilterDisplayValue(filter, rawValue, filterName) {
            var filterType = filter.getAttribute('filter-type');
            var filterOptions = {};

            // If the filter has a data-display-filter-attribute-name, use that key
            // to look up the display value in data-filter-params. This allows filters
            // like select2_ajax to show the human-readable text instead of the raw ID.
            var displayAttribute = filter.getAttribute('data-display-filter-attribute-name');
            if (displayAttribute) {
                var displayNavbar = filter.closest('.navbar-filters');
                if (displayNavbar) {
                    var displayParams = new URLSearchParams(displayNavbar.getAttribute('data-filter-params') || '');
                    var displayValue = displayParams.get(displayAttribute);
                    if (displayValue) return displayValue;
                }
            }

            try {
                filterOptions = JSON.parse(filter.getAttribute('data-filter-options') || '{}');
            } catch(e) {}

            switch(filterType) {
                case 'text':
                case 'date':
                case 'view':
                    return rawValue;
                case 'dropdown':
                case 'select2':
                    return filterOptions[rawValue] || rawValue;
                case 'select2_multiple':
                    try {
                        var values = JSON.parse(rawValue);
                        return values.map(function(v) { return filterOptions[v] || v; }).join(', ');
                    } catch(e) {
                        return rawValue;
                    }
                case 'date_range':
                    try {
                        var dates = JSON.parse(rawValue);
                        return (dates.from || '') + ' \u2192 ' + (dates.to || '');
                    } catch(e) {
                        return rawValue;
                    }
                case 'range':
                    try {
                        var range = JSON.parse(rawValue);
                        return (range.from || '') + ' \u2192 ' + (range.to || '');
                    } catch(e) {
                        return rawValue;
                    }
                case 'simple':
                case 'trashed':
                    return '';
                default:
                    return rawValue;
            }
        }
    }

    /**
     * Sync the active filter badges below the navbar from data-filter-params.
     */
    if(typeof syncFilterBadges !== 'function') {
        function syncFilterBadges(navbar) {
            var badgesContainer = document.getElementById('filter-badges-' + (navbar.getAttribute('data-component-id') || ''));
            if (!badgesContainer) return;

            var params = new URLSearchParams(navbar.getAttribute('data-filter-params') || '');
            var filters = navbar.querySelectorAll('li[filter-name]');
            var html = '';
            // Default from the navbar-level setting (on the badges container)
            var defaultShow = badgesContainer.getAttribute('data-show-filter-values-default') === 'true';

            filters.forEach(function(filter) {
                var filterName = filter.getAttribute('filter-name');
                if (!params.has(filterName)) return;

                // Check per-filter showFilterValues; if absent (NULL/empty), inherit from default
                var filterShow = filter.getAttribute('data-show-filter-values');
                if (filterShow === 'false') return;
                if (filterShow !== 'true' && !defaultShow) return;

                var rawValue = params.get(filterName);
                var displayValue = getFilterDisplayValue(filter, rawValue, filterName);
                var label = filter.querySelector('a') ? filter.querySelector('a').textContent.trim().replace(/\s*\u25BE\s*$/, '') : filterName;

                // Use custom label template if set
                var labelTemplate = filter.getAttribute('data-filter-values-label');
                if (labelTemplate && displayValue) {
                    label = labelTemplate.replace(':value', displayValue);
                } else {
                    label = displayValue ? label + ': ' + displayValue : label;
                }

                html += '<span class="filter-badge me-1 mb-1" data-filter-name="' + filterName + '">'
                    + label
                    + ' <button type="button" class="filter-badge-close" aria-label="Remove ' + label + ' filter">&times;</button>'
                    + '</span>';
            });

            badgesContainer.innerHTML = html;
            badgesContainer.style.display = html ? '' : 'none';

            // Wire up badge dismiss buttons
            badgesContainer.querySelectorAll('.filter-badge button').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var badge = btn.closest('.filter-badge');
                    var filterName = badge.getAttribute('data-filter-name');
                    var filter = navbar.querySelector('li[filter-name="' + filterName + '"]');
                    if (filter) {
                        // Clear the filter via its own event so the UI resets
                        filter.dispatchEvent(new CustomEvent('backpack:filter:clear'));

                        // Dispatch changed event with null value to trigger table refresh
                        // The global backpack:filter:changed handler will update data-filter-params and sync badges
                        document.dispatchEvent(new CustomEvent('backpack:filter:changed', {
                            detail: {
                                filterName: filterName,
                                filterValue: null,
                                shouldUpdateUrl: true,
                                debounce: filter.getAttribute('filter-debounce') || 0,
                                componentId: navbar.getAttribute('data-component-id') || '',
                            }
                        }));
                    }
                });
            });
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

    // Each filter navbar stores its own filter state in a `data-filter-params` attribute.
    // This is the source of truth for consumers (e.g. report scripts, badges) — they read from
    // the navbar DOM element, not the browser URL. This supports scenarios with
    // multiple independent filter navbars on the same page.
    // When there is only one navbar and shouldUpdateUrl is true, the browser URL is also kept
    // in sync as a convenience (bookmarkable URLs, shareable links).
    // The URL is built from the accumulated data-filter-params (not the single event param)
    // so filters that dispatch multiple events (e.g. select2_ajax) result in a single URL update
    // containing all params.
    document.addEventListener('backpack:filter:changed', function(event) {
        if (!event.detail) return;

        // Find the navbar that owns this filter
        var componentId = event.detail.componentId || '';
        var navbar = componentId
            ? document.querySelector('.navbar-filters[data-component-id="' + componentId + '"]')
            : document.querySelector('.navbar-filters');

        if (!navbar) return;

        // Always update the navbar's stored filter state (source of truth),
        // regardless of shouldUpdateUrl, so that accumulated state is available
        var params = new URLSearchParams(navbar.getAttribute('data-filter-params') || '');
        if (event.detail.filterValue !== '' && event.detail.filterValue != null) {
            params.set(event.detail.filterName, event.detail.filterValue);
        } else {
            params.delete(event.detail.filterName);
        }
        navbar.setAttribute('data-filter-params', params.toString());

        // Sync the active filter badges
        syncFilterBadges(navbar);

        // Mirror to the browser URL only when shouldUpdateUrl is true AND there is a single filter navbar.
        // Build the URL from the navbar's accumulated data-filter-params so that all params
        // are included in a single URL update, even when multiple events contributed to the state.
        if (event.detail.shouldUpdateUrl && document.querySelectorAll('.navbar-filters').length <= 1) {
            var accumulatedParams = new URLSearchParams(navbar.getAttribute('data-filter-params') || '');
            var paramsObj = {};
            accumulatedParams.forEach(function(value, key) { paramsObj[key] = value; });
            var newUrl = addOrUpdateUriParameter(window.location.href, paramsObj);
            window.history.replaceState({}, '', newUrl);
        }
    });

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

            // Seed the navbar's data-filter-params from the URL, scoped to this navbar's own filters.
            // This lets consumers read filter state from the navbar DOM element from the start,
            // and ensures shared URLs with filter params are applied correctly on load.
            var urlParams = new URLSearchParams(window.location.search);
            var navbarParams = new URLSearchParams();
            filters.forEach(function(filter) {
                var filterName = filter.getAttribute('filter-name');
                if (urlParams.has(filterName)) {
                    navbarParams.set(filterName, urlParams.get(filterName));
                }
                // Also seed any display attribute (e.g. category_text for select2_ajax filters)
                var displayAttr = filter.getAttribute('data-display-filter-attribute-name');
                if (displayAttr && urlParams.has(displayAttr)) {
                    navbarParams.set(displayAttr, urlParams.get(displayAttr));
                }
            });
            navbar.setAttribute('data-filter-params', navbarParams.toString());

            // Sync badges on initial load
            syncFilterBadges(navbar);

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
                    
                    // 1. Clear each filter's UI state
                    filters.forEach(function(filter) {
                        filter.dispatchEvent(new CustomEvent('backpack:filter:clear', {
                            detail: {
                                clearAllFilters: true,
                            }
                        }));
                    });

                    // 2. Clear the navbar's stored filter state and clean the browser URL
                    navbar.setAttribute('data-filter-params', '');

                    // Sync badges after clearing all filters
                    syncFilterBadges(navbar);

                    if (document.querySelectorAll('.navbar-filters').length <= 1) {
                        let cleanUrl = URI(window.location.href).search('').toString();
                        if (window.crud && typeof window.crud.updateUrl === 'function') {
                            window.crud.updateUrl(cleanUrl);
                        } else {
                            window.history.replaceState({}, '', cleanUrl);
                        }
                    }

                    // 3. Notify consumers that all filters have been cleared
                    document.dispatchEvent(new CustomEvent('backpack:filters:cleared', {
                        detail: {
                            navbar: navbar,
                            filters: filters,
                            tableId: tableId
                        }
                    }));

                    // 4. Re-initialize filters to ensure proper state
                    setTimeout(function() {
                        filters.forEach(function(filter) {
                            let initFunction = filter.getAttribute('filter-init-function');
                            if (window[initFunction]) {
                                window[initFunction](filter, navbar);
                            }
                        });
                    }, 50);
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

                    // Sync badges when individual filter is cleared
                    syncFilterBadges(navbar);
                });
            });
        });
    });
    </script>
@endpush
