<?php

namespace Backpack\CRUD\Tests\config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\Tests\config\Models\RepeatableUploader;

class UploaderPathCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(RepeatableUploader::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/uploader-path');
        CRUD::setEntityNameStrings('uploader', 'uploaders');
    }

    protected function setupCreateOperation()
    {
        $withFiles = ['disk' => 'uploaders', 'path' => 'uploads', 'fileNamer' => fn ($file) => $file->getClientOriginalName()];

        CRUD::field('upload')->type('upload')->withFiles($withFiles);
        CRUD::field('upload_multiple')->type('upload_multiple')->withFiles($withFiles);
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
