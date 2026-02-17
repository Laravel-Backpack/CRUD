<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Illuminate\Routing\Route;

class TestConfigHelper
{
    protected static array $operationSettingsCache = [];

    public function __construct(public CrudFeatureTestCase $testCase) {
        $cacheKey = $this->getCacheKey();
        $this->mockRoute($testCase);
        if (! isset(static::$operationSettingsCache[$cacheKey])) {
            $controllerInfo = CrudControllerDiscovery::analyzeController($testCase->controller);
            $builder = new CrudTestBuilder($controllerInfo, $testCase->operation);
            $settings = $builder->getTestConfiguration();

            static::$operationSettingsCache[$cacheKey] = $settings;
        }
    }



    public function actingAsAdmin(CrudFeatureTestCase $testCase)
    {
        $userModel = config('backpack.base.user_model_fqn', 'App\Models\User');
        $user = $userModel::find(1) ?? $userModel::factory()->create();

        return $testCase->actingAs($user, config('backpack.base.guard', 'web'));
    }

    public function getCrudUrl(?string $path = null): string
    {
        $url = backpack_url().'/'.$this->testCase->route;

        if ($path) {
            $url .= '/'.ltrim($path, '/');
        }

        return $url;
    }

    public static function getDatabaseAssertInput(string $model, array $data = []): array
    {
        $instance = new $model();

        if (self::isTranslatable($instance)) {
            return self::getTranslatableDatabaseAssertInput($model, $instance, $data);
        }

        $input = $model::factory()->make()->getAttributes();

        foreach ($input as $key => $value) {
            if (array_key_exists($key, $data)) {
                $input[$key] = $data[$key];
            }
        }

        return $input;
    }

    private static function isTranslatable($model)
    {
        if (method_exists($model, 'translationEnabled')) {
            return $model->translationEnabled();
        }

        return false;
    }

    private static function getTranslatableDatabaseAssertInput(string $model, $instance, array $data): array
    {
        $input = $model::factory()->make()->getAttributes();

        foreach ($input as $key => $value) {
            $isTranslatable = method_exists($instance, 'isTranslatableAttribute') && $instance->isTranslatableAttribute($key);

            if ($isTranslatable && isset($data[$key])) {
                $input[$key] = json_encode([app()->getLocale() => $data[$key]]);
                continue;
            }

            if (array_key_exists($key, $data)) {
                $input[$key] = $data[$key];
            }
        }

        return $input;
    }

    /**
     * Mock the current route with the given parameters.
     *
     * @param  array  $parameters
     * @param  string  $operation
     * @param  string  $uri
     * @param  string  $method
     * @return void
     */
    public function mockRoute(CrudFeatureTestCase $testCase): void
    {
        $action = ['uses' => 'Controller@method', 'operation' => $testCase->operation];

        $route = new Route(['GET', 'POST', 'PUT', 'DELETE'], $testCase->route ?? '/', $action);
        $route->setContainer(app());
        $route->setRouter(app('router'));

        $request = app('request')->merge(['operation' => $testCase->operation]);
        $route->bind($request);

        foreach ($testCase->routeParameters as $key => $value) {
            $route->setParameter($key, $value);
        }

        $request->setRouteResolver(fn () => $route);

        $router = app('router');

        try {
            $reflector = new \ReflectionClass($router);
            $property = $reflector->getProperty('current');
            $property->setValue($router, $route);
        } catch (\ReflectionException $e) {
            // Fallback
        }

        if (app()->bound('url')) {
            app('url')->setRequest($request);
        }

        app()->instance('request', $request);
    }

    public function createEntry(array $attributes = [])
    {
        return static::createTestEntry($this->testCase->model, $attributes);
    }

    public static function createTestEntry(string $model, array $attributes = [])
    {
        return $model::factory()->create($attributes);
    }

    public function getOperationSettings(): array
    {
        return static::$operationSettingsCache[$this->getCacheKey()] ?? [];
    }

    public function getOperationSetting(string $key, $default = null)
    {
        return $this->getOperationSettings()[$key] ?? $default;
    }

    private function getCacheKey(): string
    {
        return $this->testCase->controller.':'.$this->testCase->operation;
    }
}
