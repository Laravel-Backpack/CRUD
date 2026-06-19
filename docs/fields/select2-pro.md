### select2 (1-n relationship) [PRO]

Select2 dropdown for `hasOne`/`belongsTo` relationships.

```php
CRUD::field([  // Select2
   'label'     => "Category",
   'type'      => 'select2',
   'name'      => 'category_id', // the db column for the foreign key

   // optional
   'entity'    => 'category', // the method that defines the relationship in your Model
   'model'     => "App\Models\Category", // foreign key model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'default'   => 2, // set the default value of the select2

    // also optional
   'options'   => (function ($query) {
        return $query->orderBy('name', 'ASC')->where('depth', 1)->get();
    }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
]);
```
