<?php

namespace Backpack\CRUD\Tests\Config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\Tests\config\Models\User;

class UserAccessConditionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup()
    {
        $this->crud->setModel(User::class);
        $this->crud->setRoute(config('backpack.base.route_prefix').'/users-access-condition');
    }

    protected function setupUpdateOperation()
    {
        $this->crud->setAccessCondition('update', fn ($entry) => $entry?->getKey() === 1);
        $this->crud->field('name');
    }
}
