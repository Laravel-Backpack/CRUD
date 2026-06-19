### radio

Show a pretty text instead of the database value, according to an associative array. Usually used as a column for the "radio" field type.

```php
[
    'name'        => 'status',
    'label'       => 'Status',
    'type'        => 'radio',
    'options'     => [
        0 => 'Draft',
        1 => 'Published'
    ]
],
```

This example will show:
- "Draft" when the value stored in the db is 0;
- "Published" when the value stored in the db is 1;
