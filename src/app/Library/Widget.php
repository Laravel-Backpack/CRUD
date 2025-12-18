<?php

namespace Backpack\CRUD\app\Library;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;
use Backpack\CRUD\ViewNamespaces;
use Illuminate\Support\Fluent;

/**
 * Adds fluent syntax to Backpack Widgets.
 */
class Widget extends Fluent
{
    public $attributes = [];

    public function __construct($attributes)
    {
        $this->attributes = $attributes;

        $this->save();
    }

    /**
     * Add a new widget to the widgets collection in the Laravel Service Container.
     * If a widget with the same name exists, it will update the attributes of that one
     * instead of creating a new one.
     *
     * @param  string|array  $attributes  Either the name of the widget, or an array with the attributes the new widget should hold, including the name attribute.
     * @return Widget
     */
    public static function add($attributes = null)
    {
        // make sure the widget has a name
        $attributes = is_string($attributes) ? ['name' => $attributes] : $attributes;
        $attributes['name'] = $attributes['name'] ?? 'widget_'.rand(1, 999999999);

        // if that widget name already exists in the widgets collection
        // then pick up all widget attributes from that entry
        // and overwrite them with the ones passed in $attributes
        if ($existingItem = self::collection()->filter(function ($item) use ($attributes) {
            return $item->attributes['name'] === $attributes['name'];
        })->first()) {
            $attributes = array_merge($existingItem->attributes, $attributes);
        }

        // set defaults for other mandatory attributes
        $attributes['section'] = $attributes['section'] ?? 'before_content';
        $attributes['type'] = $attributes['type'] ?? 'card';

        return new static($attributes);
    }

    /**
     * Return the widget attribute value or null when it doesn't exist.
     *
     * @param  string  $attribute
     * @return mixed
     */
    public function getAttribute(string $attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }

    /**
     * Check if widget has the attribute.
     *
     * @param  string  $attribute
     * @return bool
     */
    public function hasAttribute(string $attribute)
    {
        return array_key_exists($attribute, $this->attributes);
    }

    /**
     * This method allows one to creat a widget without attaching it to any 'real'
     * widget section, by moving it to a 'hidden' section.
     *
     * It exists for one reason: so that developers can add widgets to a custom array, without
     * adding them to one of the widget sections.
     *
     * Ex: when developers need to pass multiple widgets as contents of the
     * div widget. But they don't want them added to the before_content of after_content
     * sections. So what they do is basically add them to a 'hidden' section, that nobody will ever see.
     *
     * @return Widget
     */
    public static function make($attributes = null)
    {
        $widget = static::add($attributes);
        $widget->section('hidden');

        return $widget;
    }

    /**
     * Remove an attribute from the current definition array.
     *
     * @param  string  $attribute  Name of the attribute to forget (ex: class)
     * @return Widget
     */
    public function forget($attribute)
    {
        $this->offsetUnset($attribute);

        return $this;
    }

    /**
     * Move this widget to appear right after another widget.
     *
     * @param  string  $destination  The name of the destination widget.
     * @return Widget
     */
    public function after($destination)
    {
        $collection = $this->collection();

        if (! $collection->has($destination)) {
            return $this;
        }

        $target = $collection->pull($this->attributes['name']);
        $offset = $collection->keys()->search($destination) + 1;

        $newCollection = $collection->slice(0, $offset)
            ->put($this->attributes['name'], $target)
            ->union($collection->slice($offset));

        $collection->forget($collection->keys()->toArray());
        foreach ($newCollection->all() as $key => $value) {
            $collection->put($key, $value);
        }

        return $this;
    }

    /**
     * Move this widget to appear right before another widget.
     *
     * @param  string  $destination  The name of the destination widget.
     * @return Widget
     */
    public function before($destination)
    {
        $collection = $this->collection();

        if (! $collection->has($destination)) {
            return $this;
        }

        $target = $collection->pull($this->attributes['name']);
        $offset = $collection->keys()->search($destination);

        $newCollection = $collection->slice(0, $offset)
            ->put($this->attributes['name'], $target)
            ->union($collection->slice($offset));

        $collection->forget($collection->keys()->toArray());
        foreach ($newCollection->all() as $key => $value) {
            $collection->put($key, $value);
        }

        return $this;
    }

    /**
     * Make this widget the first one in its section.
     *
     * @return Widget
     */
    public function makeFirst()
    {
        $this->collection()->pull($this->attributes['name']);
        $this->collection()->prepend($this);

        return $this;
    }

    /**
     * Make this widget the last one in its section.
     *
     * @return Widget
     */
    public function makeLast()
    {
        $this->collection()->pull($this->attributes['name']);
        $this->collection()->push($this);

        return $this;
    }

    /**
     * Get an array of full paths to the widget view, consisting of:
     * - the path given in the widget definition
     * - fallback view paths as configured in backpack/config/base.php.
     *
     * @return array
     */
    public function getFinalViewPath()
    {
        if (isset($this->attributes['viewNamespace'])) {
            $path = $this->attributes['viewNamespace'].'.'.$this->attributes['type'];

            if (view()->exists($path)) {
                return $path;
            }
        }
        $type = $this->attributes['type'];
        $paths = array_map(function ($item) use ($type) {
            return $item.'.'.$type;
        }, ViewNamespaces::getWithFallbackFor('widgets', 'backpack.ui.component_view_namespaces.widgets'));

        foreach ($paths as $path) {
            if (view()->exists($path)) {
                return $path;
            }
        }
        // if no view exists, in any of the directories above... no bueno
        if (! backpack_pro()) {
            throw new BackpackProRequiredException('Cannot find the widget view: '.$this->attributes['type'].'. Please check for typos.'.(backpack_pro() ? '' : ' If you are trying to use a PRO widget, please first purchase and install the backpack/pro addon from backpackforlaravel.com'), 1);
        }
        abort(500, 'Cannot find the view for «'.$this->attributes['type'].'» widget type. Please check for typos.', ['developer-error-exception']);
    }

    // -------
    // ALIASES
    // -------
    // Aka convenience methods.
    // These method just call other methods.

    // Alias of add()
    public static function name(...$args)
    {
        return static::add(...$args);
    }

    // Alias of section()
    public function to(...$args)
    {
        return $this->section(...$args);
    }

    // Alias of section()
    public function group(...$args)
    {
        return $this->section(...$args);
    }

    // Alias of viewNamespace()
    public function from(...$args)
    {
        return $this->viewNamespace(...$args);
    }

    // ------------------
    // COLLECTION METHODS
    // ------------------
    // Manipulate the global widget collection.

    public static function collection()
    {
        return app('widgets');
    }

    /**
     * Remove the widget from its section.
     *
     * @return Widget
     */
    public function remove()
    {
        $this->collection()->pull($this->attributes['name']);

        return $this;
    }

    /**
     * This alias of remove() exists for one reason: so that developers can add
     * widgets to a custom array, instead of adding them to one of the widget
     * sections. Ex: when developers need to pass multiple widgets as contents of the
     * div widget. But they don't want them added to the before_content of after_content
     * sections. So what they do is basically add them to a section, then remove them.
     * What's left is the widget itself, but without being attached to any section.
     *
     * @return Widget
     */
    public function onlyHere(...$args)
    {
        return $this->remove(...$args);
    }

    // ---------------
    // PRIVATE METHODS
    // ---------------

    /**
     * Update the global CrudPanel object with the current widget attributes.
     *
     * @return Widget
     */
    private function save()
    {
        $itemExists = $this->collection()->filter(function ($item) {
            return $item->attributes['name'] === $this->attributes['name'];
        })->isNotEmpty();
        if (! $itemExists) {
            $this->collection()->put($this->attributes['name'], $this);
        } else {
            $this->collection()[$this->attributes['name']] = $this;
        }

        return $this;
    }

    // -----------------
    // DEBUGGING METHODS
    // -----------------

    /**
     * Dump the current object to the screen,
     * so that the developer can see its contents.
     *
     * @return Widget
     */
    public function dump()
    {
        dump($this);

        return $this;
    }

    /**
     * Dump and die. Dumps the current object to the screen,
     * so that the developer can see its contents, then stops
     * the execution.
     *
     * @return Widget
     */
    public function dd()
    {
        dd($this);

        return $this;
    }

    /**
     * Overwritten methods to prevent BC in Laravel 11, since they introduced the `value()` method
     * in their Fluent class. Although the Widget class is Fluent, it does not behave the same
     * in regards to `value()`, since we use it as a key in widget definition.
     */
    public function value($value, $default = null)
    {
        $this->attributes['value'] = $value;

        return $this->save();
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    // -------------
    // MAGIC METHODS
    // -------------

    /**
     * Any call to a non-existing method on this class will be assumed to be
     * an attribute that the developer wants to add to that particular widget.
     *
     * Eg: class('something') will set the "class" attribute to "something"
     *
     * @param  string  $method  The method being called that doesn't exist.
     * @param  array  $parameters  The arguments when that method was called.
     * @return Widget
     */
    public function __call($method, $parameters)
    {
        $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;

        return $this->save();
    }
}
