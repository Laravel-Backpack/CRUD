### select2_nested [PRO]

Display a select2 with the values ordered hierarchically and indented, for an entity where you use Reorder. Please mind that the connected model needs:
- a ```children()``` relationship pointing to itself;
- the usual ```lft```, ```rgt```, ```depth``` attributes;

```php
CRUD::field([   // select2_nested
    'name'      => 'category_id',
    'label'     => "Category",
    'type'      => 'select2_nested',
    'entity'    => 'category', // the method that defines the relationship in your Model
    'attribute' => 'name', // foreign key attribute that is shown to user

    // optional
    'model'     => "App\Models\Category", // force foreign key model
]);
```
