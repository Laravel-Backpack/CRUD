# Filters [PRO]

## About

Backpack allows you to show a filters bar right above the entries table. When selected or modified, they reload the DataTable. The search bar will also take filters into account, only looking within filtered results.

Just like with fields, columns or buttons, you can add existing filters or create a custom filter that fits to your particular needs. Everything's done inside your ```EntityCrudController::setupListOperation()```.

> **Note:** This is a [PRO] feature. It requires that you have [purchased access to `backpack/pro`](https://backpackforlaravel.com/pricing).

### Filters API

To manipulate filters, you can use:

```php
// on one filter
CRUD::filter($name)
  ->type($type)
  ->whenActive($closure)
  ->whenInactive($closure)
  ->apply();

CRUD::filter($name)->remove();
CRUD::filter($name)->makeFirst();
CRUD::filter($name)->makeLast();
CRUD::filter($name)->before($different_filter_name);
CRUD::filter($name)->after($different_filter_name);

// on all filters
CRUD::removeAllFilters(); // removes all the filters
CRUD::filters(); // gets all the filters
```

### Adding and configuring a filter

> Some filter names are reserved. Do **NOT** use the following names in filters: **lenght**, **draw**, **start**, **search**, **totalEntryCount**, **columns** or **datatable_id**.

Inside your `setupListOperation()` you can add or select a filter using `CRUD::filter('name')`, then chain methods to completely configure it:

```php
CRUD::filter('name')
    ->type('text')
    ->label('The name')
    ->whenActive(function($value) {
        CRUD::addClause('where', 'name', 'LIKE', '%'.$value.'%');
    })->else(function() {
        // nada
    });
```

Anything you chain to the ```filter()``` method gets turned into an attribute on that filter. Except for the methods listed below. Keep in mind **in most cases you WILL need to chain ```type()```, ```whenActive()```** and maybe even ```whenInactive()``` or ```apply()```. Details below.

#### Main Chained Methods

- ```->type('date')``` - By chaining **type()** on a filter you make sure you use that filter type; it accepts a string that represents the type of filter you want to use;

```php
CRUD::filter('birthday')->type('date');
```

- ```->label('Name')``` - By chaining **label()** on a filter you define what is shown to the user as the filter label; it accepts a string;

```php
CRUD::filter('name')->label('Name');
```

- ```->whenActive(function($value) {})``` - By chaining **whenActive()** on a filter you define what should be done when that filter is active; it accepts a closure that is called when the filter is active - either immediately by further chaining ```apply()``` on the filter, or automatically after all filters are defined, by the List operation itself; you can also use ```->logic()``` or ```->ifActive()``` which are its aliases:

```php
// whenActive method, applied immediately
CRUD::filter('active')
	->type('simple')
	->whenActive(function ($value) {
	    CRUD::addClause('where', 'active', '1');
	})->apply();

// whenActive, left to be applied by the operation itself
CRUD::filter('active')
	->type('simple')
	->whenActive(function ($value) {
	    CRUD::addClause('where', 'active', '1');
	});
```

- ```->whenInactive(function($value) {})``` - By chaining **whenInactive()** on a filter you define what should be done when that filter is NOT active; it accepts a closure that is called when the filter is NOT active - either immediately by further chaining ```apply()``` on the filter, or automatically after all filters are defined, by the List operation itself; you can also use ```->else()```, ```->fallbackLogic()```, ```->whenNotActive()```, ```->ifInactive()``` and ```->ifNotActive()``` which are its aliases:

```php
// fallbackLogic method, applied immediately
CRUD::filter('active')
	->type('simple')
	->whenActive(function ($value) {
	    CRUD::addClause('where', 'active', '1');
	})->whenInactive(function ($value) {
	    CRUD::addClause('where', 'active', '0');
	})->apply();

// else alias, left to be applied by the operation itself
CRUD::filter('active')
	->type('simple')
	->label('Simple')
	->whenActive(function ($value) {
	    CRUD::addClause('where', 'active', '1');
	})->else(function ($value) {
	    CRUD::addClause('where', 'active', '0');
	});
```

- ```->apply()``` - By chaining **apply()** on a filter after you've specified the filtering logic using `whenActive()` or `whenInactive()`, you immediately call the appropriate closure; in most cases this isn't necessary, because the List operation automatically performs an `apply()` on all filters; but in some cases, where the filtering closures want to stop or change execution, you can use `apply()` to have that logic applied before the next bits of code get executed;

#### Other Chained Methods

If you chain the following methods to a ```CRUD::filter('name')```, they will do something very specific instead of adding that attribute to the filter:

- ```->remove()``` - By chaining **remove()** on a filter you remove it from the current operation;

```php
CRUD::filter('name')->remove();
```

- ```->forget('attribute_name')``` - By chaining **forget('attribute_name')** to a filter you remove that attribute from the filter;

```php
CRUD::filter('price')->prefix('$'); // will have "$" as prefix
CRUD::filter('price')->forget('prefix'); // will no longer have "$" as prefix

// Note:
// You can only call "forget" on filter attributes. Calling "forget" on "before",
// "after", "whenActive", "whenInactive" etc. will do nothing, because those
// are not attributes, they are methods. You can, however, forget filter logic
// or fallback logic by using their attributes:
CRUD::filter('price')->forget('logic');
CRUD::filter('price')->forget('fallbackLogic');
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

#### Filter logic closures

Backpack filters do not contain any default filtering _logic_, because it cannot infer that. If you use a filter and don't specify a filter logic, no filtering of entries will actually happen. You have to define that, inside a _closure_.

Filter logic closures are just an anonymous functions that gets executed when the filter is active. You can use any Laravel or Backpack functionality you want inside them. For example, a valid closure would be:

```php
CRUD::filter('draft') ->whenActive(function($value) {
    CRUD::addClause('where', 'draft', 1);
});
```

Notes about the filter logic closure:
- the code will only be run on the controller's ```index()``` or ```search()``` methods;
- you can get the filter value by specifying a parameter to the function (ex: ```$value```);
- you have access to other request variables using ```$this->crud->getRequest()```;
- you also have read/write access to public properties using ```$this->crud```;
- when building complicated "OR" logic, make sure the first "where" in your closure is a "where" and only the subsequent are "orWhere"; Laravel 5.3+ no longer converts the first "orWhere" into a "where";
