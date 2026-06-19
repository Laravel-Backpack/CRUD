#### Creating a New Operation With a Bulk Action (No Interface)

Say we want to create a ```BulkClone``` operation, with a button which clones multiple entries at the same time. So very similar to our ```BulkDelete```. What we need to do is:

1. Create a new button:

```html
@if ($crud->hasAccess('bulkClone') && $crud->get('list.bulkActions'))
  <a href="javascript:void(0)" onclick="bulkCloneEntries(this)" class="btn btn-sm btn-secondary bulk-button"><i class="fa fa-clone"></i> Clone</a>
@endif

@push('after_scripts')
<script>
  if (typeof bulkCloneEntries != 'function') {
    function bulkCloneEntries(button) {

        if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
        {
            new Noty({
            type: "warning",
            text: "<strong>{{ trans('backpack::crud.bulk_no_entries_selected_title') }}</strong><br>{{ trans('backpack::crud.bulk_no_entries_selected_message') }}"
          }).show();

          return;
        }

        var message = "Are you sure you want to clone these :number entries?";
        message = message.replace(":number", crud.checkedItems.length);

        // show confirm message
        swal({
        title: "{{ trans('backpack::base.warning') }}",
        text: message,
        icon: "warning",
        buttons: {
          cancel: {
          text: "{{ trans('backpack::crud.cancel') }}",
          value: null,
          visible: true,
          className: "bg-secondary",
          closeModal: true,
        },
          delete: {
          text: "Clone",
          value: true,
          visible: true,
          className: "bg-primary",
        }
        },
      }).then((value) => {
        if (value) {
          var ajax_calls = [];
              var clone_route = "{{ url($crud->route) }}/bulk-clone";

          // submit an AJAX delete call
          $.ajax({
            url: clone_route,
            type: 'POST',
            data: { entries: crud.checkedItems },
            success: function(result) {
              // Show an alert with the result
                    new Noty({
                    type: "success",
                    text: "<strong>Entries cloned</strong><br>"+crud.checkedItems.length+" new entries have been added."
                  }).show();

              crud.checkedItems = [];
              crud.table.ajax.reload();
            },
            error: function(result) {
              // Show an alert with the result
                    new Noty({
                    type: "danger",
                    text: "<strong>Cloning failed</strong><br>One or more entries could not be created. Please try again."
                  }).show();
            }
          });
        }
      });
      }
  }
</script>
@endpush
```

2. Create a method in your EntityCrudController (or in a trait, if you want to re-use it for multiple CRUDs):

```php
    public function bulkClone()
    {
        $this->crud->hasAccessOrFail('create');

        $entries = $this->crud->getRequest()->input('entries');
        $clonedEntries = [];

        foreach ($entries as $key => $id) {
            if ($entry = $this->crud->model->find($id)) {
                $clonedEntries[] = $entry->replicate()->push();
            }
        }

        return $clonedEntries;
    }
```

3. Add a route to point to this new method:

```php
protected function setupBulkCloneRoutes($segment, $routeName, $controller)
{
    Route::post($segment.'/bulk-clone', [
        'as'        => $routeName.'.bulkClone',
        'uses'      => $controller.'@bulkClone',
        'operation' => 'bulkClone',
    ]);
}
```

4. Setup the default features we need for the operation to work:

```php
protected function setupBulkCloneDefaults()
{
    $this->crud->allowAccess('bulkClone');

    $this->crud->operation('list', function () {
        $this->crud->enableBulkActions();
        $this->crud->addButton('bottom', 'bulk_clone', 'view', 'bulk_clone', 'beginning');
    });
}
```

Now there's a Clone button on our List bottom stack, that works as expected for multiple entries.

The button makes one call for all entries, and only triggers one notification. If you would rather make a call for each entry, you can use something like below:

```html
@if ($crud->hasAccess('create') && $crud->bulk_actions)
  <a href="javascript:void(0)" onclick="bulkCloneEntries(this)" class="btn btn-sm btn-secondary bulk-button"><i class="fa fa-clone"></i> Clone</a>
@endif

@push('after_scripts')
<script>
  if (typeof bulkCloneEntries != 'function') {
    function bulkCloneEntries(button) {

        if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
        {
          new PNotify({
                title: "{{ trans('backpack::crud.bulk_no_entries_selected_title') }}",
                text: "{{ trans('backpack::crud.bulk_no_entries_selected_message') }}",
                type: "warning"
            });

          return;
        }

        var message = "Are you sure you want to clone these :number entries?";
        message = message.replace(":number", crud.checkedItems.length);

        // show confirm message
        if (confirm(message) == true) {
            var ajax_calls = [];

            // for each crud.checkedItems
            crud.checkedItems.forEach(function(item) {
              var clone_route = "{{ url($crud->route) }}/"+item+"/clone";

              // submit an AJAX delete call
              ajax_calls.push($.ajax({
                  url: clone_route,
                  type: 'POST',
                  success: function(result) {
                      // Show an alert with the result
                      new Noty({
                        type: "success",
                        text: "<strong>Entries cloned</strong><br>"+crud.checkedItems.length+" new entries have been added."
                      }).show();
                  },
                  error: function(result) {
                      // Show an alert with the result
                      new Noty({
                        type: "danger",
                        text: "<strong>Cloning failed</strong><br>One or more entries could not be created. Please try again."
                      }).show();
                  }
              }));

          });

          $.when.apply(this, ajax_calls).then(function ( ajax_calls ) {
              crud.checkedItems = [];
              crud.table.ajax.reload();
        });
        }
      }
  }
</script>
@endpush
```
