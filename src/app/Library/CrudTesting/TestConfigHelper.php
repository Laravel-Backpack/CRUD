<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Illuminate\Routing\Route;

class TestConfigHelper implements CrudTestConfiguration
{
    protected static array $operationSettingsCache = [];
    protected static bool $isConfiguring = false;

    public function __construct(
        public string $controller,
        public string $operation = 'list',
        public ?string $route = null,
        public ?string $model = null,
    ) {
        if (static::$isConfiguring) {
            return;
        }

        $cacheKey = $this->getCacheKey();
        if (! isset(static::$operationSettingsCache[$cacheKey])) {
            $controllerInfo = CrudControllerDiscovery::analyzeController($this->controller);
            $builder = new CrudTestBuilder($controllerInfo, $this->operation);
            $settings = $builder->getTestConfiguration();

            static::$operationSettingsCache[$cacheKey] = $settings;
        }
    }

    public function setup()
    {
        // Default setup can be defined here if needed
    }

    public function actingAsAdmin($testCase)
    {
        $userModel = config('backpack.base.user_model_fqn', 'App\Models\User');
        $user = $userModel::find(1) ?? $userModel::factory()->create();

        return $testCase->actingAs($user, config('backpack.base.guard', 'web'));
    }

    public function getCrudUrl(?string $path = null): string
    {
        $url = backpack_url().'/'.$this->route;

        if ($path) {
            $url .= '/'.ltrim($path, '/');
        }

        return $url;
    }

    public function createTestEntries(int $count = 5, array $attributes = [])
    {
        return $this->model::factory()->count($count)->create($attributes);
    }

    /**
     * Create a test entry for the model.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createEntry(array $attributes = [])
    {
        return static::createTestEntry($this->model, $attributes);
    }

    public function getRouteParameters()
    {
        return [];
    }

    public function validCreateInput($model)
    {
        return $model::factory()->raw();
    }

    public function validUpdateInput($model)
    {
        return $model::factory()->raw();
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

    public function invalidInput()
    {
        return [];
    }

    /**
     * Apply the test configuration for the given controller.
     *
     * @param  string  $controllerClass
     * @return void
     */
    public static function applyConfiguration(string $controllerClass, $operation = 'list'): void
    {
        $config = config('backpack.testing.configurations.'.$controllerClass);

        if (! $config) {
            return;
        }

        // Handle Class Configuration
        if (is_string($config) && class_exists($config)) {
            static::$isConfiguring = true;
            try {
                $instance = new $config($controllerClass, $operation);
            } finally {
                static::$isConfiguring = false;
            }

            if (! $instance instanceof CrudTestConfiguration) {
                throw new \InvalidArgumentException("Configuration class {$config} must implement CrudTestConfiguration.");
            }

            if (method_exists($instance, 'setup')) {
                $instance->setup();
            }

            static::mockRoute($instance->getRouteParameters(), $operation);

            return;
        }

        static::mockRoute([], $operation);
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
    public static function mockRoute(array $parameters, string $operation, string $uri = '/', string $method = 'GET'): void
    {
        $action = ['uses' => 'Controller@method', 'operation' => $operation];

        $route = new Route([$method], $uri, $action);

        $request = app('request')->merge(['operation' => $operation]);
        $route->bind($request);

        foreach ($parameters as $key => $value) {
            $route->setParameter($key, $value);
        }

        $request->setRouteResolver(fn () => $route);

        $router = app('router');

        try {
            $reflector = new \ReflectionClass($router);
            $property = $reflector->getProperty('current');
            $property->setValue($router, $route);
        } catch (\ReflectionException $e) {
            // Fallback or ignore if implementation changes
        }

        if (app()->bound('url')) {
            app('url')->setRequest($request);
        }
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
        return $this->controller.':'.$this->operation;
    }
}
