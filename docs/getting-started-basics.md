# 1. Basics

> **Are you already comfortable with Laravel?** To understand this series and make use of Backpack, you'll need to have a decent understanding of the Laravel framework. If you don't, please [watch this excellent intro series on Laracasts](https://laracasts.com/series/laravel-8-from-scratch) and familiarize yourself with Laravel first.

## What is Backpack?
A software package that helps Laravel professionals build administration panels - secure areas where administrators login and create, read, update, and delete application information. It is *not* a CMS, it is more a framework that lets you *build your own* CMS. You can install it in your existing project or in a totally new project.

It's designed to be flexible enough to allow you to **build admin panels for everything from simple presentation websites to CRMs, ERPs, eCommerce, eLearning, etc**. 
## What's a CRUD?

A **CRUD** is what we call a section of your admin panel that lets the admin _Create, Read, Update, or Delete_ entries of a certain entity (or Model). So you can have a CRUD for Products, a CRUD for Articles, a CRUD for Categories, or whatever else you might want to create, read, update, or delete.

## Main Features

### Front-End Design

New Backpack installs come with an HTML theme installed - you choose which theme. All themes use Bootstrap, and have many HTML blocks ready for you to use. When you're building a custom page in your admin panel, it's easy to just copy-paste the HTML from the the theme's demo or from its documentation. And the page will look good, without you having to design anything. Currently, we have three first-party themes:
- [Tabler](https://github.com/Laravel-Backpack/theme-tabler)
- [CoreUI v4](https://github.com/Laravel-Backpack/theme-coreuiv4)
- [CoreUI v2](https://github.com/Laravel-Backpack/theme-tabler) (which still provides IE support)

All themes also install Noty for triggering JS notification bubbles, and SweetAlerts. So you can use these across your admin panel. You can [trigger notification bubbles in PHP](/docs/{{version}}/base-about#triggering-notification-bubbles-in-php) or [trigger notification bubbles in JavaScript](/docs/{{version}}/base-about#triggering-notification-bubbles-in-javascript).

### Authentication

Backpack comes with an authentication system that's separate from Laravel's. This way, you can have different login screens for users and admins, if you need to. If not, you can choose to use only one authentication - either Laravel's or Backpack's.

After installing, log in at `http://yourapp/admin`. Change the prefix and other options in `config/backpack/base.php`.

### CRUDs

Once [Backpack is installed](/docs/{{version}}/installation), generate CRUDs with one of these methods:

**Option A) [PRO]** - [Backpack DevTools](https://backpackforlaravel.com/products/devtools) GUI

**Option B) [FREE]** - CLI with [laracasts/generators](https://github.com/laracasts/Laravel-5-Generators-Extended):

```zsh
# STEP 0. install a 3d party tool to generate migrations
composer require --dev laracasts/generators

# STEP 1. create a migration
php artisan make:migration:schema create_tags_table --model=0 --schema="name:string:unique,slug:string:unique"
php artisan migrate

# STEP 2. create a CRUD for it
php artisan backpack:crud tag #use singular, not plural
```

Both generate:
- a **migration** file
- a **model** (`app\Models\Tag.php`)
- a **request** file for validation (`app\Http\Requests\TagCrudRequest.php`)
- a **controller** (`app\Http\Controllers\Admin\TagCrudController.php`)
- a **route** in `routes/backpack/custom.php`
- a menu item in `resources/views/vendor/backpack/ui/inc/menu_items.blade.php`

No views are generated — the package's default views are used. You can [customize views](/docs/{{version}}/crud-how-to#customize-views-for-each-crud-panel) if needed.

Customize the entity in `TagCrudController`. The migration, model, and request are standard Laravel — just ensure the Model uses `CrudTrait` and has `$fillable` set. Example controller:

```php
<?php namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\TagCrudRequest;

class TagCrudController extends CrudController {

  use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

  public function setup()
  {
      CRUD::setModel("App\Models\Tag");
      CRUD::setRoute("admin/tag");
      CRUD::setEntityNameStrings('tag', 'tags');
  }

  public function setupListOperation()
  {
      CRUD::column('name');
      CRUD::column('slug');
  }

  public function setupCreateOperation()
  {
      CRUD::setValidation(TagCrudRequest::class);

      CRUD::field('name')->type('text');
      CRUD::field('slug')->type('text')->label('URL Segment (slug)');
  }

  public function setupUpdateOperation()
  {
      $this->setupCreateOperation();
  }
}
```

You should notice:
- It uses basic inheritance (```TagCrudController extends CrudController```); so if you want to modify a behaviour (save, update, reorder, etc.), you can do that by overwriting the corresponding method in your ```TagCrudController```
- All operations are enabled by using that operation's trait on the controller
- The ```setup()``` method defines the basics of the CRUD panel
- Each operation is set up inside a `setupXxxOperation()` method

---

Next: [CRUD Operations](/docs/{{version}}/getting-started-crud-operations)
