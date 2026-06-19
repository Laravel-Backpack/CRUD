## Fluent Columns

These methods should be used inside your CrudController for operations that use Columns, most likely inside the ```setupListOperation()``` or ```setupShowOperation()``` methods.

### General

- ```column('name')``` - By specifying **column('name')** you add a column with that name to the current operation, at the end of the stack, or modify the column that already exists with that name; takes a single parameter, a string, that will become that column's ```name``` and ```key```; needs to be called directly, not chained;
```php
CRUD::column('name'); 
```

Anything you chain to the ```column()``` method gets turned into an attribute on that column. Except for the methods below:

### Chained Methods

If you chain the following methods to a ```CRUD::column('name')```, they will do something very specific instead of adding that attribute to the column:

- ```->remove()``` - By chaining **remove()** on a column you remove it from the current operation;

```php
CRUD::column('name')->remove();
```

- ```->forget('attribute_name')``` - By chaining **forget('attribute_name')** to a column you remove that attribute from the column definition array;

```php
CRUD::column('name')->forget('suffix');
```

- ```->after('destination')``` - By chaining **after('destination_column_name')** you will move the current column after the given column;

```php
CRUD::column('name')->after('description');
```

- ```->before('destination')``` - By chaining **before('destination_column_name')** you will move the current column before the given column;

```php
CRUD::column('name')->before('description');
```

- ```->makeFirst()``` - By chaining **makeFirst()** you will make the current column the first one for the current operation;

```php
CRUD::column('name')->makeFirst();
```

- ```->makeLast()``` - By chaining **makeLast()** you will make the current column the last one for the current operation;

```php
CRUD::column('name')->makeLast();
```

### Examples

```php
// a text column
CRUD::column('last_name');

// a textarea column
CRUD::column('description')->type('textarea');

// an image column
CRUD::column('profile_photo')->type('image');

// a select column with links
CRUD::column('select')
        ->type('select')
        ->entity('category')
        ->attribute('name')
        ->model("Backpack\NewsCRUD\app\Models\Category")
        ->wrapper([
            'href' => function ($crud, $column, $entry, $related_key) {
                return backpack_url('category/'.$related_key.'/show');
            },
        ]);

// a select_multiple column
CRUD::column('tags')->type('select_multiple')->entity('tags');

// a select_multiple column with everything explicitly defined, plus links
CRUD::column('tags')
        ->type('select_multiple')
        ->label('Select_multiple')
        ->entity('tags')
        ->attribute('name')
        ->model('Backpack\NewsCRUD\app\Models\Tag')
        ->wrapper([
            'href' => function ($crud, $column, $entry, $related_key) {
                return backpack_url('tag/'.$related_key.'/show');
            },
        ]);

// a checkbox column that turns a boolean into green labels if true
CRUD::column('active')
        ->type('boolean')
        ->label('Active')
        ->options([0 => 'Yes', 1 => 'No'])
        ->wrapper([
            'element' => 'span',
            'class'   => function ($crud, $column, $entry, $related_key) {
                if ($column['text'] == 'Yes') {
                    return 'badge badge-success';
                }

                return 'badge badge-default';
            },
        ]);

// a select_from_array column
CRUD::column('status')
        ->type('select_from_array')
        ->label('Status')
        ->options(['1' => 'New', '2' => 'Processing', '3' => 'Delivered']);

// a model function column, with custom search logic
CRUD::column('text_and_email')
	    ->type('model_function')
	    ->label('Text and Email')
	    ->function_name('getTextAndEmailAttribute')
	    ->searchLogic(function ($query, $column, $searchTerm) {
	        $query->orWhere('email', 'like', '%'.$searchTerm.'%');
	        $query->orWhere('text', 'like', '%'.$searchTerm.'%');
	    }); 
```
