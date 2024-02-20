<?php

namespace Backpack\CRUD\app\Library\Components\Attributes;

use Backpack\CRUD\app\Library\Components\Interfaces\SmartAttributeInterface;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartCollectionInterface;

class Multiple extends BaseAttribute implements SmartAttributeInterface
{
    public static function getDefault(SmartCollectionInterface $attributes)
    {
        switch ($attributes->getAttributeValue('relation_type')) {
            case 'BelongsToMany':
            case 'HasMany':
            case 'HasManyThrough':
            case 'HasOneOrMany':
            case 'MorphMany':
            case 'MorphOneOrMany':
            case 'MorphToMany':
                return true;
            default:
                return false;
        }

        return false;
    }

    public static function getValidationRules(SmartCollectionInterface $attributes): array
    {
        return ['nullable', 'string'];
    }

    public static function getAttributeName(): string
    {
        return 'relation_type';
    }
}
