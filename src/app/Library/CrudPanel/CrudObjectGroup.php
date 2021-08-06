<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

class CrudObjectGroup
{
    protected $objects;

    // init objects in group, supports multiple parameters or array as input.
    //
    // eg: CRUD::group(CRUD::field('field1'), CRUD::field('field2')); OR
    //     CRUD::group([
    //                   CRUD::field('field1'),
    //                   CRUD::field('field2'),
    //                 ]);

    public function __construct(...$objects)
    {
        if (is_array($objects[0])) {
            $objects = $objects[0];
        }

        $this->objects = $objects;
    }

    // -------------
    // MAGIC METHODS
    // -------------

    /**
     * We foward any call to the corresponding class passed by developer (Field, Columns, Filters etc ..).
     *
     * @param  string $method     The method being called that doesn't exist.
     * @param  array $parameters  The arguments when that method was called.
     *
     * @return CrudObjectGroup
     */
    public function __call($method, $parameter)
    {
        foreach ($this->objects as $object) {
            $object->{$method}($parameter[0]);
        }

        return $this;
    }
}
