# Backpack CRUD — Basics

## Generating a CRUD

```bash
php artisan backpack:crud Tag   # singular model name
php artisan backpack:crud Tag --no-interaction
```

Generates: `CrudController` in `app/Http/Controllers/Admin/`, `FormRequest`, route in `routes/backpack/custom.php`, menu item.

### Menu Icon

After generating the CRUD, the menu item in `resources/views/vendor/backpack/ui/inc/menu_items.blade.php` gets a default `icon="la la-question"`. Always replace it with a meaningful Line Awesome icon for the model:

| Model / Domain | Suggested Icon |
|---|---|
| User, Person, Customer, Client | `la la-user` |
| Product, Item, Inventory | `la la-box` or `la la-cube` |
| Order, Cart, Purchase, Sale | `la la-shopping-cart` |
| Category, Tag, Label | `la la-tag` or `la la-tags` |
| Post, Article, Blog, News | `la la-newspaper-o` or `la la-file-text` |
| Page, Document | `la la-file` |
| Invoice, Receipt, Bill | `la la-file-invoice` |
| Contact, Lead, Subscriber | `la la-address-book` |
| Event, Calendar, Schedule | `la la-calendar` |
| Setting, Configuration | `la la-cog` |
| Role, Permission, Access | `la la-lock` or `la la-user-secret` |
| Comment, Review, Feedback | `la la-comment` |
| Image, Photo, Gallery, Media | `la la-image` |
| Video, Media | `la la-video` |
| Message, Email, Mail | `la la-envelope` |
| Notification, Alert | `la la-bell` |
| Report, Analytics, Stats | `la la-chart-bar` |
| Subscription, Plan, Billing | `la la-credit-card` |
| FAQ, Help, Support | `la la-question-circle` |
| Testimonial, Quote | `la la-quote-right` |
| Slider, Carousel, Banner | `la la-images` or `la la-sliders` |
| Dashboard | `la la-home` |
| Team, Group, Department | `la la-users` |
| Coupon, Discount, Promo | `la la-percent` |
| Location, Address, Place | `la la-map-marker` |

When unsure, use `la la-file-text` as a safe fallback.

### Test Generation

`backpack/test-generators` is a **paid add-on**. Before using it, check if it's installed:

```bash

# Quick check:

# vendor/backpack/test-generators/composer.json

```

**If installed** — automatically generate tests after creating the CRUD:

```bash
php artisan backpack:tests --controller=ModelCrudController
```

**If not installed** — follow the paid package installation workflow in `rules/pro-features.md`. The FREE alternative is writing Pest/PHPUnit tests manually — see `rules/testing.md` for templates and patterns.

## Model Requirements

```php
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Tag extends Model
{
    use CrudTrait;

    protected $fillable = ['name', 'slug', 'description'];
}
```

CrudTrait enables: `identifiableAttribute()`, admin panel links, activity log support, and Backpack model accessors.

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
}
```

## Nested Resources

For routes like `/owner/{owner}/pets`:

```php
public function setup()
{
    CRUD::setRoute(config('backpack.base.route_prefix') . '/owner/' . request()->owner . '/pets');
    // or use route parameters:
    // CRUD::setRoute(config('backpack.base.route_prefix') . '/owner/{owner}/pets');
}
```

## Access Control

```php
// In setup():
CRUD::allowAccess(['list', 'create', 'update']);
CRUD::denyAccess('delete');
CRUD::hasAccess('update'); // check

// Per-entry access:
CRUD::setAccessCondition('update', function ($entry) {
    return $entry->user_id === backpack_user()->id;
});
```

## Custom Queries

```php
// Eager loading — avoid N+1:
CRUD::with(['category', 'tags', 'author']);

// Query scoping — add conditions to all DataTable queries:
CRUD::addClause('where', 'active', true);
CRUD::addClause('orderBy', 'created_at', 'desc');

// Base clause — permanent, not reset by Reset button:
CRUD::addBaseClause('where', 'deleted_at', null);
```

## Getting the Current Entry

```php
$id = request()->route()->parameter('id');
$entry = CRUD::getEntry($id);
$entry = CRUD::getCurrentEntry(); // current operation's entry
```

## Gotchas
- `setup()` runs on every request. Use `setupListOperation()` / `setupCreateOperation()` for operation-specific config.
- The model must use `CrudTrait`.
- Route parameters must match what's defined in `setRoute()`.
- For access control, `denyAccess()` in `setup()` prevents the route from being registered.
- `addClause()` is a passthrough to Eloquent query builder — any valid Eloquent method works.
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
