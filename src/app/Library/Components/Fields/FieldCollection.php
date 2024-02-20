<?php

namespace Backpack\CRUD\app\Library\Components\Fields;

use Backpack\CRUD\app\Library\Components\BaseCollection;

class FieldCollection extends BaseCollection
{
    public function getCollection()
    {
        return app('crud')->getOperationSetting('fields') ?? [];
    }

    public function saveCollection($collection)
    {
        app('crud')->setOperationSetting('fields', $collection);
    }
}
