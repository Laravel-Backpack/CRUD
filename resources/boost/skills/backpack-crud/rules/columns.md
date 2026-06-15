# Backpack CRUD — Columns

Columns define what is shown in the List and Show operations. Defined inside `setupListOperation()` and `setupShowOperation()`.

## API

```php
// Minimal
CRUD::column('name');

// Explicit
CRUD::column([
    'name'  => 'name',
    'label' => 'Tag Name',
    'type'  => 'text',
]);

// Fluent
CRUD::column('price')->type('number')->prefix('$');
```

## Common Column Types

### text
```php
CRUD::column(['name' => 'name', 'label' => 'Name', 'type' => 'text']);
```

### number
```php
CRUD::column(['name' => 'price', 'type' => 'number', 'prefix' => '$', 'decimals' => 2]);
```

### boolean
```php
CRUD::column(['name' => 'is_active', 'type' => 'boolean', 'label' => 'Active']);
```

### date / datetime
```php
CRUD::column(['name' => 'created_at', 'type' => 'datetime']);
CRUD::column(['name' => 'published_at', 'type' => 'date']);
```

### image
```php
CRUD::column(['name' => 'avatar', 'type' => 'image', 'disk' => 'public', 'height' => '50px']);
```

### select (belongsTo — show related attribute)
```php
CRUD::column([
    'name'      => 'category',
    'type'      => 'select',
    'entity'    => 'category',     // the belongsTo method on model
    'attribute' => 'name',         // column to show from related model
    'model'     => \App\Models\Category::class,
]);
```

### select_multiple (belongsToMany)
```php
CRUD::column([
    'name'      => 'tags',
    'type'      => 'select_multiple',
    'entity'    => 'tags',
    'attribute' => 'name',
    'model'     => \App\Models\Tag::class,
]);
```

### relationship (generic)
```php
CRUD::column([
    'name'      => 'author',
    'type'      => 'relationship',
    'attribute' => 'name',
]);
```

## Gotchas
- Relationship columns eager-load automatically only if you define `entity`. Otherwise add `with()` in `setupListOperation()` via `CRUD::addClause('with', 'relation')`.
- The `type` must match a Backpack column blade file name exactly.
- Columns can be hidden from table/modal/export using `visibleInTable`, `visibleInModal`, `visibleInExport`, `visibleInShow` (all boolean).
