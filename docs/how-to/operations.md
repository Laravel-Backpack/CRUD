## Operations

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

### How to make the form smaller or bigger

In practice, what you want is to change the class on the main `<div>` of the Create/Update operation. To learn how to do that, please take a look at the next section - how to make an operation wider or narrower.

### How to make an operation wider or narrower

If you want to make the contents of an operation take more / less space from the window, you can do that. change the class on the main `<div>` of that operation, what we call the "content class". Depending on the scope of your change (for one or all CRUDs) here's how you can do that:

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
