# Backpack CRUD — Buttons

Buttons trigger operations from three stacks in the List/Show operations.

## Button Stacks

| Stack | Position | Examples |
|-------|----------|----------|
| `top` | Above the table | Add button |
| `line` | Per entry row | Edit, Delete, Show |
| `bottom` | Below the table | Bulk actions, custom |

## Adding Buttons

```php
// Array syntax
CRUD::button('publish')->stack('line')->view('crud::buttons.quick');

// With config array
CRUD::addButton('line', 'publish', 'view', 'crud::buttons.quick');

// From model function
CRUD::addButtonFromModelFunction('line', 'publish', 'getPublishButton', 'end');

// From a Blade view
CRUD::addButtonFromView('top', 'import', 'admin.buttons.import', 'end');
```

## Quick Buttons

Quick buttons submit a POST request. Create `resources/views/vendor/backpack/crud/buttons/my_quick_button.blade.php`:

```blade
<form method="POST" action="{{ route('entity.publish', $entry->getKey()) }}" style="display:inline">
    @csrf
    <button type="submit" class="btn btn-sm btn-link">
        <i class="la la-check"></i> Publish
    </button>
</form>
```

## Button Ordering

```php
CRUD::orderButtons('line', ['edit', 'delete', 'publish']);
CRUD::moveButton('delete', 'beginning');  // or 'end'
CRUD::button('publish')->stack('line')->after('edit');
CRUD::button('publish')->stack('line')->before('delete');
```

## Removing Buttons

```php
CRUD::removeButton('create');
CRUD::removeButtonFromStack('delete', 'line');
CRUD::removeAllButtons();
CRUD::removeAllButtonsFromStack('line');
```

## Per-Entry Access Control

```php
CRUD::button('publish')
    ->stack('line')
    ->view('crud::buttons.quick')
    ->setAccessCondition(function ($entry) {
        return $entry->status === 'draft' && backpack_user()->can('publish');
    });
```

## Custom Button Types

Generate custom button: `php artisan backpack:button ButtonName`.

## Line Buttons as Dropdown

Convert line stack buttons into a single dropdown:

```php
// In setupListOperation():
CRUD::setOperationSetting('lineButtonsAsDropdown', true);
CRUD::setOperationSetting('lineButtonsAsDropdownMinimum', 5);  // min buttons before dropdown
CRUD::setOperationSetting('lineButtonsAsDropdownShowBefore', 3); // first N stay inline
```

## Gotchas
- Buttons in the `line` stack need `$entry` variable available in their view.
- Quick buttons should use `<form>` with CSRF token for POST actions.
- Stack position defaults: `'end'` for top/bottom, `'beginning'` for line.
- Model function buttons must return HTML string.
