### select

The select column will output its connected entity. Used for relationships like hasOne() and belongsTo(). Its name and definition is the same as for the select *field type*:
```php
[
   // 1-n relationship
   'label'     => 'Parent', // Table column heading
   'type'      => 'select',
   'name'      => 'parent_id', // the column that contains the ID of that connected entity;
   'entity'    => 'parent', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => "App\Models\Category", // foreign key model
   // OPTIONAL
   // 'limit' => 32, // Limit the number of characters shown
],
```
