# Backpack CRUD — Columns

Columns define table cells in List and Show operations. Defined inside `setupListOperation()` and `setupShowOperation()`.

**⚠️ Columns marked (PRO) require `backpack/pro`. Always check if `backpack/pro` is installed before generating code that uses PRO column types. See `rules/pro-features.md` for FREE alternatives.**

## API

```php
// Minimal
CRUD::column('name');

// Explicit array
CRUD::column(['name' => 'name', 'label' => 'Tag Name', 'type' => 'text']);

// Fluent
CRUD::column('price')->type('number')->prefix('$');

// Bulk
CRUD::addColumns([$def1, $def2]);
CRUD::setColumns(['name', 'description']);

// Ordering
CRUD::column('price')->before('name');
CRUD::column('price')->after('name');
CRUD::column('name')->makeFirst();
CRUD::column('name')->makeLast();

// Remove
CRUD::column('name')->remove();
CRUD::removeColumns(['name', 'email']);
CRUD::removeAllColumns();
```

## Column Types

### text / number / email / phone / url / password
```php
CRUD::column(['name' => 'name', 'type' => 'text']);
CRUD::column(['name' => 'price', 'type' => 'number', 'prefix' => '$', 'decimals' => 2]);
CRUD::column(['name' => 'email', 'type' => 'email']);
CRUD::column(['name' => 'mobile', 'type' => 'phone']);
CRUD::column(['name' => 'website', 'type' => 'url']);
CRUD::column(['name' => 'password', 'type' => 'password']);
```

### boolean / checkbox / radio / switch
```php
CRUD::column(['name' => 'is_active', 'type' => 'boolean']);
CRUD::column(['name' => 'agreed', 'type' => 'checkbox']);
CRUD::column(['name' => 'status', 'type' => 'radio', 'options' => ['draft' => 'Draft']]);
CRUD::column(['name' => 'is_published', 'type' => 'switch', 'color' => 'success']);
```

### date / datetime / date_picker (PRO) / datetime_picker (PRO)
```php
CRUD::column(['name' => 'created_at', 'type' => 'datetime']);
CRUD::column(['name' => 'published_at', 'type' => 'date']);
CRUD::column(['name' => 'scheduled_at', 'type' => 'datetime_picker']); // PRO
```

### image / upload_multiple / dropzone (PRO) / base64_image (PRO)
```php
// Upload/file columns
CRUD::column(['name' => 'avatar', 'type' => 'image', 'disk' => 'public', 'height' => '50px']);
CRUD::column(['name' => 'photos', 'type' => 'upload_multiple']);
CRUD::column(['name' => 'photos', 'type' => 'dropzone']);
CRUD::column(['name' => 'image_data', 'type' => 'base64_image']);
```

### markdown (PRO)
```php
// Renders Markdown content as formatted HTML
CRUD::column(['name' => 'description', 'type' => 'markdown']);
```

### select (belongsTo — show related attribute)
    'entity'    => 'category',
    'attribute' => 'name',
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

### relationship / relationship_count
```php
CRUD::column(['name' => 'author',  'type' => 'relationship', 'attribute' => 'name']);
CRUD::column(['name' => 'comments', 'type' => 'relationship_count',
    'label' => '# Comments', 'suffix' => ' comments']);
```

### model_function / model_function_attribute
```php
// Calls $entry->full_name()
CRUD::column(['name' => 'full_name', 'type' => 'model_function', 'function_name' => 'full_name']);

// Calls $entry->category()->getNameAttribute()
CRUD::column(['name' => 'category_details', 'type' => 'model_function_attribute',
    'function_name' => 'getDetailsAttribute']);
```

### closure
```php
CRUD::column([
    'name'     => 'status',
    'type'     => 'closure',
    'function' => fn ($entry) => '<span class="badge">'.$entry->status.'</span>',
    'escaped'  => false,  // allow HTML output
]);
```

### custom_html / view
```php
CRUD::column(['name' => 'actions', 'type' => 'custom_html', 'value' => '<button>Click</button>']);
CRUD::column(['name' => 'summary', 'type' => 'view', 'view' => 'admin.columns.summary']);
```

### row_number (row index) / check (bulk action checkbox)
```php
CRUD::column(['name' => 'row_number', 'type' => 'row_number', 'label' => '#']);
CRUD::column(['name' => 'bulk_actions', 'type' => 'check', 'label' => '']);
```

### multidimensional_array / json
```php
CRUD::column(['name' => 'metadata', 'type' => 'multidimensional_array',
    'visible_key' => 'name', 'visible_value' => 'value']);
CRUD::column(['name' => 'config', 'type' => 'json']);
```

### array / array_count (PRO)
```php
CRUD::column(['name' => 'tags', 'type' => 'array']);       // JSON array → list
CRUD::column(['name' => 'tags', 'type' => 'array_count']); // JSON array → count
```

### PRO select2 variants
```php
CRUD::column(['name' => 'tag', 'type' => 'select2', 'entity' => 'tag', 'model' => Tag::class]);
CRUD::column(['name' => 'tags', 'type' => 'select2_multiple', 'entity' => 'tags', 'model' => Tag::class]);
CRUD::column(['name' => 'category', 'type' => 'select2_from_ajax',
    'entity' => 'category', 'attribute' => 'name', 'data_source' => url('api/categories')]);
CRUD::column(['name' => 'category', 'type' => 'select2_grouped',
    'entity' => 'category', 'attribute' => 'name', 'group_by' => 'parent_id']);
CRUD::column(['name' => 'category', 'type' => 'select2_nested',
    'entity' => 'category', 'attribute' => 'name', 'model' => Category::class]);
```

### PRO: browse / browse_multiple / repeatable / table / video
```php
CRUD::column(['name' => 'file', 'type' => 'browse']);           // open file in new tab
CRUD::column(['name' => 'files', 'type' => 'browse_multiple']); // multiple files
CRUD::column(['name' => 'items', 'type' => 'repeatable']);      // repeatable data display
CRUD::column(['name' => 'data', 'type' => 'table']);            // table data display
CRUD::column(['name' => 'trailer', 'type' => 'video']);         // video embed
```

## Visibility Control

```php
CRUD::column(['name' => 'secret', 'visibleInTable' => false, 'visibleInExport' => true]);
CRUD::column(['name' => 'internal', 'visibleInTable' => true, 'visibleInShow' => false]);
CRUD::column(['name' => 'id', 'visibleInModal' => false]);

// Export-only column (never shown in table, always exported)
CRUD::column(['name' => 'email', 'exportOnlyColumn' => true]);
```

## Search & Order Logic

```php
// Search
CRUD::column(['name' => 'email', 'type' => 'text', 'searchLogic' => 'text']);
CRUD::column(['name' => 'full_name', 'type' => 'text',
    'searchLogic' => function ($query, $column, $searchTerm) {
        $query->orWhere('first_name', 'like', '%'.$searchTerm.'%')
              ->orWhere('last_name', 'like', '%'.$searchTerm.'%');
    }]);

// Order
CRUD::column(['name' => 'full_name', 'type' => 'text',
    'orderLogic' => function ($query, $column, $direction) {
        $query->orderBy('first_name', $direction)
              ->orderBy('last_name', $direction);
    }]);

// Disable sorting
CRUD::column(['name' => 'name', 'orderable' => false]);
```

## Escape & Limit

```php
CRUD::column(['name' => 'description', 'escaped' => false]);   // allow HTML
CRUD::column(['name' => 'description', 'limit' => 50]);        // truncate to 50 chars
```

## Gotchas
- Relationship columns auto-eager-load when `entity` is defined. Otherwise add `CRUD::with()`.
- `type` must match a Backpack column blade file name exactly.
- Editable columns (`editable_text`, `editable_select`, etc.) need explicit `searchLogic` — see EditableColumns docs.
- `model_function` runs `$entry->{function_name}()`. `model_function_attribute` runs `$entry->{function_name}()` and expects it to return a value — then calls `limit()` and `escaped` on that.
- The `check` column type is for bulk action checkboxes in List operation.
