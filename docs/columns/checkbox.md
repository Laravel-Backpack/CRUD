### checkbox

Shows a checkbox (the form element), and inserts the js logic needed to select/deselect multiple entries. It is mostly used for [the Bulk Delete action](/docs/{{version}}/crud-operation-delete#delete-multiple-items-bulk-delete), and [custom bulk actions](/docs/{{version}}/crud-operations#creating-a-new-operation-with-a-bulk-action-no-interface).

Shorthand:
```php
$this->crud->enableBulkActions();
```
(will also add an empty custom_html column)

Verbose:
```php
$this->crud->addColumn([
    'type'           => 'checkbox',
    'name'           => 'bulk_actions',
    'label'          => ' <input type="checkbox" class="crud_bulk_actions_main_checkbox" style="width: 16px; height: 16px;" />',
    'priority'       => 1,
    'searchLogic'    => false,
    'orderable'      => false,
    'visibleInModal' => false,
])->makeFirstColumn();
```
