<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

class CompanyCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Company');
        $this->crud->setRoute(config('backpack.base.route_prefix').'/company');
        $this->crud->setEntityNameStrings('company', 'companies');
    }

    protected function setupListOperation()
    {
        $this->crud->setColumnDetails('name', [
            'label'       => 'Company Name',
            'type'        => 'text',
            'searchable'  => true,
            'orderable'   => true,
        ]);

        $this->crud->setColumnDetails('description', [
            'label'       => 'Description',
            'type'        => 'textarea',
            'searchable'  => true,
            'limit'       => 200,
            'escaped'     => true,
        ]);

        $this->crud->setColumnDetails('email', [
            'label'       => 'Email Address',
            'type'        => 'email',
            'searchable'  => true,
        ]);

        $this->crud->setColumnDetails('website', [
            'label'       => 'Website',
            'type'        => 'url',
            'searchable'  => false,
        ]);

        $this->crud->setColumnDetails('status', [
            'label'       => 'Active',
            'type'        => 'boolean',
            'searchable'  => false,
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setFieldDetails('name', [
            'label'       => 'Company Name',
            'type'        => 'text',
            'placeholder' => 'Enter company name',
            'required'    => true,
            'attributes'  => [
                'maxlength' => 255,
            ],
        ]);

        $this->crud->setFieldDetails('slug', [
            'label'       => 'URL Slug',
            'type'        => 'text',
            'placeholder' => 'auto-generated-slug',
            'required'    => true,
            'attributes'  => [
                'maxlength' => 255,
            ],
        ]);

        $this->crud->setFieldDetails('description', [
            'label'       => 'Description',
            'type'        => 'textarea',
            'placeholder' => 'Enter company description',
            'attributes'  => [
                'rows' => 5,
            ],
        ]);

        $this->crud->setFieldDetails('email', [
            'label'       => 'Email Address',
            'type'        => 'email',
            'placeholder' => 'contact@company.com',
            'required'    => true,
        ]);

        $this->crud->setFieldDetails('website', [
            'label'       => 'Website URL',
            'type'        => 'url',
            'placeholder' => 'https://example.com',
        ]);

        $this->crud->setFieldDetails('status', [
            'label'       => 'Active',
            'type'        => 'checkbox',
            'default'     => true,
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        $this->crud->setColumnDetails('name', [
            'label' => 'Company Name',
            'type'  => 'text',
        ]);

        $this->crud->setColumnDetails('slug', [
            'label' => 'URL Slug',
            'type'  => 'text',
        ]);

        $this->crud->setColumnDetails('description', [
            'label' => 'Description',
            'type'  => 'textarea',
        ]);

        $this->crud->setColumnDetails('email', [
            'label' => 'Email Address',
            'type'  => 'email',
        ]);

        $this->crud->setColumnDetails('website', [
            'label' => 'Website URL',
            'type'  => 'url',
        ]);

        $this->crud->setColumnDetails('status', [
            'label' => 'Active',
            'type'  => 'boolean',
        ]);

        $this->crud->setColumnDetails('created_at', [
            'label' => 'Created',
            'type'  => 'datetime',
        ]);

        $this->crud->setColumnDetails('updated_at', [
            'label' => 'Updated',
            'type'  => 'datetime',
        ]);
    }
}
