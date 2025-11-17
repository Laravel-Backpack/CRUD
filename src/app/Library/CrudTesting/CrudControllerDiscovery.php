<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Backpack\CRUD\app\Http\Controllers\CrudController;
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
        $paths = $paths ?? [app_path('Http/Controllers')];
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

        return $controllers;
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
        
        // Build a temporary instance to get CRUD panel configuration
        // This needs to be done carefully to avoid side effects
        $analysis = [
            'class' => $controllerClass,
            'short_name' => $reflection->getShortName(),
            'operations' => $operations,
            'setup_methods' => static::getSetupMethods($reflection),
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
        $traits = $reflection->getTraitNames();
        $operations = [];

        foreach ($traits as $trait) {
            if (Str::endsWith($trait, 'Operation')) {
                $operationName = Str::before(class_basename($trait), 'Operation');
                $operations[] = Str::snake($operationName);
            }
        }

        return $operations;
    }

    /**
     * Get setup methods (setupXxxOperation) from the controller.
     *
     * @param  ReflectionClass  $reflection
     * @return array
     */
    protected static function getSetupMethods(ReflectionClass $reflection): array
    {
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED);
        $setupMethods = [];

        foreach ($methods as $method) {
            if (Str::startsWith($method->getName(), 'setup') && Str::endsWith($method->getName(), 'Operation')) {
                $operationName = Str::between($method->getName(), 'setup', 'Operation');
                $setupMethods[] = [
                    'method' => $method->getName(),
                    'operation' => Str::snake($operationName),
                ];
            }
        }

        return $setupMethods;
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
     * Build a CrudPanel for testing by instantiating the controller.
     * This is used to extract fields, columns, and operation settings.
     *
     * @param  string  $controllerClass
     * @param  string  $operation
     * @return \Backpack\CRUD\app\Library\CrudPanel\CrudPanel
     */
    public static function buildCrudPanel(string $controllerClass, string $operation = 'list'): object
    {
        // Create a mock request for the operation
        $request = request();
        
        // Instantiate the controller
        $controller = app()->make($controllerClass);
        
        // Get the CRUD panel
        $crud = $controller->crud;
        
        // Set the operation
        $crud->setOperation($operation);
        
        // Run setup configuration for the operation
        $crud->applyConfigurationFromSettings($operation);
        
        // Call setup method if exists
        $setupMethod = 'setup'.Str::studly($operation).'Operation';
        if (method_exists($controller, $setupMethod)) {
            $controller->{$setupMethod}();
        }

        return $crud;
    }
}
