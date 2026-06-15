# Clone Operation <span class="badge badge-info">PRO</span>

--

<a name="about"></a>
## About

This CRUD operation allows your admins to duplicate one or more entries from the database.

>**IMPORTANT:** The clone operation does NOT duplicate related entries. So n-n relationships will be unaffected. However, this also means that n-n relationships are ignored. So when you clone an entry, the new entry:
>- will NOT have the same 1-1 relationships
>- will have the same 1-n relationships
>- will NOT have the same n-1 relationships
>- will NOT have the same n-n relationships
>
>This might be somewhat counterintuitive for end users - though it should make perfect sense for us developers. This is why the Clone operation is NOT enabled by default.


<a name="requirements"></a>
## Requirements

This is a <span class="badge badge-info">PRO</span> operation. It requires that you have [purchased access to `backpack/pro`](https://backpackforlaravel.com/products/pro-for-unlimited-projects).

<a name="clone-a-single-item"></a>
## Clone a Single Item

<a name="how-it-works"></a>
### How it Works

Using AJAX, a POST request is performed towards ```/entity-name/{id}/clone```, which points to the ```clone()``` method in your EntityCrudController.

<a name="enabling"></a>
### How to Use

To enable it, you need to ```use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;``` on your EntityCrudController. For example:

```php
<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Product::class);
        CRUD::setRoute(backpack_url('product'));
        CRUD::setEntityNameStrings('product', 'products');

        // optionally you can redirect the user after the clone operation succeeds
        // if you set the `redirect_after_clone` option to true, it defaults to the edit page
        $this->crud->set('clone.redirect_after_clone', true);

        // you can also use a closure to define the redirect URL
        $this->crud->set('clone.redirect_after_clone', function($entry) {
            return backpack_url('product/'.$entry->id.'/show'); // redirect to show view instead of edit
        });
    }
}
```

This will make the Clone button appear in the table view, and will allow access to the controller method if manually accessed.

<a name="how-to-overwrite"></a>
### How to Overwrite

In case you need to change how this operation works, overwrite the ```clone()``` trait method in your EntityCrudController; make sure you give the method in the trait a different name, so that there are no conflicts:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation { clone as traitClone; }

public function clone($id)
{
    CRUD::hasAccessOrFail('clone');
    CRUD::setOperation('clone');

    // whatever you want

    // if you still want to call the old clone method
    $this->traitClone($id);
}
```

You can also overwrite the clone button by creating a file with the same name inside your ```resources/views/vendor/backpack/crud/buttons/```. You can easily publish the clone button there to make changes using:

```zsh
php artisan backpack:button --from=clone
```

<a name="clone-multiple-items-bulk-clone"></a>
## Clone Multiple Items (Bulk Clone)

In addition to the button for each entry, you can show checkboxes next to each element, and allow your admin to clone multiple entries at once.


<a name="how-it-works"></a>
### How it Works

Using AJAX, a POST request is performed towards ```/entity-name/bulk-clone```, which points to the ```bulkClone()``` method in your EntityCrudController.

**`NOTES:`**
- The bulk checkbox is added inside the first column defined in the table. For that reason the first column should be visible on table to display the bulk actions checkbox next to it.
- `Bulk Actions` also disable all click events for the first column, so make sure the first column **doesn't** contain an anchor tag (`<a>`), as it won't work.

<a name="enabling"></a>
### How to Use

To enable it, you need to ```use \Backpack\CRUD\app\Http\Controllers\Operations\BulkCloneOperation;``` on your EntityCrudController.

<a name="how-to-overwrite"></a>
### How to Overwrite

In case you need to change how this operation works, just create a ```bulkClone()``` method in your EntityCrudController:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\BulkCloneOperation { bulkClone as traitBulkClone; }

public function bulkClone($id)
{
    // your custom code here
    //
    // then you can call the old bulk clone if you want
    $this->traitBulkClone($id);
}
```

You can also overwrite the bulk clone button by creating a file with the same name inside your ```resources/views/vendor/backpack/crud/buttons/```. You can easily publish the clone button there to make changes using:

```zsh
php artisan backpack:button --from=bulk_clone
```

<a name="exempt-attribute-when-cloning"></a>
## Exempt attributes when cloning
If you have attributes that should not be cloned (eg. a SKU with an unique constraint), you can overwrite the replicate method on your model:

```php
    public function replicate(array $except = null) {

        return parent::replicate(['sku']);
    }
```
