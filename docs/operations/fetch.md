# Fetch Operation [PRO]

Allows a CrudController to respond to AJAX requests (for `relationship`, `select2_from_ajax`, `select2_from_ajax_multiple` fields) with entries from a different entity.

## Requirements

This is a [PRO] operation. It requires that you have [purchased access to `backpack/pro`](https://backpackforlaravel.com/products/pro-for-unlimited-projects).

## How to Use

To enable this operation, in your CrudController you need to **use the ```FetchOperation``` trait and add a new method** that responds to the AJAX requests (following the naming convention ```fetchEntityName()```). For example, for a `Tag` model you'd do:

```php
    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    protected function fetchTag()
    {
        return $this->fetch(\App\Models\Tag::class);
    }
```

To customize the FetchOperation, pass an array to the ```fetch()``` call, rather than a class name. For example:

```php
<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    protected function fetchTag()
    {
        return $this->fetch([
            'model' => \App\Models\Tag::class, // required
            'searchable_attributes' => ['name', 'description'],
            'paginate' => 10, // items to show per page
            'searchOperator' => 'LIKE',
            'query' => function($model) {
                return $model->active();
            } // to filter the results that are returned
        ]);
    }
}
```

## How It Works

Based on the fact that the ```fetchTag()``` method exists, the Fetch operation will create a ```/product/fetch/tag``` POST route, which points to ```fetchTag()```. Inside ```fetchTag()``` we call ```fetch()```, that responds with entries in the format ```select2``` needs.

**Preventing FetchOperation from guessing the searchable attributes**

If not specified `searchable_attributes` will be automatically inferred from model database columns. To prevent this behaviour you can setup an empty `searchable_attributes` array. For example:

```php
public function fetchUser() {
        return $this->fetch([
            'model' => User::class,
            'query' => function($model) {
                $search = request()->input('q') ?? false;
                if ($search) {
                    return $model->whereRaw('CONCAT(`first_name`," ",`last_name`) LIKE "%' . $search . '%"');
                }else{
                    return $model;
                }
            },
            'searchable_attributes' => []
        ]);
    }
```

**Adding attributes to fetched models without appends**

It's already possible to add attributes to the fetched models using the `appends` property in the model. However, this method has some drawbacks, like the fact that the appended attributes will be added to all instances of the model, whenever they are used, not only when they are fetched. If you want to add attributes to the fetched models without using the `appends` property in the model, you can use the `append_attributes` in the fetch configuration. For example:

```php
public function fetchUser() {
        return $this->fetch([
            'model' => User::class,
            'append_attributes' => ['something'],
        ]);
    }

// User.php model
public function something(): Attribute
{
    return Attribute::make(
        get: function (mixed $value, array $attributes) {
            return $attributes['something_else'];
        },
    );
}

// and in your field definition
CRUD::field('my_ajax_field')->attribute('something');
```

## Using FetchOperation with `select2_ajax` filter

The FetchOperation can also be used as the source URL for the `select2_ajax` filter. To do that, we need to:
- change the `select2_ajax` filter method from `GET` (its default) to `POST` (what FetchOperation uses);
- tell the filter what attribute we want to show to the user;

```
CRUD::addFilter([
  'name'        => 'category_id',
  'type'        => 'select2_ajax',
  'label'       => 'Category',
  'placeholder' => 'Pick a category',
  'method' => 'POST', // mandatory change
  // 'select_attribute' => 'name' // the attribute that will be shown to the user by default 'name'
  // 'select_key' => 'id' // by default is ID, change it if your model uses some other key
],
backpack_url('product/fetch/category'), // the fetch route on the ProductCrudController
function($value) { // if the filter is active
    // CRUD::addClause('where', 'category_id', $value);
});

```

## Security - Save-time Authorization Guard

The `query` closure you define in your `fetchXxx()` method does more than filter the AJAX search results — **it is also applied on the server at save time** to verify every submitted key was within the allowed set. This protects against tampered requests that try to relate entries the admin was never shown (IDOR / CWE-862).

```php
protected function fetchTag()
{
    return $this->fetch([
        'model' => \App\Models\Tag::class,
        'query' => function ($model) {
            return $model->where('active', true); // only active tags are valid at save time too
        },
    ]);
}
```

If an attacker submits IDs that your `query` would not have returned, Backpack will:
- **silently drop** the out-of-scope keys for `HasMany`, `MorphMany`, `BelongsToMany`, and `MorphToMany` relationships (the valid selections still persist);
- **abort with a validation error** on the field for `BelongsTo` relationships (the save is rejected with a 422).

If you do not define a `query` closure, all IDs are considered valid — the same behaviour as before this security fix.

> **Important — when is the guard applied automatically?** Backpack derives the guard from your `fetchXxx()` query whenever it can map the field to its fetch method — i.e. for any ajax relationship field (`relationship`, `select2_from_ajax`, `select2_from_ajax_multiple`) whose entity matches the `fetchXxx()` naming convention (e.g. entity `tag` → `fetchTag()`).
>
> If you had to set `data_source` **manually** and the field entity no longer matches the fetch method name, the convention breaks and the guard cannot be derived automatically. In that case, point the field at the right method explicitly with `'relation_options_query_source' => 'fetchMethodName'`:
> ```php
> CRUD::field([
> 'type' => 'select2_from_ajax',
> 'name' => 'category_id',
> 'data_source' => backpack_url('article/fetch/product-category'),
> 'relation_options_query_source' => 'fetchProductCategory', // reuse this fetch method's query
> ]);
> ```
>
> For fields backed by a **fully custom endpoint** (no FetchOperation at all), there is no fetch query to reuse — declare the allowed set directly with a `'relation_options_query'` closure instead.

## How to Overwrite

In case you need to change how this operation works, it's best to take a look at the ```FetchOperation.php``` trait to understand how it works. It's a pretty simple operation. Most common ways to overwrite the Fetch operation are documented below:

**Change the fetch database search operator**

You can customize the search operator for `FetchOperation` just like you can in ListOperation. By default it's `LIKE`, but you can:
- change the operator individually for each `fetchEntity` using `searchOperator => 'ILIKE'` in the fetch configuration;
- change the operator for all FetchOperations inside that CrudPanel by doing:
```php
public function setupFetchOperationOperation() {
        CRUD::setOperationSetting('searchOperator', 'ILIKE');
    }
```
- change the operator globally in your project, by creating a config file in `config/backpack/operations/fetch.php` and add the following:
```php
<?php
return [
    'searchOperator' => 'ILIKE',
];
```

**Custom behaviour for one fetch method**

To make a ```fetchCategory()``` method behave differently, you can copy-paste the logic inside the ```FetchOperation::fetch()``` and change it to do whatever you need. Instead of returning ```$this->fetch()``` you can return your own results, in this case fetch will only setup the ajax route for you.

**Custom behaviour for multiple fetch methods inside a Controller**

To make all calls to ```fetch()``` inside an EntityCrudController behave differently, you can overwrite the ```fetch()``` method in that controller:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

public function fetch($arg)
{
    // your custom code here
}
```

Then all ```$this->fetch()``` calls from that Controller will be using your custom code.

In case you need to call the original ```fetch()``` method (from the trait) inside your custom ```fetch()``` method (inside the controller), you can do:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation { fetch as traitFetch; }

public function fetch($arg)
{
    // your custom code here

    // call the method in the trait
    return $this->traitFetch();
}
```

**Custom behaviour for all fetch calls, in all Controllers**

If you want all your ```fetch()``` calls to behave differently, no matter what Controller they are in, you can:
- duplicate the ```FetchOperation``` trait inside your application;
- instead of using ```\Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation``` inside your controllers, use your custom operation trait;
