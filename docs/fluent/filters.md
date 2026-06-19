## Fluent Filters

These methods should be used inside your CrudController for operations that use Filters, most likely inside ```setupListOperation()```.

### General

```filter('name')``` - By specifying **filter('name')** you add a filter with that name to the current operation, at the end of the stack, or modify the filter that already exists with that name; takes a single parameter, a string, that will become that filter's ```name```; needs to be called directly, not chained;
```php
CRUD::filter('name'); 
```

Anything you chain to the ```filter()``` method gets turned into an attribute on that filter. Except for the methods listed below. Keep in mind in most cases you will still need to chain ```type```, ```logic```, ```fallbackLogic``` and maybe ```apply``` to this method, to define those attributes and apply the appropriate logic. Details in the examples section below.

### Main Chained Methods

- ```->type('date')``` - By chaining **type()** on a filter you make sure you use that filter type; it accepts a string that represents the type of filter you want to use;

```php
CRUD::filter('birthday')->type('date');
```

- ```->label('Name')``` - By chaining **label()** on a filter you define what is shown to the user as the filter label; it accepts a string;

```php
CRUD::filter('name')->label('Name');
```

- ```->logic(function($value) {})``` - By chaining **logic()** on a filter you define should be done when that filter is active; it accepts a closure that is called when the filter is active - either immediately by further chaining ```apply()``` on the filter, or automatically after all filters are defined, by the List operation itself; you can also use ```->whenActive()``` or ```->ifActive()``` which are its aliases:

```php
// logic method
CRUD::filter('active')
	->type('simple')
	->logic(function ($value) {
	 $this->crud->addClause('where', 'active', '1');
	})->apply();

// whenActive alias
CRUD::filter('active')
	->type('simple')
	->whenActive(function ($value) {
	 $this->crud->addClause('where', 'active', '1');
	})->apply();

// ifActive alias, left to be applied by the operation itself
CRUD::filter('active')
	->type('simple')
	->whenActive(function ($value) {
	 $this->crud->addClause('where', 'active', '1');
	});
```

- ```->fallbackLogic(function($value) {})``` - By chaining **fallbackLogic()** on a filter you define should be done when that filter is NOT active; it accepts a closure that is called when the filter is NOT active - either immediately by further chaining ```apply()``` on the filter, or automatically after all filters are defined, by the List operation itself; you can also use ```->else()```, ```->whenInactive()```, ```->whenNotActive()```, ```->ifInactive()``` and ```->ifNotActive()``` which are its aliases:

```php
// fallbackLogic method, applied immediately
CRUD::filter('active')
	->type('simple')
	->logic(function ($value) {
	 $this->crud->addClause('where', 'active', '1');
	})->fallbackLogic(function ($value) {
	 $this->crud->addClause('where', 'active', '0');
	})->apply();

// else alias, left to be applied by the operation itself
CRUD::filter('active')
	->type('simple')
	->label('Simple')
	->ifActive(function ($value) {
	 $this->crud->addClause('where', 'active', '1');
	})->else(function ($value) {
	 $this->crud->addClause('where', 'active', '0');
	});
```

### Other Chained Methods

If you chain the following methods to a ```CRUD::filter('name')```, they will do something very specific instead of adding that attribute to the filter:

- ```->remove()``` - By chaining **remove()** on a filter you remove it from the current operation;

```php
CRUD::filter('name')->remove();
```

- ```->forget('attribute_name')``` - By chaining **forget('attribute_name')** to a filter you remove that attribute from the filter;

```php
CRUD::filter('name')->forget('suffix');
```

- ```->after('destination')``` - By chaining **after('destination_filter_name')** you will move the current filter after the given filter;

```php
CRUD::filter('name')->after('description');
```

- ```->before('destination')``` - By chaining **before('destination_filter_name')** you will move the current filter before the given filter;

```php
CRUD::filter('name')->before('description');
```

- ```->makeFirst()``` - By chaining **makeFirst()** you will make the current filter the first one for the current operation;

```php
CRUD::filter('name')->makeFirst();
```

- ```->makeLast()``` - By chaining **makeLast()** you will make the current filter the last one for the current operation;

```php
CRUD::filter('name')->makeLast();
```

### Examples

```php
// instead of
CRUD::addFilter([
 'type' => 'simple',
 'name' => 'checkbox',
 'label' => 'Simple',
],
false,
function () {
 $this->crud->addClause('where', 'checkbox', '1');
});

// you can do
CRUD::filter('checkbox')
 ->type('simple')
 ->label('Simple')
 ->logic(function($value) {
 $this->crud->addClause('where', 'checkbox', '1');
 })->apply();

// or
CRUD::filter('checkbox')
 ->type('simple')
 ->label('Simple')
 ->whenActive(function($value) { // filter logic
 $this->crud->addClause('where', 'checkbox', '1');
 })->whenInactive(function($value) { // fallback logic
 $this->crud->addClause('inactive');
 })->apply();

// or 
CRUD::filter('checkbox')
 ->type('simple')
 ->label('Simple')
 ->ifActive(function($value) { // filter logic
 $this->crud->addClause('where', 'checkbox', '1');
 })->else(function($value) { // fallback logic
 $this->crud->addClause('inactive');
 })->apply();

// -------------------
// you can also now do
// -------------------
CRUD::filter('select_from_array')->label('Modified Dropdown');
CRUD::filter('select_from_array')->whenActive(function($value) {
 dd('select_from_array filter logic got modified');
})->apply();
CRUD::filter('select_from_array')->remove();
CRUD::filter('select_from_array')->forget('label');
CRUD::filter('select_from_array')->after('text');
CRUD::filter('select_from_array')->before('text');
CRUD::filter('select_from_array')->makeFirst();
CRUD::filter('select_from_array')->makeLast();

// --------------
// other examples
// --------------
// checkbox filter
CRUD::filter('checkbox')
 ->type('simple')
 ->label('Simple')
 ->logic(function($value) {
 $this->crud->addClause('where', 'checkbox', '1');
 })->apply();

// select_from_array filter
CRUD::filter('select_from_array')
 ->type('dropdown')
 ->label('DropDOWN')
 ->values([
 'one' => 'One', 
 'two' => 'Two', 
 'three' => 'Three'
 ])
 ->whenActive(function($value) {
 $this->crud->addClause('where', 'select_from_array', $value);
 })
 ->apply();

// text filter
CRUD::filter('text')
 ->type('text')
 ->label('Text')
 ->whenActive(function($value) {
 $this->crud->addClause('where', 'text', 'LIKE', "%$value%");
 })->apply();

// number filter
CRUD::filter('number')
 ->type('range')
 ->label('Range')->label_from('min value')->label_to('max value')
 ->whenActive(function($value) {
 $range = json_decode($value);
 if ($range->from && $range->to) {
 $this->crud->addClause('where', 'number', '>=', (float) $range->from);
 $this->crud->addClause('where', 'number', '<=', (float) $range->to);
 }
 })->apply();

// date filter
CRUD::filter('date')
 ->type('date')
 ->label('Date')
 ->whenActive(function($value) {
 $this->crud->addClause('where', 'date', '=', $value);
 })->apply();

// date_range filter
CRUD::filter('date_range')
 ->type('date_range')
 ->label('Date range')
 ->whenActive(function($value) {
 $dates = json_decode($value);
 $this->crud->addClause('where', 'date', '>=', $dates->from);
 $this->crud->addClause('where', 'date', '<=', $dates->to);
 })->apply();

// select2 filter
CRUD::filter('select2')
 ->type('select2')
 ->label('Select2')
 ->values(function() {
 return \Backpack\NewsCRUD\app\Models\Category::all()->keyBy('id')->pluck('name', 'id')->toArray();
 })
 ->whenActive(function($value) {
 $this->crud->addClause('where', 'select2', $value);
 })->apply();

// select2_multiple filter
CRUD::filter('select2_multiple')
 ->type('select2_multiple')
 ->label('S2 multiple')
 ->values(function() {
 return \Backpack\NewsCRUD\app\Models\Category::all()->keyBy('id')->pluck('name', 'id')->toArray();
 })
 ->whenActive(function($value) {
 foreach (json_decode($values) as $key => $value) {
 $this->crud->addClause('orWhere', 'select2', $value);
 }
 })->apply();

// select2_from_ajax filter
CRUD::filter('select2_from_ajax')
 ->type('select2_ajax')
 ->label('S2 Ajax')
 ->placeholder('Pick an article')
 ->values('api/article-search')
 ->whenActive(function($value) {
 $this->crud->addClause('where', 'select2_from_ajax', $value);
 })->apply();
```
