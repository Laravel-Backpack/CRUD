<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait BulkCloneOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupBulkCloneRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/bulk-clone', [
            'as' => $routeName.'.bulkClone',
            'uses' => $controller.'@bulkClone',
            'operation' => 'bulkClone',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupBulkCloneDefaults()
    {
        $this->crud->allowAccess('bulkClone');

        $this->crud->operation('bulkClone', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            $this->crud->enableBulkActions();
            $this->crud->addButton('bottom', 'bulk_clone', 'view', 'crud::buttons.bulk_clone', 'beginning');
        });
    }

    /**
     * Create duplicates of multiple entries in the datatabase.
     *
     * @param  int  $id
     * @return Response
     */
    public function bulkClone()
    {
        $this->crud->hasAccessOrFail('bulkClone');

        $entries = request()->input('entries', []);
        $clonedEntries = [];

        foreach ($entries as $key => $id) {
            if ($entry = $this->crud->model->find($id)) {
                $clonedEntries[] = $entry->replicate()->push();
            }
        }

        return $clonedEntries;
    }
}
