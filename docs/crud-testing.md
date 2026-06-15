# Testing

---

<a name="about-testing"></a>
## About

Testing your CRUD panels ensures that your admin interfaces work as expected and continue to function correctly as your application evolves. Backpack provides a dedicated command to generate **Feature** tests for your CrudControllers automatically.

These generated tests cover standard operations like:
- **List**: Asserts the table loads and columns are visible.
- **Create**: Asserts the form loads, validates inputs, and stores entries.
- **Update**: Asserts the form loads with existing data and updates entries.
- **Delete**: Asserts entries can be deleted.
- **Show**: Asserts the details view loads.

The tests are designed to be "smart" — they inspect your CrudController's configuration (fields, columns, validation rules) to generate relevant assertions.

<a name="generating-tests"></a>
## Generate Tests

**Step 1.** Generate feature tests for your CRUD controllers using the artisan command:

```bash
php artisan backpack:tests
```

This will scan your controllers directory (configurable via `backpack.testing.controllers_path`) and generate test files for all supported operations.

**Step 2.** Configure `tests/Feature/Backpack/DefaultTestBase.php` to make sure the admin user that is used for testing... can actually do the things you're testing. Otherwise all your generator tests will fail (403 http status code instead of 200). This most likely means giving that admin user the correct roles/permissions. If you're using PermissionManager, that file includes some commented code for you, as example.

**Step 3.** The generated tests for CrudControllers need Factories and Seeders for those Eloquent Models, in order to work. If you're using [our DevTools package](https://backpackforlaravel.com/products/devtools), they should already be there. Otherwise, frontier LLMs will do a reasonable job of generating Factories and Seeders, here's a prompt you can use to get you started:

```
In this Laravel application, not all Eloquent Models that have a CrudController have factories and seeders. Please do a full evaluation of CrudControllers, Models and Factories and make sure we have a full suite of Factories for any model that has a CrudController, so that we can build a test suite on top of them.
```

**Step 4.** You should then run your tests, to see if there's anything left to fix (there usually is):

```bash
# how to run only the CRUD tests
php artisan test --filter="crud"

# how to run tests only for a particular CRUD
php artisan test --filter="usercrud"
```

Some of the errors you meet are to be expected. We've tried to cover the most common errors in the Troubleshooting section below. We recommend taking a look at it, when debugging your CRUD tests.

### Options

| Option | Description |
| --- | --- |
| `--controller=Name` | Only generate tests for the specific controller class name (e.g., `UserCrudController`) |
| `--operation=list` | Only generate tests for the given CRUD operation (list, create, update, etc.) |
| `--type=feature` | The type of test to generate (`feature` is currently the only supported type) |
| `--framework=phpunit` | The testing framework to use (`phpunit` or `pest`). Defaults to `phpunit` |
| `--path=` | Override the controllers path from config |
| `--force` | Overwrite existing test classes |

### Examples

Generate tests for all controllers:
```bash
php artisan backpack:tests
```

Generate tests for a specific controller:
```bash
php artisan backpack:tests --controller=UserCrudController
```

Generate only list operation tests:
```bash
php artisan backpack:tests --operation=list
```

<a name="test-status"></a>
## Test Status

You can check which of your CrudControllers have tests generated and which operations are covered using:

```bash
php artisan backpack:tests:status
```

This will display a visual overview of test coverage per controller:

```
────────────────────────────────────────────
✓ MonsterCrudController  List · Create · Update
✗ UserCrudController     List · Create
────────────────────────────────────────────
Total: 2  Tested: 1  Missing: 1
```

### Options

| Option | Description |
| --- | --- |
| `--controller=Name` | Show status for a specific controller |
| `--type=feature` | Type of tests to check |

<a name="generated-file-structure"></a>
## Generated test file structure

Generated tests rely on a small hierarchy of base classes, reusable traits and on per-controller test files inside your app's `tests/Feature` folder. 

You will notice that there will be a new "Backpack" folder. That folder contain the "base" tests that each of your crud controllers will re-use.

Each controller gets a single test file that extends `DefaultTestBase` and uses trait(s) for each operation:

```markdown
tests/Feature/Admin/
├─ SomeCrudControllerTest.php  # extends DefaultTestBase, uses operation traits
├─ AnotherCrudControllerTest.php
```

For controllers in subfolders (e.g., `PetShop`), the folder structure is respected:

```markdown
tests/Feature/Admin/PetShop/
├─ OwnerCrudControllerTest.php
├─ PetCrudControllerTest.php
```

<a name="configurations-available-in-test-traits"></a>
## Operation test traits and configuration variables

The operation test traits implement the assert logic and expose variables you can set in your test `setUp()` to customise behaviour:

- `DefaultCreateTests` exposes `$createInput` and `$assertCreateInput`.
- `DefaultUpdateTests` exposes `$updateInput` and `$assertUpdateInput`.
- `DefaultListTests` and `DefaultShowTests` inspect the CRUD configuration via the test helper.

Usage pattern:

- In your test's `setUp()`, create and set `$this->createInput` or `$this->updateInput` when you need to submit additional or transformed data. The trait will use those arrays when performing the POST/PUT requests.
- Use `$assertCreateInput` / `$assertUpdateInput` when the database assertion differs from the raw submission (for example, do not include `password` or file upload metadata in assertions).

Example (from `tests/Feature/Admin/PetShop/PetCrudControllerTest.php`):

- set an avatar URL to be submitted with the create request:

```php
$this->createInput = array_merge($this->model::factory()->make()->toArray(), [
    'avatar' => ['url' => 'https://lorempixel.com/400/200/animals'],
]);
```
<a name="route-parameters-and-controller-initialization"></a>
## Route parameters and controller initialization

Controllers that require route parameters for their routes (for example nested resources like an owner ID) should define those parameters directly in the generated test class using the `$routeParameters` array and the `$route` property. Example from `tests/Feature/Admin/PetShop/OwnerPetsCrudControllerTest.php`:

```php
public string $route = 'pet-shop/owner/1/pets';
public array $routeParameters = ['owner' => 1];
```

The `$routeParameters` array provides route parameter values when the test helpers mock the current route, while the `$route` property ensures trait requests target the correct URL with concrete values.


<a name="overriding-the-traits-behaviour"></a>
## Overriding trait behaviour

If the default trait behaviour doesn't match your controller logic (e.g., you need to attach relationships before asserting the edit page), override the trait methods inside your test class. You can keep the original trait implementation available by aliasing it when importing the trait. Example from `tests/Feature/Admin/PetShop/OwnerPetsCrudControllerTest.php`:

```php
use \\Tests\\Feature\\Backpack\\DefaultUpdateTests {
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

### Short checklist when adapting or writing tests

- Ensure the controller's required route parameters are provided via `$routeParameters` and the `$route` property includes concrete values in the generated test class.
- In `setUp()` create any related models your controller requires (attached owners, categories, etc.).
- Set `$createInput` / `$updateInput` in tests when the form requires additional structured data (files, nested arrays, relationship ids).
- Use `$assertCreateInput` / `$assertUpdateInput` to shape the expected DB assertion.
- Override trait methods only when you need custom assertions; alias the trait method if you still want to call the default behaviour.

<a name="publish-the-configuration"></a>
## Publishing the Configuration

You can publish the configuration by running `php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag=config`. There you will be able to change your controllers path. 

<a name="customizing-or-creating-test-stubs"></a>
## Customizing or creating test stubs

You can customize or add new operation test stubs used by the generator by publishing them to your application.

```bash
php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag=stubs
```

This will create a `resources/views/vendor/backpack/crud/stubs/testing` directory in your application root. Any changes you make to these stubs will be used when generating tests.
Here is an example of what a custom operation stub (e.g., `clone.stub`) might look like:

```php
<?php

namespace Tests\Feature\Backpack;

trait DefaultCloneTests
{
    /**
     * Test that the list page loads and there is a clone button on page
     */
    public function test_create_clone_button_is_on_page(): void
    {
        $response = $this->get($this->testHelper->getCrudUrl('list'));
        $response->assertStatus(200);
        $response->assertSee('bp-button="clone"', true);
    }
}
```

<a name="troubleshooting"></a>
## Troubleshooting

The test generation highly relies on your Model Factories. It's highly important that your Factories are up-to-date with the database/model requirements. That being said, here are a few of the most common failures people see in their generated tests, and how to fix them:

### The field X is required

If you see a failure like this:
```
 FAILED  Tests\Feature\Admin\VenueCrudControllerTest > update endpoint modifies entry in database
  Session has unexpected errors:
{
    "default": [
        "The city field is required."
    ]
}
Failed asserting that true is false.
```

Most likely the factory for your model isn't proper. In this example, your factory is most likely missing a related city - which is mandatory in the Update operation. The test isn't your problem, but the inconsistency between your Factory and your CrudController. To make this test pass, you need to either
- (a) change your Venue factory to include a City;
- (b) change your CRUD to not have the City required;

Alternatively, it's possible that your Factory creates an entry with `city_id` but the actual Backpack field uses a relationship field, and its name is `city` (not `city_id`). This mismatch between `city` and `city_id` can be fixed my overriding what input the CRUD tests use for the Create and Update operations, to have both `city` and `city_id`. In your `XCrudControllerTest`:

```php
    protected function setUp(): void
    {
        parent::setUp();

        $data = Venue::factory()->raw();
        $data['city'] = $data['city_id'];
        // unset($data['city_id']); // if you'd like
        
        $this->createInput = $data;
        $this->updateInput = $data;
    }
```

### The password field confirmation does not match.

It's likely that you'll see a failure like this for the Create & Update tests, when you have a password confirmation field:

```
  FAILED  Tests\Feature\Admin\UserCrudControllerTest > update endpoint modifies entry in database                                                                        
  Session has unexpected errors: 

{
    "default": [
        "The password field confirmation does not match."
    ]
}
Failed asserting that true is false.
```

One way to fix this would be to hard-code the for input that gets created/updated, in your `UserCrudControllerTest`:

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


