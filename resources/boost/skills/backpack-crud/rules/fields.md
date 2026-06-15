# Backpack CRUD — Fields

Fields are used in Create and Update forms. Defined inside `setupCreateOperation()` and `setupUpdateOperation()`.

## API

```php
// Minimal — Backpack infers label and type from the column name
CRUD::field('name');

// Explicit — recommended for clarity
CRUD::field([
    'name'  => 'name',
    'label' => 'Tag Name',
    'type'  => 'text',
]);

// Fluent style
CRUD::field('price')->type('number')->label('Price (USD)')->prefix('$');

// Remove a field
CRUD::field('name')->remove();
```

## Common Field Types

### text
```php
CRUD::field(['name' => 'title', 'label' => 'Title', 'type' => 'text']);
```

### number
```php
CRUD::field(['name' => 'price', 'type' => 'number', 'prefix' => '$', 'decimals' => 2]);
```

### textarea
```php
CRUD::field(['name' => 'description', 'type' => 'textarea']);
```

### select (belongsTo)
```php
// The 'name' must match the belongsTo method on the model
CRUD::field([
    'name'  => 'category',        // matches Category belongsTo method
    'type'  => 'select',
    'entity' => 'category',       // the belongsTo method
    'attribute' => 'name',        // column to show from related model
    'model' => \App\Models\Category::class,
]);
```

### select2 (belongsTo — searchable)
```php
CRUD::field([
    'name'    => 'category',
    'type'    => 'select2',
    'entity'  => 'category',
    'attribute' => 'name',
    'model'   => \App\Models\Category::class,
]);
```

### select2_multiple (belongsToMany)
```php
CRUD::field([
    'name'    => 'tags',          // matches belongsToMany method on model
    'type'    => 'select2_multiple',
    'entity'  => 'tags',
    'attribute' => 'name',
    'model'   => \App\Models\Tag::class,
    'pivot'   => true,            // required for belongsToMany
]);
```

### select_from_array
```php
CRUD::field([
    'name'    => 'status',
    'type'    => 'select_from_array',
    'options' => ['draft' => 'Draft', 'published' => 'Published'],
    'allows_null' => false,
]);
```

### date / datetime
```php
CRUD::field(['name' => 'published_at', 'type' => 'date']);
CRUD::field(['name' => 'scheduled_at', 'type' => 'datetime']);
```

### checkbox
```php
CRUD::field(['name' => 'is_active', 'type' => 'checkbox', 'label' => 'Active?']);
```

### upload (single file)
```php
CRUD::field([
    'name'      => 'avatar',
    'type'      => 'upload',
    'upload'    => true,
    'disk'      => 'public',
]);
// The model must use the UploadedFile mutator or the HasUploadFields concern.
```

### upload_multiple
```php
CRUD::field([
    'name'      => 'photos',
    'type'      => 'upload_multiple',
    'upload'    => true,
    'disk'      => 'public',
]);
```

## Gotchas
- If `name` matches a relationship method on the model, Backpack treats it as a relationship field automatically. Use `'entity' => false` to disable this.
- For `select2_multiple` / `belongsToMany`, always set `'pivot' => true`.
- Upload fields require the model to have the uploader configured. Check `crud-uploaders.md` in the docs.
- The `type` attribute must be the exact string matching a Backpack field blade file.
