# Operations

When creating a CRUD Panel, your ```EntityCrudController``` (where Entity = your model name) is extending ```CrudController```. **By default, no operations are enabled.** No routes are registered.

To use an operation, you need to use the operation trait on your controller. For example, to enable the List operation:

```php
<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
}
```

**Operations are traits that add functionality to that controller**. Most operations will have:
- routes inside a ```setupOperationNameRoutes()```; this gets called in your ```routes/backpack/custom.php``` by the ```Route::crud('product', 'ProductCrudController``` macro, which determines which routes to register for that CrudController;
- default setup inside a ```setupOperationNameDefaults()``` method, that gets called automatically by CrudController when you use that operation on a controller;
- methods that return views, or perform certain operations;

you can [add custom operations](/docs/{{version}}/crud-operations#creating-a-custom-operation-1).

## Standard Operations

No operations are enabled by default.

But Backpack does provide the logic for the most common operations admins perform on Eloquent model. use it (and maybe configure it) in your controller.

Operations provided by Backpack:
- [List](/docs/{{version}}/crud-operation-list-entries) - allows the admin to see all entries for a model, with pagination, search [FREE] and filters [PRO]
- [Create](/docs/{{version}}/crud-operation-create) - allows the admin to add a new entry; [FREE]
- [Update](/docs/{{version}}/crud-operation-update) - allows the admin to edit an existing entry; [FREE]
- [Show](/docs/{{version}}/crud-operation-show) - allows the admin to preview an entry; [FREE]
- [Delete](/docs/{{version}}/crud-operation-delete) - allows the admin to remove and entry; [FREE]
- [BulkDelete](/docs/{{version}}/crud-operation-delete) - allows the admin to remove multiple entries in one go; [PRO]
- [Clone](/docs/{{version}}/crud-operation-clone) - allows the admin to make a copy of a database entry; [PRO]
- [BulkClone](/docs/{{version}}/crud-operation-clone) - allows the admin to make a copy of multiple database entries in one go; [PRO]
- [Reorder](/docs/{{version}}/crud-operation-reorder) - allows the admin to reorder & nest all entries of a model, in a hierarchy tree; [FREE]
- [Revisions](/docs/{{version}}/crud-operation-revisions) - shows an audit log of all changes to an entry, and allows you to undo modifications; [FREE]

### Operation Actions

Each Operation is actually _a trait_, which can be used on CrudControllers. This trait can contain one or more methods (or functions). Since Laravel calls each Controller method an _action_, that means each _Operation_ can have one or many _actions_. For example, we have the ```create``` operation and two actions: ```create()``` and ```store()```.

```php
trait CreateOperation
{
    public function create()
    {
        // ...
    }

    public function store()
    {
        // ...
    }
}
```

An action can do something with AJAX and return true/false, it can return a view, or whatever else you can do inside a controller method. Notice that it's a ```public``` method - which is a Laravel requirement, to point a route to it.

You can check which action is currently being performed using the [standard Laravel Route API](https://laravel.com/api/8.x/Illuminate/Routing/Route.html):

- ```\Route::getCurrentRoute()->getAction()``` or ```$this->crud->getRequest()->route()->getAction()```:
```
array:8 [▼
  "middleware" => array:2 [▼
    0 => "web"
    1 => "admin"
  ]
  "as" => "crud.monster.index"
  "uses" => "App\Http\Controllers\Admin\MonsterCrudController@index"
  "operation" => "list"
  "controller" => "App\Http\Controllers\Admin\MonsterCrudController@index"
  "namespace" => "App\Http\Controllers\Admin"
  "prefix" => "admin"
  "where" => []
]
```
- ```\Route::getCurrentRoute()->getActionName()``` or ```$this->crud->getRequest()->route()->getActionName()```:
```
App\Http\Controllers\Admin\MonsterCrudController@index
```
- ```\Route::getCurrentRoute()->getActionMethod()``` or ```$this->crud->getRequest()->route()->getActionMethod()```:
```
index
```

You can also use the shortcuts on the CrudPanel object:
```php
$this->crud->getActionMethod(); // returns the method on the controller that was called by the route; ex: create(), update(), edit() etc;
$this->crud->actionIs('create'); // checks if the controller method given is the one called by the route
```

### Titles, Headings and Subheadings

For standard CRUD operations, each _action_ that shows an interface uses some texts to show the user what page, operation or action he is currently performing:
- **Title** - page title, shown in the browser's title bar;
- **Heading** - biggest heading on page;
- **Subheading** - short description of the current page, sits beside the heading;

You can get and set the above using:
```php
// Getters
$this->crud->getTitle('create'); // get the Title for the create action
$this->crud->getHeading('create'); // get the Heading for the create action
$this->crud->getSubheading('create'); // get the Subheading for the create action

// Setters
$this->crud->setTitle('some string', 'create'); // set the Title for the create action
$this->crud->setHeading('some string', 'create'); // set the Heading for the create action
$this->crud->setSubheading('some string', 'create'); // set the Subheading for the create action
```

These methods are usually useful inside actions, not in ```setup()```. Since action methods are called _after_ ```setup()```, any call to these getters and setters in ```setup()``` would get overwritten by the call in the action.

### Handling Access to Operations

Admins are allowed to do an operation or not using a very simple system: ```$crud->settings['operation_name']['access']``` will either be ```true``` or ```false```. When you enable a stock Backpack operation by doing ```use SomeOperation;``` on your controller, all operations will run ```$this->crud->allowAccess('operation_name');```, which will toggle that variable to ```true```.

You can add or remove elements to this access array in your ```setup()``` method, or your custom methods, using:
```php
$this->crud->allowAccess('operation_name');
$this->crud->allowAccess(['list', 'update', 'delete']);
$this->crud->denyAccess('operation');
$this->crud->denyAccess(['update', 'create', 'delete']);

// allow or deny access depending on the entry in the table
$this->crud->operation('list', function() {
        $this->crud->setAccessCondition(['update', 'delete'], function ($entry) {
            return $entry->id===1 ? true : false;
    });
});

$this->crud->hasAccess('operation_name'); // returns true/false
$this->crud->hasAccessOrFail('create'); // throws 403 error
$this->crud->hasAccessToAll(['create', 'update']); // returns true/false
$this->crud->hasAccessToAny(['create', 'update']); // returns true/false
```

### Operation Routes

Starting with Backpack 4.0, routes can be defined in the CrudController. Your ```routes/backpack/custom.php``` file will have calls like ```Route::crud('product', 'ProductCrudController');```. This ```Route::crud()``` is a macro that will go to that controller and run all the methods that look like ```setupXxxRoutes()```. That means each operation can have its own method to define the routes it needs. And they do - if you check out the code of any operation, you'll see every one of them has a ```setupOperationNameRoutes()``` method.

If you want to add a new route to your controller, there are two ways to do it:
1. Add a route in your ```routes/backpack/custom.php```;
2. Add a method following the ```setupXxxRoutes()``` convention to your controller;

Inside a ```setupOperationNameRoutes()```, you'll notice that's also where we define the operation name:

```php
    protected function setupShowRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/show', [
            'as'        => $routeName.'.show',
            'uses'      => $controller.'@show',
            'operation' => 'show',
        ]);
    }
```

### Getting an Operation Name

Once an operation name has been set using that route, you can do ```$crud->getOperation()``` inside your views and do things according to this.

## Operation Lifecycle

When making customizations to existing operations or creating custom operations, it's important to understand how Backpack loads operations in the first place. Let's take it from the top, with a practical example. Backpack CRUDs follow the simple MVC pattern (Model-View-Controller):
- a route points to a CrudController (eg. `Route::crud('article', 'ArticleCrudController')`)
- that `ArticleCrudController` then loads operations as traits eg. `use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;`

Inside `DeleteOperation` (or any other operation) you will typically have these at least three methods, including `setupXxxRoutes()` and `setupXxxDefaults()` methods:

```php

trait DeleteOperation
{
    // Defines which routes are needed for the operation
    protected function setupDeleteRoutes($segment, $routeName, $controller) {}
    
    // Add the default settings, buttons, etc that this operation needs.
    protected function setupDeleteDefaults() {}

    // Custom methods, that the routes registered above call 
    public function destroy($id) {}
}
```

When do these get called? Well:
- `setupDeleteRoutes()` gets called _when routes are being set up_; if your CRUD routes are defined in `routes/backpack/custom.php` like our convention, then _that_ is when those methods are called;
- `setupDeleteDefaults()` and all other methods in the CrudController get called when the request is processed; let's get deeper into that;

When a request to `example.com/admin/article` gets made:
- Laravel will setup all routes; that includes the CRUD routes, so it will call all setupXxxRoutes methods, in all operation traits, used on all CrudControllers);
- Laravel will identify that the route points to a particular CrudController, and a particular method inside that controller (in our example, `DeleteOperation::destroy`, used inside `ArticleCrudController`); so it will instantiate the ArticleCrudController;
- when `ArticleCrudController` gets instantiate, its `parent::__construct()` method will set up the operation for you; and it will do it in the following order:

1. CrudController will set up the operation defaults (by calling `DeleteOperation::setupDeleteDefaults()`);
2. CrudController will call the `ArticleController::setup()`, to allow you as a developer to set up all operation in one place. It's discouraged to use the `setup()` method for that - in practice it's much cleaner to have a setup method for each operation.
3. CrudController will call the `ArticleController::setupDeleteOperation()` method, if present, to allow the developer to configure that operation;

This means you can think of the Operation lifecycle of having the following "lifecycle events":
- the operation routes being set up
- the defaults being set up
- the operation being set up (aka configured by developer)

Understanding these moments and their order, is important to place your custom logic _in the right place_ and _at the right time_ in the operation lifecycle.

### Lifecycle Hooks

At important points in the CRUD Lifecycle, Backpack triggers what we call "_lifecycle events_". You can hook into those events - by registering custom code that will run when that lifecycle event happens. This allows you to customize the process, without having to override any of the core files for that CRUD or Operation.

For example, in a Backpack CRUD all routes are setup on the **CrudController** using methods like `setupModerateOperationRoutes()`. Before those methods are called, Backpack triggers an event called `crud:before_all_route_setup`. If you want to add your own code that runs there, you can do:

```php
use Backpack\CRUD\app\Library\CrudPanel\Hooks\Facades\LifecycleHook;

LifecycleEvent::hookInto('crud:before_setup_routes', function($controller) {
    // do something before the routes are setup
});
```

#### General Lifecycle Events

Here are all the general lifecycle events Backpack triggers:

- `crud:before_setup_routes` - before any operation routes are registered
- `crud:after_setup_routes` - after all operation routes have been registered
- `crud:before_setup_defaults` - before all defaults are setup
- `crud:after_setup_defaults` - after all defaults have been setup
- `crud:before_setup` - before any operation is set up
- `crud:after_setup` - after that operation has been set up

#### Operation Lifecycle Events

In addition to the general Lifecycle events above, each operation can trigger its own lifecycle events. For example, here are the lifecycle events triggered by the Create operation:

`create:before_setup` - exposes parameters: $crud
`create:after_setup` - exposes parameters: $crud

You can hook into those events using a similar syntax to the general lifecycle events:

```php
LifecycleEvent::hookInto(['create:before_setup'], function() {
    $this->crud->addButton('top', 'create', 'view', 'crud::buttons.create');
});
```

Note that when using the hooks for specific operations, the hook is prefixed with the operation name followed by the hook name. This allow you to hook into specific operation events, or even to multiple events at the same time:

```php
LifecycleEvent::hookInto(['create:before_setup', 'list:before_setup'], function() {
    // do something before the create operation and the list operation are setup
});
```

### How to add your own hooks

You can add your own lifecycle events to your custom operations by calling the `LifecycleEvent::trigger()` method at the appropriate points in your operation. For example, if you have a custom operation that need to do something after some action happens in the operation, you can trigger a lifecycle event like this:

```php
public function moderate() {
    // do something to "moderate" the entry and register the hook
    LifecycleEvent::trigger('moderate:after_moderation', [
        'controller' => $this,
        'operation' => 'moderate',
    ]);
}
```

Then, other developers can hook into that event like this:

```php
LifecycleEvent::hookInto(['moderate:after_moderation'], function($controller, $operation) {
    // do something after the moderate operation has been executed
});
```
