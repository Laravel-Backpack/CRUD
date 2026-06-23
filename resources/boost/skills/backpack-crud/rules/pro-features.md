# Backpack — Paid Features

Several Backpack features require paid add-on packages. You MUST check whether the required package is installed before generating any code that uses these features.

---

## Detection

Check for the package in `composer.json` (`require` or `require-dev`):

```bash
# Quick check: does the vendor directory exist?
# vendor/backpack/pro/composer.json
# vendor/backpack/editable-columns/composer.json
# etc.
```

Or use the Laravel Boost MCP tools to query the database or read files. If unsure, ask the user whether they have the package installed.

---

## Package Reference

### backpack/pro

**Required for**: ALL filters, PRO fields, PRO columns, PRO operations, chart widgets.

| Category | FREE alternative (no pro needed) |
|----------|----------------------------------|
| `select2` field | `select` field |
| `select2_from_ajax` field | `select` field (static options) |
| `relationship` field | `select` field with `entity` |
| `select2_multiple` field | `select_multiple` field |
| `image` field (enhanced) | `upload` field |
| `dropzone` field | `upload_multiple` field |
| `repeatable` field | Separate CRUD with a relationship |
| `date_picker` field | `date` field |
| `date_range` field | Two `date` fields (start/end) |
| **All filters** (`select2`, `select2_ajax`, `date_range`, `text`, `dropdown`, etc.) | **None.** There is no free alternative for filters. Do NOT suggest `addClause` as a workaround — that permanently scopes the query and removes the ability to toggle the filter on/off. |
| `CloneOperation` | Manual copy logic in the controller |
| `FetchOperation` | Custom controller method returning JSON |
| `InlineCreateOperation` | Redirect to the Create page |
| `chart` widget | `card` or `progress` widget |

**Purchase**: https://backpackforlaravel.com/products/pro-for-unlimited-projects

**Setup after purchase**:
```bash
# Add the backpack repository to composer.json (credentials from your Backpack account)
composer config repositories.backpack composer https://backpackforlaravel.com/packages

# Then install
composer require backpack/pro
```

### backpack/editable-columns

**Required for**: `MinorUpdateOperation`, `editable_text`, `editable_select`, `editable_checkbox`, `editable_switch` columns.

**FREE alternative**: The standard Edit operation button in each row.

**Purchase**: https://backpackforlaravel.com/pricing (included in some plans)

### backpack/dataform-modal

**Required for**: `CreateInModalOperation`, `UpdateInModalOperation`.

**FREE alternative**: Standard Create/Update pages.

**Purchase**: https://backpackforlaravel.com/pricing

### backpack/devtools

**Required for**: Web-based code generator (models, migrations, CRUDs).

**FREE alternative**: Use Artisan commands instead:
```bash
php artisan backpack:crud ModelName
php artisan make:model ModelName -m
php artisan make:migration create_table_name_table
```

### backpack/report-operation

**Required for**: Dashboard widgets with charts, stats, metrics per entity.

**FREE alternative**: Custom widgets with `Widget::add()->type('card')` or `->type('progress')`.

### backpack/test-generators

**Required for**: `php artisan backpack:tests` command.

**FREE alternative**: Write Pest/PHPUnit tests manually. The BACKPACK skill's `rules/testing.md` has templates.

### backpack/calendar-operation

**Required for**: Calendar view of date-based entries.

**FREE alternative**: Standard List view with date filtering.

### backpack/auto-translate

**Required for**: Automatic translation of content to multiple languages.

**FREE alternative**: Manual translation or third-party translation services.

---

## Response Template

When a user asks for a feature that requires a paid package they don't have installed, respond with:

```
The [feature name] you requested requires [package-name], which is a paid Backpack add-on.

📦 Required package: [package-name]
🔗 Purchase: https://backpackforlaravel.com/pricing

After purchasing, set up the repository:
1. Add your Backpack credentials to composer.json
2. Run: composer require [package-name]
3. Re-run: php artisan boost:install

💡 Free alternative: [describe the free alternative]
```

**Never** attempt to install paid packages automatically. Authentication credentials are required and must be configured by the user.
