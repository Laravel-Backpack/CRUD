#### Creating a New Operation With An Interface

Let's say we have a ```UserCrudController``` and we want to create a simple ```Moderate``` operation, where we show a form where the admin can add his observations and what not. In this respect, it should be similar to ```Update``` - the button should lead to a separate form, then that form will probably have a Save button. So when creating the methods, we should look at ```CrudController::edit()``` and ```CrudController::updateCrud()``` for working examples.

What we need to do is:

1. Create routes for this operation - we can do that using the ```setupOperationNameRoutes()``` convention inside a ```UserCrudController```:

```php
    protected function setupModerateRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/moderate', [
            'as'        => $routeName.'.getModerate',
            'uses'      => $controller.'@getModerateForm',
            'operation' => 'moderate',
        ]);
        Route::post($segment.'/{id}/moderate', [
            'as'        => $routeName.'.postModerate',
            'uses'      => $controller.'@postModerateForm',
            'operation' => 'moderate',
        ]);
    }
```

2. Add the methods inside ```UserCrudController```:

```php
public function getModerateForm($id)
{
    $this->crud->hasAccessOrFail('update');
    $this->crud->setOperation('Moderate');

    // get the info for that entry
    $this->data['entry'] = $this->crud->getEntry($id);
    $this->data['crud'] = $this->crud;
    $this->data['title'] = 'Moderate '.$this->crud->entity_name;

    return view('vendor.backpack.crud.moderate', $this->data);
}

public function postModerateForm(Request $request = null)
{
    $this->crud->hasAccessOrFail('update');

    // TODO: do whatever logic you need here
    // ...
    // You can use
    // - $this->crud
    // - $this->crud->getEntry($id)
    // - $request
    // ...

    // show a success message
    \Alert::success('Moderation saved for this entry.')->flash();

    return \Redirect::to($this->crud->route);
}
```

3. Create the ```/resources/views/vendor/backpack/crud/moderate.php``` blade file, which shows the moderate form and what not. Best to start from the ```edit.blade.php``` file and customize:

```html
@extends(backpack_view('layouts.top_left'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => backpack_url('dashboard'),
    $crud->entity_name_plural => url($crud->route),
    'Moderate' => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
  <section class="container-fluid">
    <h2>
        <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
        <small>{!! $crud->getSubheading() ?? 'Moderate '.$crud->entity_name !!}.</small>

        @if ($crud->hasAccess('list'))
          <small><a href="{{ url($crud->route) }}" class="hidden-print font-sm"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
        @endif
    </h2>
  </section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
          <div class="card">
            <div class="card-header">
                <h3 class="card-title">Moderate</h3>
            </div>
            <div class="card-body row">
              Something in the card body
            </div><!-- /.card-body -->

            <div class="card-footer">
                Something in the box footer
            </div><!-- /.card-footer-->
          </div><!-- /.card -->
          </form>
    </div>
</div>
@endsection

```

4. Create a button for this operation. Since our operation is similar to "Update", lets start from that one and customize what we need. The button should just take the admin to the route that shows the Moderate form. Nothing fancy. We'll create a ```resources\views\vendor\backpack\crud\buttons\moderate.blade.php``` file:

```php
@if ($crud->hasAccess('moderate'))
    <a href="{{ url($crud->route.'/'.$entry->getKey().'/moderate') }}" class="btn btn-sm btn-link"><i class="fa fa-list"></i> Moderate</a>
@endif
```

4. We can now actually add this button to our ```UserCrudController::setup()```, to register that button inside the List operation:

```php
$this->crud->operation('list', function() {
  $this->crud->addButtonFromView('line', 'moderate', 'moderate', 'beginning');
});
```

Or better yet, we can do this inside a ```setupModerateDefaults()``` method, which gets called automatically by CrudController when the ```moderate``` operation is being performed (thanks to the operation name set on the routes):

```php
protected function setupModerateDefaults()
{
  $this->crud->allowAccess('moderate');

  $this->crud->operation('list', function() {
    $this->crud->addButtonFromView('line', 'moderate', 'moderate', 'beginning');
  });
}
```

>Of course, **if you plan to re-use this operation on another EntityCrudController**, it's a good idea to isolate the method inside a trait, then use that trait on each EntityCrudController where you want the operation to be enabled.

```php
<?php

namespace App\Http\Controllers\Admin\CustomOperations;

use Illuminate\Support\Facades\Route;

trait ModerateOperation
{
    protected function setupModerateRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/moderate', [
            'as'        => $routeName.'.getModerate',
            'uses'      => $controller.'@getModerateForm',
            'operation' => 'moderate',
        ]);
        Route::post($segment.'/{id}/moderate', [
            'as'        => $routeName.'.postModerate',
            'uses'      => $controller.'@postModerateForm',
            'operation' => 'moderate',
        ]);
    }

    protected function setupmoderateDefaults()
    {
        $this->crud->allowAccess('moderate');

        $this->crud->operation('list', function() {
          $this->crud->addButtonFromView('line', 'moderate', 'moderate', 'beginning');
        });
    }

    public function getModerateForm($id)
    {
        $this->crud->hasAccessOrFail('update');
        $this->crud->setOperation('Moderate');

        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['title'] = 'Moderate '.$this->crud->entity_name;

        return view('vendor.backpack.crud.moderate', $this->data);
    }

    public function postModerateForm(Request $request = null)
    {
        $this->crud->hasAccessOrFail('update');

        // TODO: do whatever logic you need here
        // ...
        // You can use
        // - $this->crud
        // - $this->crud->getEntry($id)
        // - $request
        // ...

        // show a success message
        \Alert::success('Moderation saved for this entry.')->flash();

        return \Redirect::to($this->crud->route);
    }
}
```
