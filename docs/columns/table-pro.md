### table [PRO]

The ```table``` column will output a condensed table, when used on an attribute that stores a JSON array or object. It is meant to be used inside the show functionality (not list, though it also works there).

Its definition is very similar to the [table *field type*](/docs/{{version}}/crud-fields#table).

```php
[
    'name'  => 'features',
    'label' => 'Features',
    'type'  => 'table',
    'columns' => [
        'name'        => 'Name',
        'description' => 'Description',
        'price'       => 'Price',
        'obs'         => 'Observations'
    ]
],
```
