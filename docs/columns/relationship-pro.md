### relationship [PRO]

Output the related entries, no matter the relationship:
- 1-n relationships - outputs the name of its one connected entity;
- n-n relationships - enumerates the names of all its connected entities;

Its name and definition is the same as for the relationship *field type*:
```php
[
   // any type of relationship
   'name'         => 'tags', // name of relationship method in the model
   'type'         => 'relationship',
   'label'        => 'Tags', // Table column heading
   // OPTIONAL
   // 'entity'    => 'tags', // the method that defines the relationship in your Model
   // 'attribute' => 'name', // foreign key attribute that is shown to user
   // 'model'     => App\Models\Category::class, // foreign key model
],
```

Backpack tries to guess which attribute to show for the related item. Something that the end-user will recognize as unique. If it's something common like "name" or "title" it will guess it. If not, you can manually specify the ```attribute``` inside the column definition, or you can add ```public $identifiableAttribute = 'column_name';``` to your model, and Backpack will use that column as the one the user finds identifiable. It will use it here, and it will use it everywhere you haven't explicitly asked for a different attribute.
