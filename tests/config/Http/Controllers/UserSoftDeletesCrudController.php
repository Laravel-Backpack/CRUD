<?php

namespace Backpack\CRUD\Tests\Config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\Tests\config\Models\UserWithSoftDeletes;

class UserSoftDeletesCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel(UserWithSoftDeletes::class);
        $this->crud->setRoute(config('backpack.base.route_prefix').'/users-soft-deletes');
    }

    protected function setupShowOperation()
    {
        $this->crud->set('show.softDeletes', true);
        $this->crud->addClause('where', 'extras', 'allowed');
    }
}
