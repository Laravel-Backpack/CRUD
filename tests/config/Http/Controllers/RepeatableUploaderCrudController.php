<?php

namespace Backpack\CRUD\Tests\config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\Tests\config\Models\RepeatableUploader;

class RepeatableUploaderCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    protected string $uploadPath = '';

    protected string $routeSegment = 'repeatable-uploader';

    public function setup()
    {
        CRUD::setModel(RepeatableUploader::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/'.$this->routeSegment);
        CRUD::setEntityNameStrings('uploader', 'uploaders');
    }

    protected function setupCreateOperation()
    {
        $withFiles = ['disk' => 'uploaders', 'path' => $this->uploadPath, 'fileNamer' => fn ($file) => $file->getClientOriginalName()];

        CRUD::field('repeatable')->type('repeatable')->subfields([
            ['name' => 'upload', 'type' => 'upload', 'withFiles' => $withFiles],
            ['name' => 'upload_multiple', 'type' => 'upload_multiple', 'withFiles' => $withFiles],
            ['name' => 'image', 'type' => 'image', 'withFiles' => array_merge($withFiles, ['fileNamer' => fn ($file) => 'image.jpg'])],
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
