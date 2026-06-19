### relationship [PRO]

A `select2` field for one/many Eloquent relationships. Point `name` to the relationship method on the model (e.g. `category`, not `category_id`):

```php
CRUD::field([
    'name'        => 'category', // the relationship method on your model
    'type'        => 'relationship',

    // optional:
    // 'label'       => "Category",
    // 'attribute'   => "title", // model attribute shown to user
    // 'placeholder' => "Select a category",
]);
```

Supported relationships:
- ✅ `hasOne` (1-1) — shows subform when `subfields` defined
- ✅ `belongsTo` (n-1) — single select2
- ✅ `hasMany` (1-n) — multiple select2 OR subform when `subfields` defined
- ✅ `belongsToMany` (n-n) — multiple select2 OR subform for pivot extras
- ✅ `morphOne` (1-1) — subform when `subfields` defined
- ✅ `morphMany` (1-n) — multiple select2 OR subform when `subfields` defined
- ✅ `morphToMany` (n-n) — multiple select2 OR subform for pivot extras
- ✅ `morphTo` (n-1) — shows `_type` and `_id` selects

Not supported (read-only relationships):
- ❌ `hasOneThrough`, `hasManyThrough`, Has One Of Many, Morph One Of Many
- ❌ `morphedByMany` — UI would be too complex

#### Load entries via AJAX — using FetchOperation

For large related tables, use AJAX instead of an on-page query.

Add `'ajax' => true` to the field definition:

```php
CRUD::field([
    'name'  => 'category',
    'type'  => 'relationship',
    'ajax'  => true,

    // optional:
    // 'attribute'               => "name",
    // 'placeholder'             => "Select a category",
    // 'delay'                   => 500,
    // 'data_source'             => url("fetch/category"),
    // 'minimum_input_length'    => 2,
    // 'dependencies'            => ['category'],
    // 'method'                  => 'POST',
    // 'include_all_form_fields' => false,
]);
```

Then add `FetchOperation` to the same CrudController and define a fetch method:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

public function fetchCategory()
{
    return $this->fetch(\App\Models\Category::class);
}
```

This creates a `/fetch/category` route returning search results. See [FetchOperation docs](/docs/{{version}}/crud-operation-fetch).

#### Create related entries in a modal — using InlineCreate

Works for `BelongsTo`, `BelongsToMany` and `MorphToMany`. Requires AJAX to be set up first.

Add `inline_create` to the field definition:

```php
// 1-n relationship
CRUD::field([
    'name'          => 'category',
    'type'          => 'relationship',
    'ajax'          => true,
    'inline_create' => true,
]);

// n-n relationship (relation method is plural, entity is singular)
CRUD::field([
    'name'          => 'tags',
    'type'          => 'relationship',
    'ajax'          => true,
    'inline_create' => ['entity' => 'tag'],
]);
```

Enable `InlineCreateOperation` on the related entity's CrudController:

```php
class CategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;
}
```

See [InlineCreate Operation docs](/docs/{{version}}/crud-operation-inline-create).

#### Save additional columns to a pivot table

For `BelongsToMany` / `MorphToMany` with extra pivot columns, define `withPivot()` on the model and add `subfields`:

```php
// Company model
public function people()
{
    return $this->belongsToMany(\App\Models\Person::class)
                ->withPivot('job_title', 'job_description');
}
```

```php
CRUD::field([
    'name'      => 'companies',
    'type'      => 'relationship',
    'subfields' => [
        [
            'name'    => 'job_title',
            'type'    => 'text',
            'wrapper' => ['class' => 'form-group col-md-3'],
        ],
        [
            'name'    => 'job_description',
            'type'    => 'text',
            'wrapper' => ['class' => 'form-group col-md-9'],
        ],
    ],
]);
```

#### Allow duplicate pivots

By default, the same pivot cannot be selected twice. To allow duplicates:

1. Add an auto-increment `id` column to the pivot table.
2. Include `id` in `->withPivot(...)` on the model.
3. Add `allow_duplicate_pivots => true` to the field:

```php
CRUD::field([
    'name'                   => 'companies',
    'type'                   => 'relationship',
    'allow_duplicate_pivots' => true,
    'subfields'              => [ /* do NOT add 'id' — Backpack handles it */ ],
    // 'pivot_key_name' => 'uuid', // if the PK isn't called 'id'
]);
```

#### Customize the pivot select

```php
CRUD::field([
    'name'        => 'companies',
    'type'        => 'relationship',
    'subfields'   => [ ... ],
    'pivotSelect' => [
        // 'attribute' => "title",
        'placeholder' => 'Pick a company',
        'wrapper'     => ['class' => 'col-md-6'],
        'options'     => function($model) {
            return $model->where('type', 'primary');
        },
        // 'ajax'        => true,
        // 'data_source' => backpack_url('fetch'),
    ],
]);
```

#### Manage related entries inline (create, update, delete)

For `hasMany` / `morphMany` where the secondary entry has no standalone form, define `subfields` to manage everything inside the parent form:

```php
CRUD::field([
    'name'      => 'items',
    'type'      => 'relationship',
    'subfields' => [
        [
            'name'    => 'order',
            'type'    => 'number',
            'wrapper' => ['class' => 'form-group col-md-1'],
        ],
        [
            'name'    => 'description',
            'type'    => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ],
        [
            'name'    => 'unit',
            'label'   => 'U.M.',
            'type'    => 'text',
            'wrapper' => ['class' => 'form-group col-md-1'],
        ],
        [
            'name'       => 'quantity',
            'type'       => 'number',
            'attributes' => ['step' => 'any'],
            'wrapper'    => ['class' => 'form-group col-md-2'],
        ],
        [
            'name'       => 'unit_price',
            'type'       => 'number',
            'attributes' => ['step' => 'any'],
            'wrapper'    => ['class' => 'form-group col-md-2'],
        ],
    ],
]);
```

Backpack handles create/update/delete of the related entries on save.

#### Delete related entries or fall back to default

For `hasMany` / `morphMany`, control what happens when the admin removes a relationship:

```php
CRUD::field([
    'name'        => 'comments',
    'type'        => 'relationship',
    'fallback_id' => 3,    // set FK to this ID instead of deleting
    'force_delete' => true, // delete the related entry entirely
]);
```
