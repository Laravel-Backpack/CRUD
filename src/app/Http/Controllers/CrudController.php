<?php

namespace Backpack\CRUD\app\Http\Controllers;

use Backpack\CRUD\app\Library\Attributes\DeprecatedIgnoreOnRuntime;
use Backpack\CRUD\app\Library\CrudPanel\Hooks\Contracts\OperationHook;
use Backpack\CRUD\app\Library\CrudPanel\Hooks\Contracts\PanelHook;
use Backpack\CRUD\app\Library\CrudPanel\Hooks\OperationHooks;
use Backpack\CRUD\app\Library\CrudPanel\Hooks\PanelHooks;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

/**
 * Class CrudController.
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 * @property array $data
 */
class CrudController extends Controller
{
    use DispatchesJobs, ValidatesRequests;

    public $crud;
    public $data = [];

    public function __construct()
    {
        if ($this->crud) {
            return;
        }

        // ---------------------------
        // Create the CrudPanel object
        // ---------------------------
        // Used by developers inside their ProductCrudControllers as
        // $this->crud or using the CRUD facade.
        //
        // It's done inside a middleware closure in order to have
        // the complete request inside the CrudPanel object.
        $this->middleware(function ($request, $next) {
            $this->crud = app('crud');

            $this->crud->setRequest($request);

            PanelHook::run(PanelHooks::BEFORE_SETUP_DEFAULTS, [$this]);
            $this->setupDefaults();
            PanelHook::run(PanelHooks::AFTER_SETUP_DEFAULTS, [$this]);

            PanelHook::run(PanelHooks::BEFORE_CONTROLLER_SETUP, [$this]);
            $this->setup();
            PanelHook::run(PanelHooks::AFTER_CONTROLLER_SETUP, [$this]);

            $this->setupConfigurationForCurrentOperation();

            return $next($request);
        });
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
    protected function setupConfigurationForCurrentOperation()
    {
        $operationName = $this->crud->getCurrentOperation();
        if (! $operationName) {
            return;
        }

        $setupClassName = 'setup'.Str::studly($operationName).'Operation';

        /*
         * FIRST, run all Operation Closures for this operation.
         *
         * It's preferred for this to closures first, because
         * (1) setup() is usually higher in a controller than any other method, so it's more intuitive,
         * since the first thing you write is the first thing that is being run;
         * (2) operations use operation closures themselves, inside their setupXxxDefaults(), and
         * you'd like the defaults to be applied before anything you write. That way, anything you
         * write is done after the default, so you can remove default settings, etc;
         */
        if (! OperationHook::has(OperationHooks::SETUP_OPERATION_FROM_CONFIG, $operationName)) {
            OperationHook::register(OperationHooks::SETUP_OPERATION_FROM_CONFIG, $operationName, function () use ($operationName) {
                return 'backpack.operations.'.$operationName;
            });
        }

        $this->crud->loadDefaultOperationSettingsFromConfig(OperationHook::run(OperationHooks::SETUP_OPERATION_FROM_CONFIG, $operationName, [$this]));

        OperationHook::run(OperationHooks::BEFORE_OPERATION_SETUP, $operationName, [$this]);
        /*
         * THEN, run the corresponding setupXxxOperation if it exists.
         */
        if (method_exists($this, $setupClassName)) {
            $this->{$setupClassName}();
        }

        OperationHook::run(OperationHooks::AFTER_OPERATION_SETUP, $operationName, [$this]);
    }
}
