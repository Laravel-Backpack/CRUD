## Creating a Custom Operation

### Command-line Tool

If you've installed ```backpack/generators```, you can do ```php artisan backpack:crud-operation {OperationName}``` to generate an empty operation trait, that you can edit and use on your CrudControllers. For example:

```bash
php artisan backpack:crud-operation Comment
```

Will generate ```app/Http/Controllers/Admin/Operations/CommentOperation``` with the following contents:

```php
<?php

namespace App\Http\Controllers\Admin\Operations;

use Illuminate\Support\Facades\Route;

trait CommentOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as the first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupCommentRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/comment', [
            'as'        => $routeName.'.comment',
            'uses'      => $controller.'@comment',
            'operation' => 'comment',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCommentDefaults()
    {
        $this->crud->allowAccess('comment');

        $this->crud->operation('comment', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            // $this->crud->addButton('top', 'comment', 'view', 'crud::buttons.comment');
            // $this->crud->addButton('line', 'comment', 'view', 'crud::buttons.comment');
        });
    }

    /**
     * Show the view for performing the operation.
     *
     * @return Response
     */
    public function comment()
    {
        $this->crud->hasAccessOrFail('comment');

        // prepare the fields you need to show
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? 'comment '.$this->crud->entity_name;

        // load the view
        return view("crud::operations.comment", $this->data);
    }
}
```

You'll notice the generated operation has:
- a GET route (inside ```setupCommentRoutes()```);
- a method that sets the defaults for this operation (```setupCommentDefaults()```);
- a method to perform the operation, or show an interface (```comment()```);

You can customize these to fit the operation you have in mind, then ```use \App\Http\Controllers\Admin\Operations\CommentOperation;``` inside the CrudControllers where you want the operation.

### Contents of a Custom Operation

Thanks to [Backpack's simple architecture](/docs/{{version}}/crud-basics#architecture), each CRUD panel uses a controller and a route, that are placed inside your project. That means you hold the keys to how this controller works.

To add an operation to an ```EntityCrudController```, you can:
- decide on your operation name; for example... "publish";
- create a method that loads the routes inside your controller:
```php
    protected function setupPublishRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/publish', [
            'as'        => $routeName.'.publish',
            'uses'      => $controller.'@publish',
            'operation' => 'publish',
        ]);
    }
```
- create a method that performs the operation you want:
```php
    public function publish()
    {
        // do something
        // return something
    }
```
- [add a new button for this operation to the List view](/docs/{{version}}/crud-buttons#creating-a-custom-button), or enable access to it, inside a ```setupPublishDefaults()``` method:
```php
    protected function setupPublishDefaults()
    {
        $this->crud->allowAccess('publish');

        $this->crud->operation('list', function () {
            $this->crud->addButton('line', 'publish', 'view', 'buttons.publish', 'beginning');
        });
    }
```

Take a look at the examples below for a better picture and code examples.

If you intend to reuse this operation across multiple controllers, you can group all the methods above in a trait, say ```PublishOperation.php``` and then just use that trait on the controllers where you need the operation:

```php
<?php

namespace App\Http\Controllers\Admin\Operations;

use Illuminate\Support\Facades\Route;

trait PublishOperation
{
    protected function setupPublishRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/publish', [
            'as'        => $routeName.'.publish',
            'uses'      => $controller.'@publish',
            'operation' => 'publish',
        ]);
    }

    protected function setupPublishDefaults()
    {
        $this->crud->allowAccess('publish');

        $this->crud->operation('list', function () {
            $this->crud->addButton('line', 'publish', 'view', 'buttons.publish', 'beginning');
        });
    }

    public function publish()
    {
        // do something
        // return something
    }
}
```

In the example above, you could just do ```use \App\Http\Controllers\Admin\Operations\PublishOperation;``` on any EntityCrudController, and your operation will be added - complete with routes, buttons, access, actions, everything.

### Access to Custom Operations

Since you're creating a new operation, in terms of restricting access you can:
1. allow access to this new operation depending on access to a default operation (usually if the admin can ```update```, he's OK to perform custom operations);
2. customize access to this particular operation, by just using a different key than the default ones; for example, you can allow access by using ```$this->crud->allowAccess('publish')``` in your ```setup()``` method, then check for access to that operation using ```$this->crud->hasAccess('publish')```;

### Adding Settings to the CrudPanel object

#### Using the Settings API

Anything an operation does to configure itself, or process information, should be stored inside ```$this->crud->settings```. It's an associative array, and you can add/change things using the Settings API:

```php
// for the operation that is currently being performed
$this->crud->setOperationSetting('show_title', true);
$this->crud->getOperationSetting('show_title');
$this->crud->hasOperationSetting('show_title');

// for a particular operation, pass the operation name as a last parameter
$this->crud->setOperationSetting('show_title', true, 'create');
$this->crud->getOperationSetting('show_title', 'create');
$this->crud->hasOperationSetting('show_title', 'create');

// alternatively, you could use the direct methods with no fallback to the current operation
$this->crud->set('create.show_title', false);
$this->crud->get('create.show_title');
$this->crud->has('create.show_title');
```

#### Using the crud config file

Additionally, operations can load default settings from the config file. You'll notice the ```config/backpack/crud.php``` file contains an array of operations, each with various settings. Those settings there are loaded by the operation as defaults, to allow users to change one setting in the config, and have that default changed across ALL of their CRUDs. If you take a look at the List operation you'll notice this:

```php
    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupListDefaults()
    {
        $this->crud->allowAccess('list');

        $this->crud->operation('list', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });
    }
```

You can do the same in custom operations. Because this call happens in setupListDefaults(), inside an operation closure, the settings will only be added when that operation is being performed.

### Adding Methods to the CrudPanel Object

You can add static methods to this ```$this->crud``` (which is a CrudPanel object) object with ```$this->crud->macro()```, because the object is [macroable](https://unnikked.ga/understanding-the-laravel-macroable-trait-dab051f09172). So you can do:

```php
class MonsterCrudController extends CrudController
{
   public function setup()
   {
       $this->crud->macro('doStuff', function($something) {
            echo '<pre>'; var_dump($something); echo '</pre>';
            dd($this);
        });
       $this->crud->macro('getColumnsInTheFormatIWant', function() {
            $columns = $this->columns();
            // ... do something to $columns;
            return $columns;
        });

       // bla-bla-bla the actual setup code
   }
   public function sendEmail()
   {
      // ...
      $this->crud->doStuff();
      dd($this->crud->getColumnsInTheFormatIWant());
      // ...
   }
   public function markPending()
   {
      // ...
      $this->crud->doStuff();
      dd($this->crud->getColumnsInTheFormatIWant());
      // ...
   }
}
```

So if you define a custom operation that needs some static methods added to the ```CrudPanel``` object, you can add them. The best place to register your macros in a custom operation would probably be inside your ```setupXxxDefaults()``` method, inside an operation closure. That way, the static methods you add are only added when that operation is being performed. For example:

```php
protected function setupPrintDefaults()
{
    $this->crud->allowAccess('print');

    $this->crud->operation('print', function() {
       $this->crud->macro('getColumnsInTheFormatIWant', function() {
            $columns = $this->columns();
            // ... do something to $columns;
            return $columns;
        });
    });
}
```

With the example above, you'll be able to use ```$this->crud->getColumnsInTheFormatIWant()``` inside your operation actions.

### Using a feature from another operation

Anything an operation does to configure itself, or process information, is stored on the ```$this->crud->settings``` property. Operation features (ex: fields, columns, buttons, filters, etc) are created in such a way that all they do is add an entry in settings, for an operation, and manipulate it. That means there is nothing stopping you from using a feature from one operation in a different operation.

If you create a Print operation, and want to use the ```columns``` feature that List and Show use, you can just go ahead and do ```$this->crud->addColumn()``` calls inside your operation. You'll notice the columns are stored inside ```$this->crud->settings['print.columns']```, so they're completely different from the ones in the List or Show operation. You'll need to actually do something with the columns you added, inside your operation methods or views - of course.

### Examples
