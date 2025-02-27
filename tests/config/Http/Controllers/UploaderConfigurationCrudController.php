<?php

namespace Backpack\CRUD\Tests\config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\Tests\config\Models\Uploader;
use Illuminate\Support\Facades\Route;

class UploaderConfigurationCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(Uploader::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/uploader-configuration');
        CRUD::setEntityNameStrings('uploader', 'uploaders');
    }

    protected function setupCustomConfigurationRoutes()
    {
        Route::get(config('backpack.base.route_prefix').'/uploader-configuration/invalid-file-namer', [self::class, 'invalidFileNamer'])->name('uploader-configuration.file-namer');
        Route::get(config('backpack.base.route_prefix').'/uploader-configuration/invalid-file-namer-class', [self::class, 'invalidFileNamerClass'])->name('uploader-configuration.file-namer-class');
        Route::post(config('backpack.base.route_prefix').'/uploader-configuration/custom-uploader', [self::class, 'customUploader'])->name('uploader-configuration.custom-uploader');
        Route::post(config('backpack.base.route_prefix').'/uploader-configuration/custom-invalid-uploader', [self::class, 'customInvalidUploader'])->name('uploader-configuration.custom-invalid-uploader');
        Route::get(config('backpack.base.route_prefix').'/uploader-configuration/set-temporary-options', [self::class, 'temporaryOptions'])->name('uploader-configuration.temporary-options');
    }

    protected function setupCreateOperation()
    {
        //CRUD::setValidation(UploaderRequest::class);

        CRUD::field('upload')->type('upload')->withFiles(['disk' => 'uploaders', 'path' => 'test']);
        CRUD::field('upload_multiple')->type('upload_multiple')->withFiles(['disk' => 'uploaders']);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function setupDeleteOperation()
    {
        $this->setupCreateOperation();
    }

    protected function invalidFileNamer()
    {
        CRUD::field('upload')->type('upload')->withFiles(['disk' => 'uploaders', 'fileNamer' => 'invalid']);

        return $this->store();
    }

    protected function invalidFileNamerClass()
    {
        CRUD::field('upload')->type('upload')->withFiles(['disk' => 'uploaders', 'fileNamer' => \Backpack\CRUD\Tests\config\Models\User::class]);

        return $this->store();
    }

    protected function customUploader()
    {
        CRUD::field('upload')->type('upload')->withFiles(['disk' => 'uploaders', 'uploader' => \Backpack\CRUD\Tests\config\Uploads\CustomUploader::class]);

        return $this->store();
    }

    protected function customInvalidUploader()
    {
        CRUD::field('upload')->type('upload')->withFiles(['disk' => 'uploaders', 'uploader' => 'InvalidUploader']);

        return $this->store();
    }

    protected function temporaryOptions()
    {
        CRUD::field('upload')->type('upload')->withFiles(['disk' => 'uploaders', 'temporary' => true]);

        return $this->store();
    }
}
