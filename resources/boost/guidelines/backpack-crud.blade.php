@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Backpack for Laravel

## Generating a CRUD
- Scaffold a full CRUD panel with `{{ $assist->artisanCommand('backpack:crud ModelName') }}` (singular model name).
- This generates: CrudController in `app/Http/Controllers/Admin/`, a FormRequest, and a route entry in `routes/backpack/custom.php`.

## CrudController Structure
- Every CRUD controller extends `CrudController` and uses operation traits: `ListOperation`, `CreateOperation`, `UpdateOperation`, `ShowOperation`, `DeleteOperation`.
- Configure fields inside `setupCreateOperation()` and `setupUpdateOperation()`.
- Configure columns inside `setupListOperation()`.
- Use `CRUD::field([...])` and `CRUD::column([...])` — not `$this->crud->addField()`.
- Always specify `'name'`, `'label'`, and `'type'` in every field and column array.

## Fields, Columns, Filters
- If `name` matches a model relationship method, Backpack auto-detects it as a relationship field.
- Use `'entity' => false` to prevent Backpack from treating a field as a relationship.
- Filters are added in `setupListOperation()` with `CRUD::addFilter([...], values, callback)`.

## Artisan Commands
- `{{ $assist->artisanCommand('backpack:crud ModelName') }}` — full CRUD scaffold
- `{{ $assist->artisanCommand('backpack:field FieldName') }}` — custom field type
- `{{ $assist->artisanCommand('backpack:column ColumnName') }}` — custom column type
- `{{ $assist->artisanCommand('backpack:filter FilterName') }}` — custom filter
- `{{ $assist->artisanCommand('backpack:operation OperationName') }}` — custom operation
- `{{ $assist->artisanCommand('backpack:install') }}` — first-time Backpack install

@if($assist->hasMcpEnabled())
## Backpack Documentation
- For Backpack-specific questions (fields, columns, filters, operations, widgets, relationships), use the `search-backpack-docs` MCP tool.
- Do NOT use `search-docs` for Backpack questions — it does not index Backpack documentation.
- Pass multiple queries when unsure of terminology: `["relationship field", "select2 relationship"]`.
@endif
