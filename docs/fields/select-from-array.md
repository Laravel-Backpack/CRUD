### select_from_array

Display a select with the values you want:

```php
CRUD::field([   // select_from_array
    'name'        => 'template',
    'label'       => "Template",
    'type'        => 'select_from_array',
    'options'     => ['one' => 'One', 'two' => 'Two'],
    'allows_null' => false,
    'default'     => 'one',
    // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
]);
```
