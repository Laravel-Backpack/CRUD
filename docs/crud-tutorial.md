# CRUD Crash Course

---

What's the simplest entity you can think of? It will probably be something like ```Tag```, which only holds an ```id``` and a ```name```. Let's create this new entity in the database, the model for it, then create a CRUD Panel to let admins manage entries for this entity.

We assume:
- you've [installed Backpack](/docs/{{version}}/installation);
- you don't already have a ```Tag``` model in your project;

<a name="generate-files"></a>
## Generate Files

In order to build a CRUD, we need an Eloquent model. So let's create a migration and model, using [Jeffrey Way's Generators](https://github.com/laracasts/Laravel-5-Generators-Extended):

```zsh
# install a 3rd party tool to generate migrations from the command line
composer require --dev laracasts/generators

# generate a migration and run it
php artisan make:migration:schema create_tags_table --schema="name:string:unique"
php artisan migrate
```

> **Note:** If you have a lot of database tables to generate, we heavily recommend **our paid addon - [Backpack DevTools](https://backpackforlaravel.com/products/devtools).** It's a GUI that helps you generate Migrations, Models (complete with relationships) and CRUDs from the browser. It does cost extra, but it's well worth the price if you use Backpack regularly or your models are not dead-simple.

Now that we have the ```tags``` table in the database, let's generate the actual files we'll be using:

```zsh
php artisan backpack:crud tag  #use singular, not plural
```

The code above will have generated:
- a migration (```database/migrations/yyyy_mm_dd_xyz_create_tags_table.php```);
- a database table (```tags``` with just two columns: ```id``` and ```name```);
- a model (```app/Models/Tag.php```);
- a controller (```app/Http/Controllers/Admin/TagCrudController.php```);
- a request (```app/Http/Requests/TagCrudRequest.php```);
- a resource route, as a line inside ```routes/backpack/custom.php```;
- a new menu item in ```resources/views/vendor/backpack/ui/inc/menu_items.blade.php```;

**Next up:** we'll need to go through the generated files, and customize for our needs.

<a name="customize-generated-files"></a>
## Customize Generated Files

We'll skip the migration and database table, since there's nothing there specific to Backpack, nothing to customize, and we've already run the migration.

<a name="the-model"></a>
### The Model

Let's take a look at the generated model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Tag extends Model {

  use CrudTrait;

  /*
  |--------------------------------------------------------------------------
  | GLOBAL VARIABLES
  |--------------------------------------------------------------------------
  */

  protected $table = 'tags';
  // protected $primaryKey = 'id';
  // public $timestamps = false;
  protected $guarded = ['id'];
  // protected $fillable = [];
  // protected $hidden = [];
  // protected $dates = [];

  /*
  |--------------------------------------------------------------------------
  | FUNCTIONS
  |--------------------------------------------------------------------------
  */

  /*
  |--------------------------------------------------------------------------
  | RELATIONS
  |--------------------------------------------------------------------------
  */

  /*
  |--------------------------------------------------------------------------
  | SCOPES
  |--------------------------------------------------------------------------
  */

  /*
  |--------------------------------------------------------------------------
  | ACCESSORS
  |--------------------------------------------------------------------------
  */

  /*
  |--------------------------------------------------------------------------
  | MUTATORS
  |--------------------------------------------------------------------------
  */
}
```

As we can see, this is a pretty standard Eloquent model. The only differences:
- our new model uses a trait Backpack needs, ```CrudTrait```;
- we have some comments to help us separate Eloquent logic in our Models;

By default, the model uses ```$guarded``` to prevent people from editing certain attributes. But, as with all generated classes, it might not be exactly what you want. We should make sure:
- ```$table``` contains the right table name (it does);
- ```$guarded``` contains all the attributes we want the admin to NOT be able to change; alternatively, specify which fields the admin is _allowed_ to edit using ```$fillable```;
- relationships to other models are properly defined (no need for our ```Tag``` model);

This is all _standard procedure_ for new Laravel models - nothing Backpack-specific here. In this particular case, where the entity is so simple and has no relationships, we don't need to make any changes to the generated model.

We're now done configuring the model - because we didn't already have a valid Eloquent model to use for our CRUD Panel. If we _did have_ a working Eloquent model, we only needed to add ```use \Backpack\CRUD\app\Models\Traits\CrudTrait;```

<a name="the-controller"></a>
### The Controller

Let's take a look at ```app/Http/Controllers/Admin/TagCrudController.php```. It should look something like this:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TagRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class TagCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Tag::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/tag');
        CRUD::setEntityNameStrings('tag', 'tags');
    }

    protected function setupListOperation()
    {
        CRUD::column('name');
        CRUD::column('created_at');
        CRUD::column('updated_at');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(TagRequest::class);
        CRUD::field('name');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}

```

What we should notice inside this TagCrudController is that:
- ```TagCrudController extends CrudController```;
- ```TagCrudController``` has a ```setup()``` method, where we must define the basics of our CRUD panel; everything we write here is applied on ALL operations;
- All operations are enabled by using that operation's trait on the controller;
- Each operation is set up inside a ```setupXxxOperation()``` method;

#### Operation Setup Methods

The best way to configure operations is to define each operation inside its ```setupXxxOperation()``` method, like the generated file above does. Over there the `setupXxxOperation()` methods:
- add a simple ```text``` column for our ```name``` attribute for the List operation (the table view);
- add a simple ```text``` field for our ```name``` attribute to the Create and Update forms;

#### Operation Setup Closures

An alternative to defining operations inside ```setupXxxOperation()``` methods is to do everything inside the ```setup()``` method. However, as we've mentioned before, everything you run in your ```setup()``` method is run for ALL operations. So you can easily end up bloating your operation with unnecessary operations. If you don't like having a method to configure each operation, and want to define everything in ```setup()```, you should do so inside an ```operation()``` closure. Whatever's inside that closure will only be run for that operation. For example, everything we've done above would look like this if done inside operation closures:

```php
    public function setup()
    {
        CRUD::setModel('App\Models\Tag');
        CRUD::setRoute(config('backpack.base.route_prefix') . '/tag');
        CRUD::setEntityNameStrings('tag', 'tags');

        CRUD::operation('list', function() {
          CRUD::column('name');
        });

        CRUD::operation(['create', 'update'], function() {
          CRUD::addValidation(TagCrudRequest::class);
          CRUD::field('name');
        });
    }


}
```

#### Other Calls

Here, inside your ```setup()``` or ```setupXxxOperation``` methods, you can also do a lot of other things, like adding buttons, adding filters, customizing your query, etc. For a full list of the things you can do inside ```setup()``` check out our [cheat sheet](/docs/{{version}}/crud-cheat-sheet).

Next, let's continue to another generated file.

<a name="the-request"></a>
### The Request

Backpack can also generate a [standard FormRequest file](https://laravel.com/docs/master/validation#form-request-validation), that you can use for validation of the Create and Update forms. There is nothing Backpack-specific in here, but let's take a look at the generated ```app/Http/Requests/TagRequest.php``` file:

```php
<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'name' => 'required|min:5|max:255'
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}

```

This file is a 100% pure FormRequest file - all Laravel, nothing particular to Backpack. In generated FormRequest files, no validation rules are imposed by default - unless you've generated requests using [Backpack DevTools](https://backpackforlaravel.com/products/devtools), which does its best to populate the validation rules from the database schema - just saying, it will save you time here too 😉.  But we do want ```name``` to be ```required``` and ```unique```, so let's do that, using the [standard Laravel validation rules](https://laravel.com/docs/master/validation#available-validation-rules):

```diff
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
-            // 'name' => 'required|min:5|max:255'
+            'name' => 'required|min:5|max:255|unique:tags,name'
        ];
    }
```

> If your validation needs to be different between the Create and Update operations, [you can easily do that too](/docs/{{version}}/crud-operation-create#separate-requests-for-create-and-update), by specifying different FormRequest files for each operation.

<a name="the-route"></a>
### The Route

We have already generated our CRUD route, and we don't need to do anything about it, but let's check our ```routes/backpack/custom.php```. It should look like this:

```php
<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    // CRUD resources and other admin routes
    Route::crud('tag', 'TagCrudController');
}); // this should be the absolute last line of this file
```

Here, we can see that our routes have been placed:
- under a prefix that we can change in ```config/backpack/base.php```;
- under a middleware we can change in ```config/backpack/base.php```;
- inside the ```App\Http\Controllers\Admin``` namespace, because that's where our custom CrudControllers will be generated;

**It's generally a good idea to have the all admin routes in this separate file.** If you edit this file in the future, make sure you leave the last line intact, so that other routes can be automatically generated inside this file. And of course, add your routes inside this route group, so that:
- you have a single prefix for your admin routes (ex: ```admin/tag```, ```admin/product```, ```admin/dashboard```);
- all your admin panel functionality is protected by the same middleware;
- all your admin panel controllers live in one place (```App\Http\Controllers\Admin```);

<a name="the-menu-item"></a>
### The Menu Item

We've previously generated a menu item in the ```resources/views/vendor/backpack/ui/inc/menu_items.blade.php``` file. That is using our menu item Blade components. The "active" state of the menu is done with JavaScript, based on the ```href``` attribute.

This is the bit that has been generated for you:

```php
<x-backpack::menu-item title="Tags" icon="la la-question" :link="backpack_url('tag')" />
```

You can of course change anything here, if you want. For example, change `la la-question` to `la la-tag`.

<a name="the-result"></a>
## The result

You are now ready to go to ```your-app-name.domain/admin/tag``` and see your fully functional admin panel for ```Tags```.

**Congratulations, you should now have a good understanding of how Backpack\CRUD works!** This is a very very basic example, but the process will be identical for Models with 50+ attributes, complicated logic, etc.

If you do have complex models, we _heavily_ recommend you go purchase [Backpack DevTools](https://backpackforlaravel.com/products/devtools) right now. It's the official paid GUI for generating all of the above. And while you're at it, you can [purchase a Backpack license](https://backpackforlaravel.com/pricing) too 😉
