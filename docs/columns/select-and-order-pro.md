### select_and_order [PRO]

Show selected values in the order they are saved.

Its definition is very similar to the [select_and_order *field type*](/docs/{{version}}/crud-fields#select_and_order-pro).

```php
[
    // select_from_array
    'name'    => 'status',
    'label'   => 'Status',
    'type'    => 'select_and_order',
    'options' => ['draft' => 'Draft (invisible)', 'published' => 'Published (visible)'],
],
```
