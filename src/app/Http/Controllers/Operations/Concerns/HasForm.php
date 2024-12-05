<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations\Concerns;

use Backpack\CRUD\app\Library\CrudPanel\Hooks\Facades\LifecycleHook;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait HasForm
{
    /**
     * Set up a GET and POST route, for an operation that contains a form.
     */
    protected function formRoutes(string $operationName, bool $routesHaveIdSegment, string $segment, string $routeName, string $controller): void
    {
        $secondSegment = $routesHaveIdSegment ? '/{id}/' : '/';
        $thirdSegment = Str::of($operationName)->kebab();
        $getFormMethod = 'get'.$operationName.'Form';
        $postFormMethod = 'post'.$operationName.'Form';

        Route::get($segment.$secondSegment.$thirdSegment, [
            'as' => $routeName.'.'.$getFormMethod,
            'uses' => $controller.'@'.$getFormMethod,
            'operation' => $operationName,
        ]);
        Route::post($segment.$secondSegment.$thirdSegment, [
            'as' => $routeName.'.'.$postFormMethod,
            'uses' => $controller.'@'.$postFormMethod,
            'operation' => $operationName,
        ]);
    }

    /**
     * Set up the default configurations, save actions and buttons
     * for a standard operation that contains a form.
     */
    protected function formDefaults(string $operationName, string $buttonStack = 'line', array $buttonMeta = []): void
    {
        // Access
        $this->crud->allowAccess($operationName);

        LifecycleHook::hookInto($operationName.':before_setup', function () use ($operationName) {
            // if the backpack.operations.{operationName} config exists, use that one
            // otherwise, use the generic backpack.operations.form config
            if (config()->has('backpack.operations.'.$operationName)) {
                $this->crud->loadDefaultOperationSettingsFromConfig();
            } else {
                $this->crud->loadDefaultOperationSettingsFromConfig('backpack.operations.form');
            }

            // add a reasonable "save and back" save action
            $this->crud->addSaveAction([
                'name' => 'save_and_back',
                'visible' => function ($crud) use ($operationName) {
                    return $crud->hasAccess($operationName);
                },
                'redirect' => function ($crud, $request, $itemId = null) {
                    return $request->request->has('_http_referrer') ? $request->request->get('_http_referrer') : $crud->route;
                },
                'button_text' => trans('backpack::crud.save_action_save_and_back'),
            ]);
        });

        LifecycleHook::hookInto(['list:before_setup', 'show:before_setup'], function () use ($operationName, $buttonStack, $buttonMeta) {
            $this->crud->button($operationName)->view('crud::buttons.quick')->stack($buttonStack)->meta($buttonMeta);
        });
    }

    /**
     * Method to handle the GET request and display the View with a Backpack form.
     */
    public function formView(?int $id = null): \Illuminate\Contracts\View\View
    {
        if ($id) {
            // Get entry ID from Request (makes sure its the last ID for nested resources)
            $this->data['id'] = $this->crud->getCurrentEntryId() ?: $id;
            $this->data['entry'] = $this->crud->getEntryWithLocale($this->data['id']);
        }

        $this->crud->setOperationSetting('fields', $this->crud->fields());

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? Str::of($this->crud->getCurrentOperation())->headline();
        $this->data['operation'] = $this->crud->getCurrentOperation();

        if ($this->crud->getOperationSetting('save_actions')) {
            $this->data['saveAction'] = $this->crud->getSaveAction();
        }

        $this->data['formAction'] = $this->crud->getOperationSetting('form_action');
        $this->data['formMethod'] = $this->crud->getOperationSetting('form_method');

        return view($this->crud->getOperationSetting('view') ?? 'crud::inc.form_page', $this->data);
    }

    /**
     * Method to handle the POST request and save the form.
     * It performs the validation, authorization and redirect according to the save action.
     * But it does NOT save the data. That should be done in the given $formLogic callback.
     *
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function formAction(?int $id = null, callable $formLogic)
    {
        if ($id) {
            // Get entry ID from Request (makes sure its the last ID for nested resources)
            $id = $this->crud->getCurrentEntryId() ?: $id;
            $entry = $this->crud->getEntryWithLocale($id);
        }

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();
        $request = $this->crud->getStrippedSaveRequest($request);

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // perform the actual logic, that developers give in a callback
        ($formLogic)($request ?? null, $entry ?? null);

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($id);
    }
}
