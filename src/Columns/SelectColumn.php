<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasAttribute;
use Backpack\CRUD\CRUDTraits\HasEntity;
use Backpack\CRUD\CRUDTraits\HasLabel;
use Backpack\CRUD\CRUDTraits\HasModel;
use Backpack\CRUD\CRUDTraits\HasName;

class SelectColumn extends Column
{
    use HasLabel, HasName, HasEntity, HasAttribute, HasModel;

    protected $type = 'select';
}