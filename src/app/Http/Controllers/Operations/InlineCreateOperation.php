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
            'operation' => 'InlineCreate',
        ]);
        Route::post($segment.'/inline/create', [
            'as'        => $segment.'-inline-create-save',
            'uses'      => $controller.'@storeInlineCreate',
            'operation' => 'InlineCreate',
        ]);
    }

    /**
     * Setup operation default settings. We run setup() and setupCreateOperation() because those are run in middleware
     * and to get the fields we need them earlier in application lifecycle.
     */
    protected function setupInlineCreateDefaults()
    {
        if (method_exists($this, 'setup')) {
            $this->setup();
        }
        if (method_exists($this, 'setupCreateOperation')) {
            $this->setupCreateOperation();
        }

        $this->crud->applyConfigurationFromSettings('create');
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
                'parentLoadedAssets' => request()->get('parent_loaded_assets'),
            ]
        );
    }

    /**
     * Runs the store() function in controller like a regular crud create form.
     * Developer might overwrite this if he wants some custom save behaviour when added on the fly.
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
