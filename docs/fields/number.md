### number

Shows an input type=number to the user, with optional prefix and suffix:

```php
CRUD::field([   // Number
    'name' => 'number',
    'label' => 'Number',
    'type' => 'number',

    // optionals
    // 'attributes' => ["step" => "any"], // allow decimals
    // 'prefix'     => "$",
    // 'suffix'     => ".00",
]);
```
