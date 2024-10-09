<?php

namespace Backpack\CRUD\Tests\config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\Tests\config\Http\Requests\UploaderRequest;
use Backpack\CRUD\Tests\config\Models\Uploader;

class UploaderValidationCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(Uploader::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/uploader-validation');
        CRUD::setEntityNameStrings('uploader', 'uploaders');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(UploaderRequest::class);

        CRUD::field('upload')
            ->type('upload')
            ->withFiles(['disk' => 'uploaders', 'fileNamer' => fn ($value) => $value->getClientOriginalName()]);
        CRUD::field('upload_multiple')
            ->type('upload_multiple')
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
