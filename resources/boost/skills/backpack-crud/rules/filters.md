# Backpack CRUD — Filters

Filters appear above the list table. They narrow down results in real time. **Requires `backpack/pro`.**

Defined inside `setupListOperation()`.

## API

```php
CRUD::filter('name')
    ->type('text')
    ->label('Name')
    ->whenActive(function ($value) {
        CRUD::addClause('where', 'name', 'LIKE', '%' . $value . '%');
    });
```

## Common Filter Types

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
    ->values(fn () => \App\Models\Category::pluck('name', 'id')->toArray())
    ->whenActive(fn ($val) => CRUD::addClause('where', 'category_id', $val));
```

### date range
```php
CRUD::filter('created_at')
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

## Gotchas
- Reserved filter names — do NOT use: `length`, `draw`, `start`, `search`, `totalEntryCount`, `columns`, `datatable_id`.
- `whenActive()` closure receives the filter value as a string.
- For `date_range`, the value is a JSON string — always `json_decode` it.
- Filters are a PRO feature. They won't work without `backpack/pro` installed.
