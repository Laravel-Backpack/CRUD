<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;

/**
 * Configures the CrudPanel environment for testing.
 * Allows operations to register specific setup logic that needs to run
 * during test generation/execution, even when that operation is not active.
 */
class CrudTestConfigurator
{
    /**
     * Registered configuration callbacks.
     *
     * @var array<string, callable>
     */
    protected static array $configurators = [];

    /**
     * Register default configurators.
     */
    protected static function registerDefaults(): void
    {
        if (! empty(static::$configurators)) {
            return;
        }

        // Register AjaxUpload configurator
        static::register('ajax_upload', function (CrudPanel $crud) {
            if (config('backpack.operations.ajax-upload')) {
                $crud->set('ajax-upload.temporary_folder', config('backpack.operations.ajax-upload.temporary_folder', 'backpack/temp'));
                $crud->set('ajax-upload.temporary_disk', config('backpack.operations.ajax-upload.temporary_disk', 'public'));
            }
        });
    }

    /**
     * Register a configurator for an operation.
     *
     * @param  string  $operation
     * @param  callable  $callback
     * @return void
     */
    public static function register(string $operation, callable $callback): void
    {
        static::$configurators[$operation] = $callback;
    }

    /**
     * Apply configurations to the CrudPanel based on the controller's capabilities.
     *
     * @param  CrudPanel  $crud
     * @param  object  $controller
     * @return void
     */
    public static function apply(CrudPanel $crud, object $controller): void
    {
        static::registerDefaults();

        // Detect operations present in the controller
        // We use method existence as a comprehensive check (covers traits and manual implementation)
        $checks = [
            'ajax_upload' => 'ajaxUpload',
            'inline_create' => 'storeInline',
            'fetch' => 'fetch',
        ];

        foreach (static::$configurators as $operation => $callback) {
            $methodToCheck = $checks[$operation] ?? null;

            if ($methodToCheck && method_exists($controller, $methodToCheck)) {
                $callback($crud);
            }
        }
    }
}
