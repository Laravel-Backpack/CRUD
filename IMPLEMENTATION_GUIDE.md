# Step-by-Step Implementation Guide

## Quick Overview

This guide shows exactly where to place the code and how to integrate it into your Backpack CRUD project.

## Step 1: Verify the Trait Files Are in Place

The solution consists of 2 core files. **These should already be created for you:**

### File 1: `src/app/Library/CrudPanel/Traits/TranslatableSearch.php`

**Location**: `c:\xampp\htdocs\CRUD\CRUD\src\app\Library\CrudPanel\Traits\TranslatableSearch.php`

This file provides database-specific JSON search logic for translatable fields.

### File 2: Modified `src/app/Library/CrudPanel/Traits/Search.php`

**Location**: `c:\xampp\htdocs\CRUD\CRUD\src\app\Library\CrudPanel\Traits\Search.php`

This file now:
- Imports the `TranslatableSearch` trait
- Checks if search fields are translatable
- Routes translatable JSON fields to case-insensitive search

**Changes made:**
```php
// Added at top of trait
use TranslatableSearch;

// Updated applySearchLogicForColumn() method
// Added check before searching text/email/textarea:
if ($this->isTranslatableField($column['name']) && $this->isJsonColumn($column['name'])) {
    $this->applyTranslatableJsonSearch($query, $column, $searchTerm, $searchOperator);
} else {
    // existing logic
}
```

## Step 2: Create Your Model with Translatable Fields

Create this at: `app/Models/Company.php`

```php
<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use CrudTrait;
    use HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'email',
        'website',
    ];

    protected $translatable = [
        'name',
        'description',
    ];
}
```

**Key Points:**
- Include `HasTranslations` trait
- List translatable fields in `$translatable` property
- These will be stored as JSON in database

## Step 3: Create Database Migration

Create this at: `database/migrations/YYYY_MM_DD_HHMMSS_create_companies_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
};
```

**Run migration:**
```bash
php artisan migrate
```

## Step 4: Create CRUD Controller

Create this at: `app/Http/Controllers/Admin/CompanyCrudController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

class CompanyCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Company');
        $this->crud->setRoute(config('backpack.base.route_prefix').'/company');
        $this->crud->setEntityNameStrings('company', 'companies');
    }

    protected function setupListOperation()
    {
        // These fields will automatically use case-insensitive search
        $this->crud->setColumnDetails('name', [
            'label'      => 'Company Name',
            'type'       => 'text',
            'searchable' => true,
        ]);

        $this->crud->setColumnDetails('description', [
            'label'      => 'Description',
            'type'       => 'textarea',
            'searchable' => true,
        ]);

        $this->crud->setColumnDetails('email', [
            'label'      => 'Email',
            'type'       => 'email',
            'searchable' => true,
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setFieldDetails('name', [
            'label'    => 'Company Name',
            'type'     => 'text',
            'required' => true,
        ]);

        $this->crud->setFieldDetails('description', [
            'label' => 'Description',
            'type'  => 'textarea',
        ]);

        $this->crud->setFieldDetails('email', [
            'label' => 'Email',
            'type'  => 'email',
        ]);

        $this->crud->setFieldDetails('website', [
            'label' => 'Website',
            'type'  => 'url',
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
```

## Step 5: Register the Route

Add to your `routes/web.php` or admin routes file:

```php
Route::middleware(['auth:backpack', 'web'])->prefix('admin')->group(function () {
    Route::crud('company', 'Admin\CompanyCrudController');
});
```

## Step 6: Test the Implementation

### Test via Browser

1. **Visit the list page**: `http://localhost/admin/company`

2. **Insert some test data**:
   - Name: "Smith Manufacturing" (English) / "Manufacture Smith" (French)
   - Description: "Professional Industrial Services"

3. **Test searches**:
   - Search "smith" → Should find "Smith Manufacturing"
   - Search "SMITH" → Should find "Smith Manufacturing"
   - Search "smith" → Should find "Smith Manufacturing"
   - Search "manufacturing" → Should find "Smith Manufacturing"

### Test via Tinker

```bash
php artisan tinker
```

```php
// Create test data
$company = \App\Models\Company::create([
    'name' => 'TechVision Solutions',
    'description' => 'Technology Consulting',
    'email' => 'contact@techvision.com',
    'website' => 'https://techvision.com',
]);

// Verify translatable attributes are set
$company->getTranslations('name');
// Output: ['en' => 'TechVision Solutions']

// Verify JSON storage
$company->fresh(); // Get fresh from DB
$company->getRawOriginal('name');
// Output: '{"en":"TechVision Solutions"}'

// Test your CRUD search
$companies = \App\Models\Company::where(function ($query) {
    $searchTerm = 'techvision';
    $locale = app()->getLocale();
    $tableName = (new \App\Models\Company())->getTable();
    
    // This is what the code executes internally
    $query->orWhereRaw(
        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(`$tableName`.`name`, ?))) LIKE ?",
        ["\$$locale", '%'.strtolower($searchTerm).'%']
    );
})->get();

$companies->count(); // Should be 1
```

### Run Automated Tests

```bash
# Run the translatable search tests
php artisan test tests/Feature/TranslatableJsonSearchTest.php

# Run with verbose output
php artisan test tests/Feature/TranslatableJsonSearchTest.php -v

# Run specific test
php artisan test tests/Feature/TranslatableJsonSearchTest.php --filter test_case_insensitive_search_on_translatable_field
```

## Step 7: Verify Database-Specific Configuration

Check your `.env` file to ensure the database driver is set:

```env
# For MySQL (default)
DB_CONNECTION=mysql

# For PostgreSQL
DB_CONNECTION=pgsql

# For SQLite
DB_CONNECTION=sqlite
```

The code automatically detects your database and uses the appropriate JSON syntax.

## Step 8: Troubleshooting

### Issue: Search returns no results

**Cause**: Field is not properly translatable or not JSON

**Fix**:
```bash
php artisan tinker

# Check 1: Is field translatable?
\App\Models\Company::first()->isTranslatableAttribute('name')
// Should return true

# Check 2: Is column JSON?
$company = new \App\Models\Company();
$company->getDbTableSchema()->getColumnType('name')
// Should return 'json'

# Check 3: Is locale correct?
app()->getLocale() // Should match JSON key
```

### Issue: Search is case-sensitive

**Cause**: Model doesn't implement `HasTranslations` trait or field isn't JSON

**Fix**: Ensure:
1. Model uses `HasTranslations` trait
2. Field is in `$translatable` array
3. Database column type is `json`
4. Model's translatable detector works:
   ```php
   // In your controller
   dd($this->crud->model->isTranslatableAttribute('name'));
   ```

### Issue: Getting SQL error about JSON functions

**Cause**: Database doesn't support JSON functions for your version

**Fix**: 
- MySQL: Upgrade to 5.7.8+
- PostgreSQL: Ensure version 9.2+
- SQLite: Update to 3.38.0+

Alternatively, override the search logic:
```php
$this->crud->setColumnDetails('name', [
    'label' => 'Name',
    'type' => 'text',
    'searchLogic' => false, // Disable search for this column
]);
```

## Step 9: Performance Optimization

### For Large Datasets

Add database indexes:

```php
// In your migration's up() method
Schema::table('companies', function (Blueprint $table) {
    // MySQL: Full-text search on JSON
    $table->fullText('name')->change();
    $table->fullText('description')->change();
    
    // Or add regular indexes
    $table->index('status');
});
```

### Monitor Query Performance

```bash
# Enable query logging
php artisan tinker

DB::listen(function ($query) {
    echo $query->sql . ' (' . $query->time . 'ms)' . PHP_EOL;
});

// Run your search through CRUD
```

## Complete File Checklist

Before going to production, ensure you have:

- [ ] `src/app/Library/CrudPanel/Traits/TranslatableSearch.php` ✓ (Created)
- [ ] `src/app/Library/CrudPanel/Traits/Search.php` ✓ (Modified)
- [ ] `app/Models/Company.php` (Create in your project)
- [ ] `database/migrations/*_create_companies_table.php` (Create in your project)
- [ ] `app/Http/Controllers/Admin/CompanyCrudController.php` (Create in your project)
- [ ] Route registered in `routes/web.php`
- [ ] `.env` configured with correct `DB_CONNECTION`
- [ ] Model has `HasTranslations` trait
- [ ] Model has `$translatable` property
- [ ] Database columns are `json` type
- [ ] Tests pass

## Next Steps

1. **Test with your data**: Create real test data and verify search works
2. **Performance test**: Check query times with realistic dataset
3. **Integration test**: Test across all supported languages
4. **Deploy**: Follow normal deployment procedures

## Getting Help

If something doesn't work:

1. Check the troubleshooting section above
2. Review the TRANSLATABLE_SEARCH_IMPLEMENTATION.md file
3. Run automated tests to verify the setup
4. Check Laravel and Backpack documentation for similar issues
