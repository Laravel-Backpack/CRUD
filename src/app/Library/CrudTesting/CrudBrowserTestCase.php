<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Backpack\CRUD\app\Library\CrudTesting\Helpers\ButtonHelper;
use Backpack\CRUD\app\Library\CrudTesting\Helpers\DataTablesHelper;
use Backpack\CRUD\app\Library\CrudTesting\Helpers\FilterHelper;
use Backpack\CRUD\app\Library\CrudTesting\Helpers\FormHelper;
use Backpack\CRUD\Tests\BaseTestClass;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Dusk\Browser;

/**
 * Base test case for CRUD browser tests.
 * Provides common functionality for testing CRUD operations with Dusk.
 * 
 * @example
 * // Use helpers for common tasks
 * $formHelper = $this->getFormHelper($browser);
 * $formHelper->fill(['name' => 'Test'])->submit();
 * 
 * $dataTablesHelper = $this->getDataTablesHelper($browser);
 * $dataTablesHelper->search('test')->assertContains('test');
 */
abstract class CrudBrowserTestCase extends BaseTestClass
{
    use DatabaseTransactions;

    /**
     * The controller class being tested.
     *
     * @var string
     */
    protected string $controller;

    /**
     * The CRUD route being tested.
     *
     * @var string
     */
    protected string $route;

    /**
     * The model class being tested.
     *
     * @var string
     */
    protected string $model;

    /**
     * The entity name (singular).
     */
    protected ?string $entityName = null;

    /**
     * The entity name (plural).
     */
    protected ?string $entityNamePlural = null;

    /**
     * The type of tester to use (feature or browser).
     *
     * @var string
     */
    protected string $testerType = 'browser';

    /**
     * Whether to use Dusk for browser tests.
     *
     * @var bool
     */
    protected bool $useDusk = true;

    /**
     * Get the base admin URL.
     *
     * @return string
     */
    protected function getAdminUrl(): string
    {
        return url(config('backpack.base.route_prefix', 'admin'));
    }

    /**
     * Get the full CRUD route URL.
     *
     * @param  string|null  $path
     * @return string
     */
    protected function getCrudUrl(?string $path = null): string
    {
        $url = $this->getAdminUrl().'/'.$this->route;

        if ($path) {
            $url .= '/'.ltrim($path, '/');
        }

        return $url;
    }

    /**
     * Create a test entry for the model.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function createTestEntry(array $attributes = [])
    {
        return $this->model::factory()->create($attributes);
    }

    /**
     * Create multiple test entries.
     *
     * @param  int  $count
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function createTestEntries(int $count = 5, array $attributes = [])
    {
        return $this->model::factory()->count($count)->create($attributes);
    }

    /**
     * Get a form helper for filling and submitting forms.
     *
     * @param  mixed  $browser
     * @param  array  $fields  Field configuration array
     * @return FormHelper
     */
    protected function getFormHelper($browser, array $fields = []): FormHelper
    {
        return new FormHelper($browser, $fields);
    }

    /**
     * Get a DataTables helper for interacting with the list page.
     *
     * @param  mixed  $browser
     * @return DataTablesHelper
     */
    protected function getDataTablesHelper($browser): DataTablesHelper
    {
        return new DataTablesHelper($browser);
    }

    /**
     * Get a filter helper for applying filters.
     *
     * @param  mixed  $browser
     * @param  array  $filters  Filter configuration array
     * @return FilterHelper
     */
    protected function getFilterHelper($browser, array $filters = []): FilterHelper
    {
        return new FilterHelper($browser, $filters);
    }

    /**
     * Get a button helper for interacting with buttons.
     *
     * @param  mixed  $browser
     * @return ButtonHelper
     */
    protected function getButtonHelper($browser): ButtonHelper
    {
        return new ButtonHelper($browser);
    }

    /**
     * Visit a specific operation page.
     * Generic method that works with any operation.
     *
     * @param  mixed  $browser
     * @param  string  $operation  Operation name (e.g., 'list', 'create', 'update', 'show')
     * @param  int|null  $id  Optional ID for operations that require it (update, show, delete)
     * @return mixed
     */
    protected function visitOperationPage($browser, string $operation, ?int $id = null)
    {
        $url = match ($operation) {
            'list' => $this->getCrudUrl(),
            'create' => $this->getCrudUrl('create'),
            'update', 'edit' => $this->getCrudUrl($id.'/edit'),
            'show' => $this->getCrudUrl($id.'/show'),
            default => $this->getCrudUrl($operation),
        };

        return $browser->visit($url);
    }

    /**
     * Visit the list page.
     * @deprecated Use visitOperationPage($browser, 'list') instead
     *
     * @param  mixed  $browser
     * @return mixed
     */
    protected function visitListPage($browser)
    {
        return $this->visitOperationPage($browser, 'list');
    }

    /**
     * Visit the create page.
     * @deprecated Use visitOperationPage($browser, 'create') instead
     *
     * @param  mixed  $browser
     * @return mixed
     */
    protected function visitCreatePage($browser)
    {
        return $this->visitOperationPage($browser, 'create');
    }

    /**
     * Visit the update page for an entry.
     * @deprecated Use visitOperationPage($browser, 'update', $id) instead
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @return mixed
     */
    protected function visitUpdatePage($browser, int $id)
    {
        return $this->visitOperationPage($browser, 'update', $id);
    }

    /**
     * Visit the show page for an entry.
     * @deprecated Use visitOperationPage($browser, 'show', $id) instead
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @return mixed
     */
    protected function visitShowPage($browser, int $id)
    {
        return $this->visitOperationPage($browser, 'show', $id);
    }

    /**
     * Wait for DataTables to load.
     * @deprecated Use getDataTablesHelper($browser)->waitForLoad() instead
     *
     * @param  mixed  $browser
     * @param  int  $seconds
     * @return mixed
     */
    protected function waitForDataTable($browser, int $seconds = 5)
    {
        return $browser->waitFor('#crudTable', $seconds)
            ->waitUntilMissing('.dataTables_processing', $seconds);
    }

    /**
     * Assert that DataTables contains text.
     * @deprecated Use getDataTablesHelper($browser)->assertContains($text) instead
     *
     * @param  mixed  $browser
     * @param  string  $text
     * @return void
     */
    protected function assertDataTableContains($browser, string $text): void
    {
        $browser->with('#crudTable', function ($table) use ($text) {
            $table->assertSee($text);
        });
    }

    /**
     * Assert that a column is visible in the list.
     * @deprecated
     *
     * @param  mixed  $browser
     * @param  string  $columnName
     * @return void
     */
    protected function assertColumnVisible($browser, string $columnName): void
    {
        $browser->with('#crudTable thead', function ($thead) use ($columnName) {
            $thead->assertSee($columnName);
        });
    }

    /**
     * Assert that a field is visible on the form.
     * @deprecated Use getFormHelper($browser, $fields)->assertFieldVisible($fieldName) instead
     *
     * @param  mixed  $browser
     * @param  string  $fieldName
     * @param  string|null  $fieldType
     * @return void
     */
    protected function assertFieldVisible($browser, string $fieldName, ?string $fieldType = null): void
    {
        $selector = $this->getFieldSelector($fieldName, $fieldType);
        $browser->assertVisible($selector);
    }

    /**
     * Assert that a button is visible.
     * @deprecated Use getButtonHelper($browser)->assertVisible($buttonText) instead
     *
     * @param  mixed  $browser
     * @param  string  $buttonText
     * @return void
     */
    protected function assertButtonVisible($browser, string $buttonText): void
    {
        $browser->assertSee($buttonText);
    }

    /**
     * Fill a form field.
     * @deprecated Use getFormHelper($browser, $fields)->fill($data) instead
     *
     * @param  mixed  $browser
     * @param  string  $fieldName
     * @param  mixed  $value
     * @param  string|null  $fieldType
     * @return mixed
     */
    protected function fillField($browser, string $fieldName, $value, ?string $fieldType = null)
    {
        $selector = $this->getFieldSelector($fieldName, $fieldType);

        // Handle different field types
        switch ($fieldType) {
            case 'select':
            case 'select2':
            case 'select_from_array':
                return $browser->select($selector, $value);

            case 'checkbox':
                return $value ? $browser->check($selector) : $browser->uncheck($selector);

            case 'textarea':
            case 'ckeditor':
            case 'tinymce':
                return $browser->type($selector, $value);

            default:
                return $browser->type($selector, $value);
        }
    }

    /**
     * Submit the form.
     * @deprecated Use getFormHelper($browser)->submit($buttonText) instead
     *
     * @param  mixed  $browser
     * @param  string  $buttonText
     * @return mixed
     */
    protected function submitForm($browser, string $buttonText = 'Save')
    {
        return $browser->press($buttonText);
    }

    /**
     * Get the selector for a form field.
     * @deprecated Use FieldTester::make($field)->getSelector() instead
     *
     * @param  string  $fieldName
     * @param  string|null  $fieldType
     * @return string
     */
    protected function getFieldSelector(string $fieldName, ?string $fieldType = null): string
    {
        // Most Backpack fields use name attribute
        return "[name='{$fieldName}']";
    }

    /**
     * Assert that validation error is shown.
     * @deprecated Use getFormHelper($browser)->assertValidationError($fieldName) instead
     *
     * @param  mixed  $browser
     * @param  string  $fieldName
     * @return void
     */
    protected function assertValidationError($browser, string $fieldName): void
    {
        $browser->assertVisible('.invalid-feedback')
            ->assertSee('field is required', '.invalid-feedback');
    }

    /**
     * Click a button in the DataTable.
     * @deprecated Use getButtonHelper($browser)->clickInRow($buttonClass, $rowIndex) instead
     *
     * @param  mixed  $browser
     * @param  string  $buttonClass
     * @param  int  $rowIndex
     * @return mixed
     */
    protected function clickDataTableButton($browser, string $buttonClass, int $rowIndex = 0)
    {
        $selector = "#crudTable tbody tr:nth-child({$rowIndex}) .{$buttonClass}";

        return $browser->click($selector);
    }

    /**
     * Apply a filter.
     * @deprecated Use getFilterHelper($browser, $filters)->apply($filterName, $value) instead
     *
     * @param  mixed  $browser
     * @param  string  $filterName
     * @param  mixed  $value
     * @return mixed
     */
    protected function applyFilter($browser, string $filterName, $value)
    {
        // Open filters dropdown if needed
        $browser->click('.filters-dropdown-button');

        // Select filter value
        $browser->select("[name='filter[{$filterName}]']", $value);

        // Wait for DataTables to reload
        return $this->waitForDataTable($browser);
    }

    /**
     * Search in DataTable.
     * @deprecated Use getDataTablesHelper($browser)->search($searchTerm) instead
     *
     * @param  mixed  $browser
     * @param  string  $searchTerm
     * @return mixed
     */
    protected function searchDataTable($browser, string $searchTerm)
    {
        $browser->type('#datatable_search_stack input', $searchTerm);

        // Wait for DataTables to reload
        return $this->waitForDataTable($browser);
    }

    /**
     * Assert success notification is shown.
     *
     * @param  mixed  $browser
     * @param  string|null  $message
     * @return void
     */
    protected function assertSuccessNotification($browser, ?string $message = null): void
    {
        $browser->waitFor('.alert-success', 5);

        if ($message) {
            $browser->assertSee($message, '.alert-success');
        }
    }

    /**
     * Assert error notification is shown.
     *
     * @param  mixed  $browser
     * @param  string|null  $message
     * @return void
     */
    protected function assertErrorNotification($browser, ?string $message = null): void
    {
        $browser->waitFor('.alert-danger', 5);

        if ($message) {
            $browser->assertSee($message, '.alert-danger');
        }
    }

    /**
     * Login as admin user for testing.
     *
     * @param  mixed  $browser
     * @return mixed
     */
    protected function loginAsAdmin($browser)
    {
        // This should be implemented based on your auth setup
        // Example:
        // $user = User::factory()->create(['is_admin' => true]);
        // return $browser->loginAs($user);

        return $browser;
    }
}
