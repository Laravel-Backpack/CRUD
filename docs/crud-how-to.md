# FAQs for CRUDs

---

In addition the usual CRUD functionality, Backpack also allows you to do a few more complicated things:


<a name="routes"></a>
## Routes

<a name="how-to-add-extra-crud-routes"></a>
### How to add extra CRUD routes

See: [How to add extra CRUD routes](/docs/{{version}}/crud-operations#operation-routes)

<a name="views"></a>
## Views

<a name="how-to-customize-views-for-each-crud-panel"></a>
### How to customize views for each CRUD panel

Backpack loads its views through a double-fallback mechanism:
- by default, it will load the views in the vendor folder (the package views);
- if you've included views with the exact same name in your ```resources/views/vendor/backpack/*``` folder, it will pick up those instead; you can use this method to overwrite a blade file for the whole application.
- alternatively, if you only want to change a blade file for one CRUD, you can use the methods below in your ```setup()``` method, to change a particular view:
```php
CRUD::setShowView('your-view');
CRUD::setEditView('your-view');
CRUD::setCreateView('your-view');
CRUD::setListView('your-view');
CRUD::setReorderView('your-view');
CRUD::setDetailsRowView('your-view');
```

<a name="how-to-customize-css-and-js-for-default-crud-operations"></a>
### How to add CSS or JS to a page or operation

If you want to add extra CSS or JS to a certain page, use the `script` and `style` widgets to add a new file of that type onpage, either from your CrudController or a custom blade file:

```php
use Backpack\CRUD\app\Library\Widget;

// script widget - works the same for both local paths and CDN
Widget::add()->type('script')->content('assets/js/custom-script.js');

Widget::add()->type('script')->content('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js');

Widget::add()->type('script')
             ->content('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js')
             ->integrity('sha256-0YPKAwZP7Mp3ALMRVB2i8GXeEndvCq3eSl/WsAl1Ryk=')
             ->crossorigin('anonymous');

// style widget - works the same for both local paths and CDN
Widget::add()->type('style')->content('assets/css/custom-style.css');

Widget::add()->type('style')->content('https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.58/dist/themes/light.css');

Widget::add()->type('style')
             ->content('https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.58/dist/themes/light.css')
             ->integrity('sha256-0YPKAwZP7Mp3ALMRVB2i8GXeEndvCq3eSl/WsAl1Ryk=')
             ->crossorigin('anonymous');
```

For more details please see the `script` and `style` sections in the [Widgets](/docs/{{version}}/base-widgets) page.

You can limit where that CSS/JS is added by making the `Widget::add()` call in the right place in your CrudController:
- if you do `Widget::add()` inside the `setupListOperation()` method, it will only be loaded there;
- if you do `Widget::add()` inside the `setup()` method, it will be loaded on all pages for that CRUD;
- if you want it to be loaded on all pages for all CRUDs, you can create a CustomCrudController that extends our CrudController, do it there and then make sure all your CRUDs extend `CustomCrudController`;
- if you want it to be loaded on all pages (even non-CRUD like dashboards) you can add the CSS/JS file on all pages by adding it in your `config/backpack/base.php`, under `scripts` and `styles`;

<a name="design"></a>
## Design

<a name="how-to-customize-crud-panel-design"></a>
### How to customize CRUD Panel design (CSS hooks)

Our CRUD panel design is the result of 7+ years of feedback, from both admins and developers. Each iteration has made it better and better, to the point where admins find it very intuitive to do everything they need, and Backpack is lauded for how intuitive its design is. So we do _not_ recommend moving components around.

However, you might want to change the styling - colors, border, padding etc. Especially if you're creating a new theme. For that purpose, we have made sure all CRUD operations have the `bp-section` attributes in key elements, so you can easily and reliably target them. For example:

- List operation
    - `bp-section=page-header` for the page header (between breadcrumbs and content)
    - `bp-section=crud-operation-reorder` for the content
- Create operation
    - `bp-section=page-header` for the page header (between breadcrumbs and content)
    - `bp-section=crud-operation-create` for the content
- Update operation
    - `bp-section=page-header` for the page header (between breadcrumbs and content)
    - `bp-section=crud-operation-update` for the content
- Show operation
    - `bp-section=page-header` for the page header (between breadcrumbs and content)
    - `bp-section=crud-operation-show` for the content
- Reorder operation
    - `bp-section=page-header` for the page header (between breadcrumbs and content)
    - `bp-section=crud-operation-reorder` for the content

This is a very simple yet effective solution for your custom CSS or JS to target the header or target specific operations, since each operation has its content wrapped around an element with `bp-section=crud-operation-xxx`.

<a name="columns"></a>
## Columns

<a name="how-to-use-the-same-column-name-multiple-times-in-a-crud"></a>
### How to use the same column name multiple times in a CRUD

If you try to add multiple columns with the same ```name```, by default Backpack will only show the last one. That's because ```name``` is also used as a key in the ```$column``` array. So when you ```addColumn()``` with the same name twice, it just overwrites the previous one.

In order to insert two columns with the same name, use the ```key``` attribute on the second column (or both columns). If this attribute is present for a column, Backpack will use ```key``` instead of ```name```. Example:

```diff
        CRUD::column([
           'label' => "Location",
           'type' => 'select',
           'name' => 'location_id',
           'entity' => 'location',
           'attribute' => 'title',
           'model' => "App\Models\Location"
        ]);

        CRUD::column([
           'label' => "Location Type",
           'type' => 'radio_location_type',
           'options' => [
               1 => "Country",
               2 => "Region"
           ],
           'name' => 'location_id',
+          'key' => 'location_type',
           'entity' => 'location',
           'attribute' => 'type',
           'model' => "App\Models\Location"
        ]);
```

<a name="fields"></a>
## Fields

<a name="how-to-publish-a-column-field-filter-button-to-modify"></a>
### How to publish a column / field / filter / button and modify it

All Backpack packages allow you to easily overwrite and customize the views. If you want Backpack to pick up _your_ file, instead of the one in the package, you can do that by just placing a file with the same name in your views. So if you want to overwrite the select2 field (```vendor/backpack/crud/src/resources/views/fields/select2.blade.php```) to change some functionality, you need to create ```resources/views/vendor/backpack/crud/fields/select2.blade.php```. You can do that manually, or use this command:
```shell
php artisan backpack:field --from=select2
```
This will look for the blade file and copy it inside the folder, so you can edit it.

>**Please note:** Once you place a file with the same name inside your project, Backpack will pick that one up instead of the one in the package. That means that even though the file in the package is updated, you won't be getting those updates, since you're not using that file. Blade modifications are almost never breaking changes, but it's a good thing to receive updates with zero effort. Even small ones. So please overwrite the files as little as possible. Best to create your own custom fields/column/filters/buttons, whenever you can.

<a name="how-to-filter-the-options-in-a-select-field"></a>
### How to filter the options in a select field

This also applies to: select2, select2_multiple, select2_from_ajax, select2_from_ajax_multiple.

There are 3 possible solutions:
1. If there will be few options (dozens), the easiest way would be to use ```select_from_array``` or ```select2_from_array``` instead. Since you tell it what the options are, you can do any filtering you want there.
2. If there are a lot of options (100+), best to use ```select2_from_ajax``` instead. You'll be able to filter the results any way you want in the controller method (the one that responds with the results to the AJAX call).
3. If you can't use ```select2_from_array``` for ```select2_from_ajax```, you can create another model and add a global scope to it. For example, say you only want to show the users that belong to the user's company. You can create ```App\Models\CompanyUser``` and use that in your ```select2``` field instead of ```App\Models\User```:

```php
<?php

namespace App\Models;

use App\User;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CompanyUser extends User
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // never let a company user see the users of other companies
        if (Auth::check() && Auth::user()->company_id) {
            $companyId = Auth::user()->company->id;

            static::addGlobalScope('company_id', function (Builder $builder) use ($companyId) {
                $builder->where('company_id', $companyId);
            });
        }
    }
}
```


<a name="how-to-load-fields-from-a-different-folder"></a>
### How to load fields from a different folder

If you're developing a package, you might need Backpack to pick up fields from your package folder, instead of having to publish them upon installation.

Fields, Columns and Filters all have a ```view_namespace``` parameter you can use. Type your folder there, and Backpack will check that folder first, then where the views are published, then Backpack's package folder. Example:

```php
CRUD::addFilter([ // add a "simple" filter called Draft
  'type'  => 'complex',
  'name'  => 'checkbox',
  'label' => 'Checked',
  'view_namespace' => 'custom_filters'
],
false, // the simple filter has no values, just the "Draft" label specified above
function () { // if the filter is active (the GET parameter "draft" exits)
    CRUD::addClause('where', 'checkbox', '1');
});
```
This will make Backpack look for the ```resources/views/custom_filters/complex.blade.php```, and pick that up before anything else.

<a name="how-to-add-a-select-that-depends-on-another-field"></a>
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

**DIFFERENT HERE**: ```minimum_input_length```,  ```dependencies``` and ```include_all_form_fields```.

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


<a name="what-field-should-i-use-for-a-relationship"></a>
### What field should I use for a relationship?

With so many field types, it can be a little overwhelming to understand what field type to use for a _particular_ Eloquent relationship. Here's a quick summary of all possible relationships, and the interface you might want for them. Click on the relationship you're interested in, for more details and an example:


- **[hasOne (1-1)](#hasone-1-1-relationship)** ✅
    - (A) show a subform - add a `relationship` field with `subfields`
    - (B) show a separate field for each attribute on the related entry - add any field type, with dot notation for the field name (`passport.title`)
- **[belongsTo (n-1)](#belongsto-n-1-relationship)** ✅
    - show a select2 (single) - add a `relationship` field
- **[hasMany (1-n)](#hasmany-1-n-relationship)** ✅
    - (A) show a select2_multiple - add a `relationship` field
    - (B) show a subform - add a `relationship` field with `subfields`
- **[belongsToMany (n-n)](#belongstomany-n-n-relationship)** ✅
    - (A) show a select2_multiple - add a `relationship` field
    - (B) show a subform - add a `relationship` field and define `subfields`
- **[morphOne (1-1)](#morphone-1-1-polymorphic-relationship)** ✅
    - (A) show a subform - add a `relationship` field with `subfields`
    - (B) show a separate field for each attribute on the related entry - add any field type, with dot notation for the field name (`passport.title`)
- **[morphMany (1-n)](#morphmany-1-n-polymorphic-relationship)** ✅
    - (A) show a select2_multiple - add a `relationship` field
    - (B) show a subform - add a `relationship` field with `subfields`
- **[morphToMany (n-n)](#morphtomany-n-n-polymorphic-relationship)** ✅
    - (A) show a select2_multiple - add a `relationship` field
    - (B) show a subform - add a `relationship` field and define `subfields`
- **[morphTo (n-1)](#morphto-n-1-relationship)** ✅
    - Manage both `_type` and `_id` of the morphTo relation;
- **[hasOneThrough (1-1-1)](#hasonethrough-1-1-1-relationship)** ❌
    - it's read-only, no sense having a field for it;
- **[hasManyThrough (1-1-n)](#hasmanythrough-1-1-n-relationship)** ❌
    - it's read-only, no sense having a field for it;
- **[Has One Of Many (1-n turned into 1-1)](#has-one-of-many-1-1-relationship-out-of-1-n-relationship)** ❌
    - it's read-only, no sense having a field for it;
- **[Morph One Of Many (1-n turned into 1-1)](#morph-one-of-many-1-1-relationship-out-of-1-n-relationship)** ❌
    - it's read-only, no sense having a field for it;
- **[morphedByMany (n-n inverse)](#morphedbymany-n-n-inverse-relationship)** ❌
    - never needed, UI would be very difficult to understand & use;


<a name="hasone"></a>
#### hasOne (1-1 relationship)

- example:
    - `User -> hasOne -> Phone`
    - the foreign key is stored on the Phone (`user_id` on `phones` table)
- what to use:
    - the `relationship` field with `subfields` defined for each column on the related entry
- how to use:
    - [the `hasOne` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-one) in the User model;

```php
// inside UserCrudController::setupCreateOperation()
CRUD::field('phone')->type('relationship')->subfields([
    'prefix',
    'number',
    [
        'name' => 'type',
        'type' => 'select_from_array',
        'options' => ['mobile' => 'Mobile Phone', 'landline' => 'Landline', 'fax' => 'Fax'],
    ]
]);
```

<a name="hasone"></a>
#### hasOne (1-1 relationship) - one field for each attribute of the related entry

- example:
    - `User -> hasOne -> Phone`
    - the foreign key is stored on the Phone (`user_id` on `phones` table)
- what to use:
    -  any field (eg. `text`, `number`, `textarea`), with the field name prefixed by the relationship name (dot notation);
- how to use:
    - [the `hasOne` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-one) in the User model;
    - you can easily add fields for each individual attribute on the related entry; you just need to specify in the field name that the value should not be stored on the _main model_, but on _a related model_; you can do that using dot notation (`relationship_name.column_name`); note that the prefix (before the dot) is the **Relation** name, not the table name;
    - all fields types should work fine - depending on your needs you could choose to add a [`text`](#text) field, [`number`](#number) field, [`textarea`](#textarea) field, [`select`](#select) field etc.;

```php
// inside UserCrudController::setupCreateOperation()
CRUD::field('phone.number')->type('number');
CRUD::field('phone.prefix')->type('text');
CRUD::field('phone.type')->type('select_from_array')->options(['mobile' => 'Mobile Phone', 'landline' => 'Landline', 'fax' => 'Fax']);
```

<a name="belongsto"></a>
#### belongsTo (n-1 relationship)

- example:
    - `Phone -> User`
    - a Phone belongs to one User; a Phone can only belong to one User
    - the foreign key is stored on the Phone (`user_id` on `phones` table)
- how to use:
    - [the `belongsTo` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-many-inverse) in the Phone model;
    - you can easily add a dropdown to let the admin pick which User the Phone belongs; you can use any of the dropdown fields, but for convenience we've made a list here, and broken them down depending on approximately how many entries the dropdown will have:
        - for 0-10 dropdown items - we recommend you use the [`relationship`](#relationship) or [`select`](#select) field;
        - for 0-500 dropdown items - we recommend you use the [`relationship`](#relationship) or [`select2`](#select2) field;
        - for 500-1.000.000+ dropdown items - we recommend you load the dropdown items using AJAX, by using the [`relationship`](#relationship) field and Fetch operation (alternatively, use the [`select2_from_ajax`](#select2-from-ajax) field);

```php
// inside PhoneCrudController::setupCreateOperation()
CRUD::field('user'); // notice the name is the relationship name and backpack will auto-infer the field type as [`relationship`](#relationship)
CRUD::field('user_id')->type('select')->model('App\Models\User')->attribute('name')->entity('user'); // notice the name is the foreign key attribute
CRUD::field('user_id')->type('select2')->model('App\Models\User')->attribute('name')->entity('user'); // notice the name is the foreign key attribute
```

- notes:
    - if you choose to use the [`relationship`](#relationship) field, you could also use [the InlineCreate operation](/docs/{{version}}/crud-operation-inline-create), which will add a [+ Add Item] button next to the dropdown, to let the admin create a User in a modal, without leaving the current Create Phone form;

<a name="hasmany"></a>
#### hasMany (1-n relationship)

- example:
    - `Post -> HasMany -> Comment`
    - the foreign key is stored on the Comment (`post_id` on `comments` table)
- what to use:
    - use the `relationship` field;
- how to use:
    - [the `hasMany` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-many) in the Post model;

```php
// inside PostCrudController::setupCreateOperation()
CRUD::field('comments'); // when unselected, will set foreign key to null
CRUD::field('comments')->fallback_id(3); // when unselected, will set foreign key to 3
CRUD::field('comments')->force_delete(true);  // when unselected, will delete the related entry
```

- notes:
    - when a related entry is unselected (removed), Backpack will:
        - set the foreign key to `null`, if that db column is nullable (eg. `post_id`);
        - set the foreign key to a default value, if you define a `fallback_id` on the field;
        - delete related entry entirely, if you define `'force_delete' => false` on the field;
    - you can use [the InlineCreate operation](/docs/{{version}}/crud-operation-inline-create) to show a [+ Add Item] button next to the dropdown; for it to work `post_id` on comments table need to nullable or have a default setup in database;


<a name="hasmany-1-n-relationship-with-subform-to-create-update-and-delet"></a>
#### hasMany (1-n relationship) with subform to create, update and delete related entries

If you want the admin to not only _select_ an entry, but also create them, edit their attributes or delete related entries.

- example:
    - `Post -> HasMany -> Comment`
    - the foreign key is stored on the Comment (`post_id` on `comments` table)
- what to use:
    - use the `relationship` field;
- how to use:
    - [the `hasMany` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-many) in the Post model;

```php
// inside PostCrudController::setupCreateOperation()
CRUD::field('comments')->subfields([['name' => 'body']]);
// where body is a text field in the comment table.
```

<a name="belongstomany"></a>
#### belongsToMany (n-n relationship)

Note: Starting with v5, the `BelongsToMany` relation had been improved to simplify the scenario where your pivot table has extra database columns (in addition to the foreign keys).

- example:
    - `User -> BelongsToMany -> Role`
    - the foreign keys are stored on a pivot table (usually the `user_roles` table has both `user_id` and `role_id`)
- how to use:
    - [the `belongsToMany` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#many-to-many) in both the User and Role models;
    - you can add a dropdown on your User to pick the Roles that are connected to it; for that, use the [`relationship`](#relationship), [`select_multiple`](#select-multiple), [`select2_multiple`](#select2-multiple) or  [`select2_from_ajax_multiple`](#select2-from-ajax-multiple) fields;

```php
// inside UserCrudController::setupCreateOperation()
CRUD::field('roles');

// inside RoleCrudController::setupCreateOperation()
CRUD::field('users');
```

- notes:
    - if you choose to use the [`relationship`](#relationship) field type, you can also use [the InlineCreate operation](/docs/{{version}}/crud-operation-inline-create), which will add a `[+ Add Item]` button next to the dropdown, to let the admin create a User/Role in a modal, without leaving the current Create User/Role form;

##### EXTRA: Saving additional attributes to the pivot table

If your pivot table has additional database columns (eg. not only `user_id` and `role_id` but also `notes` or `supervisor`, you can use the `relationship` field to show a subform, instead of a `select2`, and show `subfields` for each of those attributes you want edited on the pivot table. For the example above (`User -> BelongsToMany -> Roles`) you should do the following:


**Step 1.** Setup the pivot fields in your relation definition:

```php
// inside App\Models\User
public function roles() {
    return $this->belongsToMany('App\Models\Role')->withPivot('notes', 'some_other_field'); // `notes` and `some_other_field` are aditional fields in the pivot table that you plan to show in the form.
}
```

**Step 2.** Setup the pivot fields in your relation definition:

```php
// inside UserCrudController::setupCreateOperation()
CRUD::field('roles')->subfields([
    ['name' => 'notes', 'type' => 'textarea'],
    ['name' => 'some_other_field']
]);
```

And you are done: a subform will shown, with a select for the pivot connected entity field and the defined fields and Backpack will take care of the saving process.

**Need to change the pivot `select` field?** You can add any configuration to the pivot field as you would do in a [relationship](#relationship) select field, the only difference is is that it should go inside the `pivotSelect` key:
```php
CRUD::field('users')->subfields([ ['name' => 'notes'] ])
    ->pivotSelect([
        'ajax' => true,
        'data_source' => backpack_url('role/fetch/user'),
        'placeholder' => 'some placeholder',
        'wrapper' => [
            'class' => 'col-md-6'
        ]
    ]);
```

<a name="morphone"></a>
#### morphOne (1-1 polymorphic relationship)

- example:
    - Post/User -> morphOne -> Video.
    - The User model and the Post model have 1 Video each.
    - [the `morphOne` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-one-polymorphic-relations) in both the Post/User and Video models;

You can add a subform for the related entry to be created/edited/deleted from the main form:

```php
CRUD::field('video')->type('relationship')->subfields([
    'url',
    [
        'name' => 'description',
        'type' => 'ckeditor',
    ]
]);
```

Backpack will take care of the saving process and deal with the morphable relation.


<a name="morphone"></a>
#### morphOne (1-1 polymorphic relationship) one field for each related entry attribute

- example:
    - Post/User -> morphOne -> Video.
    - The User model and the Post model have 1 Video each.
    - [the `morphOne` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-one-polymorphic-relations) in both the Post/User and Video models;

You can add any type of field to change the attribute on the related entry, but make sure to prefix the field name with the name of the relationship:

```php
CRUD::field('video.description')->type('ckeditor');
CRUD::field('video.url');
```

Backpack will take care of the saving process and deal with the morphable relation.


<a name="morphmany"></a>
#### morphMany (1-n polymorphic relationship)

This is in all aspects similar to [HasMany](#hasmany) relation, the difference is that it's stored in a pivot table.
- example:
    - Video/Post -> morphMany -> Comment.
    - The Video model and the Post model can have multiple Comment model but the comment belongs to only one of them.
    - [the `morphMany` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-many-polymorphic-relations) in both the Post/Video and Comment models;

There is no sense in using this a `select` when using a polymorphic relation because the items that could/would be select might belong to different entities. So you should setup this relation as you would setup a [HasMany creatable](#hasmany-creatable).

```php
// inside PostCrudController::setupCreateOperation() and inside VideoCrudController::setupCreateOperation()
CRUD::field('comments')->subfields([['name' => 'comment_text']]); //note comment_text is a text field in the comment table.
```


<a name="morphtomany"></a>
#### morphToMany (n-n polymorphic relationship)

This is in all aspects similar to [BelongsToMany](#belongstomany) relation, the difference is that it stores the `morphable` entity in the pivot table:
    - Video/Post -> belongsToMany -> Tag.
    - The Video model and the Post model can have multiple Tag model and each Tag model can belong to one or more of them.
    - [the `morphToMany` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#many-to-many-polymorphic-relations) in both the Post/Video and Tag models;

Please read the relationship [BelongsToMany](#belongstomany) documentation, everything is the same in regards to fields definition and Backpack will take care of the morphable relation saving.

<a name="morphto"></a>
#### MorphTo (n-1 relationship)

Using this relation type Backpack will automatically manage for you both `_type` and `_id` fields of this relation.
Let's say we have `comments`, that can be either for `videos` or `posts`.
- Your `Comment` Model should have its `morphTo` relation set up.
- Your db table should have the `commentable_type` and `commentable_id` columns.
```php
// in CommentCrudController you can add the morphTo fields by naming the field the morphTo relation name
CRUD::field('commentable')
    ->addMorphOption('App\Models\Video')
    ->addMorphOption('App\Models\Post');
```
This will generate two inputs:
1 - A select with two options `Video` and `Post` as the `morph type field`.
2 - A second select that will have the options for both `Video` and `Post` models.

In a real world scenario, you might have other needs, like using AJAX to select the actual entries or changing the inputs size etc. For that, check out the available attributes:

```php
// ->addMorphOption(string $model/$morphMapName, string $labelInSelect, array $options)
CRUD::field('commentable')
    ->addMorphOption('App\Models\Video')
    ->addMorphOption('App\Models\Post', 'Posts', [
        [
            'data_source'          => backpack_url('comment/fetch/post'),
            'minimum_input_length' => 2,
            'placeholder'          => 'select an amazing post',
            'method'               => 'POST',
            'attribute'            => 'title',
        ]
    ]);

// by defining `data_source` you are telling Backpack that the `Posts` select should be an ajax select.
```
In this scenario the same two selects would be generated, but for the Post, your admin see an AJAX field, instead of a static one, use POST instead of GET etc.

To further customize the fields you can use `morphTypeField` and `morphIdField` to configure the select sizes etc.

```php
CRUD::field('commentable')
    ->addMorphOption('App\Models\Video')
    ->addMorphOption('App\Models\Post', 'Posts')
    ->morphTypeField(['wrapper' => ['class' => 'form-group col-sm-4']])
    ->morphIdField(['wrapper' => [
        'class' => 'form-group col-sm-8'],
        'attributes' => ['my_custom_attribute' => 'custom_value']
    ]);
```

Here is an example using array field definition:

```php
CRUD::field([
    'name' => 'commentable',
    'morphOptions' => [
        ['App\Models\PetShop\Owner', 'Owners'],
        ['monster', 'Monsters', [
            'placeholder' => 'Select a little monster'
        ]],
        ['App\Models\PetShop\Pet', 'Pets', [
            'data_source' => backpack_url('pet-shop/comment/fetch/pets'),
            'minimum_input_length' => 2,
            'placeholder' => 'select a fluffy pet'
        ]],
    ],
    'morphTypeField' => [
        'wrapper' => ['class' => 'form-group col-md-6']
    ],
    'morphIdField' => [
        'wrapper' => ['class' => 'form-group col-md-6']
    ]
]);
```

#### hasOneThrough (1-1-1 relationship)

- This is a "read-only" relationship. It does not make sense to add a field for it.


#### hasManyThrough (1-1-n relationship)

- This is a "read-only" relationship. It does not make sense to add a field for it.


#### Has One of Many (1-1 relationship out of 1-n relationship)

- This is a "read-only" relationship. It does not make sense to add a field for it. Please use the general-purpose relationship towards this entity (the 1-n relationship, without `latestOfMany()` or `oldestOfMany()`).


#### Morph One of Many (1-1 relationship out of 1-n relationship)

- This is a "read-only" relationship. It does not make sense to add a field for it. Please use the general-purpose relationship towards this entity (the 1-n relationship, without `latestOfMany()` or `oldestOfMany()`).

#### MorphedByMany (n-n inverse relationship)

- We do not provide an interface to edit this relationship. We never needed it, nobody ever asked for it and it would be very difficult to create an interface that is easy-to-use and easy-to-understand for the admin. If you find yourself needing this, please let us know by opening an issue on GitHub.


<a name="operations"></a>
## Operations


<a name="add-non-editable-input-inside-create-or-update-operation-stripped-request"></a>
### Add an Uneditable Input inside Create or Update Operation - Stripped Request

You might want to add a new attribute to the Model that gets saved. Let's say you want to add an `updated_by` indicator to the Update operation, containing the ID of the user currently logged in (`backpack_user()->id`).

**By default, Backpack it will only save inputs that have a corresponding CRUD field defined.** But you can override this behaviour, by using the setting called `strippedRequest`, which determine the which fields should actually be saved, and which fields should be "stripped" from the request.

Here's how you can use `strippedRequest` to add an `updated_by` item to be saved (but this will work for any changes you want to make to the request, really). You can change the request at various points in the request:
- (a) in your CrudController (eg. `CRUD::setOperationSetting('strippedRequest', StripBackpackRequest::class);` in your `setup()`);
- (b) in your Request (eg. same as above, inside `prepareForValidation()`);
- (c) in your config, if you want it to apply for all CRUDs (eg. inside `config/backpack/operations/update.php`);

Let's demonstrate each one of the above:

**Option 1.** In the controller. You can change the `strippedRequest` closure inside your `ProductCrudController::setup()`:
```php
public function setupUpdateOperation()
{
    CRUD::setOperationSetting('strippedRequest', function ($request) {
        // keep the recommended Backpack stripping (remove anything that doesn't have a field)
        // but add 'updated_by' too
        $input = $request->only(CRUD::getAllFieldNames());
        $input['updated_by'] = backpack_user()->id;

        return $input;
    });
}
```

**Option 2.** In the request. You can change the same `strippedRequest` closure inside the `ProductFormRequest` that contains your validation:
```php
    protected function prepareForValidation()
    {
        \CRUD::set('update.strippedRequest', function ($request) { //notice here that update is refering to update operation, change accordingly
            // keep the recommended Backpack stripping (remove anything that doesn't have a field)
            // but add 'updated_by' too
            $input = $request->only(\CRUD::getAllFieldNames());
            $input['updated_by'] = backpack_user()->id;

            return $input;
        });
    }
```

**Option 3.** In the config file. You cannot use a closure (because closures don't get cached). But you can create an invokable class, and use that as your `strippedRequest`, in your `config/backpack/operations/update.php` (for example). Then it will apply to ALL update operations, on all entities:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;

class StripBackpackRequest
{
    public function __invoke(Request $request)
    {
        $input = $request->only(\CRUD::getAllFieldNames());
        $input['updated_by'] = backpack_user()->id;
        return $input;
    }
}
```


<a name="how-to-make-the-form-smaller-or-bigger"></a>
### How to make the form smaller or bigger

In practice, what you want is to change the class on the main `<div>` of the Create/Update operation. To learn how to do that, please take a look at the next section - how to make an operation wider or narrower.

<a name="how-to-make-an-operation-wider-or-narrower"></a>
### How to make an operation wider or narrower

If you want to make the contents of an operation take more / less space from the window, you can easily do that. You just need to change the class on the main `<div>` of that operation, what we call the "content class". Depending on the scope of your change (for one or all CRUDs) here's how you can do that:

(A) for all CRUDs, by specifying the custom content class in your ```config/backpack/crud.php```:

```php
    // Here you may override the css-classes for the content section of the create view globally
    // To override per view use $this->crud->setCreateContentClass('class-string')
    'create_content_class' => 'col-md-8 col-md-offset-2',

    // Here you may override the css-classes for the content section of the edit view globally
    // To override per view use $this->crud->setEditContentClass('class-string')
    'edit_content_class'   => 'col-md-8 col-md-offset-2',

    // Here you may override the css-classes for the content section of the revisions timeline view globally
    // To override per view use $this->crud->setRevisionsTimelineContentClass('class-string')
    'revisions_timeline_content_class'   => 'col-md-10 col-md-offset-1',

    // Here you may override the css-class for the content section of the list view globally
    // To override per view use $this->crud->setListContentClass('class-string')
    'list_content_class' => 'col-md-12',

    // Here you may override the css-classes for the content section of the show view globally
    // To override per view use $this->crud->setShowContentClass('class-string')
    'show_content_class'   => 'col-md-8 col-md-offset-2',

    // Here you may override the css-classes for the content section of the reorder view globally
    // To override per view use $this->crud->setReorderContentClass('class-string')
    'reorder_content_class'   => 'col-md-8 col-md-offset-2',
```

(B) for a single CRUD, by using:

```php
CRUD::setCreateContentClass('col-md-8 col-md-offset-2');
CRUD::setUpdateContentClass('col-md-8 col-md-offset-2');
CRUD::setListContentClass('col-md-8 col-md-offset-2');
CRUD::setShowContentClass('col-md-8 col-md-offset-2');
CRUD::setReorderContentClass('col-md-8 col-md-offset-2');
CRUD::setRevisionsTimelineContentClass('col-md-8 col-md-offset-2');
```


<a name="miscellaneous"></a>
## Miscellaneous

<a name="use-the-media-library"></a>
### Use the Media Library (File Manager)

The default Backpack installation doesn't come with a file management component. Because most projects don't need it. But we've created a first-party add-on that brings the power of [elFinder](http://elfinder.org/) to your Laravel projects. To install it, [follow the instructions on the add-ons page](https://github.com/Laravel-Backpack/FileManager). It's as easy as running:

```bash
# require the package
composer require backpack/filemanager

# then run the installation process
php artisan backpack:filemanager:install
```

If you've chosen to install [backpack/filemanager](https://github.com/Laravel-Backpack/FileManager), you'll have elFinder integrated into:
- TinyMCE (as "tinymce" field type)
- CKEditor (as "ckeditor" field type)
- CRUD (as "browse" and "browse_multiple" field types)
- stand-alone, at the */admin/elfinder* route;

For the integration, we use [barryvdh/laravel-elfinder](https://github.com/barryvdh/laravel-elfinder).

![Backpack CRUD ListEntries](https://backpackforlaravel.com/uploads/docs-4-0/media_library.png)

<a name="how-to-manually-install-backpack-crud"></a>
### How to manually install Backpack

If the automatic installation doesn't work for you and you need to manually install CRUD, here are all the commands it is running:

1) In your terminal:

``` bash
composer require backpack/crud
```

2) Instead of running ```php artisan backpack:install``` you can run:
```bash
php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag="minimum"
php artisan migrate
php artisan backpack:publish-middleware
composer require --dev backpack/generators
php artisan basset:install --no-check --no-interaction

# then install ONE of the first-party themes:
php artisan backpack:require:theme-tabler
php artisan backpack:require:theme-coreuiv4
php artisan backpack:require:theme-coreuiv2

# then check assets can be correctly used
php artisan basset:check
```


<a name="overwrite-a-method-on-the-crud-panel-object"></a>
### Overwrite a Method on the CrudPanel Object

Starting with Backpack v4, you can use a custom CrudPanel object instead of the one in the package. In your custom CrudPanel object, you can overwrite any method you want, but please note that this means that you're overwriting core components, and will be making it more difficult to upgrade to newer versions of Backpack.

You can do this in any of your service providers (ex: ```app/Providers/AppServiceProvider.php```) to load your class instead of the one in the package:

```php
$this->app->extend('crud', function () {
    return new \App\MyExtendedCrudPanel;
});
```

Details and implementation [here](https://github.com/Laravel-Backpack/CRUD/pull/1990).



<a name="error-failed-to-download-backpack-pro"></a>
### Error: Failed to Download Backpack PRO

When trying to install Backpack\PRO (or any of our closed-source add-ons, really), you might run into the following error message:

```bash
Downloading backpack/pro (1.1.1)
Failed to download backpack/pro from dist: The "https://backpackforlaravel.com/satis/download/dist/backpack/pro/backpack-pro-xxx-zip-zzz.zip" file could not be downloaded (HTTP/2 402 )
```

Or maybe:

```bash
Syncing backpack/pro (1.1.1) into cache
Cloning failed using an ssh key for authentication, enter your GitHub credentials to access private repos
Head to https://github.com/settings/tokens/new?scopes=repo&description=Composer+on+DESKTOP-BLABLA+2022-07-14+1559
to retrieve a token.
```

What's happening there? That is a general Composer error - "file could not be downloaded". The error itself doesn't give too much information, but we can make an educated guess.

**99% of the people who report this error have the same problem - they do not have access to that package version.** They bought updates until 1.0.13 (for example), so they DO NOT have access to the latest version (1.1.1 in this example). What you can do, in that case, is **lock the installation to the latest you have access to**, for example

```bash
composer require backpack/pro:"1.0.13"
```

Alternatively, you can purchase more access on the [Backpack website](https://backpackforlaravel.com/pricing). Or contact the team if there's a mistake.

--

How do you find out what's the last version you have access to?

(1) **Whenever the error above happens, Backpack will send you an email**, with details and instructions. **Check your email**, it will also include the latest version you have access to.

(2) [Your Tokens page](https://backpackforlaravel.com/user/tokens) will show more details. For each token you have, it will say when it stops giving you access to updates. If it doesn't say the last version directly, you can corroborate that last day with [the changelog](https://backpackforlaravel.com/products/pro-for-unlimited-projects/CHANGELOG.md ), to determine what's the last version that _you_ have access to.

--

Why the ugly, general error? Because Composer doesn't allow vendors to customize the error, unfortunately. Backpack's server returns a better error message, but Composer doesn't show it.


<a name="enable-database-transactions-for-create-and-update"></a>
### Enable database transactions for create and update

In v6.6 we introduced the ability to enable database transactions for create and update operations. This is useful if you have a lot of relationships and you want to make sure that all of them are saved or none of them are saved. 
You can enable this feature globaly at `config/backpack/base.php` by enabling `useDatabaseTransactions`. 

> **Note:** This feature will be enable by default starting `v7`
