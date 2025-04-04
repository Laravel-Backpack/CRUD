 @if ($crud->get('list.detailsRow'))
  <script>
    // Define the function in the global scope
    window.registerDetailsRowButtonAction = function() {
        console.log('registerDetailsRowButtonAction called');
        // Process all DataTables on the page
        $('table.dataTable').each(function() {
          // Check if this table has already been initialized for details row
          if ($(this).data('details-row-initialized')) {
            console.log('Table already initialized for details row: ' + $(this).attr('id'));
            return; // Skip already initialized tables
          }
          
          var tableId = $(this).attr('id');
          if (!tableId) return; // Skip tables without ID
          
          var tableSelector = '#' + tableId;
          console.log('Registering details row button action for table: ' + tableSelector);
          
          // Remove any previously registered event handlers from draw.dt event callback
          $(tableSelector + ' tbody').off('click', 'td .details-row-button');

          // Make sure the ajaxDatatables rows also have the correct classes
          $(tableSelector + ' tbody td .details-row-button').parent('td')
            .removeClass('details-control').addClass('details-control')
            .removeClass('text-center').addClass('text-center')
            .removeClass('cursor-pointer').addClass('cursor-pointer');

          // Mark this table as initialized
          $(this).data('details-row-initialized', true);
          
          // Add event listener for opening and closing details
          $(tableSelector + ' tbody td .details-control').on('click', function (e) {
                e.stopPropagation();

                var tr = $(this).closest('tr');
                var btn = $(this).find('.details-row-button');
                var table = $(tableSelector).DataTable();
                var row = table.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    btn.removeClass('la-minus-square-o').addClass('la-plus-square-o');
                    $('div.table_row_slider', row.child()).slideUp( function () {
                        row.child.hide();
                        tr.removeClass('shown');
                    } );
                } else {
                    // Open this row
                    btn.removeClass('la-plus-square-o').addClass('la-minus-square-o');
                    // Get the details with ajax
                    $.ajax({
                      url: '{{ url($crud->route) }}/'+btn.data('entry-id')+'/details',
                      type: 'GET',
                    })
                    .done(function(data) {
                      row.child("<div class='table_row_slider'>" + data + "</div>", 'no-padding').show();
                      tr.addClass('shown');
                      $('div.table_row_slider', row.child()).slideDown();
                    })
                    .fail(function(data) {
                      row.child("<div class='table_row_slider'>{{ trans('backpack::crud.details_row_loading_error') }}</div>").show();
                      tr.addClass('shown');
                      $('div.table_row_slider', row.child()).slideDown();
                    });
                }
            } );
          });
      }
      window.crud.defaultTableConfig.addFunctionToDataTablesDrawEventQueue('registerDetailsRowButtonAction');
    
  </script>
@endif
