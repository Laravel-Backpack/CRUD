### select2_from_array [PRO]

Display a select2 with the values you want:

```php
CRUD::field([   // select2_from_array
    'name'        => 'template',
    'label'       => "Template",
    'type'        => 'select2_from_array',
    'options'     => ['one' => 'One', 'two' => 'Two'],
    'allows_null' => false,
    'default'     => 'one',
    // 'multiple' => true, // allows multiple selections 
    // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
    // 'sortable' => true, // requires the field to accept multiple values, and allow the selected options to be sorted.
    // 'tagging' => true, // allow users to type and create new options.
]);
```
