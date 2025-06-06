@if($crud->get('list.detailsRow'))
    <script>
    // Define the function in the global scope
    window.registerDetailsRowButtonAction = function(tableId = 'crudTable') {        
        // Get the target table element
        const tableElement = document.getElementById(tableId);
        if (!tableElement) {
            console.error(`Table #${tableId} not found in DOM`);
            return;
        }
        
        // Check if this table has already been initialized for details row
        if (tableElement.getAttribute('data-details-row-initialized') === 'true') {
            return;
        }
        
        // Mark this table as initialized
        tableElement.setAttribute('data-details-row-initialized', 'true');
        
        // Make sure the ajaxDatatables rows also have the correct classes
        const detailsButtons = tableElement.querySelectorAll('tbody td .details-row-button');
        detailsButtons.forEach(button => {
            const parentCell = button.closest('td');
            if (parentCell) {
                // Ensure the cell has the correct classes but DO NOT add cursor-pointer to the cell
                // as we only want the button to be clickable
                parentCell.classList.add('details-control');
            }
        });
        
        // Now add event listeners ONLY to the buttons
        const buttons = tableElement.querySelectorAll('tbody td .details-row-button');
        buttons.forEach(button => {
            // Remove any existing event listeners by cloning and replacing each button
            const newButton = button.cloneNode(true);
            if (button.parentNode) {
                button.parentNode.replaceChild(newButton, button);
            }
            
            // Add the event listener to the new button
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const tr = this.closest('tr');
                // Ensure the tr reference is valid
                if (!tr) {
                    console.error('Could not find parent row');
                    return;
                }
                
                // Make sure we have access to the table
                const table = window.crud.tables[tableId];
                if (!table) {
                    console.error(`Table ${tableId} not found in crud.tables`);
                    return;
                }
                
                // Use DataTables API to get the row
                const row = table.row(tr);
                
                if (row.child.isShown()) {
                    // This row is already open - close it
                    this.classList.remove('la-minus-square-o');
                    this.classList.add('la-plus-square-o');
                    
                    // Hide with animation
                    $(row.child()).find('div.table_row_slider').slideUp(function() {
                        row.child.hide();
                        tr.classList.remove('shown');
                    });
                } else {
                    // Open this row
                    this.classList.remove('la-plus-square-o');
                    this.classList.add('la-minus-square-o');
                    
                    // Get the details with fetch API
                    const entryId = this.getAttribute('data-entry-id');
                    const url = '{{ url($crud->route) }}/' + entryId + '/details';
                                        
                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(data => {                            
                            // Use DataTables API properly
                            row.child(`<div class='table_row_slider'>${data}</div>`, 'details-row').show();
                            tr.classList.add('shown');
                            
                            // Ensure the new content is correctly shown
                            $(row.child()).find('div.table_row_slider').slideDown();
                        })
                        .catch(error => {
                            console.error('Error fetching details:', error);
                            
                            row.child(`<div class='table_row_slider'>{{ trans('backpack::crud.details_row_loading_error') }}</div>`).show();
                            tr.classList.add('shown');
                            
                            $(row.child()).find('div.table_row_slider').slideDown();
                        });
                }
            });
        });
    };
    
    // Register the function to be called for each table
    window.crud.defaultTableConfig.addFunctionToDataTablesDrawEventQueue('registerDetailsRowButtonAction');
    
    // Also run immediately for any tables already in the DOM
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.crud !== 'undefined') {
            if (window.crud.tables) {
                // For multiple tables
                Object.keys(window.crud.tables).forEach(tableId => {
                    window.registerDetailsRowButtonAction(tableId);
                });
            }
        }
    });
    </script>
@endif