<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\app\Library\CrudPanel\Hooks\Facades\LifecycleHook;

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
     * @deprecated use LifecycleHook::hookInto($operation.':before_setup', $closure) instead
     */
    public function operation($operations, $closure = false)
    {
        $operations = is_array($operations) ? $operations : [$operations];
        foreach ($operations as $operation) {
            LifecycleHook::hookInto($operation.':before_setup', $closure);
        }
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
     * @deprecated use LifecycleHook::hookInto($operation.':before_setup', $closure) instead
     */
    public function configureOperation($operations, $closure = false)
    {
        $operations = is_array($operations) ? $operations : [$operations];

        foreach ($operations as $operation) {
            LifecycleHook::hookInto($operation.':before_setup', $closure);
        }
    }

    /**
     * Run the closures that have been specified for each operation, as configurations.
     * This is called when an operation does setCurrentOperation().
     *
     *
     * @param  string|array  $operations
     * @return void
     */
    public function applyConfigurationFromSettings($operations)
    {
        $operations = is_array($operations) ? $operations : [$operations];

        foreach ($operations as $operation) {
            LifecycleHook::trigger($operation.':before_setup');
        }
    }
}
