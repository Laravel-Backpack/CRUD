### Datatable

Shows a datatable component from a particular CrudController. For more info about the configuration parameter, please see the [datatable component docs](/docs/{{version}}/base-components#datatable).

```php
[
    'type' => 'datatable',
    'controller' => 'App\Http\Controllers\Admin\PetShop\InvoiceCrudController',
    'name' => 'invoices',
    'setup' => function($crud, $parent) {
        // you can use this closure to modify your CrudController definition.
        if ($parent) {
            $crud->addClause('where', 'owner_id', $parent->id);
        }
    }
]
```
