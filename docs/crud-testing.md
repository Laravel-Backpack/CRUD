# CRUD Testing

## About

Generates Feature tests for CrudControllers automatically. Covers List, Create, Update, Delete, and Show operations. Tests inspect the CrudController configuration (fields, columns, validation rules) to generate relevant assertions.

## Installation

```bash
composer require --dev backpack/test-generators
```

## Generate Tests

**Step 1.** Generate tests:

```bash
php artisan backpack:tests
```

Scans the controllers directory (configurable via `backpack.testing.controllers_path`) and generates test files for all supported operations.

**Step 2.** Configure `tests/Feature/Backpack/DefaultTestBase.php` — ensure the admin user used for testing has the correct roles/permissions. If using PermissionManager, the file includes commented example code.

**Step 3.** Models need Factories and Seeders. If using DevTools they should already exist. Otherwise generate them.

**Step 4.** Run tests:

```bash
# All CRUD tests
php artisan test --filter="crud"

# Specific CRUD
php artisan test --filter="usercrud"
```

### Options

| Option | Description |
|--------|-------------|
| `--controller=Name` | Generate tests for a specific controller (e.g. `UserCrudController`) |
| `--operation=list` | Generate tests for a specific operation (list, create, update, etc.) |
| `--type=feature` | Test type (`feature` only currently) |
| `--framework=phpunit` | Testing framework: `phpunit` or `pest`. Default: `phpunit` |
| `--path=` | Override controllers path from config |
| `--force` | Overwrite existing test classes |

### Examples

```bash
php artisan backpack:tests
php artisan backpack:tests --controller=UserCrudController
php artisan backpack:tests --operation=list
```

## Test Status

```bash
php artisan backpack:tests:status
```

Output:

```
────────────────────────────────────────────
✓ MonsterCrudController  List · Create · Update
✗ UserCrudController     List · Create
────────────────────────────────────────────
Total: 2  Tested: 1  Missing: 1
```

Options: `--controller=Name`, `--type=feature`.

## Generated File Structure

```
tests/Feature/Admin/
├─ SomeCrudControllerTest.php     # extends DefaultTestBase, uses operation traits
├─ AnotherCrudControllerTest.php

tests/Feature/Admin/PetShop/      # subfolders respected
├─ OwnerCrudControllerTest.php
├─ PetCrudControllerTest.php
```

A `Backpack` folder is created containing base test classes reused by each CrudController test.

## Operation Test Traits

| Trait | Exposes |
|-------|---------|
| `DefaultCreateTests` | `$createInput`, `$assertCreateInput` |
| `DefaultUpdateTests` | `$updateInput`, `$assertUpdateInput` |
| `DefaultListTests` | Inspects CRUD config via test helper |
| `DefaultShowTests` | Inspects CRUD config via test helper |

In `setUp()`, set `$createInput` / `$updateInput` for additional or transformed data. Use `$assertCreateInput` / `$assertUpdateInput` when the DB assertion differs from raw submission (e.g. exclude `password` or file metadata).

```php
$this->createInput = array_merge($this->model::factory()->make()->toArray(), [
    'avatar' => ['url' => 'https://lorempixel.com/400/200/animals'],
]);
```

## Route Parameters

For nested resources requiring route parameters:

```php
public string $route = 'pet-shop/owner/1/pets';
public array $routeParameters = ['owner' => 1];
```

## Overriding Trait Behavior

Alias the original method when overriding:

```php
use \Tests\Feature\Backpack\DefaultUpdateTests {
    test_update_page_loads_successfully as default_test_update_page_loads_successfully;
}

public function test_update_page_loads_successfully(): void
{
    $this->skipIfModelDoesNotHaveFactory();
    $entry = $this->model::factory()->create();
    $entry->owners()->attach(1, ['role' => 'Owner']);
    $response = $this->get($this->testHelper->getCrudUrl($entry->getKey().'/edit'));
    $response->assertStatus(200);
}
```

### Checklist

- Set `$routeParameters` and `$route` for controllers needing route parameters
- Create related models in `setUp()` (owners, categories, etc.)
- Set `$createInput` / `$updateInput` for structured data (files, nested arrays, relationship ids)
- Use `$assertCreateInput` / `$assertUpdateInput` to shape expected DB assertions
- Override trait methods only when needed; alias to keep default behavior

## Configuration

```bash
php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag=config
```

Change the controllers path via `backpack.testing.controllers_path`.

## Customizing Test Stubs

```bash
php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag=stubs
```

Creates `resources/views/vendor/backpack/crud/stubs/testing`. Example custom operation stub (`clone.stub`):

```php
<?php

namespace Tests\Feature\Backpack;

trait DefaultCloneTests
{
    public function test_create_clone_button_is_on_page(): void
    {
        $response = $this->get($this->testHelper->getCrudUrl('list'));
        $response->assertStatus(200);
        $response->assertSee('bp-button="clone"', true);
    }
}
```

## Troubleshooting

### "The field X is required"

```
FAILED  Tests\Feature\Admin\VenueCrudControllerTest > update endpoint modifies entry in database
Session has unexpected errors: {"default": ["The city field is required."]}
```

Factory is missing a related model required by the CrudController. Fix by:
- (a) updating the factory to include the related model
- (b) removing the required validation from the CRUD

Or when factory uses `city_id` but the field is `city`:

```php
protected function setUp(): void
{
    parent::setUp();
    $data = Venue::factory()->raw();
    $data['city'] = $data['city_id'];
    $this->createInput = $data;
    $this->updateInput = $data;
}
```

### "The password field confirmation does not match"

```
FAILED  Tests\Feature\Admin\UserCrudControllerTest > update endpoint modifies entry in database
Session has unexpected errors: {"default": ["The password field confirmation does not match."]}
```

Hard-code the input in the test:

```php
protected function setUp(): void
{
    parent::setUp();

    $this->createInput = [
        'name'                  => 'Test User',
        'email'                 => 'testuser@example.com',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $this->assertCreateInput = [
        'name'  => 'Test User',
        'email' => 'testuser@example.com',
    ];

    $this->updateInput = [
        'name'                  => 'Updated User',
        'email'                 => 'updateduser@example.com',
        'password'              => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ];

    $this->assertUpdateInput = [
        'name'  => 'Updated User',
        'email' => 'updateduser@example.com',
    ];
}
```
