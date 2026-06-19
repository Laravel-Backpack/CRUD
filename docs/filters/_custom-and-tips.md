## Creating custom filters

Creating a new filter type is as easy as using the template below and placing a new view in your ```resources/views/vendor/backpack/crud/filters``` folder. You can then call this new filter by its view's name (ex: ```custom_select.blade.php``` will mean your filter type is called ```custom_select```).

The filters bar is actually a [bootstrap navbar](http://getbootstrap.com/components/#navbar) at its core, but slimmer. So adding a new filter will be just like adding a menu item (for the HTML). Start from the ```text``` filter below and build your functionality.

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

## Tips and Tricks

### Use Filters on custom admin panel pages

Filters can be added to any admin panel page, not just the main CRUD table. Imagine that you want to have a dashboard page, with a few widgets that show some data. You can add filters to that page, and use them to filter the data shown in the widgets. 

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
