# Backpack CRUD — Fetch Operation [PRO]

The Fetch Operation is the **recommended way to build AJAX data endpoints** directly in your CrudController. It powers `select2_from_ajax`, `select2_from_ajax_multiple`, and `relationship` fields by responding with paginated, searchable JSON — no separate API controllers needed.

Requires `backpack/pro`.

---

## Basic Usage

Add the trait and define a `fetchEntity()` method on the **target** model's CrudController:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

protected function fetchTag()
{
    return $this->fetch(\App\Models\Tag::class);
}
```

**Naming convention**: The method name must be `fetch` + `EntityName` (studly case). For entity `tag` → `fetchTag()`, for entity `productCategory` → `fetchProductCategory()`.

The Fetch operation automatically creates a `POST /{entity}/fetch/{target}` route. For a `ProductCrudController`, the route is `POST /product/fetch/tag`.

---

## Full Configuration

Pass an array instead of a class name for full control:

```php
protected function fetchTag()
{
    return $this->fetch([
        'model' => \App\Models\Tag::class,           // required
        'searchable_attributes' => ['name', 'description'], // or [] to disable auto-detection
        'paginate' => 10,                             // results per page (default: 10)
        'searchOperator' => 'LIKE',                   // or 'ILIKE' (PostgreSQL) — default: 'LIKE'
        'query' => function ($model) {                 // scope the results
            return $model->active();
        },
        'append_attributes' => ['computed_field'],    // add accessor attributes without model $appends
        'text_attr' => 'full_name',                   // attribute shown in select2 dropdown (for filters)
        'key' => 'uuid',                              // model key used for option value (default: 'id')
    ]);
}
```

---

## Field Integration

### select2_from_ajax (PRO)

```php
CRUD::field([
    'name'        => 'tag_id',
    'type'        => 'select2_from_ajax',
    'label'       => 'Tag',
    'entity'      => 'tag',      // must match fetchTag() method name
    'attribute'   => 'name',     // shown in the select2 dropdown
    'data_source' => backpack_url('product/fetch/tag'), // optional — auto-detected if naming convention matches
]);
```

### relationship (PRO)

```php
CRUD::field([
    'name'        => 'tags',        // belongsToMany relationship
    'type'        => 'relationship',
    'label'       => 'Tags',
    'entity'      => 'tags',
    'attribute'   => 'name',
    'ajax'        => true,          // enables AJAX loading
    'data_source' => backpack_url('product/fetch/tag'),
]);
```

### select2_from_ajax_multiple (PRO)

```php
CRUD::field([
    'name'        => 'tags',
    'type'        => 'select2_from_ajax_multiple',
    'label'       => 'Tags',
    'entity'      => 'tags',
    'attribute'   => 'name',
    'data_source' => backpack_url('product/fetch/tag'),
]);
```

---

## Using with select2_ajax Filters

FetchOperation can serve as the data source for `select2_ajax` filters. Two key differences from fields:

1. The filter uses `GET` by default — you **must** change it to `POST`.
2. Specify `select_attribute` to tell the filter which column to display.

```php
$this->crud->addFilter([
    'name'       => 'category_id',
    'type'       => 'select2_ajax',
    'label'      => 'Category',
    'placeholder' => 'Pick a category',
    'method'     => 'POST',               // REQUIRED for FetchOperation
    'select_attribute' => 'name',         // column displayed to user
    // 'select_key' => 'id',              // only if model uses non-standard key
],
backpack_url('product/fetch/category'),
function ($value) {
    $this->crud->addClause('where', 'category_id', $value);
});
```

---

## Security Guarantee (Save-Time Guard)

The `query` closure you define in `fetchXxx()` serves dual purpose — it filters AJAX search results **AND** validates IDs at save time. This prevents IDOR attacks where a malicious user submits IDs they were never shown.

```php
protected function fetchTag()
{
    return $this->fetch([
        'model' => \App\Models\Tag::class,
        'query' => function ($model) {
            return $model->where('active', true); // ONLY active tags are valid at save
        },
    ]);
}
```

**Behavior**:
- `HasMany`, `MorphMany`, `BelongsToMany`, `MorphToMany`: out-of-scope IDs are silently dropped
- `BelongsTo`: save is rejected with a 422 validation error on the field

If you don't define a `query` closure, all IDs are accepted.

**When the guard applies automatically**: For any ajax relationship field whose `entity` matches the `fetchXxx()` naming convention. If you set `data_source` manually and the entity no longer matches the method name, point the field to the right guard explicitly:

```php
CRUD::field([
    'type' => 'select2_from_ajax',
    'name' => 'category_id',
    'data_source' => backpack_url('article/fetch/product-category'),
    'relation_options_query_source' => 'fetchProductCategory', // reuse this method's query for guard
]);
```

For fully custom endpoints (no FetchOperation), declare the allowed set directly:

```php
CRUD::field([
    'type' => 'select2_from_ajax',
    'name' => 'category_id',
    'data_source' => url('api/custom-endpoint'),
    'relation_options_query' => function ($model) {
        return $model->where('active', true); // explicit guard for custom endpoint
    },
]);
```

---

## Customization

### Change search operator

Per fetch method:
```php
return $this->fetch([
    'model' => Tag::class,
    'searchOperator' => 'ILIKE',
]);
```

Per controller (all fetches in this CrudController):
```php
public function setupFetchOperationOperation()
{
    CRUD::setOperationSetting('searchOperator', 'ILIKE');
}
```

Globally — create `config/backpack/operations/fetch.php`:
```php
<?php
return [
    'searchOperator' => 'ILIKE',
];
```

### Prevent auto-detected searchable attributes

If you don't specify `searchable_attributes`, Backpack infers them from the model's database columns. To prevent this (e.g., when using a custom `query` with raw SQL searching):

```php
return $this->fetch([
    'model' => User::class,
    'searchable_attributes' => [],  // disables auto-detection
    'query' => function ($model) {
        $search = request()->input('q');
        if ($search) {
            return $model->whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ["%{$search}%"]);
        }
        return $model;
    },
]);
```

### Append attributes without model $appends

Use `append_attributes` to add computed attributes only during fetch (not globally on the model):

```php
return $this->fetch([
    'model' => User::class,
    'append_attributes' => ['full_name'],
]);

// In User model:
public function fullName(): Attribute
{
    return Attribute::get(fn ($value, $attrs) => "{$attrs['first_name']} {$attrs['last_name']}");
}
```

### Custom behavior for a single fetch method

Instead of calling `$this->fetch()`, copy the FetchOperation logic and return your own response. The trait still registers the route — you just provide the response.

### Custom behavior for all fetch methods in a controller

Override the `fetch()` method in your CrudController:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

public function fetch($arg)
{
    // your custom logic — all $this->fetch() calls now use this
}
```

---

## Route Format

| CrudController | Method | Generated Route |
|----------------|--------|-----------------|
| `ProductCrudController` | `fetchTag()` | `POST /admin/product/fetch/tag` |
| `ProductCrudController` | `fetchProductCategory()` | `POST /admin/product/fetch/product-category` |
| `ArticleCrudController` | `fetchTag()` | `POST /admin/article/fetch/tag` |

The URL always follows the pattern: `{admin_prefix}/{entity}/fetch/{target_name}`.

---

## Best Practices

- **Always define a `query` closure** to scope results and activate the security guard — even if you just return `$model`.
- **Name fetch methods consistently**: `fetchTag()` for entity `tag`, `fetchCategory()` for entity `category`.
- **Use `searchable_attributes`** explicitly rather than relying on auto-detection — it's more predictable.
- **For complex search logic**, set `searchable_attributes` to `[]` and handle searching inside the `query` closure with raw SQL.
- **One CrudController per fetched model**: the fetch method lives on the controller of the model being fetched, not the controller where the field is used.
