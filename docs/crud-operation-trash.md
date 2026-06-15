# Trash Operation <span class="badge badge-info">PRO</span>

--

<a name="about"></a>
## About

This CRUD operation allows your admins to soft delete, restore and permanently delete entries from the table. In other words, admins can send entries to the trash, recover them from trash or completely destroy them.

<a name="requirements"></a>
## Requirements

1. This is a <span class="badge badge-info">PRO</span> operation. It requires that you have [purchased access to `backpack/pro`](https://backpackforlaravel.com/products/pro-for-unlimited-projects).

2. In addition, it needs that your Model uses Laravel's `SoftDeletes` trait. For that, you should:
- generate a migration to add the `deleted_at` column to your table, eg. `php artisan make:migration add_soft_deletes_to_products --table=products`;
- inside that file's `Schema::table()` closure, add `$table->softDeletes();`
- run `php artisan migrate`
- add `use SoftDeletes` on the corresponding model (and import that class namespace);

<a name="trash-a-single-item"></a>
## Trash a Single Item <span class="badge badge-info">PRO</span>

<a name="how-it-works"></a>
### How it Works
- **Trash**:
Using AJAX, a DELETE request is performed towards ```/entity-name/{id}/trash```, which points to the ```trash()``` method in your EntityCrudController.

- **Restore**:
Using AJAX, a PUT request is performed towards ```/entity-name/{id}/restore```, which points to the ```restore()``` method in your EntityCrudController.

- **Destroy**:
Using AJAX, a DELETE request is performed towards ```/entity-name/{id}/destroy```, which points to the ```destroy()``` method in your EntityCrudController.

<a name="enabling"></a>
### How to Use

**Step 1.** If your EntityCrudController uses the `DeleteOperation`, remove it. `TrashOperation` is a complete alternative to `DeleteOperation`.

**Step 2.** You need to ```use \Backpack\Pro\Http\Controllers\Operations\TrashOperation;``` inside your EntityCrudController. Ideally the TrashOperation should be the last one that gets used. For example:

```php
class ProductCrudController extends CrudController
{
    // ... other operations
    use \Backpack\Pro\Http\Controllers\Operations\TrashOperation;
}
```
This will make a Trash button and Trashed filter show up in the list view, and will enable the routes and functionality needed for the operation. If you're getting a "Trait not found" exception, make sure in the namespace you have typed `Backpack\Pro`, not `Backpack\PRO`.


<a name="how-to-configure"></a>
### How to configure

You can easily disable the default trash filter:
```php
public function setupTrashOperation()
{
    CRUD::setOperationSetting('withTrashFilter', false);

    // one of the things the filter does is preventing the update/access to trashed items. 
    // if you disable it and use Update/Show operations, you should manually disable the 
    // access to those operations or they will throw 404:
    CRUD::setAccessCondition(['update', 'show'], function($entry) {
        return ! $entry->trashed();
    });
}
```

Disabling the filter also will make the trashed items show in your List view.  Note that, by default, the `Destroy` button is only shown in _trashed items_. If you want to allow your admins to _permanently delete_ without sending first to trash... you can achieve that by defining in your operation setup:

```php
// in the setupTrashOperation method
CRUD::setOperationSetting('canDestroyNonTrashedItems', true);
```

<a name="how-to-configure-redirect-after-trash-or-destroy"></a>
#### How to configure redirect after Trash or Destroy

When the Trash or Destroy buttons are used **outside a DataTable** (e.g. on the Show page), the entry is no longer accessible after the action. By default, the user will be redirected to the List route. If you want to customize where the user is redirected, you can set:

```php
public function setupTrashOperation()
{
    // redirect to a custom URL after trashing an entry (outside a DataTable)
    CRUD::setOperationSetting('trashButtonRedirect', url('admin/dashboard'));

    // redirect to a custom URL after permanently destroying an entry (outside a DataTable)
    CRUD::setOperationSetting('destroyButtonRedirect', url('admin/dashboard'));
}
```

You can also use a Closure for dynamic redirects:

```php
CRUD::setOperationSetting('trashButtonRedirect', function () {
    return url('admin/dashboard');
});
```

> **Note:** When the Trash, Destroy or Restore buttons are used **inside a DataTable** (on the List page, or inside a related-entries table on the Show page), the table is simply redrawn - no redirect occurs. The redirect settings only affect buttons that are rendered outside of a DataTable context.

<a name="how-to-control-access-to-operation-actions"></a>
### How to control access to operation actions

When `TrashOperation` is used, it automatically controls button visibility based on whether an entry is trashed:

- **Trashed entries** show only: `Restore`, `Destroy` buttons
- **Non-trashed entries** show only: `Trash` button (and all other operation buttons like Update, Show, Delete, etc.)

When the Trashed filter is active, **all non-trash line buttons are removed automatically** — this includes buttons from any operation (Update, UpdateInModal, Show, Delete, Clone, etc.), regardless of the order in which operations are declared in your controller.

Each action inside the TrashOperation (`trash`, `restore` and `destroy`) also checks for access before being performed. Likewise, `BulkTrashOperation` checks for access to `bulkTrash`, `bulkRestore` and `bulkDestroy`.

That means you can revoke access to some operations, depending on user roles or anything else you want:
```php
// if user is not superadmin, don't allow permanently delete items
public function setupTrashOperation()
{
    if(! backpack_user()->hasRole('superadmin')) {
         CRUD::denyAccess('destroy');
    }
}
```

<a name="how-to-override"></a>
### How to Override

In case you need to change how this operation works, just create ```trash()```, ```restore()```,```destroy()``` methods in your EntityCrudController, and they will be used instead of the default ones. For example for `trash()`:

```php
use \Backpack\Pro\Http\Controllers\Operations\TrashOperation { trash as traitTrash; }

public function trash($id)
{
    CRUD::hasAccessOrFail('trash');

    // your custom code here
}
```

You can also override the buttons by creating a file with the same name inside your ```resources/views/vendor/backpack/crud/buttons/```. You can easily publish the buttons there to make changes using:

```zsh
php artisan backpack:button --from=trash

php artisan backpack:button --from=restore

php artisan backpack:button --from=destroy
```

<a name="trash-multiple-items-bulk-trash"></a>
## BulkTrash (Trash Multiple Items) <span class="badge badge-info">PRO</span>

In addition to the button for each entry, <span class="badge badge-info">PRO</span> developers can show checkboxes next to each element, to allow their admin to trash, restore & delete multiple entries at once.


<a name="how-it-works"></a>
### How it Works

- **BulkTrash**:
Using AJAX, a DELETE request is performed towards ```/entity-name/{id}/bulk-trash```, which points to the ```bulkTrash()``` method in your EntityCrudController.

- **BulkRestore**:
Using AJAX, a PUT request is performed towards ```/entity-name/{id}/bulk-restore```, which points to the ```bulkRestore()``` method in your EntityCrudController.

- **BulkDestroy**:
Using AJAX, a DELETE request is performed towards ```/entity-name/{id}/bulk-destroy```, which points to the ```bulkDestroy()``` method in your EntityCrudController.

<a name="enabling"></a>
### How to Use

Assuming your Model already uses Larave's `SoftDeletes`, you just need to ```use \Backpack\Pro\Http\Controllers\Operations\BulkTrashOperation;``` on your EntityCrudController.

<a name="how-to-override"></a>
### How to Override

In case you need to change how this operation works, just create a ```bulkTrash()```, `bulkRestore()` or `bulkDestroy()`  methods in your EntityCrudController:

```php
use \Backpack\Pro\Http\Controllers\Operations\BulkTrashOperation { bulkTrash as traitBulkTrash; }

public function bulkTrash()
{
    CRUD::hasAccessOrFail('bulkTrash');

    // your custom code here
}
```

You can also override the buttons by creating a file with the same name inside your ```resources/views/vendor/backpack/crud/buttons/```. You can easily publish the buttons there to make changes using:

```zsh
php artisan backpack:button --from=bulk_trash

php artisan backpack:button --from=bulk_restore

php artisan backpack:button --from=bulk_destroy
```
