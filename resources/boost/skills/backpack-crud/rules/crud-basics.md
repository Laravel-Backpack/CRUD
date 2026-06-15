# Backpack CRUD — Basics

## Generating a CRUD

```bash
php artisan backpack:crud tag   # singular model name
```

This generates:
- `app/Http/Controllers/Admin/TagCrudController.php`
- `app/Http/Requests/TagCrudRequest.php`
- A route entry in `routes/backpack/custom.php`
- A menu item in `resources/views/vendor/backpack/ui/inc/menu_items.blade.php`

The model must already exist (or be created separately). The model must `use \Backpack\CRUD\app\Models\Traits\CrudTrait`.

## Minimal CrudController

```php
<?php

namespace App\Http\Controllers\Admin;

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
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(\App\Http\Requests\TagCrudRequest::class);
        CRUD::field('name');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
```

## Rules
- Always use `CRUD::` facade calls, not `$this->crud->`.
- `setup()` is where you set model, route, and entity name strings.
- Each `setup*Operation()` method configures that operation in isolation.
- `setupUpdateOperation()` usually delegates to `setupCreateOperation()`.
- No operations are enabled by default — you must use the operation traits.
