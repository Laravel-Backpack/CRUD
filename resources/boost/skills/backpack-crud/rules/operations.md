# Backpack CRUD — Operations

## Built-in Operations

Enable by using the trait on your CrudController:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;   // PRO
use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;     // PRO
use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation; // PRO
use \Backpack\CRUD\app\Http\Controllers\Operations\BulkCloneOperation;  // PRO
```

Each operation has a corresponding `setup*Operation()` method where you configure it.

## Overriding an Operation's Logic

Override the action method directly on your controller — PHP's OOP takes over:

```php
// Override just the store() logic, keep everything else from CreateOperation
public function store()
{
    // your custom logic here
    $response = $this->traitStore(); // call the original if you want
    return $response;
}
```

## Custom Operation

```php
// 1. Add the route in setupRoutes() inside your controller:
protected function setupPublishRoutes($segment, $routeName, $controller)
{
    Route::get($segment . '/{id}/publish', [
        'as'         => $routeName . '.publish',
        'uses'       => $controller . '@publish',
        'operation'  => 'publish',
    ]);
}

// 2. Add defaults if needed:
protected function setupPublishDefaults()
{
    CRUD::allowAccess('publish');
}

// 3. Add the action method:
public function publish($id)
{
    $entry = CRUD::getEntry($id);
    $entry->update(['status' => 'published']);

    \Alert::success('Entry published.')->flash();

    return redirect()->back();
}

// 4. Add a button in setupListOperation():
CRUD::button('publish')->stack('line')->view('crud::buttons.quick_publish');
```

## Gotchas
- No operations are enabled by default — always use the traits explicitly.
- `setupListOperation()`, `setupCreateOperation()`, `setupUpdateOperation()`, `setupShowOperation()` are the correct method names — not `setupList()`.
- To restrict access to an operation: `CRUD::denyAccess('delete')` inside `setup()`.
