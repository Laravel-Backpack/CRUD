### select (1-n relationship)

Show a Select with the names of the connected entity and let the user select one of them.
```php
CRUD::field([  // Select
   'label'     => "Category",
   'type'      => 'select',
   'name'      => 'category_id', // the db column for the foreign key

   // optional
   // 'entity' should point to the method that defines the relationship in your Model
   // defining entity will make Backpack guess 'model' and 'attribute'
   'entity'    => 'category',

   // optional - manually specify the related model and attribute
   'model'     => "App\Models\Category", // related model
   'attribute' => 'name', // foreign key attribute that is shown to user

   // optional - force the related options to be a custom query, instead of all();
   'options'   => (function ($query) {
        return $query->orderBy('name', 'ASC')->where('depth', 1)->get();
    }), //  you can use this to filter the results show in the select
]);
```
