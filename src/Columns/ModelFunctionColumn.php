<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasFunctionName;
use Backpack\CRUD\CRUDTraits\HasLabel;

class ModelFunctionColumn extends Column
{
    use HasLabel, HasFunctionName;

    protected $type = 'model_function';
}