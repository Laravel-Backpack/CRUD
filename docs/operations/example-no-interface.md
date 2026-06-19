#### Creating a New Operation With No Interface

Let's say we have a ```UserCrudController``` and we want to create a simple ```Clone``` operation, which would create another entry with the same info. So very similar to ```Delete```. What we need to do is:

1. Create a route for this operation - as we've learned above we can do that in a ```setupXxxRoutes()``` method:

```php
    protected function setupCloneRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/{id}/clone', [
            'as'        => $routeName.'.clone',
            'uses'      => $controller.'@clone',
            'operation' => 'clone',
        ]);
    }
```

2. Add the method inside ```UserCrudController```:

```php
public function clone($id)
{
    $this->crud->hasAccessOrFail('create');
    $this->crud->setOperation('Clone');

    $clonedEntry = $this->crud->model->findOrFail($id)->replicate();

    return (string) $clonedEntry->push();
}
```

3. Create a button for this method. Since our operation is similar to "Delete", lets start from that one and customize what we need. The button should clone the entry using an AJAX call. No need to load another page for an operation this simple. We'll create a ```resources\views\vendor\backpack\crud\buttons\clone.blade.php``` file:

```php
@if ($crud->hasAccess('create'))
	<a href="javascript:void(0)" onclick="cloneEntry(this)" data-route="{{ url($crud->route.'/'.$entry->getKey().'/clone') }}" class="btn btn-xs btn-default" data-button-type="clone"><i class="fa fa-clone"></i> Clone</a>
@endif

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
	if (typeof cloneEntry != 'function') {
	  $("[data-button-type=clone]").unbind('click');

	  function cloneEntry(button) {
	      // ask for confirmation before deleting an item
	      // e.preventDefault();
	      var button = $(button);
	      var route = button.attr('data-route');

          $.ajax({
              url: route,
              type: 'POST',
              success: function(result) {
                  // Show an alert with the result
                  new Noty({
                    type: "success",
                    text: "<strong>Entry cloned</strong><br>A new entry has been added, with the same information as this one."
                  }).show();

                  // Hide the modal, if any
                  $('.modal').modal('hide');

                  if (typeof crud !== 'undefined') {
                    crud.table.ajax.reload();
                  }
              },
              error: function(result) {
                  // Show an alert with the result
                  new Noty({
                    type: "warning",
                    text: "<strong>Cloning failed</strong><br>The new entry could not be created. Please try again."
                  }).show();
              }
          });
      }
	}

	// make it so that the function above is run after each DataTable draw event
	// crud.addFunctionToDataTablesDrawEventQueue('cloneEntry');
</script>
@if (!request()->ajax()) @endpush @endif
```

4. We can now actually add this button to our ```UserCrudController::setupCloneOperation()``` method, or our ```setupCloneDefaults()``` method:

```php
protected function setupCloneDefaults() {
  $this->crud->allowAccess('clone');

  $this->crud->operation(['list', 'show'], function () {
    $this->crud->addButtonFromView('line', 'clone', 'clone', 'beginning');
  });
}
```

>Of course, **if you plan to re-use this operation on another EntityCrudController**, it's a good idea to isolate the method inside a trait, then use that trait on each EntityCrudController where you want the operation to work.
