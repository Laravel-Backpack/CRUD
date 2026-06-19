### select2_from_ajax_multiple [PRO]

Display a select2 that takes its values from an AJAX call. Same as [select2_from_ajax](#section-select2_from_ajax) above, but allows for multiple items to be selected. The only difference in the field definition is the "pivot" attribute.

```php
CRUD::field([   // n-n relationship
    'label'       => "Cities", // Table column heading
    'type'        => "select2_from_ajax_multiple",
    'name'        => 'cities', // a unique identifier (usually the method that defines the relationship in your Model)
    'entity'      => 'cities', // the method that defines the relationship in your Model
    'attribute'   => "name", // foreign key attribute that is shown to user
    'data_source' => url("api/city"), // url to controller search function (with /{id} should return model)
    'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?

    // OPTIONAL
    'delay'                      => 500, // the minimum amount of time between ajax requests when searching in the field
    'model'                      => "App\Models\City", // foreign key model
    'placeholder'                => "Select a city", // placeholder for the select
    'minimum_input_length'       => 2, // minimum characters to type before querying results
    // 'method'                  => 'POST', // optional - HTTP method to use for the AJAX call (GET, POST)
    // 'include_all_form_fields' => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
]);
```

create a controller and routes for the data_source above. Here's an example:

> **Security note:** When this field is powered by the [FetchOperation](/docs/{{version}}/crud-operation-fetch), Backpack reuses your `fetchXxx()` query to validate submitted IDs at save time. This happens automatically when the field entity matches the fetch method name. If you set `data_source` **manually** and the names no longer match, tell Backpack which method to use via `relation_options_query_source`:
> ```php
> CRUD::field([
> 'type' => 'select2_from_ajax_multiple',
> 'name' => 'cities',
> 'data_source' => backpack_url('article/fetch/active-city'),
> 'pivot' => true,
> 'relation_options_query_source' => 'fetchActiveCity',
> ]);
> ```
> If your `data_source` is a **custom endpoint** (not the FetchOperation), declare the allowed set directly with a [`relation_options_query`](#relation-options-query) closure instead. Skipping both leaves the field open to tampered requests (IDOR).

```php
Route::post('/api/city', 'Api\CityController@index');
Route::post('/api/city/{id}', 'Api\CityController@show');
```

```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\City;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $search_term = $request->input('q');
        $page = $request->input('page');

        if ($search_term)
        {
            $results = City::where('name', 'LIKE', '%'.$search_term.'%')->paginate(10);
        }
        else
        {
            $results = City::paginate(10);
        }

        return $results;
    }

    public function show($id)
    {
        return City::find($id);
    }
}
```

**Note:** If you want to also make this field work inside `repeatable` too, your API endpoint will also need to respond to the `keys` parameter, with the actual items that have those keys. For example:

```php
        if ($request->has('keys')) {
            return City::findMany($request->input('keys'));
        }
```
