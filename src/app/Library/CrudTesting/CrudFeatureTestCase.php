<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

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
     * The type of tester to use (feature or browser).
     *
     * @var string
     */
    protected string $testerType = 'feature';

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
    protected function createTestEntry(array $attributes = [])
    {
        return $this->model::factory()->create($attributes);
    }


    /**
     * Cache for operation settings
     */
    protected static array $operationSettingsCache = [];

    /**
     * Get the operation configuration for the current controller.
     * 
     * @param string $operation
     * @return array
     */
    protected function getOperationSettings(string $operation): array
    {
        $cacheKey = $this->controller . ':' . $operation;

        if (isset(static::$operationSettingsCache[$cacheKey])) {
            return static::$operationSettingsCache[$cacheKey];
        }

        $controllerInfo = CrudControllerDiscovery::analyzeController($this->controller);
        $builder = new CrudTestBuilder($controllerInfo, $operation);
        $settings = $builder->getTestConfiguration();

        static::$operationSettingsCache[$cacheKey] = $settings;

        return $settings;
    }

    protected function setUp(): void
    {
        parent::setUp();
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
    protected function createTestEntries(int $count = 5, array $attributes = [])
    {
        return $this->model::factory()->count($count)->create($attributes);
    }
}
