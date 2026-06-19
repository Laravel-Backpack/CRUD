# Revise Operation

Provides an audit trail for Eloquent model changes. Lets admins store, view and revert entry changes using [venturecraft/revisionable](https://github.com/VentureCraft/revisionable#implementation).

When enabled, ```Revise``` will show another button in the table view, between _Edit_ and _Delete_. On click, that button opens another page which will allow an admin to see all changes and who made them:

## How to Use

To enable this operation for a CrudController, you need to:

**Step 1.** Install [the package](https://github.com/laravel-backpack/revise-operation) that provides this operation. This will also install venturecraft/revisionable if it's not already installed in your project.

```bash
composer require backpack/revise-operation
```

**Step 2.** Create the revisions table:

```bash
cp vendor/venturecraft/revisionable/src/migrations/2013_04_09_062329_create_revisions_table.php database/migrations/ && php artisan migrate
```

**Step 3.** Use [venturecraft/revisionable](https://github.com/VentureCraft/revisionable#implementation)'s trait on your model, and an ```identifiableName()``` method that returns an attribute on the model that the admin can use to distinguish between entries (ex: name, title, etc). If you are using another bootable trait be sure to override the boot method in your model.

```php
namespace App\Models;

class Article extends Eloquent {
    use \Backpack\CRUD\app\Models\Traits\CrudTrait, \Venturecraft\Revisionable\RevisionableTrait;
    
    public function identifiableName()
    {
        return $this->name;
    }

    // If you are using another bootable trait
    // be sure to override the boot method in your model
    public static function boot()
    {
        parent::boot();
    }
}
```

**Step 4.** In your CrudController, use the operation trait:

```php
<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class ArticleCrudController extends CrudController
{
    use \Backpack\ReviseOperation\ReviseOperation;
```

For complex usage, head over to [VentureCraft/revisionable](https://github.com/VentureCraft/revisionable) to see the full documentation and configuration options.
