{{-- checkbox with loose false/null/0 checking --}}
<span style="display:flex">
    <input type="checkbox"
    		class="crud_bulk_actions_row_checkbox"
    		data-primary-key-value="{{ $entry->getKey() }}"
    		style="width: 16px; height: 16px;"
    		>
</span>

<script>
	if (typeof addOrRemoveCrudCheckedItem != 'function') {
	  function addOrRemoveCrudCheckedItem(element) {
	  	crud.lastCheckedItem = false;

	  	$("input.crud_bulk_actions_row_checkbox").click(function (e) {
	  		e.stopPropagation();

			var checked = this.checked;
			var primaryKeyValue = $(this).attr('data-primary-key-value');
			// console.log(this.checked);
			// console.log(primaryKeyValue);

			if (typeof crud.checkedItems === 'undefined') {
				crud.checkedItems = [];
			}

			if (checked) {
				// add item to crud.checkedItems variable
				crud.checkedItems.push(primaryKeyValue);

				// if shift has been pressed, also select all elements
				// between the last checked item and this one
				if (crud.lastCheckedItem && e.shiftKey) {
					var start_and_end = $("#crudTable .crud_bulk_actions_row_checkbox[data-primary-key-value="+crud.lastCheckedItem+"], #crudTable .crud_bulk_actions_row_checkbox[data-primary-key-value="+primaryKeyValue+"]");

					var start = start_and_end.first();
					var end = start_and_end.last();

					start.parentsUntil('tr').parent().nextUntil('tr:has([data-primary-key-value="'+end.attr('data-primary-key-value')+'"])', ).each(function(i, element) {
						$(element).find('input.crud_bulk_actions_row_checkbox:not(:checked)').trigger('click');
					});
				}

				// remember that this one was the last checked item
				crud.lastCheckedItem = primaryKeyValue;
			} else {
				// remove item from crud.checkedItems variable
				var index = crud.checkedItems.indexOf(primaryKeyValue);
				if (index > -1) {
				  crud.checkedItems.splice(index, 1);
				}
			}

			// if no items are selected, disable all bulk buttons
			enableOrDisableBulkButtons();
	  	});
	  }
	}

	if (typeof markCheckboxAsCheckedIfPreviouslySelected != 'function') {
	  function markCheckboxAsCheckedIfPreviouslySelected() {
	  	$('#crudTable input[type=checkbox][data-primary-key-value]').each(function(i, element) {
			var checked = element.checked;
			var primaryKeyValue = $(element).attr('data-primary-key-value');

	  		if (typeof crud.checkedItems !== 'undefined' && crud.checkedItems.length > 0)
	  		{
	  			var index = crud.checkedItems.indexOf(primaryKeyValue);
				if (index > -1) {
					element.checked = true;
				}
			}
	  	});
	  }
	}

	if (typeof addBulkActionMainCheckboxesFunctionality != 'function') {
      function addBulkActionMainCheckboxesFunctionality() {
      	$(".crud_bulk_actions_main_checkbox").prop('checked', false);

        // when the crud_bulk_actions_main_checkbox is selected, toggle all visible checkboxes
        $("input.crud_bulk_actions_main_checkbox").click(function(event) {
          if (this.checked) { // if checked, check all visible checkboxes
              $("input.crud_bulk_actions_row_checkbox:not(:checked)").trigger('click');
              // make sure the other checkbox has the same checked status
              $("input.crud_bulk_actions_main_checkbox").prop('checked', true);
          } else { // if not checked, uncheck all visible checkboxes
              $("input.crud_bulk_actions_row_checkbox:checked").trigger('click');
              // make sure the other checkbox has the same checked status
              $("input.crud_bulk_actions_main_checkbox").prop('checked', false);
          }
        });
      }
    }

    if (typeof enableOrDisableBulkButtons != 'function') {
      function enableOrDisableBulkButtons() {
		if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0) {
			$(".bulk-button").addClass('disabled');
		} else {
			$(".bulk-button").removeClass('disabled');
		}
      }
    }

	// activate checkbox if the page reloaded and the item is remembered as selected
	// make it so that the function above is run after each DataTable draw event
	crud.addFunctionToDataTablesDrawEventQueue('addOrRemoveCrudCheckedItem');
	crud.addFunctionToDataTablesDrawEventQueue('markCheckboxAsCheckedIfPreviouslySelected');
	crud.addFunctionToDataTablesDrawEventQueue('addBulkActionMainCheckboxesFunctionality');
	crud.addFunctionToDataTablesDrawEventQueue('enableOrDisableBulkButtons');
</script>