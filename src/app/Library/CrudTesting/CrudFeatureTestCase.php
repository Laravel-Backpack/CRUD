<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;

/**
 * Base test case for CRUD feature tests.
 * Provides common functionality for testing CRUD operations
 */
abstract class CrudFeatureTestCase extends IlluminateTestCase
{
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
     * Cache for operation settings
     */
    protected static array $operationSettingsCache = [];


    public string $operation = 'list'; // Default operation, can be overridden in child classes

    public CrudTestConfiguration $testConfig;

    public CrudPanel $crudPanel;

    /**
     * Get the base admin URL.
     *
     * @return string
     */
    protected function getAdminUrl(): string
    {
        return backpack_url();
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
    public function createTestEntry(array $attributes = [])
    {
        return TestConfigHelper::createTestEntry($this->model, $attributes);
    }

    /**
     * Get the operation configuration for the current controller.
     * 
     * @return array
     */
    protected function getOperationSettings(): array
    {
        return static::$operationSettingsCache[$this->getCacheKey()] ?? [];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $cacheKey = $this->getCacheKey();
        if (! isset(static::$operationSettingsCache[$cacheKey])) {
            //$panel = CrudControllerDiscovery::buildCrudPanel($this->controller, $this->operation);
            $controllerInfo = CrudControllerDiscovery::analyzeController($this->controller);
            $builder = new CrudTestBuilder($controllerInfo, $this->operation);
            $settings = $builder->getTestConfiguration();

            static::$operationSettingsCache[$cacheKey] = $settings;
        }
        $this->testConfig = new TestConfigHelper();

        // Disable CSRF protection for feature tests as they make direct requests
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    /**
     * Authenticate as an admin user.
     *
     * @return self
     */
    protected function actingAsAdmin(): self
    {
        $userModel = config('backpack.base.user_model_fqn', 'App\Models\User');
        $user = $userModel::find(1) ?? $userModel::factory()->create();
        
        return $this->actingAs($user, config('backpack.base.guard', 'web'));
    }

    /**
     * Create multiple test entries.
     *
     * @param  int  $count
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createTestEntries(int $count = 5, array $attributes = [])
    {
        return $this->model::factory()->count($count)->create($attributes);
    }


    private function getCacheKey(): string 
    {
        return $this->controller . ':' . $this->operation;
    }
}
