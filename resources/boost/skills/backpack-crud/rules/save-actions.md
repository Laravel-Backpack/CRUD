# Backpack CRUD — Save Actions

Save actions control what happens after a Create or Update form is submitted. They appear as buttons in the form's bottom bar.

## Default Save Actions

| Class | Order | Behavior |
|-------|-------|----------|
| `SaveAndBack` | 1 | Save, redirect to list |
| `SaveAndEdit` | 2 | Save, stay on edit form |
| `SaveAndNew` | 3 | Save, open new create form |
| `SaveAndPreview` | 4 | Save, open Show page |
| `SaveAndList` | 5 | Save, redirect to list (alias) |

## Configuring Save Actions

```php
// In setupCreateOperation() or setupUpdateOperation():

// Replace all save actions (order matters)
CRUD::setSaveActions([
    \Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade::SAVE_ACTION_SAVE_AND_BACK,
    \Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade::SAVE_ACTION_SAVE_AND_EDIT,
]);

// Add additional save action
CRUD::addSaveAction(\App\SaveActions\SaveAndSendEmail::class);

// Remove a save action
CRUD::removeSaveAction(CRUD::SAVE_ACTION_SAVE_AND_NEW);

// Order save actions
CRUD::orderSaveActions([CRUD::SAVE_ACTION_SAVE_AND_EDIT, CRUD::SAVE_ACTION_SAVE_AND_BACK]);
```

## Creating a Custom Save Action

**Step 1.** Create the class:

```php
<?php
namespace App\SaveActions;

use Backpack\CRUD\app\Library\CrudPanel\SaveAction;
use Backpack\CRUD\app\Library\CrudPanel\AbstractSaveAction;

class SaveAndSendEmail extends AbstractSaveAction
{
    public static function getName(): string
    {
        return 'save_and_send_email';
    }

    public function getButtonHtml(): string
    {
        return '<button type="submit" name="save_action" value="save_and_send_email" class="btn btn-primary">
            <i class="la la-envelope"></i> Save and Send Email
        </button>';
    }

    public function getRedirectUrl(?int $entryId = null): string
    {
        return backpack_url('article/' . $entryId . '/send-email');
    }

    public function getVisible(): bool
    {
        return backpack_user()->can('send-emails');
    }
}
```

**Step 2.** Register and use:

```php
use App\SaveActions\SaveAndSendEmail;

CRUD::setSaveActions([
    CRUD::SAVE_ACTION_SAVE_AND_BACK,
    SaveAndSendEmail::class,
]);
```

## Gotchas
- Save action button `name` attributes must be `save_action`.
- The `value` attribute of the button identifies which action to perform.
- The `order()` method controls the button's position (lower = earlier).
- Save actions work with the `bp-dataform` component (used by DataFormModal).
- Disable save action notifications: `CRUD::setOperationSetting('showSaveActionChange', false)`.
