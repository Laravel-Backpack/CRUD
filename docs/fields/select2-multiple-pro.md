### select2_multiple (n-n relationship) [PRO]

[Works just like the SELECT field, but prettier]

Shows a Select2 with the names of the connected entity and let the user select any number of them.
Your relationship should already be defined on your models as belongsToMany().

```php
CRUD::field([    // Select2Multiple = n-n relationship (with pivot table)
     'label'     => "Tags",
     'type'      => 'select2_multiple',
     'name'      => 'tags', // the method that defines the relationship in your Model

     // optional
     'entity'    => 'tags', // the method that defines the relationship in your Model
     'model'     => "App\Models\Tag", // foreign key model
     'attribute' => 'name', // foreign key attribute that is shown to user
     'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?
     // 'select_all' => true, // show Select All and Clear buttons?

     // optional
     'options'   => (function ($query) {
         return $query->orderBy('name', 'ASC')->where('depth', 1)->get();
     }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
]);
```
