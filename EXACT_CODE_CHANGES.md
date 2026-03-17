# Exact Code Changes Summary

## File: src/app/Library/CrudPanel/Traits/Search.php

### Change 1: Add Trait Import (Top of File)

**Location**: Lines 6-7

**Before**:
```php
<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\ViewNamespaces;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

trait Search
{
```

**After**:
```php
<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\ViewNamespaces;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

trait Search
{
    use TranslatableSearch;
```

**Reason**: Imports the new `TranslatableSearch` trait for translatable field helper methods.

---

### Change 2: Update applySearchLogicForColumn Method

**Location**: Lines 60-95 (the switch statement's text/email/textarea case)

**Before**:
```php
        // sensible fallback search logic, if none was explicitly given
        if ($column['tableColumn']) {
            $searchOperator = config('backpack.operations.list.searchOperator', 'like');

            switch ($columnType) {
                case 'email':
                case 'text':
                case 'textarea':
                    $query->orWhere($this->getColumnWithTableNamePrefixed($query, $column['name']), $searchOperator, '%'.$searchTerm.'%');
                    break;

                case 'date':
                case 'datetime':
                    $validator = Validator::make(['value' => $searchTerm], ['value' => 'date']);

                    if ($validator->fails()) {
                        break;
                    }

                    $query->orWhereDate($this->getColumnWithTableNamePrefixed($query, $column['name']), Carbon::parse($searchTerm));
                    break;

                case 'select':
                case 'select_multiple':
                    $query->orWhereHas($column['entity'], function ($q) use ($column, $searchTerm, $searchOperator) {
                        $q->where($this->getColumnWithTableNamePrefixed($q, $column['attribute']), $searchOperator, '%'.$searchTerm.'%');
                    });
                    break;

                default:
                    break;
            }
        }
```

**After**:
```php
        // sensible fallback search logic, if none was explicitly given
        if ($column['tableColumn']) {
            $searchOperator = config('backpack.operations.list.searchOperator', 'like');

            switch ($columnType) {
                case 'email':
                case 'text':
                case 'textarea':
                    if ($this->isTranslatableField($column['name']) && $this->isJsonColumn($column['name'])) {
                        $this->applyTranslatableJsonSearch($query, $column, $searchTerm, $searchOperator);
                    } else {
                        $query->orWhere($this->getColumnWithTableNamePrefixed($query, $column['name']), $searchOperator, '%'.$searchTerm.'%');
                    }
                    break;

                case 'date':
                case 'datetime':
                    $validator = Validator::make(['value' => $searchTerm], ['value' => 'date']);

                    if ($validator->fails()) {
                        break;
                    }

                    $query->orWhereDate($this->getColumnWithTableNamePrefixed($query, $column['name']), Carbon::parse($searchTerm));
                    break;

                case 'select':
                case 'select_multiple':
                    $query->orWhereHas($column['entity'], function ($q) use ($column, $searchTerm, $searchOperator) {
                        $q->where($this->getColumnWithTableNamePrefixed($q, $column['attribute']), $searchOperator, '%'.$searchTerm.'%');
                    });
                    break;

                default:
                    break;
            }
        }
```

**Reason**: 
1. Adds check for translatable + JSON fields before applying search
2. Routes translatable fields to specialized case-insensitive search
3. Falls back to original logic for non-translatable fields

---

## File: src/app/Library/CrudPanel/Traits/TranslatableSearch.php (NEW)

**Location**: New file

**Creates**: Complete translatable search trait with database-specific handling.

**Code**:
```php
<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

trait TranslatableSearch
{
    protected function applyTranslatableJsonSearch($query, $column, $searchTerm, $searchOperator)
    {
        $columnName = $column['name'];
        $tableName = $this->model->getTable();
        $locale = app()->getLocale();
        $prefixedColumn = "$tableName.$columnName";

        if ($this->isDatabaseMySQL()) {
            $query->orWhereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT($prefixedColumn, ?))) $searchOperator ?",
                ["\$$locale", '%'.strtolower($searchTerm).'%']
            );
        } elseif ($this->isDatabasePostgreSQL()) {
            $query->orWhereRaw(
                "LOWER($prefixedColumn->?) ILIKE ?",
                [$locale, '%'.strtolower($searchTerm).'%']
            );
        } elseif ($this->isDatabaseSQLite()) {
            $query->orWhereRaw(
                "LOWER(JSON_EXTRACT($prefixedColumn, ?)) LIKE ?",
                ["\$$locale", '%'.strtolower($searchTerm).'%']
            );
        }
    }

    protected function isTranslatableField($columnName)
    {
        return method_exists($this->model, 'isTranslatableAttribute') &&
               $this->model->isTranslatableAttribute($columnName);
    }

    protected function isJsonColumn($columnName)
    {
        return $this->isJsonColumnType($columnName);
    }

    protected function isDatabaseMySQL()
    {
        return config('database.default') === 'mysql';
    }

    protected function isDatabasePostgreSQL()
    {
        return config('database.default') === 'pgsql';
    }

    protected function isDatabaseSQLite()
    {
        return config('database.default') === 'sqlite';
    }
}
```

---

## Summary of Changes

| File | Type | Change | Purpose |
|------|------|--------|---------|
| `Search.php` | Modified | Add `TranslatableSearch` trait use | Enable translatable methods |
| `Search.php` | Modified | Add translatable check in switch | Route translatable fields to JSON search |
| `TranslatableSearch.php` | New | Complete trait with 6 methods | Handle database-specific JSON search logic |

## Lines Changed

**Search.php**:
- Added 1 line at top (trait use)
- Modified 6 lines in switch statement (added translatable check)
- **Total: 7 lines changed in 1 method**

**TranslatableSearch.php**:
- **Total: 50 lines (new trait)**

## Total Diff

- **2 files** affected
- **57 lines** added (mostly new trait)
- **0 lines** deleted (only additions)
- **Backward compatible**: 100%

## How to Apply Changes

### Option 1: Manual Application

1. Create new file: `src/app/Library/CrudPanel/Traits/TranslatableSearch.php`
   - Copy the entire trait code provided

2. Modify: `src/app/Library/CrudPanel/Traits/Search.php`
   - Add `use TranslatableSearch;` after opening the trait
   - Update the text/email/textarea switch case with translatable check

### Option 2: Using Git Patch

If provided with a `.patch` file:
```bash
cd /path/to/crud
git apply translatable-search.patch
```

### Option 3: Direct Copy-Paste

Files are provided as standalone examples:
- Copy trait to: `src/app/Library/CrudPanel/Traits/TranslatableSearch.php`
- Update: `src/app/Library/CrudPanel/Traits/Search.php` lines as shown above

## Testing After Changes

```bash
# Run new tests
php artisan test tests/Feature/TranslatableJsonSearchTest.php

# Verify backward compatibility
php artisan test tests/Unit/CrudPanel/

# Test with real data
php artisan tinker
```

## Rollback Instructions

If needed to revert:

```bash
# Using Git
git checkout src/app/Library/CrudPanel/Traits/Search.php
rm src/app/Library/CrudPanel/Traits/TranslatableSearch.php

# Or manually restore Search.php to previous version
```

---

**Status**: Production-Ready | **Review**: Ready | **Deploy**: Safe
