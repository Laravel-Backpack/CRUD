# CRUD Fluent API

---


<a name="fluent-syntax-about"></a>
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

<a name="fluent-fields-api"></a>
## Fluent Fields

These methods should be used inside your CrudController for operations that use Fields, most likely inside the ```setupCreateOperation()``` or ```setupUpdateOperation()``` methods.

<a name="general-fluent-fields-api"></a>
### General

```field('name')``` - By specifying **field('name')** you add a field with that name to the current operation, at the end of the stack, or modify the field that already exists with that name; it accepts a single parameter, a string, that will become that field's ```name```; needs to be called directly, not chained;
```php
CRUD::field('name'); 
```

Anything you chain to the ```field()``` method gets turned into an attribute on that field. Except for the methods below.

<a name="fluent-fields-chained-methods"></a>
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

<a name="fluent-fields-examples"></a>
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
	        'format'   => 'dd-mm-yyyy',
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

<a name="fluent-columns-api"></a>
## Fluent Columns

These methods should be used inside your CrudController for operations that use Columns, most likely inside the ```setupListOperation()``` or ```setupShowOperation()``` methods.


<a name="general-fluent-columns-api"></a>
### General

- ```column('name')``` - By specifying **column('name')** you add a column with that name to the current operation, at the end of the stack, or modify the column that already exists with that name; takes a single parameter, a string, that will become that column's ```name``` and ```key```; needs to be called directly, not chained;
```php
CRUD::column('name'); 
```

Anything you chain to the ```column()``` method gets turned into an attribute on that column. Except for the methods below:


<a name="fluent-columns-chained-methods"></a>
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


<a name="fluent-columns-examples"></a>
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

<a name="fluent-buttons-api"></a>
## Fluent Buttons

These methods should be used inside your CrudController for operations that use Buttons, most likely inside the ```setupListOperation()``` or ```setupShowOperation()``` methods.


<a name="general-fluent-buttons-api"></a>
### General

- ```button('name')``` - By specifying **button('name')** you add a button with that name to the current operation, at the end of the stack, or modify the button that already exists with that name; takes a single parameter, a string, that will become that button's ```name```; needs to be called directly, not chained;
```php
CRUD::button('name'); 
```

Anything you chain to the ```button()``` method gets turned into an attribute on that button. Except for the methods listed below. Keep in mind in most cases you will still need to chain ```stack```, ```view``` and maybe ```type``` to this method, to define those attributes. Details in the examples section below.


<a name="fluent-buttons-chained-methods"></a>
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


<a name="fluent-buttons-examples"></a>
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


<a name="fluent-filters-api"></a>
## Fluent Filters


These methods should be used inside your CrudController for operations that use Filters, most likely inside ```setupListOperation()```.


<a name="general-fluent-filters-api"></a>
### General

```filter('name')``` - By specifying **filter('name')** you add a filter with that name to the current operation, at the end of the stack, or modify the filter that already exists with that name; takes a single parameter, a string, that will become that filter's ```name```; needs to be called directly, not chained;
```php
CRUD::filter('name'); 
```

Anything you chain to the ```filter()``` method gets turned into an attribute on that filter. Except for the methods listed below. Keep in mind in most cases you will still need to chain ```type```, ```logic```, ```fallbackLogic``` and maybe ```apply``` to this method, to define those attributes and apply the appropriate logic. Details in the examples section below.

<a name="fluent-filters-main-chained-methods"></a>
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

<a name="fluent-filters-other-chained-methods"></a>
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


<a name="fluent-filters-examples"></a>
### Examples

```php
// instead of
CRUD::addFilter([
    'type'  => 'simple',
    'name'  => 'checkbox',
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
        'one'   => 'One', 
        'two'   => 'Two', 
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
