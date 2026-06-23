# Backpack DevTools

## About

A web interface to generate migrations, models, and full CRUDs for Laravel Backpack projects. Fill in one form and get a complete working entity — no command line needed.

Generates:
- Migration with columns and relationships
- Model with `$fillable` and relationships
- Factory and seeder
- CrudController with fields and columns pre-defined
- CrudRequest with validation rules inferred from database
- Route and sidebar item

Access at `/admin/devtools` after installation.

## Requirements

- Laravel 8+
- PHP 7.3+
- MySQL 5.7.x / 8.x, or SQLite 3.36+
- backpack/crud v4.1+ properly installed

## Installation

### Quick Install

```bash
php artisan backpack:require:devtools
```

Prompts for token and password (available from your Backpack account after purchase).

### Manual Install

**Step 1.** Add your token:

```bash
composer config http-basic.backpackforlaravel.com [your-token-username] [your-token-password]
```

Add to `composer.json`:

```json
"repositories": [
    {
        "type": "composer",
        "url": "https://repo.backpackforlaravel.com/"
    }
]
```

**Step 2.** Install:

```bash
# Recommended — get latest with updated dependencies
composer require --dev --with-all-dependencies backpack/devtools

# Alternative — install without updating other packages
composer require --dev backpack/devtools
```

Common errors: `composer require conflict` or `Class X not auto-loaded` — run `composer update`.

**Step 3.** Run the installer:

```bash
php artisan backpack:devtools:install
```

### Production Safety

DevTools must NOT be present in staging/production:
- Run `composer install --no-dev` during deployment
- Or run `composer remove --dev backpack/devtools` after generating code

## Features

### Generate Entities

Fill in a single form to generate a complete entity with migration, model, factory, seeder, CrudController, CrudRequest, route, and sidebar item.

### Manage Migrations

View all migrations in a web interface. See which are run, run them, roll them back, or open them in your editor.

### Manage Models

View all models, see which have CRUDs/factories/seeders, and insert dummy data directly.

### Generate Custom Components

Generate custom Backpack blade files (columns, fields, filters, buttons, widgets), custom Operations, and custom pages (e.g. dashboards) from templates.