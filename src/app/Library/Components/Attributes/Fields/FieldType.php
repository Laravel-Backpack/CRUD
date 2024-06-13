<?php

namespace Backpack\CRUD\app\Library\Components\Attributes\Fields;

use Backpack\CRUD\app\Library\Components\Attributes\BaseAttribute;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartAttributeInterface;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartCollectionInterface;

class FieldType extends BaseAttribute implements SmartAttributeInterface
{
    public static function getDefault(SmartCollectionInterface $attributes)
    {
        if (backpack_pro() && $attributes->getAttributeValue('relation_type')) {
            return 'relationship';
        }

        switch ($attributes->getAttributeValue('relation_type')) {
            case 'BelongsTo':
                return 'select';
            case 'BelongsToMany':
            case 'MorphToMany':
                return 'select_multiple';
            default:
                return 'text';
        }
    }

    public static function getValidationRules(SmartCollectionInterface $attributes): array
    {
        return ['required'];
    }

    public static function getAttributeName(): string
    {
        return 'type';
    }
}
