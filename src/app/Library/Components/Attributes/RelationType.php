<?php

namespace Backpack\CRUD\app\Library\Components\Attributes;

use Backpack\CRUD\app\Library\Components\Interfaces\SmartAttributeInterface;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartCollectionInterface;

class RelationType extends BaseAttribute implements SmartAttributeInterface
{
    public static function getDefault(SmartCollectionInterface $attributes)
    {
        return $attributes->getAttributeValue('entity') ? app('crud')->inferRelationTypeFromRelationship($attributes->toArray()) : false;
    }

    public static function getValidationRules(SmartCollectionInterface $attributes): array
    {
        return [
            function ($attribute, $value, $fail) use ($attributes) {
                if ($attributes->hasAttribute('entity') && $attributes->getAttributeValue('entity')) {
                    if (! is_string($value)) {
                        $fail('The '.$attribute.' should be a valid relation type string.');
                    }
                }
            },
        ];
    }

    public static function getAttributeName(): string
    {
        return 'relation_type';
    }
}
