@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Backpack for Laravel

## Getting Started

- When helping a user set up Backpack for the first time, always check if the storage symlink exists. Run `{{ $assist->artisanCommand('storage:link -q') }}` if `public/storage` is missing — otherwise uploads and assets will silently break.
- If the user reports broken images, missing CSS, or upload fields not working, the storage symlink is the first thing to check.

## Paid Features — Check Before Using

**CRITICAL: Before generating code that uses any PRO or paid feature, check if the required package is installed.** Never assume the user has access to paid packages. If a package is missing, stop and tell the user — do NOT attempt to install it yourself.

### How to check
- Read the project's `composer.json` and look for the package in `require` or `require-dev`.
- Or check if the vendor directory exists (e.g., `vendor/backpack/pro/composer.json`).

### Paid packages and their features

| Package | Provides |
|---------|----------|
| `backpack/pro` | PRO fields (select2, select2_from_ajax, relationship, repeatable, dropzone, etc.), PRO columns, ALL filters, FetchOperation, CloneOperation, BulkDeleteOperation, BulkCloneOperation, InlineCreateOperation, TrashOperation, AjaxUploadOperation, CustomViewOperation, chart widgets |
| `backpack/editable-columns` | Inline editing in the List view (`MinorUpdateOperation`, `editable_text`, `editable_select`, etc.) |
| `backpack/dataform-modal` | Modal Create/Update forms (`CreateInModalOperation`, `UpdateInModalOperation`) |
| `backpack/devtools` | Web UI for generating migrations, models, CRUDs |
| `backpack/report-operation` | Dashboard with stat/chart metrics per entity |
| `backpack/test-generators` | Auto-generate tests for CrudControllers |
| `backpack/calendar-operation` | Calendar interface for date-based entries |
| `backpack/auto-translate` | Auto-translate content to multiple languages |

### If a paid package is not installed

When a user asks to install a paid package (e.g., "install backpack/pro"), follow this workflow:

1. **Check if `auth.json` exists** — Look for Backpack credentials in:
   - Windows: `%APPDATA%/Composer/auth.json` (e.g., `C:\Users\<user>\AppData\Roaming\Composer\auth.json`)
   - Linux/Mac: `~/.composer/auth.json`
   - Project-level: `<project>/auth.json`
   Read the file and look for `http-basic` entries with `backpackforlaravel.com` or `repo.backpackforlaravel.com` URLs.

2. **Check if the repository is configured** — Look in the project's `composer.json` under `repositories` for a `backpack` entry pointing to `https://repo.backpackforlaravel.com` or `https://backpackforlaravel.com/packages`.

3. **If credentials exist AND repository is configured** → Proceed to run `composer require <package-name>`.

4. **If credentials exist but repository is NOT configured** → Add the repository first, then install:
   ```
   composer config repositories.backpack composer https://repo.backpackforlaravel.com
   composer require <package-name>
   ```

5. **If auth.json is missing or lacks Backpack credentials** → Inform the user:
   - What feature they asked for requires which paid package
   - How to purchase: `https://backpackforlaravel.com/pricing`
   - How to set up `auth.json` (credentials are in their Backpack account dashboard)
   - Offer a FREE alternative if one exists (e.g., use FREE `select` field instead of PRO `select2` field, FREE `upload` field instead of PRO `image` field). **There is no free alternative for filters — all filter types require `backpack/pro`. Do NOT suggest `addClause` as a filter substitute; it permanently scopes the query and is not toggleable.**

## Generating a CRUD
- Scaffold a full CRUD panel with `{{ $assist->artisanCommand('backpack:crud ModelName') }}` (singular model name).
- This generates: CrudController in `app/Http/Controllers/Admin/`, a FormRequest, a route entry in `routes/backpack/custom.php`, and a menu item.
- Use `--no-interaction` to run without prompts.
- **After generating the CRUD**, always edit `resources/views/vendor/backpack/ui/inc/menu_items.blade.php` and replace the default `icon="la la-question"` with a meaningful Line Awesome icon. Pick an icon that reflects the model's domain. Common mappings:

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
- **If `backpack/test-generators` is installed** (check `vendor/backpack/test-generators/composer.json`), automatically generate tests for the new CRUD: `{{ $assist->artisanCommand('backpack:tests --controller=ModelCrudController') }}`.

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
- **Line buttons as dropdown**: Group line buttons into a dropdown when there are many. Enable with `CRUD::setOperationSetting('lineButtonsAsDropdown', true)`. Control minimum count with `lineButtonsAsDropdownMinimum` (default: 1) and show first N inline with `lineButtonsAsDropdownShowBefore` (default: 0).

## List Operation
- **Line buttons as dropdown**: See Buttons section above.
- **Export buttons**: `CRUD::enableExportButtons()`. Control per-column with `visibleInExport`, `exportOnlyColumn`.
- **Responsive table**: `CRUD::setOperationSetting('responsiveTable', true)`.
- **Persistent table**: `CRUD::setPersistentTable(true)` — saves filters, sorting, page length across visits.
- **Details row**: `CRUD::enableDetailsRow()` then `Widget::add()->to('details_row')...`.
- **Custom views**: `CRUD::setListView('path.to.view')`, `CRUD::setCreateView()`, `CRUD::setEditView()`.
- **Page length**: `CRUD::setDefaultPageLength(25)`. Options: `CRUD::setPageLengthMenu([10, 25, 50, 100])`.

## Operations
- FREE traits: ListOperation, CreateOperation, UpdateOperation, ShowOperation, DeleteOperation, ReorderOperation.
- PRO traits (in crud, require backpack/pro): CloneOperation, BulkDeleteOperation, BulkCloneOperation, FetchOperation, InlineCreateOperation.
- PRO-only traits (in backpack/pro): TrashOperation, BulkTrashOperation, CustomViewOperation, AjaxUploadOperation.
- Add-on traits (require separate paid packages): ReportOperation (backpack/report-operation), MinorUpdateOperation (backpack/editable-columns), CreateInModalOperation + UpdateInModalOperation (backpack/dataform-modal).
- Each operation has: `setupXxxOperation()` for config, `setupXxxRoutes()` for routes, `setupXxxDefaults()` for defaults.
- Custom operations: `{{ $assist->artisanCommand('backpack:operation OperationName') }}`.
- **Before using any PRO or add-on operation trait, check the Paid Features section above.**

## Fetch Operation (PRO)
- The Fetch Operation is the recommended way to build AJAX data endpoints for `select2_from_ajax`, `select2_from_ajax_multiple`, and `relationship` fields. Use it instead of creating separate API controllers.
- Add `use FetchOperation` on the **target** model's CrudController (the model being fetched, not the model with the field).
- Define methods following the naming convention `fetchEntity()`: `fetchTag()` for entity `tag`, `fetchCategory()` for entity `category`.
- Always define a `query` closure to activate the save-time security guard and scope results: `'query' => fn($model) => $model->active()`.
- For select2_ajax filters using FetchOperation, the filter method **must** be `'method' => 'POST'`.
- Use `'relation_options_query_source' => 'fetchMethodName'` when `data_source` is set manually and the field entity doesn't match the fetch method name.
- For fully custom endpoints (no FetchOperation), declare the guard with `'relation_options_query' => fn($model) => ...`.
- Prefer explicit `searchable_attributes` over auto-detection. Set to `[]` when using raw SQL searching in the query closure.

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
- `backpack/pro` — 28+ fields, 10+ filters, 5 extra operations (Clone, BulkDelete, BulkClone, InlineCreate, Fetch), chart widgets. **Paid.**
- `backpack/permissionmanager` — CRUD interface for users, roles, permissions (spatie/laravel-permission based). Free.
- `backpack/editable-columns` — inline editing of columns in List view. **Paid.**
- `backpack/dataform-modal` — Create/Update forms in Bootstrap modals. **Paid.**
- `backpack/report-operation` — dashboard with stat/chart metrics per entity. **Paid.**
- `backpack/devtools` — web interface for generating migrations, models, CRUDs. **Paid.**
- `backpack/test-generators` — auto-generate tests for CrudControllers. **Paid.**
- `backpack/calendar-operation` — calendar interface for date-based entries. **Paid.**
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
- `backpack/auto-translate` — auto-translate content to multiple languages. **Paid.**
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
- Package: `backpack/test-generators` is a **paid add-on**. If not installed, follow the paid package installation workflow in the Paid Features section.
- **After generating a CRUD**, if the package is installed, automatically run `{{ $assist->artisanCommand('backpack:tests --controller=ModelCrudController') }}` to generate tests for the new CRUD.
- Generate: `{{ $assist->artisanCommand('backpack:tests') }}`.
- Status: `{{ $assist->artisanCommand('backpack:tests:status') }}`.
- Options: `--controller=Name`, `--operation=list`, `--framework=pest|phpunit`, `--force`.
- FREE alternative: Write Pest/PHPUnit tests manually — `rules/testing.md` has templates and examples.
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
