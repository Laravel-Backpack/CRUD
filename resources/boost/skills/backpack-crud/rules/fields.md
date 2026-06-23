# Backpack CRUD — Fields

Fields define form inputs for Create and Update operations. Defined inside `setupCreateOperation()` and `setupUpdateOperation()`.

**⚠️ Fields marked (PRO) require `backpack/pro`. Always check if `backpack/pro` is installed before generating code that uses PRO field types. See `rules/pro-features.md` for FREE alternatives.**

## API

```php
// Minimal — Backpack infers label and type from column name/type
CRUD::field('name');

// Explicit array — recommended
CRUD::field(['name' => 'name', 'label' => 'Tag Name', 'type' => 'text']);

// Fluent
CRUD::field('price')->type('number')->label('Price (USD)')->prefix('$');

// Remove
CRUD::field('name')->remove();
CRUD::removeAllFields();

// Bulk
CRUD::addFields([$definitionArray1, $definitionArray2]);

// Ordering
CRUD::field('price')->before('name');
CRUD::field('price')->after('name');
CRUD::field('price')->makeFirst();
```

## Field Types

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
CRUD::field([
    'name'  => 'category',        // matches belongsTo method name
    'type'  => 'select',
    'entity' => 'category',
    'attribute' => 'name',
    'model' => \App\Models\Category::class,
]);
```

### select2 (PRO — belongsTo, searchable)
```php
CRUD::field([
    'name'    => 'category',
    'type'    => 'select2',
    'entity'  => 'category',
    'attribute' => 'name',
    'model'   => \App\Models\Category::class,
]);
```

### select2_multiple (PRO — belongsToMany)
```php
CRUD::field([
    'name'    => 'tags',
    'type'    => 'select2_multiple',
    'entity'  => 'tags',
    'attribute' => 'name',
    'model'   => \App\Models\Tag::class,
    'pivot'   => true,
]);
```

### select_from_array / select_grouped / select_multiple
```php
CRUD::field([
    'name'    => 'status',
    'type'    => 'select_from_array',
    'options' => ['draft' => 'Draft', 'published' => 'Published'],
    'allows_null' => false,
]);

CRUD::field([
    'name'    => 'category',
    'type'    => 'select_grouped',
    'entity'  => 'category',
    'attribute' => 'name',
    'model'   => Category::class,
    'group_by' => 'parent_id',
]);

CRUD::field([
    'name'    => 'tags',
    'type'    => 'select_multiple',
    'entity'  => 'tags',
    'attribute' => 'name',
    'model'   => Tag::class,
    'pivot'   => true,
]);
```

### select2_from_array / select2_grouped / select2_nested (PRO)
```

### select2_from_ajax / select2_from_ajax_multiple (PRO)
```php
CRUD::field([
    'name'          => 'category',
    'type'          => 'select2_from_ajax',
    'entity'        => 'category',
    'attribute'     => 'name',
    'model'         => \App\Models\Category::class,
    'data_source'   => url('api/categories'),
    'placeholder'   => 'Select a category',
    'minimum_input_length' => 2,
]);
```

### select2_from_ajax_multiple / select2_grouped / select2_nested / select2_json_from_api (PRO)
```php
CRUD::field([
    'name'        => 'tags', 'type' => 'select2_from_ajax_multiple',
    'entity' => 'tags', 'attribute' => 'name', 'model' => Tag::class,
    'data_source' => url('api/tags'), 'pivot' => true,
]);

CRUD::field([
    'name' => 'category', 'type' => 'select2_grouped',
    'entity' => 'category', 'attribute' => 'name',
    'model' => Category::class, 'group_by' => 'parent_id',
]);

CRUD::field([
    'name' => 'category', 'type' => 'select2_nested',
    'entity' => 'category', 'attribute' => 'name',
    'model' => Category::class,
]);

CRUD::field([
    'name' => 'remote_data', 'type' => 'select2_json_from_api',
    'attribute' => 'name',
    'data_source' => url('api/external'),
]);
```

### select_and_order (PRO — sortable belongsToMany)
```php
CRUD::field([
    'name' => 'tags', 'type' => 'select_and_order',
    'entity' => 'tags', 'attribute' => 'name',
    'model' => Tag::class, 'pivot' => true,
]);
```

### radio / checklist / checklist_dependency
```php
CRUD::field(['name' => 'status', 'type' => 'radio',
    'options' => ['draft' => 'Draft', 'published' => 'Published']]);

CRUD::field(['name' => 'roles', 'type' => 'checklist',
    'entity' => 'roles', 'attribute' => 'name',
    'model' => Role::class, 'pivot' => true]);

CRUD::field(['name' => 'subcategories', 'type' => 'checklist_dependency',
    'field_unique_name' => 'subcategories_checklist',
    'primary_dependency' => ['name' => 'category_id'],
    'secondary_dependency' => ['name' => 'subcategory_id'],
    'entity_secondary' => 'subcategories', 'attribute_secondary' => 'name',
    'model_secondary' => Subcategory::class, 'pivot_secondary' => true]);
```

### date / datetime / time / month / week
```php
CRUD::field(['name' => 'published_at', 'type' => 'date']);
CRUD::field(['name' => 'scheduled_at', 'type' => 'datetime']);
CRUD::field(['name' => 'start_time', 'type' => 'time']);
CRUD::field(['name' => 'birthday_month', 'type' => 'month']);
CRUD::field(['name' => 'start_week', 'type' => 'week']);
```

### date_picker / datetime_picker / date_range (PRO)
```php
CRUD::field(['name' => 'published_at', 'type' => 'date_picker']);
CRUD::field(['name' => 'scheduled_at', 'type' => 'datetime_picker']);
CRUD::field(['name' => 'event_dates', 'type' => 'date_range',
    'start_name' => 'start_date', 'end_name' => 'end_date']);
```

### checkbox / switch
```php
CRUD::field(['name' => 'is_active', 'type' => 'checkbox', 'label' => 'Active?']);
CRUD::field(['name' => 'is_published', 'type' => 'switch', 'color' => 'success']);
```

### slug / relationship (PRO)
```php
CRUD::field(['name' => 'slug', 'type' => 'slug', 'target' => 'name']);
CRUD::field(['name' => 'author', 'type' => 'relationship']);
// relationship auto-detects entity, attribute, model from the model's method
```

### upload / upload_multiple
```php
CRUD::field(['name' => 'avatar', 'type' => 'upload', 'upload' => true, 'disk' => 'public']);
CRUD::field(['name' => 'photos', 'type' => 'upload_multiple', 'upload' => true, 'disk' => 'public']);
```

### image / dropzone (PRO)
```php
CRUD::field(['name' => 'avatar', 'type' => 'image', 'crop' => true, 'aspect_ratio' => 1]);
CRUD::field(['name' => 'photos', 'type' => 'dropzone', 'disk' => 'public']);
```

### repeatable / table (PRO)
```php
CRUD::field(['name' => 'items', 'type' => 'repeatable',
    'fields' => [
        ['name' => 'product', 'type' => 'text'],
        ['name' => 'quantity', 'type' => 'number'],
        ['name' => 'price', 'type' => 'number', 'prefix' => '$'],
    ]]);

CRUD::field(['name' => 'prices', 'type' => 'table',
    'columns' => ['currency' => 'Currency', 'amount' => 'Amount']]);
```

### view / custom_html / hidden / enum
```php
CRUD::field(['name' => 'intro', 'type' => 'view', 'view' => 'admin.fields.intro']);
CRUD::field(['name' => 'separator', 'type' => 'custom_html', 'value' => '<hr>']);
CRUD::field(['name' => 'token', 'type' => 'hidden']);
CRUD::field(['name' => 'status', 'type' => 'enum']);
```

### email / password / url / color / range
```php
CRUD::field(['name' => 'email', 'type' => 'email']);
CRUD::field(['name' => 'password', 'type' => 'password']);
CRUD::field(['name' => 'website', 'type' => 'url']);
CRUD::field(['name' => 'color_hex', 'type' => 'color']);
CRUD::field(['name' => 'stars', 'type' => 'range', 'attributes' => ['min' => 0, 'max' => 5]]);
```

### summernote (FREE) / easymde (PRO)
```php
CRUD::field(['name' => 'content', 'type' => 'summernote']);
CRUD::field(['name' => 'content', 'type' => 'easymde']);
```

### ckeditor / tinymce (third-party add-ons)
```php
// Requires separate add-on packages
CRUD::field(['name' => 'content', 'type' => 'ckeditor']);
CRUD::field(['name' => 'content', 'type' => 'tinymce']);
```

### icon_picker / video / code_mirror / base64_image (PRO)
```php
CRUD::field(['name' => 'icon', 'type' => 'icon_picker']);
CRUD::field(['name' => 'trailer', 'type' => 'video']);
CRUD::field(['name' => 'code', 'type' => 'code_mirror']);
CRUD::field(['name' => 'image_data', 'type' => 'base64_image']);
```

### address_google / phone / google_map (PRO)
```php
CRUD::field(['name' => 'address', 'type' => 'address_google', 'store_as_json' => true]);
CRUD::field(['name' => 'mobile', 'type' => 'phone']);
CRUD::field(['name' => 'location', 'type' => 'google_map']);
```

## Field Events

```php
CRUD::field('name')->on('saving', function ($entry) {
    $entry->slug = Str::slug($entry->name);
});
```

## Organizing Fields

### Tabs
```php
CRUD::field('name')->tab('General');
CRUD::field('description')->tab('General');
CRUD::field('price')->tab('Pricing');
```

### Wrappers / Hints
```php
CRUD::field('name')->wrapper(['class' => 'col-md-6']);
CRUD::field('email')->wrapper(['class' => 'col-md-6']);
CRUD::field('email')->hint('We will never share your email.');
CRUD::field('price')->prefix('$')->suffix('.00');
CRUD::field('name')->default('Untitled');
```

### Fake Fields (data stored in JSON column)
```php
CRUD::field(['name' => 'meta_title', 'fake' => true, 'store_in' => 'extras']);
CRUD::field(['name' => 'banner', 'fake' => true, 'store_in' => 'extras', 'type' => 'upload']);
```

### Field Dependencies (JavaScript)
```php
// Controller:
Widget::add()->type('script')->content('assets/js/admin/forms/product.js');

// JS file:
crud.field('category_id').onChange(function(field) {
    crud.field('subcategory_id').show(field.value == 1);
}).change();
```

## Gotchas
- If `name` matches a relationship method, Backpack auto-detects it as a relationship field. Use `'entity' => false` to disable.
- `select2_multiple` with belongsToMany requires `'pivot' => true`.
- `relationship` type auto-detects everything — simpler but less configurable.
- Upload fields need `WithFiles` concern on the controller (v8+) or `HasUploadFields`.
- Date filter value from JavaScript is a JSON string — always `json_decode()`.
- For `select2_multiple` / `belongsToMany`, always set `'pivot' => true`.
- Upload fields require the model to have the uploader configured. Check `crud-uploaders.md` in the docs.
- The `type` attribute must be the exact string matching a Backpack field blade file.
