<?php

namespace Backpack\CRUD\app\Library\Components\Attributes;

use Backpack\CRUD\app\Library\Components\Interfaces\SmartAttributeInterface;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartCollectionInterface;
use Illuminate\Support\Str;

class Entity extends BaseAttribute implements SmartAttributeInterface
{
    public static function getDefault(SmartCollectionInterface $attributes)
    {
        $model = $attributes->hasAttribute('baseModel') ? (new $attributes->getAttributeValue('baseModel')) : app('crud')->model;

        $fieldName = $attributes->getAttributeValue('name');

        //if the name is dot notation we are sure it's a relationship
        if (strpos($fieldName, '.') !== false) {
            $possibleMethodName = Str::of($fieldName)->before('.');

            return self::modelMethodIsRelationship($model, $possibleMethodName) ? $fieldName : false;
        }

        // if there's a method on the model with this name
        if (method_exists($model, $fieldName)) {
            // check model method for possibility of being a relationship
            return self::modelMethodIsRelationship($model, $fieldName);
        }

        // if the name ends with _id and that method exists,
        // we can probably use it as an entity
        if (Str::endsWith($fieldName, '_id')) {
            $possibleMethodName = Str::replaceLast('_id', '', $fieldName);

            if (method_exists($model, $possibleMethodName)) {
                // check model method for possibility of being a relationship
                return self::modelMethodIsRelationship($model, $possibleMethodName);
            }
        }

        return false;
    }

    public static function getValidationRules(SmartCollectionInterface $attributes): array
    {
        return ['required'];
    }

    public static function getAttributeName(): string
    {
        return 'entity';
    }

    /**
     * Checks the properties of the provided method to better verify if it could be a relation.
     * Case the method is not public, is not a relation.
     * Case the return type is Attribute, or extends Attribute is not a relation method.
     * If the return type extends the Relation class is for sure a relation
     * Otherwise we just assume it's a relation.
     *
     * DEV NOTE: In future versions we will return `false` when no return type is set and make the return type mandatory for relationships.
     *           This function should be refactored to only check if $returnType is a subclass of Illuminate\Database\Eloquent\Relations\Relation.
     *
     * @param $model
     * @param $method
     * @return bool|string
     */
    private static function modelMethodIsRelationship($model, $method)
    {
        $methodReflection = new \ReflectionMethod($model, $method);

        // relationship methods function does not have parameters
        if ($methodReflection->getNumberOfParameters() > 0) {
            return false;
        }

        // relationships are always public methods.
        if (! $methodReflection->isPublic()) {
            return false;
        }

        $returnType = $methodReflection->getReturnType();

        if ($returnType) {
            $returnType = $returnType->getName();

            if (is_a($returnType, 'Illuminate\Database\Eloquent\Casts\Attribute', true)) {
                return false;
            }

            if (is_a($returnType, 'Illuminate\Database\Eloquent\Relations\Relation', true)) {
                return $method;
            }
        }

        return $method;
    }
}
