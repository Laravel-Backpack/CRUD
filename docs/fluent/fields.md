## Fluent Fields

These methods should be used inside your CrudController for operations that use Fields, most likely inside the ```setupCreateOperation()``` or ```setupUpdateOperation()``` methods.

### General

```field('name')``` - By specifying **field('name')** you add a field with that name to the current operation, at the end of the stack, or modify the field that already exists with that name; it accepts a single parameter, a string, that will become that field's ```name```; needs to be called directly, not chained;
```php
CRUD::field('name'); 
```

Anything you chain to the ```field()``` method gets turned into an attribute on that field. Except for the methods below.

### Chained Methods

If you chain the following methods to a ```CRUD::field('name')```, they will do something very specific instead of adding that attribute to the field:

- ```->remove()``` - By chaining **remove()** on a field you remove it from the current operation;

```php
CRUD::field('name')->remove();
```

- ```->forget('attribute_name')``` - By chaining **forget('attribute_name')** to a field you remove that attribute from the field definition array;

```php
CRUD::field('name')->forget('suffix');
```

- ```->after('destination')``` - By chaining **after('destination_field_name')** you will move the current field after the given field;

```php
CRUD::field('name')->after('description');
```

- ```->before('destination')``` - By chaining **before('destination_field_name')** you will move the current field before the given field;

```php
CRUD::field('name')->before('description');
```

- ```->makeFirst()``` - By chaining **makeFirst()** you will make the current field the first one for the current operation;

```php
CRUD::field('name')->makeFirst();
```

- ```->makeLast()``` - By chaining **makeLast()** you will make the current field the last one for the current operation;

```php
CRUD::field('name')->makeLast();
```

- ```->size(6)``` - By chaining **size(4)** you will make the field span across this many bootstrap columns (instead of the default 12 columns which is a full row); it accepts a single parameter, an integer from 1 to 12; for more information and to see how you can create convenience methods like this one, see [the PR](https://github.com/Laravel-Backpack/CRUD/pull/2638);

```php
CRUD::field('name')->size(6);

// alternative to
CRUD::addField([
	'name' => 'name',
	'wrapper' => ['class' => 'form-group col-md-6'],
]);
```

- Do you have an idea for a new chained method aka. convenience method? [Let us know](https://github.com/laravel-backpack/crud/issues).

### Examples

```php
// a text field
CRUD::field('last_name');

// an email field, put inside a tab and resized to half the width
CRUD::field('email')->type('email')->size(6)->tab('Simple');

// a number field with prefix and suffix (stored as fake in extras)
CRUD::field('price')->type('number')->prefix('$')->suffix(".00")->fake(true);

// a date picker field with custom options
CRUD::field('birthday')
	 ->type('date_picker')
	 ->label('Birthday')
	 ->date_picker_options([
	 'todayBtn' => true,
	 'format' => 'dd-mm-yyyy',
	 'language' => 'en',
	 ])
	 ->size(6);

// a select field, half the width
CRUD::field('category_id')
 ->type('select')
 ->label('Category')
 ->entity('category')
 ->attribute('name')
 // ->model('Backpack\NewsCRUD\app\Models\Category') // optional; guessed from entity;
 // ->wrapper(['class' => 'form-group col-md-6']) // possible, but easier with size below;
 ->size(6);

// a select2_from_ajax field
CRUD::field('article')
 ->type('select2_from_ajax')
 ->label("Article")
 ->entity('article')
 // ->attribute('title') // starting with Backpack 4.1 this is optional & guessed
 // ->model('Backpack\NewsCRUD\app\Models\Article') // optional; guessed;
 ->data_source(url('api/article'))
 ->placeholder('Select an article')
 ->minimum_input_length(2);

// a relationship field for an n-n relationship
// also uses the Fetch and InlineCreate operations
CRUD::field('products')
 ->type('relationship')
 ->label('Products')
 // ->entity('products') // optional
 // ->attribute('name') // optional
 ->ajax(true)
 ->data_source(backpack_url('monster/fetch/product'))
 ->inline_create(['entity' => 'product'])
 // ->wrapper(['class' => 'form-group col-md-6'])
 ->tab('Others');
```
