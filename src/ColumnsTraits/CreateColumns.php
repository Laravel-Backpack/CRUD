<?php

namespace Backpack\CRUD\ColumnsTraits;

use Backpack\CRUD\Columns\ArrayColumn;
use Backpack\CRUD\Columns\ArrayCountColumn;
use Backpack\CRUD\Columns\BooleanColumn;
use Backpack\CRUD\Columns\CheckColumn;
use Backpack\CRUD\Columns\CustomColumn;
use Backpack\CRUD\Columns\ModelFunctionAttributeColumn;
use Backpack\CRUD\Columns\ModelFunctionColumn;
use Backpack\CRUD\Columns\MultidimensionalArrayColumn;
use Backpack\CRUD\Columns\RadioColumn;
use Backpack\CRUD\Columns\SelectColumn;
use Backpack\CRUD\Columns\SelectMultipleColumn;
use Backpack\CRUD\Columns\TextColumn;
use Backpack\CRUD\Columns\VideoColumn;

trait CreateColumns
{
    /**
     * @return \Backpack\CRUD\Columns\ArrayColumn
     */
    public function array()
    {
        return new ArrayColumn();
    }

    /**
     * @return \Backpack\CRUD\Columns\ArrayCountColumn
     */
    public function arrayCount()
    {
        return new ArrayCountColumn();
    }

    /**
     * @return \Backpack\CRUD\Columns\BooleanColumn
     */
    public function boolean()
    {
        return new BooleanColumn();
    }

    /**
     * @return \Backpack\CRUD\Columns\CheckColumn
     */
    public function check()
    {
        return new CheckColumn();
    }

    /**
     * @param $type
     * @return \Backpack\CRUD\Columns\CustomColumn
     */
    public function custom($type)
    {
        return (new CustomColumn())->type($type);
    }

    /**
     * @return \Backpack\CRUD\Columns\ModelFunctionAttributeColumn
     */
    public function modelFunctionAttribute()
    {
        return new ModelFunctionAttributeColumn();
    }

    /**
     * @return \Backpack\CRUD\Columns\ModelFunctionColumn
     */
    public function modelFunction()
    {
        return new ModelFunctionColumn();
    }

    /**
     * @return \Backpack\CRUD\Columns\MultidimensionalArrayColumn
     */
    public function multidimensionalArray()
    {
        return new MultidimensionalArrayColumn();
    }

    /**
     * @return \Backpack\CRUD\Columns\RadioColumn
     */
    public function radio()
    {
        return new RadioColumn();
    }

    /**
     * @return \Backpack\CRUD\Columns\SelectColumn
     */
    public function select()
    {
        return new SelectColumn();
    }

    /**
     * @return \Backpack\CRUD\Columns\SelectMultipleColumn
     */
    public function selectMultiple()
    {
        return new SelectMultipleColumn();
    }

    /**
     * @return \Backpack\CRUD\Columns\TextColumn
     */
    public function text()
    {
        return new TextColumn();
    }

    /**
     * @return \Backpack\CRUD\Columns\VideoColumn
     */
    public function video()
    {
        return new VideoColumn();
    }
}