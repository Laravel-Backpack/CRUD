# Backpack CRUD — Testing

Automatically generated Feature tests for CrudControllers. Requires `backpack/test-generators`.

**⚠️ `backpack/test-generators` is a paid add-on.** Check if the user has it installed before suggesting `backpack:tests`. Do NOT run `composer require` for it — the user must purchase and configure credentials first. See `rules/pro-features.md`.

The FREE alternative is writing Pest/PHPUnit tests manually — this file includes templates and patterns you can use.

## Setup

```bash
composer require --dev backpack/test-generators
php artisan backpack:tests
```

## Commands

```bash
# Generate tests
php artisan backpack:tests
php artisan backpack:tests --controller=UserCrudController
php artisan backpack:tests --operation=list
php artisan backpack:tests --framework=pest      # Pest instead of PHPUnit
php artisan backpack:tests --force               # overwrite existing

# Check test coverage status
php artisan backpack:tests:status
php artisan backpack:tests:status --controller=UserCrudController
```

## Generated File Structure

```
tests/Feature/Admin/
├─ SomeCrudControllerTest.php    # extends DefaultTestBase
├─ AnotherCrudControllerTest.php
└─ PetShop/
    ├─ OwnerCrudControllerTest.php
    └─ PetCrudControllerTest.php
```

## Test Traits and Variables

| Trait | Variables to set in setUp() |
|-------|---------------------------|
| `DefaultCreateTests` | `$createInput`, `$assertCreateInput` |
| `DefaultUpdateTests` | `$updateInput`, `$assertUpdateInput` |
| `DefaultListTests` | (inspects CRUD config automatically) |
| `DefaultShowTests` | (inspects CRUD config automatically) |

```php
protected function setUp(): void
{
    parent::setUp();

    // Submit additional data with create/update
    $this->createInput = array_merge($this->model::factory()->make()->toArray(), [
        'avatar' => ['url' => 'https://example.com/image.jpg'],
    ]);

    // What to assert in the database (may differ from submit)
    $this->assertCreateInput = ['name' => 'Test', 'email' => 'test@example.com'];
}
```

## Route Parameters (nested resources)

```php
public string $route = 'pet-shop/owner/1/pets';
public array $routeParameters = ['owner' => 1];
```

## Overriding Trait Methods

Alias the original when overriding:

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

## Customizing Test Stubs

```bash
php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag=stubs
```

Edits files in `resources/views/vendor/backpack/crud/stubs/testing/`.

## Requirements

- Models with CrudControllers need Factories and Seeders.
- Configure admin user roles/permissions in `tests/Feature/Backpack/DefaultTestBase.php`.
- If using PermissionManager, the DefaultTestBase includes commented example code.

## Gotchas
- Factory-column mismatch: if a Factory creates `city_id` but the field is `city`, set both in `$createInput`.
- Password confirmation: manually set matching `password` + `password_confirmation` in `$createInput`/`$updateInput`.
- Run specific tests: `php artisan test --filter="usercrud"` or `php artisan test --filter="crud"`.
