<?php

namespace Backpack\CRUD\app\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\Contracts\CrudControllerContract;
use Backpack\CRUD\app\Library\Attributes\DeprecatedIgnoreOnRuntime;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\Hooks\Facades\LifecycleHook;
use Backpack\CRUD\CrudManager;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

/**
 * Class CrudController.
 *
 * @property-read CrudPanel $crud
 * @property array $data
 */
class CrudController extends Controller implements CrudControllerContract
{
    use DispatchesJobs, ValidatesRequests;

    public $data = [];

    public function __construct()
    {
        // ---------------------------
        // Create the CrudPanel object
        // ---------------------------
        // Used by developers inside their ProductCrudControllers as
        // $this->crud or using the CRUD facade.
        //
        // It's done inside a middleware closure in order to have
        // the complete request inside the CrudPanel object.
        $this->middleware(function ($request, $next) {
            if (! CrudManager::hasCrudPanel(get_class($this))) {
                $this->initializeCrudPanel($request);

                return $next($request);
            }

            $this->setupCrudController();

            CrudManager::getCrudPanel($this)->setRequest($request);

            return $next($request);
        });
    }

    public function initializeCrudPanel($request, $crudPanel = null): void
    {
        $crudPanel ??= CrudManager::getCrudPanel($this);

        $crudPanel = $crudPanel->initialize(get_class($this), $request);

        CrudManager::storeInitializedOperation(
            get_class($this),
            $crudPanel->getCurrentOperation()
        );

        if (! $crudPanel->isInitialized()) {
            $crudPanel->initialized = true;

            $this->setupCrudController($crudPanel->getCurrentOperation());
        }

        CrudManager::storeCrudPanel(get_class($this), $crudPanel);
    }

    private function setupCrudController($operation = null)
    {
        LifecycleHook::trigger('crud:before_setup_defaults', [$this]);
        $this->setupDefaults();
        LifecycleHook::trigger('crud:after_setup_defaults', [$this]);
        LifecycleHook::trigger('crud:before_setup', [$this]);
        $this->setup();
        LifecycleHook::trigger('crud:after_setup', [$this]);
        $this->setupConfigurationForCurrentOperation($operation);
    }

    /**
     * Allow developers to set their configuration options for a CrudPanel.
     */
    public function setup()
    {
    }

    /**
     * Load routes for all operations.
     * Allow developers to load extra routes by creating a method that looks like setupOperationNameRoutes.
     *
     * @param  string  $segment  Name of the current entity (singular).
     * @param  string  $routeName  Route name prefix (ends with .).
     * @param  string  $controller  Name of the current controller.
     */
    #[DeprecatedIgnoreOnRuntime('we dont call this method anymore unless you had it overwritten in your CrudController')]
    public function setupRoutes($segment, $routeName, $controller)
    {
        preg_match_all('/(?<=^|;)setup([^;]+?)Routes(;|$)/', implode(';', get_class_methods($this)), $matches);

        if (count($matches[1])) {
            foreach ($matches[1] as $methodName) {
                $this->{'setup'.$methodName.'Routes'}($segment, $routeName, $controller);
            }
        }
    }

    /**
     * Load defaults for all operations.
     * Allow developers to insert default settings by creating a method
     * that looks like setupOperationNameDefaults.
     */
    protected function setupDefaults()
    {
        preg_match_all('/(?<=^|;)setup([^;]+?)Defaults(;|$)/', implode(';', get_class_methods($this)), $matches);
        if (count($matches[1])) {
            foreach ($matches[1] as $methodName) {
                $this->{'setup'.$methodName.'Defaults'}();
            }
        }
    }

    /**
     * Load configurations for the current operation.
     *
     * Allow developers to insert default settings by creating a method
     * that looks like setupOperationNameOperation (aka setupXxxOperation).
     */
    protected function setupConfigurationForCurrentOperation(?string $operation = null)
    {
        $operationName = $operation ?? $this->crud->getCurrentOperation();
        if (! $operationName) {
            return;
        }
        $setupClassName = 'setup'.Str::studly($operationName).'Operation';

        /*
         * FIRST, run all Operation Closures for this operation.
         *
         * It's preferred for this to run closures first, because
         * (1) setup() is usually higher in a controller than any other method, so it's more intuitive,
         * since the first thing you write is the first thing that is being run;
         * (2) operations use operation closures themselves, inside their setupXxxDefaults(), and
         * you'd like the defaults to be applied before anything you write. That way, anything you
         * write is done after the default, so you can remove default settings, etc;
         */
        LifecycleHook::trigger($operationName.':before_setup', [$this]);

        $this->crud->applyConfigurationFromSettings($operationName);
        /*
         * THEN, run the corresponding setupXxxOperation if it exists.
         */
        if (method_exists($this, $setupClassName)) {
            $this->{$setupClassName}();
        }

        LifecycleHook::trigger($operationName.':after_setup', [$this]);
    }

    public function __get($name)
    {
        if ($name === 'crud') {
            return CrudManager::getActiveCrudPanel(get_class($this));
        }

        return $this->{$name};
    }
}
