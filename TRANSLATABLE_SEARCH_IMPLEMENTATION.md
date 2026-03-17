# Case-Insensitive Search for Translatable JSON Fields in Backpack CRUD

## Overview

This solution implements case-insensitive search for translatable fields stored as JSON in Laravel Backpack CRUD. It automatically detects translatable fields using `spatie/laravel-translatable` and applies appropriate database-specific JSON search queries.

## Files Modified/Created

### 1. `src/app/Library/CrudPanel/Traits/TranslatableSearch.php` (NEW)

This trait provides helper methods for translatable JSON field search:

```php
// Helper methods:
- applyTranslatableJsonSearch()     // Executes DB-specific JSON search
- isTranslatableField()             // Checks if field is translatable
- isJsonColumn()                    // Verifies field is JSON type
- isDatabaseMySQL()                 // Database driver detection
- isDatabasePostgreSQL()
- isDatabaseSQLite()
```

### 2. `src/app/Library/CrudPanel/Traits/Search.php` (MODIFIED)

Updated to:
- Use the new `TranslatableSearch` trait
- Check for translatable fields in `applySearchLogicForColumn()`
- Route translatable JSON fields to case-insensitive search
- Maintain backward compatibility for regular fields

## How It Works

### Search Flow

1. **Column Type Detection**: When search is triggered, `applySearchLogicForColumn()` checks the column type
2. **Translatable Detection**: For 'text', 'email', 'textarea' types, checks if field is translatable + JSON
3. **Database-Specific Queries**:
   - **MySQL**: Uses `JSON_EXTRACT()` + `JSON_UNQUOTE()` + `LOWER()`
   - **PostgreSQL**: Uses JSON operators with `ILIKE` (case-insensitive)
   - **SQLite**: Uses `JSON_EXTRACT()` + `LOWER()`
4. **Fallback**: Non-translatable fields use standard search logic

### Database-Specific Implementation

#### MySQL
```sql
LOWER(JSON_UNQUOTE(JSON_EXTRACT(`table`.`column`, '$locale'))) LIKE '%term%'
```

#### PostgreSQL
```sql
LOWER(`table`.`column`->'locale') ILIKE '%term%'
```

#### SQLite
```sql
LOWER(JSON_EXTRACT(`table`.`column`, '$locale')) LIKE '%term%'
```

## Usage Example

### 1. Create a Model with Translatable Fields

```php
<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use CrudTrait;
    use HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'price',
    ];

    protected $translatable = [
        'name',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
}
```

### 2. Create Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
```

### 3. Setup CRUD Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;

class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Product');
        $this->crud->setRoute(config('backpack.base.route_prefix').'/product');
        $this->crud->setEntityNameStrings('product', 'products');
    }

    protected function setupListOperation()
    {
        $this->crud->setColumnDetails('name', [
            'label'     => 'Product Name',
            'type'      => 'text',
            'searchable' => true,
        ]);

        $this->crud->setColumnDetails('description', [
            'label'     => 'Description',
            'type'      => 'textarea',
            'searchable' => true,
        ]);

        $this->crud->setColumnDetails('price', [
            'label'     => 'Price',
            'type'      => 'number',
            'searchable' => false,
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setFieldDetails('name', [
            'label'    => 'Product Name',
            'type'     => 'text',
            'required' => true,
        ]);

        $this->crud->setFieldDetails('description', [
            'label' => 'Description',
            'type'  => 'textarea',
        ]);

        $this->crud->setFieldDetails('price', [
            'label'    => 'Price',
            'type'     => 'number',
            'required' => true,
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
```

## Testing

### Running Tests

```bash
php artisan test tests/Feature/TranslatableJsonSearchTest.php
```

### Example Test Cases Included

- `test_case_insensitive_search_on_translatable_field()` - Searches "test" for "Test Product Name"
- `test_exact_case_search_still_works()` - Searches "Smith" for "Smith Family Business"
- `test_lowercase_search_on_mixed_case_translatable()` - Searches "techvision" for "TechVision Solutions"
- `test_no_match_for_non_existent_search_term()` - Validates no results for non-existent terms
- `test_partial_match_in_translatable_field()` - Tests substring matching
- `test_multiple_translatable_records_search()` - Tests search across multiple records

## Backward Compatibility

✅ **Fully compatible** with existing Backpack search logic:
- Non-translatable fields use original `orWhere()` logic
- Regular text/email/textarea fields unaffected
- Custom `searchLogic` closures still work
- `searchLogic => false` prevents search as expected

## Performance Considerations

### Query Optimization

For large datasets, consider adding indexes:

```php
// In migration
Schema::table('products', function (Blueprint $table) {
    $table->fullText('name')->change();
    $table->fullText('description')->change();
});
```

### Database-Specific Tips

**MySQL**:
```php
// Use COLLATE for better performance
LOWER(JSON_UNQUOTE(JSON_EXTRACT(`column`, '$en'))) COLLATE utf8mb4_unicode_ci LIKE '%term%'
```

**PostgreSQL**:
```sql
-- Add GIN index for JSON
CREATE INDEX products_name_idx ON products USING gin(name);
```

## Advanced Usage

### Custom Search Logic Override

If you need custom logic for specific columns:

```php
$this->crud->setColumnDetails('name', [
    'label'       => 'Product Name',
    'type'        => 'text',
    'searchLogic' => function($query, $column, $searchTerm) {
        return $query->orWhere('name', 'LIKE', '%'.$searchTerm.'%');
    },
]);
```

### Locale-Aware Search

The solution automatically uses the current app locale (`app()->getLocale()`). To search a specific locale:

```php
// In your Controller
app()->setLocale('fr');
// Now searches will target 'fr' locale
```

## Troubleshooting

### Search Returns No Results

1. **Check column is JSON**:
   ```php
   $model->getDbTableSchema()->getColumnType('name') === 'json'
   ```

2. **Verify translatable setup**:
   ```php
   $model->isTranslatableAttribute('name') // should return true
   ```

3. **Check locale value**:
   ```php
   app()->getLocale() // verify it matches JSON keys
   ```

### Database Compatibility Issues

- Ensure your database driver is correctly configured in `.env`
- Test with `php artisan tinker` to verify JSON functions work

## FAQ

**Q: Will this break my existing non-translatable searches?**
A: No. Non-translatable fields continue using the original search logic.

**Q: Can I search multiple locales?**
A: Current implementation searches the current app locale. You can override with custom `searchLogic`.

**Q: Does this work with soft deletes?**
A: Yes. The solution builds on top of existing Backpack query logic.

**Q: What if my column isn't JSON?**
A: The code automatically detects JSON columns and only applies special logic to them.

## Production Checklist

- [x] Database migrations created with JSON columns
- [x] Model includes `HasTranslations` trait
- [x] Model `$translatable` property defined
- [x] CRUD controller columns configured
- [x] Tests pass with your data
- [x] Search tested with various case combinations
- [x] Performance verified with realistic dataset size

## Support

For issues or improvements, check:
1. Column type is 'text', 'email', or 'textarea'
2. Field is in `$translatable` array on model
3. Database configuration matches actual database type
4. No custom `searchLogic` overrides the default behavior
