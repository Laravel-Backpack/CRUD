### table [PRO]

Show a table with multiple inputs per row and store the values as JSON array of objects in the database. The user can add more rows and reorder the rows as they please.

```php
CRUD::field([   // Table
    'name'            => 'options',
    'label'           => 'Options',
    'type'            => 'table',
    'entity_singular' => 'option', // used on the "Add X" button
    'columns'         => [
        'name'  => 'Name',
        'desc'  => 'Description',
        'price' => 'Price'
    ],
    'max' => 5, // maximum rows allowed in the table
    'min' => 0, // minimum rows allowed in the table
]);
```

>It's highly recommended that you use [attribute casting](https://mattstauffer.com/blog/laravel-5.0-eloquent-attribute-casting) on your model when working with JSON arrays stored in database columns, and cast this attribute to either ```object``` or ```array``` in your Model.

##### Using the table in a repeatable field

When using this field in a [repeatable field](#repeatable) as subfield, you need to take ensure this field is not double encoded. For that you can overwrite the store and update methods in your CrudController. Here's an example:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
}
use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
    update as traitUpdate;
}

public function update($id)
{
    $this->decodeTableFieldsFromRequest();
    return $this->traitUpdate($id);
}

public function store()
{
    $this->decodeTableFieldsFromRequest();
    return $this->traitStore();
}

private function decodeTableFieldsFromRequest()
{
    $request = $this->crud->getRequest();
    $repeatable = $request->get('repeatable'); // change to your repeatable field name

    if(is_array($repeatable)) {
        array_map(function($item) {
            $item['table_field_name'] = json_decode($item['table_field_name'] ?? '', true); // change to your table field name
            return $item;
        }, $repeatable);
    }
    $request->request->set('repeatable', $repeatable); // change to your repeatable field name
    $this->crud->setRequest($request);
}

```
