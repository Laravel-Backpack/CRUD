# Backpack CRUD — Operations

**⚠️ Operations marked PRO require `backpack/pro`. Operations marked as add-ons require their respective paid packages (`backpack/editable-columns`, `backpack/dataform-modal`, `backpack/report-operation`). Check installation before generating code. See `rules/pro-features.md`.**

## Built-in Operations

Enable by using the trait on your CrudController:

```php
// FREE
use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;

// PRO (traits exist in crud as stubs, real implementation in backpack/pro)
use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\BulkCloneOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;

// PRO-only (traits only in backpack/pro)
use \Backpack\Pro\Http\Controllers\Operations\TrashOperation;
use \Backpack\Pro\Http\Controllers\Operations\BulkTrashOperation;
use \Backpack\Pro\Http\Controllers\Operations\CustomViewOperation;
use \Backpack\Pro\Http\Controllers\Operations\AjaxUploadOperation;

// Add-ons
use \Backpack\ReportOperation\Http\Controllers\Operations\ReportOperation;
use \Backpack\EditableColumns\Http\Controllers\Operations\MinorUpdateOperation;
use \Backpack\DataFormModal\Http\Controllers\Operations\CreateInModalOperation;
use \Backpack\DataFormModal\Http\Controllers\Operations\UpdateInModalOperation;
```

## Operation Lifecycle

Each operation trait typically provides:

```php
trait SomeOperation
{
    protected function setupSomeOperationDefaults() { /* runs first, sets defaults */ }
    protected function setupSomeOperationRoutes($segment, $routeName, $controller) { /* registers routes */ }
    public function someAction() { /* the action method */ }
}
```

Your `setupSomeOperation()` in the controller runs **after** `setupSomeOperationDefaults()` but **before** the action method.

Hooks available in setup:
```php
// Runs before the operation's setup
CRUD::setOperationSetting('someKey', 'value');
```

## Operation-Specific Features

### List Operation
- **Details row**: `CRUD::enableDetailsRow()` then `Widget::add()->to('details_row')...`. Override view with `CRUD::setDetailsRowView('view.name')`.
- **Export buttons**: `CRUD::enableExportButtons()`. Control per-column with `visibleInExport`, `exportOnlyColumn`.
- **Responsive table**: `CRUD::setOperationSetting('responsiveTable', true)`.
- **Persistent table**: `CRUD::setPersistentTable(true)` — saves filters, sorting, page length.
- **Custom views**: `CRUD::setListView('path.to.view')`, `CRUD::setCreateView()`, `CRUD::setEditView()`.
- **Line buttons as dropdown**: `CRUD::setOperationSetting('lineButtonsAsDropdown', true)`. Thresholds: `lineButtonsAsDropdownMinimum`, `lineButtonsAsDropdownShowBefore`.
- **Page length**: `CRUD::setDefaultPageLength(25)`. Options: `CRUD::setPageLengthMenu([10, 25, 50, 100])`.

### Create / Update Operations
- **Separate validation per operation**: Use different FormRequest classes for create vs update.
- **Callbacks**: Override `store()` / `update()` on the controller.
- **Model events**: Standard Laravel events (`creating`, `saving`, etc.) fire normally.
- **Translatable**: Set `CRUD::setOperationSetting('showTranslatableFields', true)`.
- **Field events**: `CRUD::field('name')->on('saving', fn($entry) => ...)`.

### Show Operation
- **Tabs**: `CRUD::column('name')->tab('General')`.
- **Auto-setup**: Columns auto-configured from `setupListOperation()` if `setupShowOperation()` is empty.
- **Language dropdowns**: `CRUD::setOperationSetting('showLanguagesInShowOperation', false)`.

### Delete Operation
- **Soft deletes**: Works automatically if model uses `SoftDeletes`. Trashed entries show in list.
- **Bulk delete (PRO)**: `use BulkDeleteOperation` — adds checkbox column and bulk delete button.
- **Force delete**: Override `destroy()` for permanent deletion with soft-delete models.

### Clone Operation (PRO)
- `CloneOperation` adds a clone button to the line stack.
- **Bulk clone (PRO)**: `use BulkCloneOperation`.
- **Exempt attributes**: Set `$clone_exempt_attributes = ['id', 'created_at', 'updated_at']` on the model.
- **Redirect after clone**: Override `clone()` method.

### Trash Operation (PRO)
- Adds trash/restore functionality for soft-deletable models.
- `use TrashOperation` — adds trash button and trash view.
- **Bulk trash (PRO)**: `use BulkTrashOperation`.
- Requires model to use `SoftDeletes`.

### AjaxUpload Operation (PRO)
- Required trait for any CRUD using ajax uploaders (dropzone, easymde, summernote PRO versions).
- `use AjaxUploadOperation` — registers the AJAX upload and temp-file-delete endpoints.
- Also registers the `backpack:purge-temporary-folder` cleanup command.

### CustomView Operation (PRO)
- Replaces a CRUD operation's view with a fully custom Blade view.
- `use CustomViewOperation` — allows `CRUD::setListView()`, `CRUD::setCreateView()`, `CRUD::setEditView()`, `CRUD::setShowView()`.

### Translatable Models (multi-language)
- Uses `spatie/laravel-translatable`. Requires MySQL 5.7+ or PostgreSQL with JSON columns.
- Model: use `Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations` (NOT spatie's trait).
- Define `protected $translatable = ['name', 'description']` on model.
- DB columns for translatable fields must be JSON or TEXT; do NOT cast them as array on the model.
- Configure available locales in `config/backpack/crud.php` → `locales`.
- Entries are created in the current user's locale. Language switchers appear on Edit/Show buttons.

### Reorder Operation
- Hierarchy tree with drag-and-drop. Requires `lft`, `rgt`, `depth` columns (Baum/NestedSet pattern).
- `CRUD::set('reorder.max_level', 3)` — set max depth. `0` for unlimited.
- `CRUD::set('reorder.label', 'name')` — column to show as label.
- `CRUD::set('reorder.escaped', true)` — escape HTML in label.

### Revisions Operation
- Requires `venturecraft/revisionable` package.
- Model must implement `\Venturecraft\Revisionable\RevisionableTrait`.
- Set `protected $identifiableName = 'name'` on model for revision display.

### Fetch Operation (PRO)
- Used by `select2_from_ajax`, `select2_from_ajax_multiple`, and `relationship` fields for AJAX search.
- This is the recommended way to build data endpoints directly in your CrudController.
- See `rules/fetch.md` for full documentation covering: basic setup, field integration, filter integration, security guard, and customization.
- Quick reference for the Operations skill: `use FetchOperation` on the **target** model's CrudController. Define `fetchEntity()` methods matching the naming convention (`fetchTag()` for entity `tag`).

### InlineCreate Operation (PRO)
- Opens a modal on the List/Create page to create a related entry inline.
- `use InlineCreateOperation` on the parent CrudController.
- Configure: `CRUD::setOperationSetting('inlineCreateModalClass', 'modal-lg')`.
- Include main form fields: `CRUD::setOperationSetting('include_main_form_fields', true)`.
- Widgets in inline: `Widget::add()->to('inline_create')...`.

### Report Operation (Add-on)
- Dashboard with stat/chart metrics on any entity.
- `use ReportOperation` — adds Report button to List.
- Define metrics in `setupReportOperation()`: `$this->addMetric('name', ['type' => 'stat', ...])`.
- Types: stat, line, bar, stacked-bar, stacked-line, pie, table, view.
- Auto-injected filters: date_range, interval dropdown.
- Group AJAX requests: `$this->groupMetrics('groupName', ['metric1', 'metric2'])`.

### MinorUpdate (Editable Columns Add-on)
- Inline editing of list columns via AJAX.
- `use MinorUpdateOperation` — enables editable columns.
- Column types: `editable_text`, `editable_checkbox`, `editable_switch`, `editable_select`.
- Set validation: `CRUD::setValidation(StoreRequest::class)` in `setupMinorUpdateOperation()`.
- Editable columns need explicit `searchLogic`.

### CreateInModal / UpdateInModal (DataFormModal Add-on)
- Opens Create/Update forms in a Bootstrap modal.
- `use CreateInModalOperation` / `use UpdateInModalOperation`.
- Blade component: `<x-bp-dataform-modal :controller="..." formOperation="..." />`.
- Define fields in `setupCreateInModalOperation()` or reuse `setupCreateOperation()`.

## Custom Operations

**Step 1.** Generate: `php artisan backpack:operation OperationName`.

**Step 2.** Register routes in the trait or controller:

```php
protected function setupPublishRoutes($segment, $routeName, $controller)
{
    Route::get($segment . '/{id}/publish', [
        'as'         => $routeName . '.publish',
        'uses'       => $controller . '@publish',
        'operation'  => 'publish',  // REQUIRED — identifies this as an operation route
    ]);
}
```

**Step 3.** Add defaults:

```php
protected function setupPublishDefaults()
{
    CRUD::allowAccess('publish');
}
```

**Step 4.** Create the action method:

```php
public function publish($id)
{
    $entry = CRUD::getEntry($id);
    $entry->update(['status' => 'published']);
    \Alert::success('Entry published.')->flash();
    return redirect()->back();
}
```

**Step 5.** Add a button in `setupListOperation()`:

```php
CRUD::button('publish')->stack('line')->view('crud::buttons.quick');
```

## Overriding Built-in Actions

Override any action method on the controller — PHP's OOP takes over:

```php
public function store()
{
    // custom logic
    $response = $this->traitStore(); // call original
    return $response;
}
```

## Gotchas
- No operations are enabled by default — always use the trait explicitly.
- Method names MUST follow the convention: `setup{OperationName}Operation()`, `setup{OperationName}Routes()`, `setup{OperationName}Defaults()`.
- Route definitions for operations need `'operation' => 'name'` in the route action array.
- `denyAccess()` in `setup()` prevents the operation route from being registered entirely.
- Custom views (PRO) for list/create/update are set via `CRUD::setListView()`, `CRUD::setCreateView()`, etc.
