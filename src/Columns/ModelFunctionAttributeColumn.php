<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasAttribute;
use Backpack\CRUD\CRUDTraits\HasFunctionName;
use Backpack\CRUD\CRUDTraits\HasLabel;

class ModelFunctionAttributeColumn extends Column
{
    use HasLabel, HasFunctionName, HasAttribute;

    protected $type = 'model_function_attribute';
}