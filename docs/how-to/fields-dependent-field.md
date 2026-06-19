### How to add a relationship field that depends on another field

The `relationship`, `select2_from_ajax` and `select2_from_ajax_multiple` fields allow you to filter the results depending on what has already been written or selected in a form. Say you have two `select2` fields, when the AJAX call is made to the second field, all other variables in the page also get passed - that means you can filter the results of the second `select2`.

Say you want to show two selects:
- the first one shows Categories
- the second one shows Articles, but only from the category above

1. In your CrudController you would do:

```php
// select2
CRUD::addField([
    'label'         => 'Category',
    'type'          => 'select',
    'name'          => 'category',
    'entity'        => 'category',
    'attribute'     => 'name',
]);

// select2_from_ajax: 1-n relationship
CRUD::addField([
    'label'                   => "Article", // Table column heading
    'type'                    => 'select2_from_ajax_multiple',
    'name'                    => 'articles', // the column that contains the ID of that connected entity;
    'entity'                  => 'article', // the method that defines the relationship in your Model
    'attribute'               => 'title', // foreign key attribute that is shown to user
    'data_source'             => url('api/article'), // url to controller search function (with /{id} should return model)
    'placeholder'             => 'Select an article', // placeholder for the select
    'minimum_input_length'    => 0, // minimum characters to type before querying results
    'dependencies'            => ['category'], // when a dependency changes, this select2 is reset to null
    'include_all_form_fields' => true, // what to send alongside the search query (see note below)
    'method'                  => 'POST', // recommended when using include_all_form_fields
]);
```

**DIFFERENT HERE**: ```minimum_input_length```, ```dependencies``` and ```include_all_form_fields```.

**`include_all_form_fields`** controls what gets sent in the AJAX request alongside the search term. It accepts three values:

| Value | What gets sent |
|---|---|
| `false` (default for `select2_from_ajax`, `select2_from_ajax_multiple`) | Nothing extra — unless `dependencies` are set, in which case only the dependency field values are sent |
| `true` (default for `relationship`/fetch fields) | All form fields are serialised and sent |
| `['field_a', 'field_b']` | Only the listed fields are sent. Any fields listed in `dependencies` are automatically merged in, even if they are not in the array — so dependency values are never accidentally dropped |

Note: whenever `include_all_form_fields` is `true` or an array, we recommend setting `'method' => 'POST'` and registering a POST route. GET requests have URL-length limits that can truncate a large form payload.

2. That second select points to routes that need to be registered:

```php
Route::post('api/article', 'App\Http\Controllers\Api\ArticleController@index');
Route::post('api/article/{id}', 'App\Http\Controllers\Api\ArticleController@show');
```

**DIFFERENT HERE**: Nothing.

3. Then that controller would look something like this:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Backpack\NewsCRUD\app\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $search_term = $request->input('q'); // the search term in the select2 input

        // if you are inside a repeatable we will send some aditional data to help you
        $triggeredBy = $request->input('triggeredBy'); // you will have the `fieldName` and the `rowNumber` of the element that triggered the ajax

        // NOTE: this is a Backpack helper that parses your form input into an usable array.
        // you still have the original request as `request('form')`
        $form = backpack_form_input();

        $options = Article::query();

        // if no category has been selected, show no options
        if (! $form['category']) {
            return [];
        }

        // if a category has been selected, only show articles in that category
        if ($form['category']) {
            $options = $options->where('category_id', $form['category']);
        }

        if ($search_term) {
            $results = $options->where('title', 'LIKE', '%'.$search_term.'%')->paginate(10);
        } else {
            $results = $options->paginate(10);
        }

        return $results;
    }

    public function show($id)
    {
        return Article::find($id);
    }
}
```

**DIFFERENT HERE**: We use ```$form``` to determine what other variables have been selected in the form, and modify the result accordingly.
