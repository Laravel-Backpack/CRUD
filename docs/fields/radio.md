### radio

Show radios according to an associative array you give the input and let the user pick from them. You can choose for the radio options to be displayed inline or one-per-line.

```php
CRUD::field([   // radio
    'name'        => 'status', // the name of the db column
    'label'       => 'Status', // the input label
    'type'        => 'radio',
    'options'     => [
        // the key will be stored in the db, the value will be shown as label;
        0 => "Draft",
        1 => "Published"
    ],
    // optional
    //'inline'      => false, // show the radios all on the same line?
]);
```
