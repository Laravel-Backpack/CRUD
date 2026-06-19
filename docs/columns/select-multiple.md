### select_multiple

The select_multiple column will output a comma separated list of its connected entities. Used for relationships like hasMany() and belongsToMany(). Its name and definition is the same as the select_multiple field:
```php
[
   // n-n relationship (with pivot table)
   'label'     => 'Tags', // Table column heading
   'type'      => 'select_multiple',
   'name'      => 'tags', // the method that defines the relationship in your Model
   'entity'    => 'tags', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => 'App\Models\Tag', // foreign key model
   // OPTIONAL
   'separator' => ',', // if you want to use a different separator than the default ','
],
```
