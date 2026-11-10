<?php

namespace Backpack\CRUD\Tests\config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\Concerns\HasForm;
use Backpack\CRUD\Tests\config\Models\User;

class UserFormOperationCrudController extends CrudController
{
    use HasForm;

    public function setup()
    {
        $this->crud->setModel(User::class);
        $this->crud->setRoute(config('backpack.base.route_prefix').'/users-form-operation');
    }

    protected function setupRenameRoutes(string $segment, string $routeName, string $controller): void
    {
        $this->formRoutes('rename', true, $segment, $routeName, $controller);
    }

    protected function setupRenameDefaults(): void
    {
        $this->formDefaults('rename');
    }

    protected function setupRenameOperation()
    {
        $this->crud->setAccessCondition('rename', fn ($entry) => $entry?->getKey() === 1);
        $this->crud->field('name');
    }

    public function getRenameForm(int $id)
    {
        return $this->formView($id);
    }

    public function postRenameForm(int $id)
    {
        return $this->formAction($id, function ($inputs, $entry) {
            $entry->update(['name' => $inputs['name']]);
        });
    }
}
