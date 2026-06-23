# Backpack CRUD — Filters

Filters appear above the List table. Defined inside `setupListOperation()`. **All filters require `backpack/pro`.**

## Filter Types

| Type | Description |
|------|-------------|
| `text` | Text input search |
| `select2` | Dropdown from array or model |
| `select2_multiple` | Multi-select dropdown |
| `select2_ajax` | AJAX-loaded dropdown |
| `date` | Single date picker |
| `date_range` | Date range picker |
| `simple` | Toggle checkbox |
| `view` | Custom Blade view |
| `dropdown` | Simple HTML dropdown |
| `range` | Numeric range slider |

## API

```php
// whenActive pattern (recommended)
CRUD::filter('name')
    ->type('text')
    ->label('Name')
    ->whenActive(function ($value) {
        CRUD::addClause('where', 'name', 'LIKE', '%' . $value . '%');
    });

// With fallback when filter is NOT active
CRUD::filter('active_only')
    ->type('simple')
    ->label('Active only')
    ->whenActive(fn () => CRUD::addClause('where', 'active', 1))
    ->else(fn () => CRUD::addClause('where', 'active', 0));

// Logic pattern (auto-applied regardless of active state)
CRUD::filter('category')
    ->type('select2')
    ->label('Category')
    ->values(fn () => Category::pluck('name', 'id'))
    ->logic(fn ($val) => $val ? CRUD::addClause('where', 'category_id', $val) : null);

// Remove
CRUD::filter('name')->remove();
CRUD::removeFilter('category_id');
CRUD::removeAllFilters();
```

## Filter Examples

### text
```php
CRUD::filter('name')
    ->type('text')
    ->label('Name')
    ->whenActive(fn ($val) => CRUD::addClause('where', 'name', 'LIKE', "%{$val}%"));
```

### select2 (from array)
```php
CRUD::filter('status')
    ->type('select2')
    ->label('Status')
    ->values(['draft' => 'Draft', 'published' => 'Published'])
    ->whenActive(fn ($val) => CRUD::addClause('where', 'status', $val));
```

### select2 (from model)
```php
CRUD::filter('category_id')
    ->type('select2')
    ->label('Category')
    ->values(fn () => Category::pluck('name', 'id')->toArray())
    ->whenActive(fn ($val) => CRUD::addClause('where', 'category_id', $val));
```

### select2_multiple
```php
CRUD::filter('tags')
    ->type('select2_multiple')
    ->label('Tags')
    ->values(fn () => Tag::pluck('name', 'id')->toArray())
    ->whenActive(function ($values) {
        $values = json_decode($values);
        if (! empty($values)) {
            CRUD::addClause('whereHas', 'tags', fn ($q) => $q->whereIn('id', $values));
        }
    });
```

### select2_ajax
```php
CRUD::filter('category_id')
    ->type('select2_ajax')
    ->label('Category')
    ->values(fn () => Category::pluck('name', 'id')->toArray())
    ->whenActive(fn ($val) => CRUD::addClause('where', 'category_id', $val));
```

### date / date_range
```php
CRUD::filter('created_at')
    ->type('date')
    ->label('Created on')
    ->whenActive(fn ($val) => CRUD::addClause('whereDate', 'created_at', $val));

CRUD::filter('created_between')
    ->type('date_range')
    ->label('Created between')
    ->whenActive(function ($value) {
        $dates = json_decode($value);
        CRUD::addClause('where', 'created_at', '>=', $dates->from);
        CRUD::addClause('where', 'created_at', '<=', $dates->to . ' 23:59:59');
    });
```

### simple (toggle)
```php
CRUD::filter('is_active')
    ->type('simple')
    ->label('Active only')
    ->whenActive(fn () => CRUD::addClause('where', 'is_active', 1));
```

### view (custom)
```php
CRUD::filter('custom')
    ->type('view')
    ->view('vendor.backpack.crud.filters.custom')
    ->whenActive(fn ($val) => /* ... */);
```

## Reserved Filter Names

**NEVER use these as filter names:** `length`, `draw`, `start`, `search`, `totalEntryCount`, `columns`, `datatable_id`.

## Gotchas
- `whenActive()` receives the filter value as a string. For multi-select, it's a JSON string — always `json_decode()` it.
- `date_range` value is a JSON string with `from` and `to` properties.
- Always append ` 23:59:59` to date_range `to` for inclusive end-of-day filtering.
- `logic()` runs regardless of whether the filter is active — use `whenActive()` + `else()` for conditional logic.
- Filters are registered in order. Use `CRUD::filter('name')->before('other')` to reorder.
