### select2_multiple [PRO]

The select2_multiple column will output a comma separated list of its connected entities. Used for relationships like hasMany() and belongsToMany(). Its name and definition is the same as the select2_multiple field:

```php
[
   // n-n relationship (with pivot table)
   'label'     => 'Tags', // Table column heading
   'type'      => 'select2_multiple',
   'name'      => 'tags', // the method that defines the relationship in your Model
   'entity'    => 'tags', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => 'App\Models\Tag', // foreign key model
],
```
