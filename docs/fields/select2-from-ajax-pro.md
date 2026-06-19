### select2_from_ajax [PRO]

Display a select2 that takes its values from an AJAX call.

```php
CRUD::field([   // 1-n relationship
    'label'       => "End", // Table column heading
    'type'        => "select2_from_ajax",
    'name'        => 'category_id', // the column that contains the ID of that connected entity
    'entity'      => 'category', // the method that defines the relationship in your Model
    'attribute'   => "name", // foreign key attribute that is shown to user
    'data_source' => url("api/category"), // url to controller search function (with /{id} should return model)

    // OPTIONAL
    // 'delay'                   => 500, // the minimum amount of time between ajax requests when searching in the field
    // 'placeholder'             => "Select a category", // placeholder for the select
    // 'minimum_input_length'    => 2, // minimum characters to type before querying results
    // 'model'                   => "App\Models\Category", // foreign key model
    // 'dependencies'            => ['category'], // when a dependency changes, this select2 is reset to null
    // 'method'                  => 'POST', // optional - HTTP method to use for the AJAX call (GET, POST)
    // 'include_all_form_fields' => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
]);
```

The `data_source` endpoint must respond to AJAX calls. Use [FetchOperation](/docs/{{version}}/crud-operation-fetch) in your CrudController, or create a custom Route+Controller:

> **Security note:** When this field is powered by the [FetchOperation](/docs/{{version}}/crud-operation-fetch), Backpack reuses your `fetchXxx()` query to validate submitted IDs at save time. This happens automatically when the field entity matches the fetch method name. If you set `data_source` **manually** and the names no longer match, tell Backpack which method to use via `relation_options_query_source`:
> ```php
> CRUD::field([
> 'type' => 'select2_from_ajax',
> 'name' => 'category_id',
> 'data_source' => backpack_url('article/fetch/product-category'),
> 'relation_options_query_source' => 'fetchProductCategory',
> ]);
> ```
> If your `data_source` is a **custom endpoint** (not the FetchOperation), declare the allowed set directly with a [`relation_options_query`](#relation-options-query) closure instead. Skipping both leaves the field open to tampered requests (IDOR).

```php
Route::post('/api/category', 'Api\CategoryController@index');
```

```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Backpack\NewsCRUD\app\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term)
        {
            $results = Category::where('name', 'LIKE', '%'.$search_term.'%')->paginate(10);
        }
        else
        {
            $results = Category::paginate(10);
        }

        return $results;
    }
}
```

**Note:** If you want to also make this field work inside `repeatable` too, your API endpoint will also need to respond to the `keys` parameter, with the actual items that have those keys. For example:

```php
        if ($request->has('keys')) {
            return Category::findMany($request->input('keys'));
        }
```
