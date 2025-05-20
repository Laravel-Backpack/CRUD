 @if ($crud->get('list.detailsRow'))
    <script>
    // Define the function in the global scope
    window.registerDetailsRowButtonAction = function(tableId = 'crudTable') {
        console.log(`registerDetailsRowButtonAction called for table: ${tableId}`);
        
        // Get the target table element
        const tableElement = document.getElementById(tableId);
        if (!tableElement) {
            console.error(`Table #${tableId} not found in DOM`);
            return;
        }
        
        // Check if this table has already been initialized for details row
        if (tableElement.getAttribute('data-details-row-initialized') === 'true') {
            console.log(`Table already initialized for details row: ${tableId}`);
            return; // Skip already initialized tables
        }
        
        // Mark this table as initialized
        tableElement.setAttribute('data-details-row-initialized', 'true');
        
        // Make sure the ajaxDatatables rows also have the correct classes
        const detailsButtons = tableElement.querySelectorAll('tbody td .details-row-button');
        detailsButtons.forEach(button => {
            const parentCell = button.closest('td');
            if (parentCell) {
                // Ensure the cell has the correct classes
                parentCell.classList.add('details-control', 'text-center', 'cursor-pointer');
            }
        });
        
        // Add event listener for opening and closing details
        const detailsControls = tableElement.querySelectorAll('tbody td.details-control');
        detailsControls.forEach(cell => {
            // Remove any existing event listeners by cloning and replacing the element
            const newCell = cell.cloneNode(true);
            cell.parentNode.replaceChild(newCell, cell);
            
            newCell.addEventListener('click', function(e) {
                e.stopPropagation();
                
                const tr = this.closest('tr');
                const btn = this.querySelector('.details-row-button');
                const table = window.crud.tables[tableId];
                const row = table.row(tr);
                
                if (row.child.isShown()) {
                    // This row is already open - close it
                    btn.classList.remove('la-minus-square-o');
                    btn.classList.add('la-plus-square-o');
                    
                    const slider = row.child().find('div.table_row_slider');
                    slider.slideUp(function() {
                        row.child.hide();
                        tr.classList.remove('shown');
                    });
                } else {
                    // Open this row
                    btn.classList.remove('la-plus-square-o');
                    btn.classList.add('la-minus-square-o');
                    
                    // Get the details with fetch API
                    const entryId = btn.getAttribute('data-entry-id');
                    const url = '{{ url($crud->route) }}/' + entryId + '/details';
                    
                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(data => {
                            row.child("<div class='table_row_slider'>" + data + "</div>", 'no-padding').show();
                            tr.classList.add('shown');
                            
                            // We still need to use jQuery for the slideDown animation as it's more complex to implement in vanilla JS
                            const slider = row.child().find('div.table_row_slider');
                            slider.slideDown();
                        })
                        .catch(error => {
                            row.child("<div class='table_row_slider'>{{ trans('backpack::crud.details_row_loading_error') }}</div>").show();
                            tr.classList.add('shown');
                            
                            const slider = row.child().find('div.table_row_slider');
                            slider.slideDown();
                            console.error('Error fetching details:', error);
                        });
                }
            });
        });
    };
    
    // Register the function to be called for each table
    window.crud.defaultTableConfig.addFunctionToDataTablesDrawEventQueue('registerDetailsRowButtonAction');
  </script>
@endif
