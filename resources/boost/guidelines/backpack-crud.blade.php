@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Backpack for Laravel

## Getting Started

- When helping a user set up Backpack for the first time, always check if the storage symlink exists. Run `{{ $assist->artisanCommand('storage:link -q') }}` if `public/storage` is missing — otherwise uploads and assets will silently break.
- If the user reports broken images, missing CSS, or upload fields not working, the storage symlink is the first thing to check.

## Generating a CRUD
- Scaffold a full CRUD panel with `{{ $assist->artisanCommand('backpack:crud ModelName') }}` (singular model name).
- This generates: CrudController in `app/Http/Controllers/Admin/`, a FormRequest, and a route entry in `routes/backpack/custom.php`.
- Use `--no-interaction` to run without prompts.

## CrudController Structure
- Every CRUD controller extends `CrudController` and uses operation traits: `ListOperation`, `CreateOperation`, `UpdateOperation`, `ShowOperation`, `DeleteOperation`.
- Configure fields inside `setupCreateOperation()` and `setupUpdateOperation()`.
- Configure columns inside `setupListOperation()`.
- Use `CRUD::field([...])` and `CRUD::column([...])`. Both array and fluent syntax work.
- Always specify `'name'`, `'label'`, and `'type'` in every field and column array.

## Fields
- If `name` matches a model relationship method, Backpack auto-detects it as a relationship field. Use `'entity' => false` to disable.
- FREE types: text, number, textarea, select, select_multiple, select_from_array, select_grouped, checkbox, switch, radio, checklist, checklist_dependency, date, datetime, time, month, week, email, password, url, color, range, upload, upload_multiple, summernote, view, custom_html, hidden, enum.
- PRO types: select2, select2_multiple, select2_from_array, select2_from_ajax, select2_from_ajax_multiple, select2_grouped, select2_nested, select2_json_from_api, select_and_order, relationship, repeatable, table, slug, image, dropzone, date_picker, date_range, datetime_picker, address_google, phone, google_map, icon_picker, base64_image, video, code_mirror, easymde.
- Third-party add-ons: ckeditor, tinymce (separate packages).
- Field events: `CRUD::field('name')->on('saving', fn($entry) => ...)`.
- Organize with tabs: `CRUD::field('name')->tab('General')`.
- Wrappers: `CRUD::field('name')->wrapper(['class' => 'col-md-6'])`.
- Hints: `CRUD::field('name')->hint('Help text')`.

## Columns
- FREE types: text, number, boolean, checkbox, radio, switch, date, datetime, time, month, week, email, phone, url, password, color, range, image, upload, upload_multiple, select, select_multiple, select_from_array, select_grouped, relationship_count, model_function, model_function_attribute, closure, custom_html, view, row_number, check, json, multidimensional_array, enum, hidden, summernote, textarea, checklist, checklist_dependency.
- PRO types: select2, select2_multiple, select2_from_ajax, select2_from_ajax_multiple, select2_grouped, select2_nested, select_and_order, array, array_count, relationship, image (enhanced), base64_image, date_picker, date_range, datetime_picker, dropzone, easymde, icon_picker, markdown, slug, table, video, repeatable, browse, browse_multiple, address_google, code_mirror.
- Visibility: `visibleInTable`, `visibleInModal`, `visibleInExport`, `visibleInShow`. Use `exportOnlyColumn => true` for export-only columns.
- Search logic: `'searchLogic' => 'text'` or closure. Editable columns need explicit searchLogic.
- Order logic: `'orderLogic' => function ($query, $column, $direction) { ... }`.
- Column ordering: `CRUD::column('name')->before('email')`, `->after()`, `->makeFirst()`, `->makeLast()`.
- Visibility: `visibleInTable`, `visibleInModal`, `visibleInExport`, `visibleInShow`. Use `exportOnlyColumn => true` for export-only columns.
- Search logic: `'searchLogic' => 'text'` or closure. Editable columns need explicit searchLogic.
- Order logic: `'orderLogic' => function ($query, $column, $direction) { ... }`.
- Column ordering: `CRUD::column('name')->before('email')`, `->after()`, `->makeFirst()`, `->makeLast()`.

## Filters (all require backpack/pro)
- Available types: text, select2, select2_multiple, select2_ajax, date, date_range, simple, view, dropdown, range.
- Always use whenActive for filter logic: `->whenActive(fn($val) => CRUD::addClause('where', ...))`.
- Use `->else(fn() => ...)` or `->fallbackLogic(...)` for fallback when filter is not active.
- Reserved names (never use): `length`, `draw`, `start`, `search`, `totalEntryCount`, `columns`, `datatable_id`.
- Remove: `CRUD::filter('name')->remove()` or `CRUD::removeFilter('name')`.

## Buttons
- Stacks: top (above table), line (per entry row), bottom (below table).
- Add: `CRUD::button('name')->stack('line')->view('view.path')`.
- Quick buttons: `CRUD::button('quick')->stack('line')->view('crud::buttons.quick')`.
- Order: `CRUD::orderButtons('line', ['edit', 'delete'])`.
- Remove: `CRUD::removeButton('name')`, `CRUD::removeAllButtons()`.
- Per-entry access: `CRUD::button('name')->stack('line')->view('...')->setAccessCondition(fn($entry) => $entry->user_id === backpack_user()->id)`.

## Operations
- FREE traits: ListOperation, CreateOperation, UpdateOperation, ShowOperation, DeleteOperation, ReorderOperation.
- PRO traits (in crud, require backpack/pro): CloneOperation, BulkDeleteOperation, BulkCloneOperation, FetchOperation, InlineCreateOperation.
- PRO-only traits (in backpack/pro): TrashOperation, BulkTrashOperation, CustomViewOperation, AjaxUploadOperation.
- Add-on traits: ReportOperation, MinorUpdateOperation (EditableColumns), CreateInModalOperation, UpdateInModalOperation (DataFormModal).
- Each operation has: `setupXxxOperation()` for config, `setupXxxRoutes()` for routes, `setupXxxDefaults()` for defaults.
- Custom operations: `{{ $assist->artisanCommand('backpack:operation OperationName') }}`.

## Uploaders
- Add `->withFiles()` to upload fields for automatic file handling (upload, storage, retrieval, deletion).
- FREE uploaders: `SingleFile` (upload), `MultipleFiles` (upload_multiple), `SingleBase64Image` (image).
- PRO uploaders: `DropzoneUploader` (dropzone), `EasyMDEUploader` (easymde), `SummernoteUploader` (summernote) — require `AjaxUploadOperation`.
- Config: `->withFiles(['disk' => 'public', 'path' => 'uploads'])`.
- Model must use `CrudTrait`. Always run `php artisan storage:link`.
- DeleteOperation must define upload fields for auto-deletion on delete.
- Custom validation: `new ValidUpload('field_name')`, `new ValidUploadMultiple('field_name')`.
- Temp file cleanup (PRO): schedule `backpack:purge-temporary-folder` daily.

## Translatable Models (multi-language CRUDs)
- Uses `spatie/laravel-translatable`. Requires MySQL 5.7+ or PostgreSQL with JSON columns.
- Model: use `Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations` (NOT spatie's trait).
- Define `protected $translatable = ['name', 'description']` on the model.
- DB columns for translatable fields must be JSON or TEXT type.
- Do NOT cast translatable string columns as array — Eloquent sees them as strings.
- Config available locales in `config/backpack/crud.php` → `locales`.
- `spatie/laravel-translatable` is a separate composer package.

## Ecosystem Packages (first-party Backpack add-ons)
- `backpack/pro` — 28+ fields, 10+ filters, 5 extra operations (Clone, BulkDelete, BulkClone, InlineCreate, Fetch), chart widgets.
- `backpack/permissionmanager` — CRUD interface for users, roles, permissions (spatie/laravel-permission based). Free.
- `backpack/editable-columns` — inline editing of columns in List view. Paid.
- `backpack/dataform-modal` — Create/Update forms in Bootstrap modals. Paid.
- `backpack/report-operation` — dashboard with stat/chart metrics per entity. Paid.
- `backpack/devtools` — web interface for generating migrations, models, CRUDs. Paid.
- `backpack/test-generators` — auto-generate tests for CrudControllers. Paid.
- `backpack/calendar-operation` — calendar interface for date-based entries. Paid.
- `backpack/translation-manager` — UI to translate multi-language apps. Free.
- `backpack/settings` — interface for website settings stored as config. Free.
- `backpack/pagemanager` — admin panel for presentation pages with templates. Free.
- `backpack/menucrud` — add, edit, reorder, nest menu items. Free.
- `backpack/newscrud` — news articles, categories, tags CRUD. Free.
- `backpack/medialibrary-uploaders` — Spatie MediaLibrary integration (use `->withMedia()` instead of `->withFiles()`). Free.
- `backpack/activity-log` — track who changed what and when. Free.
- `backpack/filemanager` — admin interface for files & folders (elFinder). Free.
- `backpack/logmanager` — preview, download, delete Laravel logs. Free.
- `backpack/backupmanager` — database and file backups via spatie/laravel-backup. Free.
- `backpack/revise-operation` — audit log with undo via venturecraft/revisionable. Free.
- `backpack/auto-translate` — auto-translate content to multiple languages. Paid.
- Temp file cleanup (PRO): schedule `backpack:purge-temporary-folder` daily.

## Queries & Access Control
- Eager loading: `CRUD::with(['relation1', 'relation2'])`.
- Query scoping: `CRUD::addClause('where', 'active', true)`. Base clause: `CRUD::addBaseClause(...)`.
- Access: `CRUD::allowAccess('list')`, `CRUD::denyAccess('delete')`, `CRUD::hasAccess('update')`.
- Per-entry: `CRUD::setAccessCondition('update', fn($entry) => ...)`.

## Widgets
- Sections: before_content, after_content, before_filters, after_filters, details_row.
- Add: `Widget::add()->type('progress')->to('before_content')->value(135)->description('Progress')`.
- Types: progress, card, chart, view, script, style, chip.
- Script widget: `Widget::add()->type('script')->content('assets/js/admin/forms/product.js')`.
- Remove: `Widget::remove('section-name')`. Make hidden: `Widget::make()`.

## Chips (view-based, no PHP class)
- Column: `CRUD::addColumn(['type' => 'chip', 'heading' => fn($e) => ..., 'details' => fn($e) => [...]])`.
- Widget: `Widget::add()->type('chip')->to('before_content')->heading('...')->details([...])`.
- Generate custom: `{{ $assist->artisanCommand('backpack:chip ChipName') }}`.

## Save Actions
- Default: SaveAndBack, SaveAndEdit, SaveAndNew, SaveAndPreview, SaveAndList.
- Configure: `CRUD::setSaveActions([...])`, `CRUD::addSaveAction(...)`, `CRUD::orderSaveActions([...])`.
- Custom: extend AbstractSaveAction, implement `order()` and `getActionButtonHtml()`.

## Testing
- Package: `composer require --dev backpack/test-generators`.
- Generate: `{{ $assist->artisanCommand('backpack:tests') }}`.
- Status: `{{ $assist->artisanCommand('backpack:tests:status') }}`.
- Options: `--controller=Name`, `--operation=list`, `--framework=pest|phpunit`, `--force`.
- Requires factories and seeders for models with CrudControllers.
- Customize stubs: `php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag=stubs`.

## JavaScript API (crud.field)
- Selector: `crud.field('field_name')`.
- Properties: `.name`, `.type`, `.input`, `.value`, `.row`.
- Events: `.onChange(fn(field) => ...)`, `.change()`.
- Methods: `.hide()`, `.show()`, `.disable()`, `.enable()`, `.require()`, `.unrequire()`, `.check()`, `.uncheck()`.
- Subfields: `.subfield('subfield_name')`.
- Always load scripts via `Widget::add()->type('script')`.

## Artisan Commands
- `{{ $assist->artisanCommand('backpack:crud ModelName') }}` — full CRUD scaffold
- `{{ $assist->artisanCommand('backpack:field FieldName') }}` — custom field type
- `{{ $assist->artisanCommand('backpack:column ColumnName') }}` — custom column type
- `{{ $assist->artisanCommand('backpack:filter FilterName') }}` — custom filter
- `{{ $assist->artisanCommand('backpack:operation OperationName') }}` — custom operation
- `{{ $assist->artisanCommand('backpack:button ButtonName') }}` — custom button
- `{{ $assist->artisanCommand('backpack:widget WidgetName') }}` — custom widget
- `{{ $assist->artisanCommand('backpack:page PageName') }}` — custom admin page
- `{{ $assist->artisanCommand('backpack:chart ChartName') }}` — custom chart widget
- `{{ $assist->artisanCommand('backpack:install') }}` — first-time Backpack install
- `{{ $assist->artisanCommand('backpack:tests') }}` — generate CRUD tests
- `{{ $assist->artisanCommand('backpack:tests:status') }}` — check test coverage

@if($assist->hasMcpEnabled())
## Documentation Search
- For Backpack questions (fields, columns, filters, operations, widgets, relationships), always use the `search-backpack-docs` MCP tool FIRST.
- Do NOT use `search-docs` for Backpack questions — it does not index Backpack documentation.
- Pass multiple queries for OR logic: `["relationship field", "select2 belongsTo", "select2_from_ajax"]`.
- Use `"quoted phrases"` for exact matching.
@endif
