<?php

namespace Backpack\CRUD\app\Library\Components\Attributes;

use Backpack\CRUD\app\Library\Components\Interfaces\SmartAttributeInterface;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartCollectionInterface;

class Label extends BaseAttribute implements SmartAttributeInterface
{
    public static function getDefault(SmartCollectionInterface $attributes)
    {
        return ucfirst($attributes->getAttributeValue('name'));
    }

    public static function getValidationRules(SmartCollectionInterface $attributes): array
    {
        return ['required', 'string'];
    }

    public static function getAttributeName(): string
    {
        return 'label';
    }
}
