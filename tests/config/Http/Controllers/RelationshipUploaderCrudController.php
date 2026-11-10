<?php

namespace Backpack\CRUD\Tests\config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\Tests\config\Models\Uploader;

class RelationshipUploaderCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(Uploader::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/relationship-uploader');
        CRUD::setEntityNameStrings('uploader', 'uploaders');
    }

    protected function setupCreateOperation()
    {
        $withFiles = ['disk' => 'uploaders', 'fileNamer' => fn ($file) => $file->getClientOriginalName()];

        CRUD::field('relatedUploaders')->subfields([
            ['name' => 'upload', 'type' => 'upload', 'withFiles' => $withFiles],
            ['name' => 'image', 'type' => 'image', 'withFiles' => $withFiles],
        ]);
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
