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
                
                // DataTables row.child() returns a jQuery object — unwrap to get the raw DOM element
                const getChildElement = (child) => (child && child.length !== undefined) ? child[0] : child;

                if (row.child.isShown()) {
                    // This row is already open - close it
                    this.classList.remove('la-minus-square-o');
                    this.classList.add('la-plus-square-o');
                    
                    // Slide up with vanilla JS animation, then hide
                    const childEl = getChildElement(row.child());
                    const slider = childEl ? childEl.querySelector('div.table_row_slider') : null;
                    if (slider) {
                        slider.style.overflow = 'hidden';
                        const startHeight = slider.scrollHeight;
                        slider.animate(
                            [{ height: startHeight + 'px' }, { height: '0' }],
                            { duration: 300, easing: 'ease' }
                        ).finished.then(() => {
                            row.child.hide();
                            tr.classList.remove('shown');
                        });
                    } else {
                        row.child.hide();
                        tr.classList.remove('shown');
                    }
                } else {
                    // Open this row
                    this.classList.remove('la-plus-square-o');
                    this.classList.add('la-minus-square-o');
                    
                    // Get the details with fetch API
                    const entryId = this.getAttribute('data-entry-id');
                    const url = '{{ url($crud->route) }}/' + entryId + '/details';
                                        
                    // Helper to animate slideDown on a row child slider
                    const animateSlideDown = () => {
                        const childEl = getChildElement(row.child());
                        const slider = childEl ? childEl.querySelector('div.table_row_slider') : null;
                        if (slider) {
                            slider.style.height = '0';
                            slider.style.overflow = 'hidden';
                            const targetHeight = slider.scrollHeight;
                            slider.animate(
                                [{ height: '0' }, { height: targetHeight + 'px' }],
                                { duration: 300, easing: 'ease' }
                            ).finished.then(() => {
                                slider.style.height = '';
                                slider.style.overflow = '';
                            });
                        }
                    };

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
                            animateSlideDown();
                        })
                        .catch(error => {
                            console.error('Error fetching details:', error);
                            
                            row.child(`<div class='table_row_slider'>{{ trans('backpack::crud.details_row_loading_error') }}</div>`).show();
                            tr.classList.add('shown');
                            animateSlideDown();
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