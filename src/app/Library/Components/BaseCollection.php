<?php

namespace Backpack\CRUD\app\Library\Components;

use Backpack\CRUD\app\Library\Components\Attributes\BaseAttribute;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartAttributeInterface;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartCollectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class BaseCollection implements SmartCollectionInterface
{
    private Collection $attributes;

    public function __construct(array|string $initAttributes,
                                private array $componentAttributes = [],
                                private array $rules = [],
                                private array $defaults = [],
                                private array $blockedAttributes = []
                            ) {
        $item = $this->getItemByName($initAttributes['name'] ?? $initAttributes);
        if ($item) {
            $this->buildAttributes((array) $item, $componentAttributes, $rules, $defaults);
        } else {
            $this->addCollectionItem($initAttributes, $componentAttributes);
        }
    }

    private function buildAttributes(array|string $attributes)
    {
        if (is_string($attributes)) {
            $attributes = ['name' => $attributes];
        }

        $rules = $this->rules;
        $defaults = $this->defaults;

        $attributeNames = collect($this->componentAttributes)->mapWithKeys(function ($attribute) {
            return [$attribute::getAttributeName() => $attribute];
        })->toArray();

        $attributeRules = collect($rules)->mapWithKeys(function ($rule) {
            if (is_a($rule, SmartAttributeInterface::class, true)) {
                return [$rule::getAttributeName() => $rule::getValidationRules($this)];
            }

            return [$rule::getAttributeName() => $rule];
        })->toArray();

        $this->attributes = collect($attributes)->mapWithKeys(function ($value, $key) use ($attributeNames, $attributeRules, $defaults) {
            if (isset($attributeNames[$key])) {
                return [$key => $attributeNames[$key]::make($key, $value, $attributeNames[$key]::getValidationRules($this))];
            }

            return [$key => new BaseAttribute($key, $value, $attributeRules[$key] ?? [], $defaults)];
        });

        $this->setAttributeDefaults();
        $this->validate();
    }

    public function addCollectionItem($attributes)
    {
        $this->buildAttributes($attributes);
        $collection = $this->getCollection();
        $collection[$this->getAttributeValue('name')] = $this->toArray();
        $this->saveCollection($collection);
    }

    public function getItems(): Collection
    {
        return collect($this->getCollection());
    }

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function getItemByName(string $name)
    {
        return $this->getItems()->first(function ($item, $key) use ($name) {
            return $key === $name;
        });
    }

    public function hasAttribute(string $attribute): bool
    {
        return $this->attributes->has($attribute);
    }

    public function getAttributeValue($attribute, $default = null)
    {
        return $this->attributes->has($attribute) ? $this->attributes->get($attribute, $default)->value() : $default;
    }

    public function validate()
    {
        $attributes = $this->toArray();

        $rules = $this->getValidationRules();

        $validator = Validator::make($attributes, $rules)->stopOnFirstFailure();

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }

    private function getValidationRules()
    {
        $rules = [];
        foreach ($this->attributes as $attribute) {
            $rules[$attribute->name()] = $attribute->rules();
        }

        return $rules;
    }

    public function setAttribute($attribute, $value)
    {
        $item = $this->hasAttribute($attribute) ? $this->attributes->get($attribute)->setValue($value) : new BaseAttribute($attribute, $value);
        $this->updateItem($item);
    }

    private function updateItem(SmartAttributeInterface $item)
    {
        $this->attributes[$item->name()] = $item;
        $collection = $this->getCollection();
        $collection[$this->getAttributeValue('name')] = $this->toArray();
        $this->saveCollection($collection);
    }

    public function deleteItem(string $name)
    {
        $this->attributes->forget($name);
        $collection = $this->getCollection();
        $collection->forget($name);
        $this->saveCollection($collection);
    }

    public function toCollection(): Collection
    {
        return $this->attributes->mapWithKeys(function ($item, $key) {
            return [$key => $item->value()];
        });
    }

    public function toArray(): array
    {
        return $this->toCollection()->toArray();
    }

    private function setAttributeDefaults()
    {
        foreach ($this->componentAttributes as $attribute) {
            if (! $this->hasAttribute($attribute::getAttributeName())) {
                $this->attributes[$attribute::getAttributeName()] = $attribute::make($attribute::getAttributeName(), $attribute::getDefault($this), $attribute::getValidationRules($this));
            }
        }
    }

    public static function attributes(): array
    {
        return [];
    }

    public function getCollection()
    {
        return collect();
    }

    public function saveCollection($collection)
    {
        return [];
    }
}
