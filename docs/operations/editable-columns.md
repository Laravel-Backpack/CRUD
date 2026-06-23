# Editable Columns

## About

Provides inline-editable columns for the List operation: `editable_text`, `editable_checkbox`, `editable_switch`, `editable_select`. On edit, an AJAX request updates the attribute in the database. Success shows green; failure shows red.

Keyboard navigation for `editable_text` and `editable_select`: Enter saves, Esc reverts, ↑/↓ move rows, Tab/Shift+Tab move between inputs.

`editable_select` saves immediately on change by default. Set `save_on_change => false` to save only on focus out. For dynamic options with closures, enable `forceReloadAfterUpdate` to redraw on update.

## Installation

```bash
composer require backpack/editable-columns
```

Or quick install:

```bash
php artisan backpack:require:editablecolumns
```

Manual setup for private repo:

```bash
composer config http-basic.backpackforlaravel.com [token-username] [token-password]
```

```json
"repositories": [
    { "type": "composer", "url": "https://repo.backpackforlaravel.com/" }
]
```

## How to Use

**Step 1.** Add the `MinorUpdateOperation` trait:

```php
class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\EditableColumns\Http\Controllers\Operations\MinorUpdateOperation;
}
```

**Step 2.** Add editable columns in `setupListOperation()`:

### editable_text

```php
CRUD::addColumn([
    'name'  => 'price',
    'type'  => 'editable_text',
    'label' => 'Price',

    // Optionals
    'underlined'       => true,    // dotted underline? default: true
    'min_width'        => '120px',
    'select_on_click'  => false,   // select all text on click? default: false
    'save_on_focusout' => false,   // save on click-out (vs greyed out)
    'on_error' => [
        'text_color'          => '#df4759',
        'text_color_duration' => 0,       // ms, 0 = until refresh
        'text_value_undo'     => false,   // revert to original value
    ],
    'on_success' => [
        'text_color'          => '#42ba96',
        'text_color_duration' => 3000,
    ],
    'auto_update_row' => true,    // update related columns in row after AJAX
]);
```

### editable_checkbox

```php
CRUD::addColumn([
    'name'  => 'agreed',
    'label' => 'Agreed',
    'type'  => 'editable_checkbox',

    // Optionals
    'underlined' => true,
    'on_error' => [
        'status_color'          => '#df4759',
        'status_color_duration' => 0,
        'switch_value_undo'     => false,
    ],
    'on_success' => [
        'status_color'          => '#42ba96',
        'status_color_duration' => 3000,
    ],
    'auto_update_row' => true,
]);
```

### editable_switch

```php
CRUD::addColumn([
    'name'  => 'agreed',
    'label' => 'Agreed',
    'type'  => 'editable_switch',

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
    'label'   => 'Categories',
    'type'    => 'editable_select',
    'options' => \App\Models\Category::all()->pluck('name', 'id')->toArray(),
    // or closure (receives $entry):
    'options' => (function ($entry) {
        return \App\Models\Category::whereDate('created_at', '<=', $entry->created_at)
            ->pluck('name', 'id')->toArray();
    }),
    // or static array:
    'options' => ['1' => 'One', '2' => 'Two', '3' => 'Three'],

    // Optionals
    'underlined'       => true,
    'save_on_focusout' => true,
    'save_on_change'   => true,
    'on_error' => [
        'text_color'          => '#df4759',
        'text_color_duration' => 0,
        'text_value_undo'     => false,
    ],
    'on_success' => [
        'text_color'          => '#42ba96',
        'text_color_duration' => 3000,
    ],
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

MinorUpdate only considers the validation rule for the currently edited attribute.

### Custom Validation & Save

Override `saveMinorUpdateFormValidation()` and `saveMinorUpdateEntry()`:

```php
public function saveMinorUpdateFormValidation()
{
    if (request('attribute') === 'price' && !is_numeric(request('value'))) {
        throw ValidationException::withMessages([
            'price' => ['The price has to be a number.'],
        ]);
    }
}

public function saveMinorUpdateEntry()
{
    $entry = $this->crud->getModel()->find(request('id'));
    $entry->{request('attribute')} = request('value');
    $entry->status = 'draft';
    $entry->save();

    return $entry->refresh();
}
```

## Nullable Select

Add an empty option for nullable columns:

```php
CRUD::addColumn([
    'name'    => 'categories',
    'label'   => 'Categories',
    'type'    => 'editable_select',
    'options' => array_merge(['' => 'No category'], \App\Models\Category::all()->pluck('name', 'id')->toArray()),
    // or
    'options' => [
        ''  => 'No Category',
        '1' => 'One',
        '2' => 'Two',
        '3' => 'Three',
    ],
]);
```

## Search Logic

Editable columns need explicit `searchLogic`:

```php
CRUD::addColumn([
    'name'        => 'email',
    'type'        => 'editable_text',
    'label'       => 'Email',
    'searchLogic' => 'text',  // use text column search logic
    // or custom:
    'searchLogic' => function ($query, $column, $searchTerm) {
        $query->orWhere('email', 'like', '%'.$searchTerm.'%');
    },
]);
```

| Column Type | Recommended searchLogic |
|-------------|------------------------|
| `editable_text` | `'text'` |
| `editable_checkbox` | write custom |
| `editable_switch` | write custom |
| `editable_select` | write custom |

## Reloading Table After Update

```php
// In setupListOperation():
CRUD::setOperationSetting('forceReloadAfterUpdate', true);                  // always reload
CRUD::setOperationSetting('forceReloadAfterUpdate', ['price', 'status']);   // reload for specific columns
CRUD::setOperationSetting('forceReloadAfterUpdate', 'status');              // single column
```