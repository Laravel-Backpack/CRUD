<?php

namespace Backpack\CRUD\app\Library\Components;

use Backpack\CRUD\app\Library\Components\Interfaces\SmartComponentInterface;
use Closure;
use Illuminate\Support\Collection;

class CollectionRepository
{
    public function __construct(private Closure $getCollection, private Closure $saveCollection)
    {
    }

    public function addItem($key, $value, $save = true)
    {
        $items = $this->getItems();
        $items[$key] = $value;
        ! $save ?: $this->save($items->toArray());
    }

    public function getItems(): Collection
    {
        if ($this->getCollection) {
            $collection = ($this->getCollection)();

            return collect($collection);
        }
    }

    public function removeItem($attribute, $save = true)
    {
        $items = $this->getItems()->filter(function ($collectionItem, $key) use ($attribute) {
            return $collectionItem->getName() !== $attribute;
        });

        ! $save ?: $this->save($items);
    }

    private function save($collection)
    {
        if (is_callable($this->saveCollection)) {
            ($this->saveCollection)($collection);
        }
    }

    public function setItemAttributes($key, $attributes)
    {
        $this->addItem($key, $attributes);
    }

    public function getItemByName($name)
    {
        return $this->getItems()->first(function ($item, $key) use ($name) {
            return $key === $name;
        });
    }

    public function replaceItem(SmartComponentInterface $item)
    {
        $items = $this->removeItem($item, false);
        $items = $this->addItem($item, false);

        $this->save($items);
    }

    public function getAttribute($attribute)
    {
        return $this->attributes[$attribute];
    }
}
