<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Backpack\CRUD\app\Library\CrudTesting\OperationTesters\OperationTester;
use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Base test case for CRUD feature tests.
 * Provides common functionality for testing CRUD operations with standard HTTP requests.
 */
abstract class CrudFeatureTestCase extends IlluminateTestCase
{
    use DatabaseTransactions;

    /**
     * Boot the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../../../../../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

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
     * The type of tester to use (feature or browser).
     *
     * @var string
     */
    protected string $testerType = 'feature';

    /**
     * Cached operation testers.
     *
     * @var array
     */
    protected array $operationTesters = [];

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
     * Get an operation tester for the specified operation.
     *
     * @param  string  $operation
     * @param  array  $config
     * @return OperationTester
     */
    protected function getOperationTester(string $operation, array $config = []): OperationTester
    {
        $cacheKey = $operation;

        if (! isset($this->operationTesters[$cacheKey])) {
            $config = array_merge($this->getOperationSettings($operation), $config);

            $defaultConfig = [
                'route' => $this->getCrudUrl(),
                'entity_name' => class_basename($this->model),
                'entity_name_plural' => str(class_basename($this->model))->plural(),
                'model' => $this->model,
                'controller' => $this->controller ?? null,
                'custom_data_source' => $this,
            ];

            $this->operationTesters[$cacheKey] = OperationTester::make(
                $operation,
                array_merge($defaultConfig, $config),
                $this->testerType
            );
        }

        return $this->operationTesters[$cacheKey];
    }

    /**
     * Get the operation configuration for the current controller.
     * 
     * @param string $operation
     * @return array
     */
    protected function getOperationSettings(string $operation): array
    {
        $controllerInfo = CrudControllerDiscovery::analyzeController($this->controller);
        $builder = new CrudTestBuilder($controllerInfo, $operation);
        return $builder->getTestConfiguration();
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
