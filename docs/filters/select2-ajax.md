### Select2_ajax

Shows a select2 and allows the user to select one item from the list or search for an item. This list is fetched through an AJAX call by the select2. Useful when the values list is long (over 1000 elements).

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
