# Backpack CRUD — Data Form Modal

Add-on package `backpack/dataform-modal`. Opens Create/Update forms inside a Bootstrap modal via AJAX. Provides `CreateInModalOperation` and `UpdateInModalOperation` traits.

**⚠️ `backpack/dataform-modal` is a paid add-on.** Check if the user has it installed before generating modal form code. Do NOT run `composer require` for it — the user must purchase and configure credentials first. See `rules/pro-features.md`.

**FREE alternative**: Standard Create/Update pages with `CreateOperation` and `UpdateOperation`.

## Installation

```bash
composer require backpack/dataform-modal
```

## How to Use

```php
use Backpack\DataFormModal\Http\Controllers\Operations\CreateInModalOperation;
use Backpack\DataFormModal\Http\Controllers\Operations\UpdateInModalOperation;

class MonsterCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use CreateInModalOperation;
    use UpdateInModalOperation;
}
```

Traits register routes (`create-in-modal`, `store-in-modal`, `edit-in-modal`, `update-in-modal`), add Create (top) / Update (line) buttons, and provide controller methods. Define fields in `setupCreateInModalOperation()` or reuse `setupCreateOperation()`.

## Blade Component

```blade
<x-bp-dataform-modal
    :controller="\App\Http\Controllers\Admin\MonsterCrudController::class"
    formOperation="createInModal"
    :title="'Create Monster'"
    refresh-datatable="true"
/>
```

### Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `controller` | `string` | *(required)* | FQCN of the CrudController |
| `formOperation` | `string` | `createInModal` | `createInModal` or `updateInModal` |
| `formId` | `string` | `backpack-form` | DOM id for the modal |
| `formUrl` | `string\|null` | Auto | GET URL for form HTML |
| `formAction` | `string\|null` | Auto | POST/PUT URL for submission |
| `formMethod` | `string\|null` | Auto | `post` or `put` |
| `hasUploadFields` | `bool` | — | Sets `enctype="multipart/form-data"` |
| `entry` | — | — | Model instance for edit forms |
| `setup` | `Closure` | — | Configure isolated CRUD panel |
| `title` | `string` | — | Modal title |
| `classes` | `string` | — | CSS classes (e.g. `modal-dialog modal-lg`) |
| `refreshDatatable` | `bool` | — | Refresh DataTable on success |

## Controller Behavior

- `createInModal()` / `editInModal()` — return `backpack.dataform-modal::component.ajax_response` with form HTML.
- `storeInModal()` / `updateInModal()` — validate, create/update entry, flash success, return `performSaveAction()` result.

## Gotchas
- Can use alongside normal `CreateOperation` / `UpdateOperation`.
- Override trait methods for custom behavior; keep route names and response conventions for JS error/success handling.
- Upload fields in the modal need `hasUploadFields` set to `true` on the component.
