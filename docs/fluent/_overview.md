# CRUD Fluent API

## About

Starting with Backpack 4.1, working with Fields, Columns, Filters, Buttons and Widgets **inside your EntityCrudController** can also be done using a fluent syntax. For example, instead of doing:

```php
$this->crud->addField([   // Number
    'name'   => 'price',
    'label'  => 'Price',
    'type'   => 'number',
    'prefix' => "$",
    'suffix' => ".00",
]);
```

You can now do:
```php
$this->crud->field('price')
		->type('number')
		->label('Price')
		->prefix('$')
		->suffix('.00');
```

But you can go a little further, by using the CrudPanel class at the top of your controller with an alias:

```php
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel as CRUD;

CRUD::field('price')
	->type('number')
	->label('Price')
	->prefix('$')
	->suffix('.00');
```

Or maybe even condense it on just one line:
```php
CRUD::field('price')->type('number')->label('Price')->prefix('$')->suffix('.00');
```

Those who prefer this new fluent syntax do so because:
- method chains have better highlighting and suggestions in most IDEs;
- method chains take up slightly fewer lines of code than arrays;
- method chains are faster to write & modify than arrays;
- you no longer have to decide if you're adding or modifying a field, since ```CRUD::field()``` basically functions as a ```CRUD::addOrModifyField()```;
- it allows us to add methods that are exclusive to the fluent syntax, that will make our lives easier; for example, to make a field take up only 6 bootstrap columns, using the non-fluent syntax you'd have to write ```'wrapper' => ['class' => 'form-group col-md-6'],``` - but using the fluent syntax you can just do ```size(6)```;

But keep in mind that it does have downsides: it's more difficult to debug and arguably makes it more difficult to understand how the admin panel works. Developers who are not already comfortable with Backpack might not understand that:
- referencing ```$this->crud``` is the same thing as ```CRUD``` because it's actually a ```singleton```, a "global" instance of the ```CrudPanel``` object, which gets defined in the Controller and is then read inside the views;
- the fluent syntax merely turns those chained methods into an array, which gets stored inside ```$this->crud``` like it does with ```addField()``` or ```modifyField()```;
