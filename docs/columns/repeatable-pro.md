### repeatable [PRO]

Show stored JSON in a table. It's definition is similar to the [repeatable *field type*](/docs/{{version}}/crud-fields#repeatable-pro).

```php
[
    'name'      => 'features',
    'label'     => 'Features',
    'type'      => 'repeatable',
    'subfields' => [
        [
            'name'    => 'feature',
            'wrapper' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name'    => 'value',
            'wrapper' => [
                'class' => 'col-md-6',
            ],
        ],
        [
            'name'    => 'quantity',
            'type'    => 'number',
            'wrapper' => [
                'class' => 'col-md-3',
            ],
        ],
    ],
]
```
