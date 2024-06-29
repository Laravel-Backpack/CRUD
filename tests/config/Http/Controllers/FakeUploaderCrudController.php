<?php

namespace Backpack\CRUD\Tests\config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\Tests\config\Models\FakeUploader;

class FakeUploaderCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(FakeUploader::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/fake-uploader');
        CRUD::setEntityNameStrings('uploader', 'uploaders');
    }

    protected function setupCreateOperation()
    {
        CRUD::field('upload')
            ->type('upload')
            ->fake(true)
            ->withFiles(['disk' => 'uploaders', 'fileNamer' => fn ($value) => $value->getClientOriginalName()]);
        CRUD::field('upload_multiple')
            ->type('upload_multiple')
            ->fake(true)
            ->withFiles(['disk' => 'uploaders', 'fileNamer' => fn ($value) => $value->getClientOriginalName()]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function setupDeleteOperation()
    {
        $this->setupCreateOperation();
    }
}
