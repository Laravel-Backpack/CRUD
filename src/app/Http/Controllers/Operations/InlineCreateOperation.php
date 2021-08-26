<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;
use Prologue\Alerts\Facades\Alert;

trait InlineCreateOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupInlineCreateRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/inline/create/modal', [
            'as'        => $segment.'-inline-create',
            'uses'      => $controller.'@getInlineCreateModal',
            'operation' => 'DefaultInlineCreate',
        ]);
        Route::post($segment.'/inline/create', [
            'as'        => $segment.'-inline-create-save',
            'uses'      => $controller.'@storeInlineCreate',
            'operation' => 'DefaultInlineCreate',
        ]);
    }

    /**
     *  This operation have some known quirks that can only be addressed using this setup instead of `setupInlineCreateDefaults`
     *  1 - InlineCreateOperation must be added AFTER CreateOperation trait.
     *  2 - setup() in controllers that have the InlineCreateOperations need to be called twice (technically we are re-creating a new crud).
     *
     *  We use this setup to make this operation behaviour similar to other operations, so developer could still override
     *  the defaults with `setupInlineCreateOperation` as with any other operation.
     *
     *  `setupInlineCreateDefaults` is not used because of the time it is called in the stack. The "defaults" are applied in backpack
     *  to all operations, before applying operation specific setups. As this operation directly need to call other
     *  methods in the CrudController, it should only be "initialized" when it's requested.
     *
     *  To solve the mentioned problems we initialize the operation defaults at the same time we apply developer setup. Developer settings
     *  are applied after the defaults to prevail.
     */
    protected function setupDefaultInlineCreateOperation()
    {
        if (method_exists($this, 'setup')) {
            $this->setup();
        }
        if (method_exists($this, 'setupCreateOperation')) {
            $this->setupCreateOperation();
        }

        $this->crud->applyConfigurationFromSettings('create');

        if (method_exists($this, 'setupInlineCreateOperation')) {
            $this->setupInlineCreateOperation();
        }
    }

    /**
     * Returns the HTML of the create form. It's used by the CreateInline operation, to show that form
     * inside a popup (aka modal).
     */
    public function getInlineCreateModal()
    {
        if (! request()->has('entity')) {
            abort(400, 'No "entity" inside the request.');
        }

        return view(
            'crud::fields.relationship.inline_create_modal',
            [
                'fields' => $this->crud->getCreateFields(),
                'action' => 'create',
                'crud' => $this->crud,
                'entity' => request()->get('entity'),
                'modalClass' => request()->get('modal_class'),
                'parentLoadedFields' => request()->get('parent_loaded_fields'),
            ]
        );
    }

    /**
     * Runs the store() function in controller like a regular crud create form.
     * Developer might overwrite this if they want some custom save behaviour when added on the fly.
     *
     * @return void
     */
    public function storeInlineCreate()
    {
        $result = $this->store();

        // do not carry over the flash messages from the Create operation
        Alert::flush();

        return $result;
    }
}
