# Backpack CRUD — DevTools

Add-on package `backpack/devtools`. Provides a web-based UI for generating migrations, models, and full CRUD panels through a browser interface instead of the command line.

**⚠️ `backpack/devtools` is a paid add-on.** Check if the user has it installed before suggesting the web UI. Do NOT run `composer require` for it — the user must purchase and configure credentials first. See `rules/pro-features.md`.

**FREE alternative**: Everything DevTools does can be done with free Artisan commands. The web UI is a convenience layer — the underlying code generation is the same.

---

## What DevTools Provides

| Web UI Feature | Free Artisan Equivalent |
|---------------|------------------------|
| Generate a CRUD panel | `php artisan backpack:crud ModelName` |
| Generate a model | `php artisan make:model ModelName -m` |
| Generate a migration | `php artisan make:migration create_table_name_table` |
| Generate a controller | `php artisan make:controller Admin/ModelNameCrudController` |
| Generate a request | `php artisan make:request ModelNameRequest` |
| View table structure | `php artisan db:table table_name` or Boost's `database-schema` |
| Browse database | Boost MCP `database-query` or `php artisan tinker` |

---

## FREE Workflow (without DevTools)

Everything can be done from the command line. The typical flow:

```bash
# 1. Create the migration
php artisan make:migration create_products_table

# 2. Write the migration schema, then run it
php artisan migrate

# 3. Create the model
php artisan make:model Product

# 4. Generate the full CRUD (controller, request, route, menu)
php artisan backpack:crud Product

# 5. Customize the CrudController
# Edit app/Http/Controllers/Admin/ProductCrudController.php
```

For inspecting database structure without DevTools, use the Boost MCP tools:

```
database-schema  → see all tables and columns
database-query   → run SELECT queries
```

Or use Artisan:

```bash
php artisan db:table products
php artisan tinker --execute '\App\Models\Product::first()->toArray();'
```

---

## When DevTools Helps

The web UI is most useful when:
- You prefer a visual interface over the command line
- You want to browse the database schema without writing queries
- You're onboarding developers who aren't comfortable with Artisan

For all other cases, the free Artisan commands provide the same code generation with the same output — the generated files are identical regardless of whether you used the web UI or the command line.
