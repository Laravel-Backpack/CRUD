# CRUD API

Here are all the features you will be using **inside your EntityCrudController**, grouped by the operation you will most likely use them for.

## Operations

- **operation()** - allows you to add a set of instructions inside ```setup()```, that only gets called when a certain operation is being performed; 
```php
public function setup() {
	// ...
	$this->crud->operation('list', function() {
		$this->crud->addColumn('name');
	});
}
```
