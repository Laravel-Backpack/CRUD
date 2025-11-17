<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters;

use Backpack\CRUD\app\Library\CrudTesting\FieldTesters\FieldTester;

/**
 * Base class for operation testing strategies.
 */
abstract class OperationTester
{
    protected array $config;
    protected string $route;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->route = $config['route'];
    }

    /**
     * Get test methods for this operation.
     *
     * @return array
     */
    abstract public function getTestMethods(): array;

    /**
     * Generate test data for this operation.
     *
     * @return array
     */
    abstract public function generateTestData(): array;

    /**
     * Create an operation tester for the given operation type.
     *
     * @param  string  $operation
     * @param  array  $config
     * @return OperationTester
     */
    public static function make(string $operation, array $config): OperationTester
    {
        $testerClass = static::resolveTesterClass($operation);

        return new $testerClass($config);
    }

    /**
     * Resolve the tester class for a given operation.
     *
     * @param  string  $operation
     * @return string
     */
    protected static function resolveTesterClass(string $operation): string
    {
        // 1. Check for custom override in config
        $customClass = config("backpack.crud-testing.operation_testers.{$operation}");
        if ($customClass && class_exists($customClass)) {
            return $customClass;
        }

        // 2. Try convention-based class name in this namespace
        $conventionClass = static::getConventionBasedClass($operation);
        if (class_exists($conventionClass)) {
            return $conventionClass;
        }

        // 3. Try convention-based class name in custom paths
        foreach (static::getCustomTesterPaths() as $namespace) {
            $customPath = $namespace.'\\'.static::getTesterClassName($operation);
            if (class_exists($customPath)) {
                return $customPath;
            }
        }

        throw new \Exception("Operation tester not found for: {$operation}");
    }

    /**
     * Get convention-based class name for an operation.
     *
     * @param  string  $operation
     * @return string
     */
    protected static function getConventionBasedClass(string $operation): string
    {
        return __NAMESPACE__.'\\'.static::getTesterClassName($operation);
    }

    /**
     * Get the class name for an operation following naming conventions.
     *
     * @param  string  $operation
     * @return string
     */
    protected static function getTesterClassName(string $operation): string
    {
        return ucfirst($operation).'OperationTester';
    }

    /**
     * Get custom tester namespaces from configuration.
     *
     * @return array
     */
    protected static function getCustomTesterPaths(): array
    {
        return config('backpack.crud-testing.operation_tester_namespaces', []);
    }
}
