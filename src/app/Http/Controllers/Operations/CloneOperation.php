<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait CloneOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupCloneRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/{id}/clone', [
            'as' => $routeName.'.clone',
            'uses' => $controller.'@clone',
            'operation' => 'clone',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCloneDefaults()
    {
        $this->crud->allowAccess('clone');

        $this->crud->operation('clone', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation(['list', 'show'], function () {
            $this->crud->addButton('line', 'clone', 'view', 'crud::buttons.clone', 'end');
        });
    }

    /**
     * Create a duplicate of the current entry in the datatabase.
     *
     * @param  int  $id
     * @return Response
     */
    public function clone($id)
    {
        $this->crud->hasAccessOrFail('clone');

        $clonedEntry = $this->crud->model->findOrFail($id)->replicate();

        return (string) $clonedEntry->push();
    }
}
