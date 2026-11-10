<?php

namespace Backpack\CRUD\Tests\Config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\Tests\config\Models\User;

class UserAccessConditionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        $this->crud->setModel(User::class);
        $this->crud->setRoute(config('backpack.base.route_prefix').'/users-access-condition');
        $this->crud->setEntityNameStrings('user', 'users');
    }

    protected function setupUpdateOperation()
    {
        $this->crud->setAccessCondition('update', fn ($entry) => $entry?->getKey() === 1);
        $this->crud->field('name');
    }

    protected function setupDeleteOperation()
    {
        $this->crud->setAccessCondition('delete', fn ($entry) => $entry?->getKey() === 1);
    }
}
