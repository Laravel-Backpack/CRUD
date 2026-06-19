### select_multiple (n-n relationship)

Show a Select with the names of the connected entity and let the user select any number of them.
Your relationship should already be defined on your models as belongsToMany().

```php
CRUD::field([   // SelectMultiple = n-n relationship (with pivot table)
    'label'     => "Tags",
    'type'      => 'select_multiple',
    'name'      => 'tags', // the method that defines the relationship in your Model

    // optional
    'entity'    => 'tags', // the method that defines the relationship in your Model
    'model'     => "App\Models\Tag", // foreign key model
    'attribute' => 'name', // foreign key attribute that is shown to user
    'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?

    // also optional
    'options'   => (function ($query) {
        return $query->orderBy('name', 'ASC')->where('depth', 1)->get();
    }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
]);
```
