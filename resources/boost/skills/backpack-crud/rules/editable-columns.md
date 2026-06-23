# Backpack CRUD — Editable Columns

Add-on package `backpack/editable-columns`. Provides inline-editable columns that update via AJAX without leaving the List page. Requires the `MinorUpdateOperation` trait.

**⚠️ `backpack/editable-columns` is a paid add-on.** Check if the user has it installed before generating editable column code. Do NOT run `composer require` for it — the user must purchase and configure credentials first. See `rules/pro-features.md`.

**FREE alternative**: The standard Edit operation button in each row.

## Installation

```bash
composer require backpack/editable-columns
```

## How to Use

**Step 1.** Add the trait:

```php
use \Backpack\EditableColumns\Http\Controllers\Operations\MinorUpdateOperation;

class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use MinorUpdateOperation;
}
```

**Step 2.** Add editable columns in `setupListOperation()`. Four types:

### editable_text

```php
CRUD::addColumn([
    'name'  => 'price',
    'type'  => 'editable_text',
    'label' => 'Price',
    // Optionals:
    'underlined'       => true,    // default: true
    'min_width'        => '120px',
    'select_on_click'  => false,
    'save_on_focusout' => false,   // save on click-out (vs greyed out)
    'on_error'   => ['text_color' => '#df4759', 'text_color_duration' => 0, 'text_value_undo' => false],
    'on_success' => ['text_color' => '#42ba96', 'text_color_duration' => 3000],
    'auto_update_row' => true,
]);
```

### editable_checkbox

```php
CRUD::addColumn([
    'name'  => 'agreed',
    'type'  => 'editable_checkbox',
    'label' => 'Agreed',
    // Optionals:
    'on_error'   => ['status_color' => '#df4759', 'status_color_duration' => 0, 'switch_value_undo' => false],
    'on_success' => ['status_color' => '#42ba96', 'status_color_duration' => 3000],
    'auto_update_row' => true,
]);
```

### editable_switch

```php
CRUD::addColumn([
    'name'  => 'agreed',
    'type'  => 'editable_switch',
    'label' => 'Agreed',
    // Same options as editable_checkbox, plus:
    'color'    => 'success',
    'onLabel'  => '✓',
    'offLabel' => '✕',
]);
```

### editable_select

```php
CRUD::addColumn([
    'name'    => 'categories',
    'type'    => 'editable_select',
    'label'   => 'Categories',
    'options' => Category::pluck('name', 'id')->toArray(),
    // or closure (receives $entry):
    'options' => fn($entry) => Category::where('created_at', '<=', $entry->created_at)->pluck('name', 'id')->toArray(),
    // Optionals:
    'save_on_change'   => true,     // save immediately on select
    'save_on_focusout' => true,
    'on_error'   => ['text_color' => '#df4759', 'text_color_duration' => 0, 'text_value_undo' => false],
    'on_success' => ['text_color' => '#42ba96', 'text_color_duration' => 3000],
    'auto_update_row' => true,
]);
```

**Step 3.** Set validation in `setupMinorUpdateOperation()`:

```php
protected function setupMinorUpdateOperation()
{
    $this->crud->setValidation(StoreRequest::class);
}
```

MinorUpdate only validates the currently edited attribute. Override `saveMinorUpdateFormValidation()` and `saveMinorUpdateEntry()` for custom logic.

## Nullable Select

```php
'options' => array_merge(['' => 'No category'], Category::pluck('name', 'id')->toArray()),
```

## Search Logic (REQUIRED)

Editable columns MUST have explicit `searchLogic`:

```php
CRUD::addColumn([
    'name'        => 'email',
    'type'        => 'editable_text',
    'searchLogic' => 'text',  // use text search logic
    // or custom:
    'searchLogic' => fn($query, $column, $searchTerm) => $query->orWhere('email', 'like', '%'.$searchTerm.'%'),
]);
```

| Column Type | searchLogic |
|-------------|-------------|
| `editable_text` | `'text'` |
| `editable_checkbox` | custom |
| `editable_switch` | custom |
| `editable_select` | custom |

## Table Reload After Update

```php
// In setupListOperation():
CRUD::setOperationSetting('forceReloadAfterUpdate', true);                    // always
CRUD::setOperationSetting('forceReloadAfterUpdate', ['price', 'status']);     // specific columns
CRUD::setOperationSetting('forceReloadAfterUpdate', 'status');                // single column
```

## Gotchas
- Every editable column MUST have `searchLogic` defined — without it filtering breaks.
- `editable_text` and `editable_select` support keyboard nav: Enter saves, Esc reverts, ↑↓ move rows, Tab/Shift+Tab move between inputs.
- `editable_select` with closure options: enable `forceReloadAfterUpdate` to refresh options after each edit.
- `save_on_change` saves immediately on select; `save_on_focusout` saves when clicking away.
