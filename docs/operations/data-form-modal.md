# Data Form Modal Operation

## About

Opens Create/Update forms inside a Bootstrap modal via AJAX, avoiding page reloads. Provides `CreateInModalOperation` and `UpdateInModalOperation` traits for CrudControllers.

Routes: `create-in-modal`, `store-in-modal`, `edit-in-modal`, `update-in-modal`.

Flow: button click → GET form HTML → places in modal → user submits → POST/PUT via AJAX → on validation errors, modal content is replaced with error form → on success, modal closes and table refreshes.

## Installation

```bash
composer require backpack/dataform-modal
```

## How to Use

Use the traits in your CrudController:

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

The traits register modal-specific routes, add Create (top) / Update (line) buttons that open the modal, and provide `createInModal`, `storeInModal`, `editInModal`, `updateInModal` controller methods.

You can use `CreateInModalOperation` alongside the normal `CreateOperation`. Define fields in `setupCreateInModalOperation()` (or reuse `setupCreateOperation()` if already defined).

## Blade Component

The package provides `<x-bp-dataform-modal>` (`Backpack\DataFormModal\View\Components\DataformModal`) which renders the modal markup and AJAX load/submit JS. The button partials include this component automatically.

Manual usage:

```blade
<x-bp-dataform-modal
    :controller="\App\Http\Controllers\Admin\MonsterCrudController::class"
    formOperation="createInModal"
    :title="'Create Monster'"
    refresh-datatable="true"
/>
```

### Component Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `controller` | `string` | *(required)* | Fully-qualified controller class |
| `formOperation` | `string` | `createInModal` | Operation to isolate: `createInModal` or `updateInModal` |
| `formId` | `string` | `backpack-form` | DOM id for the modal |
| `formUrl` | `string\|null` | Auto from controller + formOperation | GET URL returning form HTML |
| `formAction` | `string\|null` | Auto from controller endpoint | POST/PUT URL for form submission |
| `formMethod` | `string\|null` | Auto | HTTP verb: `post` or `put` |
| `hasUploadFields` | `bool` | — | Whether form contains file inputs (sets `enctype`) |
| `entry` | — | — | Model instance for edit forms |
| `setup` | `Closure` | — | Closure to configure the isolated CRUD panel |
| `title` | `string` | — | Modal title |
| `classes` | `string` | — | Modal dialog classes (e.g. `modal-dialog modal-lg`) |
| `refreshDatatable` | `bool` | — | Refresh the DataTable on success |

## Controller Behavior

- `createInModal()` / `editInModal()` — return `backpack.dataform-modal::component.ajax_response` view with form HTML.
- `storeInModal()` / `updateInModal()` — run CrudPanel validation, create/update the entry, flash success alert, return `performSaveAction()` result for the modal JS.

Override trait methods in your controller for custom behavior, keeping route names and response conventions for JS error/success handling.