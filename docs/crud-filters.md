# Filters <span class="badge badge-info">PRO</span>

---

<a name="about"></a>
## About

Backpack allows you to show a filters bar right above the entries table. When selected or modified, they reload the DataTable. The search bar will also take filters into account, only looking within filtered results.

![Backpack CRUD Filters](https://backpackforlaravel.com/uploads/docs-4-0/filters/filters.png)

Just like with fields, columns or buttons, you can add existing filters or create a custom filter that fits to your particular needs. Everything's done inside your ```EntityCrudController::setupListOperation()```.

> **Note:** This is a <span class="badge badge-info">PRO</span> feature. It requires that you have [purchased access to `backpack/pro`](https://backpackforlaravel.com/pricing).

<a name="methods"></a>
### Filters API

In order to manipulate filters, you can use:

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

<a name="adding-a-filter"></a>
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


<a name="fluent-filters-main-chained-methods"></a>
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

<a name="fluent-filters-other-chained-methods"></a>
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

<a name="filter-logic-closures"></a>
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


<a name="filter-types"></a>
## Filter Types

<a name="simple"></a>
### Simple

Only shows a label and can be toggled on/off. Useful for things like active/inactive and easily paired with [Eloquent Scopes](https://laravel.com/docs/5.3/eloquent#local-scopes). The "Draft" and "Has Video" filters in the screenshot below are simple filters.

![Backpack CRUD Simple Filter](https://user-images.githubusercontent.com/1838187/159197347-e38fc63b-ceb8-4806-98dc-1e10773a57cd.png)

```php
CRUD::filter('active')
    ->type('simple')
    ->whenActive(function() {
      // CRUD::addClause('active'); // apply the "active" eloquent scope
    });
```

<hr>

<a name="text"></a>
### Text

Shows a text input. Most useful for letting the user filter through information that's not shown as a column in the CRUD table - otherwise they could just use the DataTables search field.

![Backpack CRUD Text Filter](https://backpackforlaravel.com/uploads/docs-4-0/filters/text.png)

```php
CRUD::filter('description')
    ->type('text')
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'description', 'LIKE', "%$value%");
    });
```

<hr>


<a name="date"></a>
### Date

Show a datepicker. The user can select one day.

![Backpack CRUD Date Filter](https://backpackforlaravel.com/uploads/docs-4-0/filters/date.png)

```php
CRUD::filter('birthday')
    ->type('date')
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'date', $value);
    });
```

<hr>

<a name="date-range"></a>
### Date range

Show a daterange picker. The user can select a start date and an end date.

![Backpack CRUD Date Range Filter](https://backpackforlaravel.com/uploads/docs-4-0/filters/date_range.png)

```php
CRUD::filter('from_to')
    ->type('date_range')
    // set options to customize, www.daterangepicker.com/#options
    ->date_range_options([
       'timePicker' => true // example: enable/disable time picker
    ])
    ->whenActive(function($value) {
      // $dates = json_decode($value);
      // CRUD::addClause('where', 'date', '>=', $dates->from);
      // CRUD::addClause('where', 'date', '<=', $dates->to);
    });
```

<hr>

<a name="dropdown"></a>
### Dropdown

Shows a list of elements (that you provide) in a dropdown. The user can only select one of these elements.

![Backpack CRUD Dropdown Filter](https://backpackforlaravel.com/uploads/docs-4-0/filters/dropdown.png)

```php
CRUD::filter('status')
    ->type('dropdown')
    ->values([
      1 => 'In stock',
      2 => 'In provider stock',
      3 => 'Available upon ordering',
      4 => 'Not available',
    ])
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'status', $value);
    });
```

<hr>


<a name="select2"></a>
### Select2

Shows a select2 and allows the user to select one item from the list or search for an item. Useful when the values list is long (over 10 elements).

![Backpack CRUD Select2 Filter](https://backpackforlaravel.com/uploads/docs-4-0/filters/select2.png)

```php
CRUD::filter('status')
    ->type('select2')
    ->values(function () {
      return [
        1 => 'In stock',
        2 => 'In provider stock',
        3 => 'Available upon ordering',
        4 => 'Not available',
      ];
    })
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'status', $value);
    });
```

**Note:** If you want to pass all entries of a Laravel model to your filter, you can do it in the closure with something like `return \App\Models\Category::all()->keyBy('id')->pluck('name', 'id')->toArray();`

<hr>

<a name="select2_multiple"></a>
### Select2_multiple

Shows a select2 and allows the user to select one or more items from the list or search for an item. Useful when the values list is long (over 10 elements) and your user should be able to select multiple elements.

![Backpack CRUD Select2_multiple Filter](https://backpackforlaravel.com/uploads/docs-4-0/filters/select2_multiple.png)

```php
CRUD::filter('status')
    ->type('select2_multiple')
    ->values(function () {
      return [
        1 => 'In stock',
        2 => 'In provider stock',
        3 => 'Available upon ordering',
        4 => 'Not available',
      ];
    })
    ->whenActive(function($values) {
      // CRUD::addClause('whereIn', 'status', json_decode($values));
    });
```

**Note:** If you want to pass all entries of a Laravel model to your filter, you can do it in the closure with something like `return \App\Models\Category::all()->keyBy('id')->pluck('name', 'id')->toArray();`

<hr>

<a name="select2_ajax"></a>
### Select2_ajax

Shows a select2 and allows the user to select one item from the list or search for an item. This list is fetched through an AJAX call by the select2. Useful when the values list is long (over 1000 elements).

![Backpack CRUD Select2_ajax Filter](https://backpackforlaravel.com/uploads/docs-4-0/filters/select2_ajax.png)

**Option (A) Use the FetchOperation to return the results**

Since Backpack already provides an operation that returns results from the DB, to be shown in Select2 fields, we can use that to populate the select2_ajax filter:

Step 1. In your CrudController, set up the [FetchOperation](/docs/{{version}}/crud-operation-fetch) to return the entity you want:

```php
    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    protected function fetchCategory()
    {
        return $this->fetch(\App\Models\Category::class);
    }
```

Step 2. Use this filter and make sure you specify the method as "POST":
```php
CRUD::filter('category_id')
    ->type('select2_ajax')
    ->values(backpack_url('product/fetch/category'))
    ->method('POST')
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'category_id', $value);
    });

    // other methods
    // ->placeholder('Pick a category')
    // ->select_attribute('name') // the attribute that will be shown to the user by default 'name'
    // ->select_key('id') // by default is ID, change it if your model uses some other key
```

**Option (B) Use a custom controller to return the results**

Alternatively, you can use a completely custom endpoint, that returns the options for the select2:

Step 1. Add a route for the ajax search, right above your usual ```CRUD::resource()``` route. Example:

```php
Route::get('test/ajax-category-options', 'TestCrudController@categoryOptions');
CRUD::resource('test', 'TestCrudController');
```

Step 2. Add a method to your EntityCrudController that responds to a search term. The result should be an array with ```id => value```. Example for a 1-n relationship:

```php
public function categoryOptions(Request $request) {
  $term = $request->input('term');
  $options = App\Models\Category::where('name', 'like', '%'.$term.'%')->get()->pluck('name', 'id');
  // optionally you can return a paginated instance: App\Models\Category::where('name', 'like', '%'.$term.'%')::paginate(10)
  return $options;
}
```

Step 3. Add the select2_ajax filter and for the second parameter ("values") specify the exact route.

```php
CRUD::filter('category_id')
    ->type('select2_ajax')
    ->values(url('admin/test/ajax-category-options'))
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'category_id', $value);
    });

    // other methods:
    // ->placeholder('Pick a category')
    // ->method('POST') // by default it's GET

    // when returning a paginated instance you can specify the attribute and the key to be used:
    // ->select_attribute('title') // by default it's name
    // ->select_key('custom_key')  // by default it's id
```

<hr>

<a name="range"></a>
### Range

Shows two number inputs, for min and max.

![Backpack CRUD Range Filter](https://backpackforlaravel.com/uploads/docs-4-0/filters/range.png)

```php
CRUD::filter('number')
    ->type('range')
    ->whenActive(function($value) {
      $range = json_decode($value);
      // if ($range->from) {
      //     CRUD::addClause('where', 'number', '>=', (float) $range->from);
      // }
      // if ($range->to) {
      //     CRUD::addClause('where', 'number', '<=', (float) $range->to);
      // }
    });

    // other methods
    // label_from('min value')
    // label_to('max value)
```

<hr>

<a name="view"></a>
### View

Display any custom column filter you want. Usually used by Backpack package developers, to use views from within their packages, instead of having to publish them.

```php
CRUD::filter('category_id')
    ->type('view')
    ->view('package::columns.column_type_name')  // or path to blade file
    ->whenActive(function($value) {
    // CRUD::addClause('where', 'category_id', $value);
    });
```


<a name="creating-a-custom-filter-type"></a>
## Creating custom filters

Creating a new filter type is as easy as using the template below and placing a new view in your ```resources/views/vendor/backpack/crud/filters``` folder. You can then call this new filter by its view's name (ex: ```custom_select.blade.php``` will mean your filter type is called ```custom_select```).

The filters bar is actually a [bootstrap navbar](http://getbootstrap.com/components/#navbar) at its core, but slimmer.  So adding a new filter will be just like adding a menu item (for the HTML). Start from the ```text``` filter below and build your functionality.

Inside this file, you'll have:
- ```$filter``` object - includes everything you've defined on the current field;
- ```$crud``` - the CrudPanel object;

```php
{{-- Text Backpack CRUD filter --}}

<li filter-name="{{ $filter->name }}"
  filter-type="{{ $filter->type }}"
  class="dropdown {{ Request::get($filter->name) ? 'active' : '' }}">
  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
  <div class="dropdown-menu">
    <div class="form-group backpack-filter m-b-0">
      <div class="input-group">
            <input class="form-control pull-right"
                id="text-filter-{{ str_slug($filter->name) }}"
                type="text"
            @if ($filter->currentValue)
              value="{{ $filter->currentValue }}"
            @endif
                >
            <div class="input-group-addon">
              <a class="text-filter-{{ str_slug($filter->name) }}-clear-button" href="#"><i class="fa fa-times"></i></a>
            </div>
        </div>
    </div>
  </div>
</li>

{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
  <!-- include select2 js-->
  <script>
    jQuery(document).ready(function($) {
      $('#text-filter-{{ str_slug($filter->name) }}').on('change', function(e) {

        var parameter = '{{ $filter->name }}';
        var value = $(this).val();

          // behaviour for ajax table
        var ajax_table = $('#crudTable').DataTable();
        var current_url = ajax_table.ajax.url();
        var new_url = addOrUpdateUriParameter(current_url, parameter, value);

        // replace the datatables ajax url with new_url and reload it
        new_url = normalizeAmpersand(new_url.toString());
        ajax_table.ajax.url(new_url).load();

        // mark this filter as active in the navbar-filters
        if (URI(new_url).hasQuery('{{ $filter->name }}', true)) {
          $('li[filter-name={{ $filter->name }}]').removeClass('active').addClass('active');
        } else {
          $('li[filter-name={{ $filter->name }}]').trigger('filter:clear');
        }
      });

      $('li[filter-name={{ str_slug($filter->name) }}]').on('filter:clear', function(e) {
        $('li[filter-name={{ $filter->name }}]').removeClass('active');
        $('#text-filter-{{ str_slug($filter->name) }}').val('');
      });

      // datepicker clear button
      $(".text-filter-{{ str_slug($filter->name) }}-clear-button").click(function(e) {
        e.preventDefault();

        $('li[filter-name={{ str_slug($filter->name) }}]').trigger('filter:clear');
        $('#text-filter-{{ str_slug($filter->name) }}').val('');
        $('#text-filter-{{ str_slug($filter->name) }}').trigger('change');
      })
    });
  </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
```

<hr>

<a name="examples"></a>
## Examples

Use a dropdown to filter by the values of a MySQL ENUM column:

```php
CRUD::filter('published')
  ->type('select2')
  ->values(function() {
    return \App\Models\Test::getEnumValuesAsAssociativeArray('published');
  })
  ->whenActive(function($value) {
    CRUD::addClause('where', 'published', $value);
  });
```

Use a select2 to filter by a 1-n relationship:
```php
CRUD::filter('category_id')
    ->type('select2')
    ->values(function() {
      return \App\Models\Category::all()->pluck('name', 'id')->toArray();
    })
    ->whenActive(function($value) {
      CRUD::addClause('where', 'category_id', $value);
    });
```

Use a select2_multiple to filter Products by an n-n relationship:
```php
CRUD::filter('tags')
    ->type('select2_multiple')
    ->values(function() { // the options that show up in the select2
      return Product::all()->pluck('name', 'id')->toArray();
    })
    ->whenActive(function($values) {
      foreach (json_decode($values) as $key => $value) {
          $this->crud->query = $this->crud->query->whereHas('tags', function ($query) use ($value) {
              $query->where('tag_id', $value);
          });
      }
    });
```

Use a simple filter to add a scope if the filter is active:
```php
// add a "simple" filter called Published
CRUD::filter('published')
    ->type('simple')
    ->whenActive(function() { // if the filter is active (the GET parameter "published" exits)
        CRUD::addClause('published');
    });
```

Use a simple filter to show the softDeleted items (trashed):
```php
CRUD::filter('trashed')
    ->type('simple')
    ->whenActive(function($values) {
        $this->crud->query = $this->crud->query->onlyTrashed();
    });
```

<a name="tips-and-tricks"></a>
## Tips and Tricks

<a name="using-filters-on-admin-page"></a>
### Use Filters on custom admin panel pages

Filters can be added to any admin panel page, not just the main CRUD table. Imagine that you want to have a dashboard page, with a few widgets that show some data. You can add filters to that page, and use them to filter the data shown in the widgets. 


![](https://backpackforlaravel.com/uploads/docs/filters/filters-in-custom-page.png)

You start by [creating a new page](/docs/{{version}}/base-about#custom-pages-1) to hold your custom content, eg: a reports page. 

```bash
php artisan backpack:page Reports
```

To use filters on a custom admin panel page, you should edit the blade file (in this example the `resources/views/admin/reports.blade.php` file) to **add the filters navbar** and **the event listeners**:
```diff
@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Reports</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">Page for Reports</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
+                   @include('crud::inc.filters_navbar')
                </div>
            </div>
        </div>
    </div>
@endsection

+@push('after_scripts')
+<script>
+    document.addEventListener('backpack:filters:cleared', function (event) {       
+       console.log('all filters cleared', event);
+    });
+
+    document.addEventListener('backpack:filter:cleared', function (event) {       
+       console.log('one filter cleared', event);
+    });
+
+   document.addEventListener('backpack:filter:changed', function (event) {
+        let filterName = event.detail.filterName;
+        let filterValue = event.detail.filterValue;
+        let shouldUpdateUrl = event.detail.shouldUpdateUrl;
+        console.log('one filter changed', filterName, filterValue, shouldUpdateUrl);
+    });
+</script>
+@endpush
```

After that, time to add your own filters in your controller (in this example, `ReportsController.php`):

```php
class ReportsController extends Controller
{
    public function index()
    {
        $crud = app('crud');

        $crud->addFilter([
                'type'  => 'simple',
                'name'  => 'checkbox',
                'label' => 'Simple',
            ], false);

        $crud->addFilter([ // dropdown filter
            'name' => 'select_from_array',
            'type' => 'dropdown',
            'label'=> 'Dropdown',
        ], ['one' => 'One', 'two' => 'Two', 'three' => 'Three']);

        return view('admin.reports', [
            'title'       => 'Reports',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Reports'                     => false,
            ],
            'crud'       => $crud,
        ]);
	}
}
```

That's it, you should now have the filters navbar on your reports page. You can use the event listeners to update the data shown on the page based on the filters selected by the user. 
Here are the Javascript events you can listen to: 
- `backpack:filter:changed` when a filter is changed;
- `backpack:filter:cleared` when a filter is cleared;
- `backpack:filters:cleared` when all filters are cleared;

<a name="debouncing-filters"></a>
### Add a debounce time to filters

Filters can be debounced, so that the filtering logic is only applied after the user has stopped typing for a certain amount of time. This is useful when the filtering logic is expensive and you don't want it to run on every keystroke. To debounce a filter, you can use the following code:

```php

CRUD::filter('name')
    ->type('text')
    ->debounce(1000) // debounce time in milliseconds
    ->whenActive(function($value) {
        // CRUD::addClause('where', 'name', 'LIKE', "%$value%");
    });
```

All filter types accept a `debounce`, like for example the simple filter, range filter etc.

<a name="adding-a-filter-using-array-syntax"></a>
### Add a filter using array syntax

In Backpack v4-v5 we used an "array syntax" to add and manipulate filters. That syntax is still supported for backwards-compatiblity. But it most cases it's easier to use the fluent syntax.

When adding a filter using the array syntax you need to specify the 3 parameters of the ```addFilter()``` method:
- `$options` - an array of options (name, type, label are most important)
- `$values` - filter values - can be an array or a closure
- `$filter_logic` - what should happen if the filter is applied (usually add a clause to the query) - can be a closure, a string for a simple operation or false for a simple "where";

Here's a simple example, with comments that explain what we're doing:
```php
// add a "simple" filter called Draft
$this->crud->addFilter([
  'type'  => 'simple',
  'name'  => 'draft',
  'label' => 'Draft'
],
false, // the simple filter has no values, just the "Draft" label specified above
function() { // if the filter is active (the GET parameter "draft" exits)
  CRUD::addClause('where', 'draft', '1');
  // we've added a clause to the CRUD so that only elements with draft=1 are shown in the table
  // an alternative syntax to this would have been
  // $this->crud->query = $this->crud->query->where('draft', '1');
  // another alternative syntax, in case you had a scopeDraft() on your model:
  // CRUD::addClause('draft');
});
```
