<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationStrategies;

use InvalidArgumentException;

/**
 * Factory for creating operation strategy instances.
 */
class OperationStrategyFactory
{
    /**
     * Registered operation strategies.
     *
     * @var array<string, class-string<OperationStrategyInterface>>
     */
    protected static array $strategies = [
        'list' => ListOperationStrategy::class,
        'create' => CreateOperationStrategy::class,
        'update' => UpdateOperationStrategy::class,
        'show' => ShowOperationStrategy::class,
        'delete' => DeleteOperationStrategy::class,
    ];

    /**
     * Create a strategy instance for the given operation.
     *
     * @param  string  $operation
     * @param  object  $crudPanel
     * @param  array  $controllerInfo
     * @return OperationStrategyInterface
     *
     * @throws InvalidArgumentException
     */
    public static function make(string $operation, object $crudPanel, array $controllerInfo): OperationStrategyInterface
    {
        $strategyClass = static::$strategies[$operation] ?? null;

        if (! $strategyClass) {
            throw new InvalidArgumentException("Unknown operation: {$operation}. No strategy registered.");
        }

        return new $strategyClass($crudPanel, $controllerInfo);
    }

    /**
     * Register a custom operation strategy.
     *
     * @param  string  $operation
     * @param  class-string<OperationStrategyInterface>  $strategyClass
     * @return void
     */
    public static function register(string $operation, string $strategyClass): void
    {
        if (! is_subclass_of($strategyClass, OperationStrategyInterface::class)) {
            throw new InvalidArgumentException(
                "Strategy class must implement OperationStrategyInterface."
            );
        }

        static::$strategies[$operation] = $strategyClass;
    }

    /**
     * Check if a strategy is registered for the given operation.
     *
     * @param  string  $operation
     * @return bool
     */
    public static function hasStrategy(string $operation): bool
    {
        return isset(static::$strategies[$operation]);
    }

    /**
     * Get all registered operations.
     *
     * @return array<string>
     */
    public static function getRegisteredOperations(): array
    {
        return array_keys(static::$strategies);
    }
}
