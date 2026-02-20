<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Backpack\CRUD\app\Library\CrudTesting\Support\MockApplicationRoute;

final class TestConfigHelper
{
    protected static array $operationSettingsCache = [];

    public function __construct(public CrudFeatureTestCase $testCase) {
        $cacheKey = $this->getCacheKey();

        MockApplicationRoute::mockRoute($testCase->operation, $testCase->controller, $testCase->routeParameters);

        if (! isset(static::$operationSettingsCache[$cacheKey])) {
            $controllerInfo = CrudControllerDiscovery::analyzeController($testCase->controller);
            $builder = new CrudTestBuilder($controllerInfo, $testCase->operation);
            $settings = $builder->getTestConfiguration();

            static::$operationSettingsCache[$cacheKey] = $settings;
        }
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
