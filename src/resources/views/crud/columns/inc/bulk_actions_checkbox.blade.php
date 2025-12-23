@if (!isset($entry))
    <span class="crud_bulk_actions_checkbox">
        <input type="checkbox" class="crud_bulk_actions_general_checkbox form-check-input">
    </span>
@else
    <span class="crud_bulk_actions_checkbox">
        <input type="checkbox" class="crud_bulk_actions_line_checkbox form-check-input" data-primary-key-value="{{ $entry->getKey() }}">
    </span>
@endif
@push('after_scripts')
    @bassetBlock('backpack/crud/operations/list/bulk-actions-checkbox.js')
<script>
    // Make sure window.crud exists before we try to use it
    window.crud = window.crud || {};
    window.crud.tableConfigs = window.crud.tableConfigs || {};
    
    // Intercept changes to crud.checkedItems to sync with tableConfigs
    if (!Object.getOwnPropertyDescriptor(window.crud, 'checkedItems')?.get) {
        let _checkedItems = window.crud.checkedItems || [];
        
        Object.defineProperty(window.crud, 'checkedItems', {
            get: function() {
                return _checkedItems;
            },
            set: function(value) {
                _checkedItems = value;
                
                // Sync with the main table config if it exists
                // This handles the case where legacy bulk buttons clear crud.checkedItems
                if (!window.crud.ignoreNextSetterSync && window.crud.table && window.crud.table.table && window.crud.table.table().node) {
                    let tableId = window.crud.table.table().node().id;
                    if (window.crud.tableConfigs[tableId]) {
                         if (Array.isArray(value) && value.length === 0) {
                             window.crud.tableConfigs[tableId].checkedItems = [];
                         }
                    }
                }
            },
            configurable: true
        });
    }

if (typeof window.crud.addOrRemoveCrudCheckedItem !== 'function') {
    window.crud.addOrRemoveCrudCheckedItem = function(element, tableId) {        
        const tableConfig = window.crud.tableConfigs[tableId] || window.crud;
        
        tableConfig.checkedItems = Array.isArray(tableConfig.checkedItems) ? tableConfig.checkedItems : [];
        tableConfig.lastCheckedItem = tableConfig.lastCheckedItem || false;
        
        document.querySelectorAll(`#${tableId} input.crud_bulk_actions_line_checkbox`).forEach(checkbox => {
            // check if there is a dtr-control element in the row, in case it does, add a mt-2 class to the checkbox
            const row = checkbox.closest('tr');
            const dtrControl = row.querySelector('.dtr-control');
            if (dtrControl && ! dtrControl.classList.contains('d-none')) {
                checkbox.classList.add('mt-2');
            } else {
                checkbox.classList.remove('mt-2');
            }

            const newCheckbox = checkbox.cloneNode(true);
            checkbox.parentNode.replaceChild(newCheckbox, checkbox);
            
            newCheckbox.addEventListener('click', function(e) {
                e.stopPropagation();
                
                const checked = this.checked;
                const primaryKeyValue = this.dataset.primaryKeyValue;
                
                if (checked) {
                    // add item to checkedItems if not already there
                    if (tableConfig.checkedItems.indexOf(primaryKeyValue) === -1) {
                        tableConfig.checkedItems.push(primaryKeyValue);
                    }
                    
                    // if shift has been pressed, also select all elements
                    // between the last checked item and this one
                    if (tableConfig.lastCheckedItem && e.shiftKey) {
                        let getNodeindex = elm => [...elm.parentNode.children].indexOf(elm);
                        let first = document.querySelector(`#${tableId} input.crud_bulk_actions_line_checkbox[data-primary-key-value="${tableConfig.lastCheckedItem}"]`).closest('tr');
                        let last = document.querySelector(`#${tableId} input.crud_bulk_actions_line_checkbox[data-primary-key-value="${primaryKeyValue}"]`).closest('tr');
                        let firstIndex = getNodeindex(first);
                        let lastIndex = getNodeindex(last);
                        
                        while (first !== last) {
                            first = firstIndex < lastIndex ? first.nextElementSibling : first.previousElementSibling;
                            first.querySelector('input.crud_bulk_actions_line_checkbox:not(:checked)')?.click();
                        }
                    }
                    
                    // remember that this one was the last checked item
                    tableConfig.lastCheckedItem = primaryKeyValue;
                } else {
                    // remove item from checkedItems
                    let index = tableConfig.checkedItems.indexOf(primaryKeyValue);
                    if (index > -1) tableConfig.checkedItems.splice(index, 1);
                }
                            
                window.crud.enableOrDisableBulkButtons(tableId);
            });
        });
    }
}

if (typeof window.crud.markCheckboxAsCheckedIfPreviouslySelected !== 'function') {
    window.crud.markCheckboxAsCheckedIfPreviouslySelected = function(tableId = 'crudTable') {
        const tableConfig = window.crud.tableConfigs[tableId] || window.crud;
        
        // Ensure checkedItems is always an array
        tableConfig.checkedItems = Array.isArray(tableConfig.checkedItems) ? tableConfig.checkedItems : [];
        
        let checkedItems = tableConfig.checkedItems;
        let pageChanged = localStorage.getItem('page_changed') ?? false;
        let tableUrl = window.crud.tables[tableId]?.ajax.url() || '';
        let hasFilterApplied = false;

        if (tableUrl.indexOf('?') > -1) {
            if (tableUrl.substring(tableUrl.indexOf('?') + 1).length > 0) {
                hasFilterApplied = true;
            }
        }

        // if it was not a page change, we check if datatables have any search, or the url have any parameters.
        // if you have filtered entries, and then remove the filters we are sure the entries are in the table.
        // we don't remove them in that case.
        if (!pageChanged && (window.crud.tables[tableId]?.search().length !== 0 || hasFilterApplied)) {
            tableConfig.checkedItems = [];
        }
        
        document
            .querySelectorAll(`#${tableId} input.crud_bulk_actions_line_checkbox[data-primary-key-value]`)
            .forEach(function(elem) {
                let checked = checkedItems.length && checkedItems.indexOf(elem.dataset.primaryKeyValue) > -1;
                elem.checked = checked;
                if (checked && tableConfig.checkedItems.indexOf(elem.dataset.primaryKeyValue) === -1) {
                    tableConfig.checkedItems.push(elem.dataset.primaryKeyValue);
                }
            });
        
        localStorage.removeItem('page_changed');
    }
}

window.crud.addBulkActionMainCheckboxesFunctionality = function(tableId = 'crudTable') {    
    const tableConfig = window.crud.tableConfigs[tableId] || window.crud;
    
    let mainCheckboxes = Array.from(document.querySelectorAll(`#${tableId} input.crud_bulk_actions_general_checkbox`));
    
    mainCheckboxes.forEach(checkbox => {
        const getRowCheckboxes = () => Array.from(document.querySelectorAll(`#${tableId} input.crud_bulk_actions_line_checkbox`));
        
        // set initial checked status - recalculate based on current visible checkboxes
        const updateMainCheckboxState = () => {
            const rowCheckboxes = getRowCheckboxes();
            checkbox.checked = rowCheckboxes.length > 0 && 
                document.querySelectorAll(`#${tableId} input.crud_bulk_actions_line_checkbox:not(:checked)`).length === 0;
        };
        
        // Initial state
        updateMainCheckboxState();

        // when the crud_bulk_actions_general_checkbox is selected, toggle all visible checkboxes
        checkbox.onclick = event => {            
            // Get fresh list of row checkboxes that are currently visible
            const currentRowCheckboxes = getRowCheckboxes();            
            // Only toggle checkboxes that need to change state
            currentRowCheckboxes
                .filter(elem => checkbox.checked !== elem.checked)
                .forEach(elem => {
                    elem.click();
                });
            
            // make sure all other main checkboxes have the same checked status
            mainCheckboxes.forEach(elem => elem.checked = checkbox.checked);

            // Ensure buttons are updated after mass checkbox changes
            window.crud.enableOrDisableBulkButtons(tableId);

            event.stopPropagation();
        };
    });
};

if (typeof window.crud.enableOrDisableBulkButtons !== 'function') {
    window.crud.enableOrDisableBulkButtons = function(tableId) {        
        // Get the correct table configuration
        const tableConfig = window.crud.tableConfigs[tableId] || window.crud;
        
        // Initialize checkedItems array if it doesn't exist
        tableConfig.checkedItems = Array.isArray(tableConfig.checkedItems) ? tableConfig.checkedItems : [];
        
        // Check if any items are selected
        const hasSelectedItems = tableConfig.checkedItems.length > 0;
        
        // Find the table element
        const tableElement = document.getElementById(tableId);
        if (!tableElement) {
            console.error(`Table element not found: ${tableId}`);
            return;
        }
        
        // Check if this table is configured for bulk actions
        const hasBulkActions = tableElement.getAttribute('data-has-bulk-actions') === 'true' || 
                          tableElement.getAttribute('data-has-bulk-actions') === '1';
        
        // Find all bulk buttons - search in table-specific locations first
        let bulkButtons = [];
        
        const tableSpecificContainers = [
            document.querySelector(`#bottom_buttons_${tableId}`),
            document.querySelector(`#datatable_button_stack_${tableId}`),
            document.querySelector(`.top_buttons_${tableId}`)
        ];
        
        for (const container of tableSpecificContainers) {
            if (container) {
                const containerButtons = container.querySelectorAll('.bulk-button');
                if (containerButtons.length > 0) {
                    bulkButtons = containerButtons;
                    break;
                }
            }
        }
        
        if (bulkButtons.length === 0) {
            const tableWrapper = document.getElementById(`${tableId}_wrapper`);
            if (tableWrapper) {
                bulkButtons = tableWrapper.querySelectorAll('.bulk-button');
            }
        }
        
        // Update all buttons based on selection state
        bulkButtons.forEach(btn => {
            if (hasSelectedItems) {
                btn.classList.remove('disabled');
                btn.removeAttribute('disabled');

                if (btn.hasAttribute('onclick') && !btn._onclickReplaced) {
                    const originalOnclick = btn.getAttribute('onclick');
                    
                    // Remove the original onclick attribute
                    btn.removeAttribute('onclick');
                    
                    // Add a click event listener that synchronizes first, then calls the original handler
                    btn.addEventListener('click', function(e) {
                        // Synchronize checked items with global crud object
                        window.crud.synchronizeCheckedItems(tableId);
                        
                        // Then execute the original handler
                        try {
                            // If it's a simple function call
                            if (originalOnclick.includes('(')) {
                                const funcName = originalOnclick.split('(')[0];
                                if (window[funcName]) {
                                    window[funcName](btn);
                                }
                            } else {
                                // Just evaluate it
                                eval(originalOnclick);
                            }
                        } catch (err) {
                            console.error('Error executing bulk action:', err);
                        }
                    });
                    
                    // Mark this button as having its onclick replaced
                    btn._onclickReplaced = true;
                }
            } else {
                btn.classList.add('disabled');
                btn.setAttribute('disabled', 'disabled');
            }
        });
    }
}

if (typeof window.crud.synchronizeCheckedItems !== 'function') {
    window.crud.synchronizeCheckedItems = function(tableId = 'crudTable') {
        const tableConfig = window.crud.tableConfigs[tableId] || window.crud;
        
        // Make sure both are arrays
        tableConfig.checkedItems = Array.isArray(tableConfig.checkedItems) ? tableConfig.checkedItems : [];
        
        // Copy items from tableConfig to global crud object
        window.crud.ignoreNextSetterSync = true;
        window.crud.checkedItems = [...tableConfig.checkedItems];
        window.crud.ignoreNextSetterSync = false;
    }
}

// Define a function that initializes bulk actions for a specific table
window.registerBulkActionsCheckboxes = function(tableId) {    
    // Make sure we have access to crud functions
    if (typeof window.crud !== 'object') {
        console.error('window.crud not available yet');
        return;
    }
    
    // Initialize the tableConfigs object for this table if it doesn't exist
    if (!window.crud.tableConfigs[tableId]) {
        window.crud.tableConfigs[tableId] = {
            checkedItems: [],
            lastCheckedItem: false
        };
    }
    
    // Check if the table element exists and has bulk actions enabled
    const tableElement = document.getElementById(tableId);
    if (!tableElement) {
        console.error(`Table element #${tableId} not found`);
        return;
    }
    
    const hasBulkActions = tableElement.getAttribute('data-has-bulk-actions') === 'true' || 
                        tableElement.getAttribute('data-has-bulk-actions') === '1';
    
    if (!hasBulkActions) return;
    
    // Call the required functions for setting up bulk actions
    window.crud.addOrRemoveCrudCheckedItem(null, tableId);
    window.crud.markCheckboxAsCheckedIfPreviouslySelected(tableId);
    window.crud.addBulkActionMainCheckboxesFunctionality(tableId);
    window.crud.enableOrDisableBulkButtons(tableId);
};

// Initialize all existing tables on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.bulk-button').forEach(btn => {
        btn.classList.add('disabled');
    });
});

// Add event listener for DataTables initialization
window.addEventListener('backpack:table:initialized', function(e) {
    const tableId = e.detail.tableId;
    
    // Check if the table has bulk actions
    const tableElement = document.getElementById(tableId);
    if (!tableElement) {
        console.error(`Table element #${tableId} not found`);
        return;
    }
    
    const hasBulkActions = tableElement.getAttribute('data-has-bulk-actions') === 'true' || 
                        tableElement.getAttribute('data-has-bulk-actions') === '1';
    
    if (hasBulkActions) {        
        // Make sure the function is called on each draw event
        if (window.crud.tables[tableId]) {
            window.crud.tables[tableId].on('draw.dt', function() {
                window.registerBulkActionsCheckboxes(tableId);
            });
        }
    }
});
</script>
@endBassetBlock
@endpush