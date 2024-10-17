<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\app\Library\CrudPanel\Hooks\Contracts\OperationHook;
use Backpack\CRUD\app\Library\CrudPanel\Hooks\OperationHooks;

trait Operations
{
    /*
    |--------------------------------------------------------------------------
    |                               OPERATIONS
    |--------------------------------------------------------------------------
    | Helps developers set and get the current CRUD operation, as defined by
    | the controller method being run.
    */
    protected $currentOperation;

    /**
     * Get the current CRUD operation being performed.
     *
     * @return string Operation being performed in string form.
     */
    public function getOperation()
    {
        return $this->getCurrentOperation();
    }

    /**
     * Set the CRUD operation being performed in string form.
     *
     * @param  string  $operation_name  Ex: create / update / revision / delete
     */
    public function setOperation($operation_name)
    {
        return $this->setCurrentOperation($operation_name);
    }

    /**
     * Get the current CRUD operation being performed.
     *
     * @return string Operation being performed in string form.
     */
    public function getCurrentOperation()
    {
        return $this->currentOperation ?? \Route::getCurrentRoute()->action['operation'] ?? null;
    }

    /**
     * Set the CRUD operation being performed in string form.
     *
     * @param  string  $operation_name  Ex: create / update / revision / delete
     */
    public function setCurrentOperation($operation_name)
    {
        $this->currentOperation = $operation_name;
    }

    /**
     * Convenience method to make sure all calls are made to a particular operation.
     *
     * @param  string|array  $operation  Operation name in string form
     * @param  bool|\Closure  $closure  Code that calls CrudPanel methods.
     * @return void
     *
     * @deprecated use OperationHook::register(OperationHooks::BEFORE_OPERATION_SETUP, $operation, $closure) instead
     */
    public function operation($operations, $closure = false)
    {
        OperationHook::register(OperationHooks::BEFORE_OPERATION_SETUP, $operations, $closure);
    }

    /**
     * Store a closure which configures a certain operation inside settings.
     * All configurations are put inside that operation's namespace.
     * Ex: show.configuration.
     *
     * @param  string|array  $operation  Operation name in string form
     * @param  bool|\Closure  $closure  Code that calls CrudPanel methods.
     * @return void
     *
     * @deprecated use OperationHook::register(OperationHooks::BEFORE_OPERATION_SETUP, $operation, $closure) instead
     */
    public function configureOperation($operations, $closure = false)
    {
        $operations = (array) $operations;

        OperationHook::register(OperationHooks::BEFORE_OPERATION_SETUP, $operations, $closure);
    }

    /**
     * Run the closures that have been specified for each operation, as configurations.
     * This is called when an operation does setCurrentOperation().
     *
     *
     * @param  string|array  $operations  [description]
     * @return void
     *
     * @deprecated use OperationHook::register(OperationHooks::BEFORE_OPERATION_SETUP, $operation, $closure) instead
     */
    public function applyConfigurationFromSettings($operations)
    {
        $operations = (array) $operations;

        OperationHook::run(OperationHooks::BEFORE_OPERATION_SETUP, $operations, []);
    }
}
