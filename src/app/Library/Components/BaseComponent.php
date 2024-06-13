<?php

namespace Backpack\CRUD\app\Library\Components;

use Backpack\CRUD\app\Library\Components\Interfaces\SmartCollectionInterface;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartComponentInterface;

class BaseComponent implements SmartComponentInterface
{
    public function __construct(protected SmartCollectionInterface $attributes)
    {
    }

    public static function makeOf(SmartCollectionInterface $attributes)
    {
        return new static($attributes);
    }

    public static function make(string|array $name): SmartComponentInterface
    {
        $attributes = new BaseCollection($name, static::attributes(), static::rules(), static::defaults(), static::blocked());

        return  static::makeOf($attributes);
    }

    protected function remove()
    {
        $this->attributes->deleteItem($this->getName());
    }

    public function __call($name, $arguments)
    {
        if (! method_exists($this, $name)) {
            if ($this->isAttributeBlocked($name)) {
                throw new \Exception("Attribute {$name} cannot be changed in this component.");
            }

            $this->attributes->setAttribute($name, $arguments[0] ?? null);

            return $this;
        }
    }

    private function isAttributeBlocked(string $attribute): bool
    {
        return in_array($attribute, $this->blocked());
    }

    public static function defaults(): array
    {
        return static::attributes();
    }

    public static function rules(): array
    {
        return static::attributes();
    }

    public static function blocked(): array
    {
        return static::attributes();
    }

    public static function attributes(): array
    {
        return [];
    }
}
