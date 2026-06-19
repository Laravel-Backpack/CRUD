### Dataform

Shows a dataform component from a particular CrudController. For more info about the configuration parameter, please see the [dataform component docs](/docs/{{version}}/base-components#dataform).

```php
[
    'type' => 'dataform',
    'controller' => 'App\Http\Controllers\Admin\InvoiceCrudController',
    'name' => 'invoice_form',
    'setup' => function($crud, $parent) {
        // you can use this closure to modify your CrudController definition.
        $crud->removeField('notes');
    }
]
```
