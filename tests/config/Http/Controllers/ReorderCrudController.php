<?php

namespace Backpack\CRUD\Tests\config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\Tests\config\Models\Reorder;

class ReorderCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;

    public function setup()
    {
        $this->crud->setModel(Reorder::class);
        $this->crud->setRoute(config('backpack.base.route_prefix').'/reorder-items');
    }

    protected function setupReorderOperation()
    {
        $this->crud->set('reorder.label', 'name');
        $this->crud->set('reorder.max_level', 2);

        if (request()->query('disabled')) {
            $this->crud->disableReorder();
        }
    }
}
