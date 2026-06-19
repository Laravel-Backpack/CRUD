### select_and_order [PRO]

Display items on two columns and let the user drag&drop between them to choose which items are selected and which are not, and reorder the selected items with drag&drop.

Its definition is exactly as ```select_from_array```, but the value will be stored as JSON in the database: ```["3","5","7","6"]```, so it needs the attribute to be cast to array on the Model:

```php
protected $casts = [
    'featured' => 'array'
];
```

Definition:

```php
CRUD::field([   // select_and_order
    'name'  => 'featured',
    'label' => "Featured",
    'type'  => 'select_and_order',
    'options' => [
        1 => "Option 1",
        2 => "Option 2"
    ]
]);
```

Also possible:

```php
CRUD::field([   // select_and_order
    'name'    => 'featured',
    'label'   => 'Featured',
    'type'    => 'select_and_order',
    'options' => Product::get()->pluck('title','id')->toArray(),
]);
```
