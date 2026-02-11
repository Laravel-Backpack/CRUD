<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\CrudManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * Discovers CrudControllers in the application and extracts their configuration.
 */
class CrudControllerDiscovery
{
    /**
     * Discover all CrudController classes in the application.
     *
     * @param  string|array  $paths  Paths to search for controllers (defaults to app/Http/Controllers)
     * @return array Array of discovered controller information
     */
    public static function discover($paths = null): array
    {
        if ($paths === null) {
            $paths = config('backpack.testing.controllers_path', app_path('Http/Controllers'));
        }

        $paths = is_array($paths) ? $paths : [$paths];

        $controllers = [];

        foreach ($paths as $path) {
            if (! File::exists($path)) {
                continue;
            }

            $files = File::allFiles($path);

            foreach ($files as $file) {
                $className = static::getClassNameFromFile($file->getPathname());

                if (! $className) {
                    continue;
                }

                if (! class_exists($className)) {
                    continue;
                }

                if (! is_subclass_of($className, CrudController::class)) {
                    continue;
                }

                $controllers[] = static::analyzeController($className);
            }
        }

        return collect($controllers)->unique('class')->values()->toArray();
    }

    /**
     * Analyze a CrudController and extract its configuration.
     *
     * @param  string  $controllerClass  The fully qualified controller class name
     * @return array Controller analysis information
     */
    public static function analyzeController(string $controllerClass): array
    {
        $reflection = new ReflectionClass($controllerClass);

        // Get the operations used by this controller
        $operations = static::getOperations($reflection);

        $analysis = [
            'class' => $controllerClass,
            'short_name' => $reflection->getShortName(),
            'operations' => $operations,
        ];

        return $analysis;
    }

    /**
     * Get the operations used by a controller.
     *
     * @param  ReflectionClass  $reflection
     * @return array
     */
    protected static function getOperations(ReflectionClass $reflection): array
    {
        $traits = [];
        $currentReflection = $reflection;

        // Collect traits from the class and all its parents
        while ($currentReflection) {
            $traits = array_merge($traits, $currentReflection->getTraitNames());
            $currentReflection = $currentReflection->getParentClass();
        }

        $operations = [];

        foreach (array_unique($traits) as $trait) {
            try {
                $traitReflection = new ReflectionClass($trait);
            } catch (\ReflectionException $e) {
                continue;
            }

            foreach ($traitReflection->getMethods() as $method) {
                if (preg_match('/^setup(.+)(Routes|Defaults)$/', $method->getName(), $matches)) {
                    $operations[] = Str::kebab($matches[1]);
                    break;
                }
            }
        }

        return collect($operations)->unique()->values()->toArray();
    }

    /**
     * Extract class name from a file path.
     *
     * @param  string  $filePath
     * @return string|null
     */
    protected static function getClassNameFromFile(string $filePath): ?string
    {
        $contents = File::get($filePath);

        // Extract namespace
        if (! preg_match('/namespace\s+([^;]+);/', $contents, $namespaceMatches)) {
            return null;
        }

        $namespace = $namespaceMatches[1];

        // Extract class name
        if (! preg_match('/class\s+(\w+)/', $contents, $classMatches)) {
            return null;
        }

        $className = $classMatches[1];

        return $namespace.'\\'.$className;
    }

    /**
     * Build a CrudPanel for testing.
     *
     * @param  string  $controllerClass
     * @param  string  $operation
     * @return \Backpack\CRUD\app\Library\CrudPanel\CrudPanel
     */
    public static function buildCrudPanel(string $controllerClass, string $operation = 'list'): object
    {
        self::clearCrudPanelBindings($controllerClass);

        TestConfigHelper::applyConfiguration($controllerClass, $operation);

        $controller = app()->make($controllerClass);

        if (! CrudManager::hasCrudPanel($controllerClass)) {
            $controller->initializeCrudPanel(request());

            return CrudManager::getCrudPanel($controllerClass);
        }

        $controller->setupCrudController($operation);

        return CrudManager::getCrudPanel($controller);
    }

    private static function clearCrudPanelBindings(): void
    {
        if (app()->bound('crud')) {
            app()->forgetInstance('crud');
            app()->forgetInstance(\Backpack\CRUD\app\Library\CrudPanel\CrudPanel::class);
        }

        if (app()->bound('CrudManager')) {
            app()->forgetInstance('CrudManager');
        }

        \Illuminate\Support\Facades\Facade::clearResolvedInstance('CrudManager');
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('crud');
    }
}
