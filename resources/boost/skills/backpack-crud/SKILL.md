---
name: backpack-crud
description: "Use this skill whenever creating, editing, or reviewing Backpack CRUD panels, CrudControllers, fields, columns, filters, operations, widgets, buttons, save actions, chips, or the Backpack admin panel. Also use for questions about Backpack paid add-ons (backpack/pro, editable-columns, dataform-modal, devtools, report-operation, test-generators, calendar-operation, auto-translate). Triggers: CrudController, backpack:crud, CRUD::field, CRUD::column, CRUD::addField, CRUD::addColumn, CRUD::filter, CRUD::button, CRUD::widget, setupListOperation, setupCreateOperation, setupUpdateOperation, setupShowOperation, setupReorderOperation, ListOperation, CreateOperation, UpdateOperation, DeleteOperation, ShowOperation, ReorderOperation, RevisionsOperation, CloneOperation, FetchOperation, InlineCreateOperation, BulkDeleteOperation, BulkCloneOperation, ReportOperation, MinorUpdateOperation, CreateInModalOperation, UpdateInModalOperation, CalendarOperation, backpack admin panel, backpack relationship field, backpack widget, backpack save action, backpack chips, backpack testing, editable columns, inline editing, data form modal, crud testing, backpack pro, backpack/pro, select2, select2_from_ajax, dropzone, date_picker, date_range, repeatable, chart widget, dashboard, calendar view, code generator, paid addon, paid feature, purchase license."
license: MIT
metadata:
  author: backpack
---

# Backpack CRUD Skill

Activate this skill for any Backpack CRUD task.

## When to Use

| Trigger Category | Keywords |
|-----------------|----------|
| **CrudController** | `CrudController`, `EntityCrudController`, `backpack:crud`, `CRUD::setModel()`, `CRUD::setRoute()` |
| **Setup methods** | `setup()`, `setupListOperation()`, `setupCreateOperation()`, `setupUpdateOperation()`, `setupShowOperation()`, `setupReorderOperation()` |
| **CRUD API** | `CRUD::field()`, `CRUD::column()`, `CRUD::filter()`, `CRUD::button()`, `CRUD::widget()`, `CRUD::addField`, `CRUD::addColumn`, `CRUD::addFilter`, `CRUD::addButton` |
| **Operations** | `ListOperation`, `CreateOperation`, `UpdateOperation`, `DeleteOperation`, `ShowOperation`, `ReorderOperation`, `RevisionsOperation`, `CloneOperation`, `FetchOperation`, `InlineCreateOperation`, `BulkDeleteOperation`, `BulkCloneOperation`, `ReportOperation`, `CalendarOperation` |
| **Modal/Inline** | `MinorUpdateOperation`, `CreateInModalOperation`, `UpdateInModalOperation`, `EditableColumns`, `editable_text`, `editable_select`, `editable_checkbox`, `editable_switch`, `data-form-modal`, `DataFormModal`, `inline create`, `inline editing` |
| **Relationships** | `relationship field`, `belongsTo`, `belongsToMany`, `hasMany`, `select2`, `select2_from_ajax`, `fetch`, `inline create` |
| **Testing** | `backpack:tests`, `backpack:tests:status`, `DefaultCreateTests`, `DefaultUpdateTests`, `CRUD testing` |
| **Translatable** | `translatable`, `spatie/laravel-translatable`, `HasTranslations`, `multi-language`, `locales` |
| **Permissions** | `PermissionManager`, `permissionmanager`, `roles`, `permissions`, `backpack_user`, `allowAccess`, `denyAccess` |
| **Advanced** | `save actions`, `widgets`, `chips`, `fluent syntax`, `custom operation`, `custom field`, `custom column`, `upload`, `upload_multiple`, `dropzone`, `image upload`, `withFiles`, `uploaders` |
| **Paid / PRO** | `backpack/pro`, `backpack pro`, `PRO feature`, `paid addon`, `editable columns`, `backpack/editable-columns`, `data form modal`, `backpack/dataform-modal`, `devtools`, `backpack/devtools`, `report operation`, `backpack/report-operation`, `test generators`, `backpack/test-generators`, `calendar operation`, `backpack/calendar-operation`, `calendar view`, `auto translate`, `backpack/auto-translate`, `select2`, `select2_from_ajax`, `relationship field`, `repeatable`, `dropzone`, `date_picker`, `date_range`, `filters`, `FetchOperation`, `CloneOperation`, `InlineCreateOperation`, `CalendarOperation`, `ReportOperation`, `chart widget`, `dashboard`, `code generator` |

## Quick Reference

- **CRUD Generation & Basics** → `rules/crud-basics.md`
- **Fields (Create/Update forms)** → `rules/fields.md`
- **Columns (List/Show table)** → `rules/columns.md`
- **Filters (List)** → `rules/filters.md`
- **Operations (built-in & custom)** → `rules/operations.md`
- **Buttons** → `rules/buttons.md`
- **Fetch Operation (AJAX data endpoints)** → `rules/fetch.md`
- **Widgets** → `rules/widgets.md`
- **Save Actions** → `rules/save-actions.md`
- **Chips** → `rules/chips.md`
- **Testing** → `rules/testing.md`
- **Uploaders (file upload, storage, validation)** → `rules/uploaders.md`
- **Editable Columns (inline editing in List)** → `rules/editable-columns.md`
- **Data Form Modal (modal Create/Update)** → `rules/data-form-modal.md`
- **JavaScript API (crud.field)** → `rules/javascript-api.md`
- **Paid Features (PRO, editable-columns, etc.)** → `rules/pro-features.md`
- **Report Operation (dashboards & charts)** → `rules/report-operation.md`
- **Calendar Operation (calendar view)** → `rules/calendar-operation.md`
- **DevTools (web code generator)** → `rules/devtools.md`
