## Fluent Buttons

These methods should be used inside your CrudController for operations that use Buttons, most likely inside the ```setupListOperation()``` or ```setupShowOperation()``` methods.

### General

- ```button('name')``` - By specifying **button('name')** you add a button with that name to the current operation, at the end of the stack, or modify the button that already exists with that name; takes a single parameter, a string, that will become that button's ```name```; needs to be called directly, not chained;
```php
CRUD::button('name'); 
```

Anything you chain to the ```button()``` method gets turned into an attribute on that button. Except for the methods listed below. Keep in mind in most cases you will still need to chain ```stack```, ```view``` and maybe ```type``` to this method, to define those attributes. Details in the examples section below.

### Chained Methods

If you chain the following methods to a ```CRUD::button('name')```, they will do something very specific instead of adding that attribute to the button:

- ```->remove()``` - By chaining **remove()** on a button you remove it from the current operation;

```php
CRUD::button('name')->remove();
```

- ```->forget('attribute_name')``` - By chaining **forget('attribute_name')** to a button you remove that attribute from the button;

```php
CRUD::button('name')->forget('suffix');
```

- ```->after('destination')``` - By chaining **after('destination_button_name')** you will move the current button after the given button;

```php
CRUD::button('name')->after('description');
```

- ```->before('destination')``` - By chaining **before('destination_button_name')** you will move the current button before the given button;

```php
CRUD::button('name')->before('description');
```

- ```->makeFirst()``` - By chaining **makeFirst()** you will make the current button the first one for the current operation;

```php
CRUD::button('name')->makeFirst();
```

- ```->makeLast()``` - By chaining **makeLast()** you will make the current button the last one for the current operation;

```php
CRUD::button('name')->makeLast();
```

### Examples

```php
// ---------
// Example 1
// ---------
// instead of
$this->crud->addButton('top', 'create', 'view', 'crud::buttons.create');
// you can now do
CRUD::button('create')->stack('top')->view('crud::buttons.create');

// ---------
// Example 2
// ---------
// instead of
$this->crud->addButton('line', 'update', 'view', 'crud::buttons.update', 'end');
// you can now do
CRUD::button('edit')->stack('line')->view('crud::buttons.edit');

// ---------
// Example 3
// ---------
// instead of
$this->crud->addButtonFromModelFunction('line', 'open_google', 'openGoogle', 'beginning');
// you can now do
CRUD::button('open_google')
	->stack('line')
	->type('model_function')
	->content('openGoogle')
	->makeFirst();

// ---------
// Example 4
// ---------
// instead of
$this->crud->addButtonFromView('top', 'create', 'crud::buttons.create', 'beginning');
// you can now do
CRUD::button('create')->stack('top')->view('crud::buttons.create');

// ---------
// Example 5
// ---------
// instead of
$this->crud->removeButton('create');
// you can now do
CRUD::button('create')->remove();

// ------
// Extras
// ------
// but you don't have to give it a name, so you can also do
CRUD::button()->stack('line')->type('model_function')->content('openGoogle')->makeFirst();
// and we also have helpers for setting both the type to view/model_function and its content
CRUD::button()->stack('line')->modelFunction('openGoogle')->makeFirst();

// -------
// Aliases
// -------
// the "stack" attribute can also be set using the "group", "section" and "to" aliases
// all of the calls below do the exact same thing
CRUD::buton('create')->stack('top')->view('crud::butons.create');
CRUD::buton('create')->to('top')->view('crud::butons.create');
CRUD::buton('create')->group('top')->view('crud::butons.create');
CRUD::buton('create')->section('top')->view('crud::butons.create');
```
