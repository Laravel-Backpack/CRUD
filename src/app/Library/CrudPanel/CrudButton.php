<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

/**
 * Adds fluent syntax to Backpack CRUD Buttons.
 *
 * In addition to the existing:
 * - CRUD::addButton('top', 'create', 'view', 'crud::buttons.create');
 *
 * Developers can also do:
 * - CRUD::button('create')->stack('top')->view('crud::buttons.create');
 *
 * And if the developer uses CrudButton as Button in their CrudController:
 * - Button::name('create')->stack('top')->view('crud::butons.create');
 */
class CrudButton
{
    public $stack;
    public $name;
    public $type;
    public $content;
    public $position;

    public function __construct($name, $stack = null, $type = null, $content = null, $position = null)
    {
        // in case an array was passed as name
        // assume it's an array that includes [$name, $stack, $type, $content]
        if (is_array($name)) {
            extract($name);
        }

        $this->name = $name ?? 'button_'.rand(1, 999999999);
        $this->stack = $stack ?? 'top';
        $this->type = $type ?? 'view';
        $this->content = $content;

        // if no position was passed, the defaults are:
        // - 'beginning' for the 'line' stack
        // - 'end' for all other stacks
        $this->position = $position ?? ($this->stack == 'line' ? 'beginning' : 'end');

        return $this->save();
    }

    // ------
    // MAKERS
    // ------

    /**
     * Add a new button to the default stack.
     *
     * @param  string|array $attributes Button name or array that contains name, stack, type and content.
     */
    public static function name($attributes = null)
    {
        return new static($attributes);
    }

    /**
     * Add a new button to the default stack.
     *
     * @param  string|array $attributes Button name or array that contains name, stack, type and content.
     */
    public static function add($attributes = null)
    {
        return new static($attributes);
    }

    /**
     * This method allows one to create a button without attaching it to any 'real'
     * button stack, by moving it to a 'hidden' stack.
     *
     * It exists for one reason: so that developers can add buttons to a custom array, without
     * adding them to one of the button stacks.
     *
     * Ex: when developers need to pass multiple buttons as contents of the
     * div button. But they don't want them added to the before_content of after_content
     * stacks. So what they do is basically add them to a 'hidden' stack, that nobody will ever see.
     *
     * @param  string|array $attributes Button name or array that contains name, stack, type and content.
     * @return CrudButton
     */
    public static function make($attributes = null)
    {
        $button = static::add($attributes);
        $button->stack('hidden');

        return $button;
    }

    // -------
    // SETTERS
    // -------

    /**
     * Set the button stack (where the button will be shown).
     *
     * @param  string $stack The name of the stack where the button should be moved.
     * @return CrudButton
     */
    public function stack($stack)
    {
        $this->stack = $stack;

        return $this->save();
    }

    /**
     * Sets the button type (view or model_function).
     *
     * @param  string $type The type of button - view or model_function.
     * @return CrudButton
     */
    public function type($type)
    {
        $this->type = $type;

        return $this->save();
    }

    /**
     * Sets the content of the button.
     * For the view button type, set it to the view path, including namespace.
     * For the model_function button type, set it to the name of the method on the model.
     *
     * @param  string $content Path to view or name of method on Model.
     * @return CrudButton
     */
    public function content($content)
    {
        $this->content = $content;

        return $this->save();
    }

    /**
     * Sets the namespace and path of the view for this button.
     * Sets the button type as 'view'.
     *
     * @param  string $value Path to view file.
     * @return CrudButton
     */
    public function view($value)
    {
        $this->content = $value;
        $this->type = 'view';

        return $this->save();
    }

    /**
     * Sets the name of the method on the model that contains the HTML for this button.
     * Sets the button type as 'model_function'.
     *
     * @param  string $value Name of the method on the model.
     * @return CrudButton
     */
    public function modelFunction($value)
    {
        $this->content = $value;
        $this->type = 'model_function';
        $this->stack = 'line';

        return $this->save();
    }

    /**
     * Sets the name of the method on the model that contains the HTML for this button.
     * Sets the button type as 'model_function'.
     * Alias of the modelFunction() method.
     *
     * @param  string $value Name of the method on the model.
     * @return CrudButton
     */
    public function model_function($value)
    {
        return $this->modelFunction($value);
    }

    /**
     * Unserts an property that is set on the current button.
     * Possible properties: name, stack, type, content.
     *
     * @param  string $property Name of the property that should be cleared.
     * @return CrudButton
     */
    public function forget($property)
    {
        $this->{$property} = null;

        return $this->save();
    }

    // --------------
    // SETTER ALIASES
    // --------------

    /**
     * Moves the button to a certain button stack.
     * Alias of stack().
     *
     * @param  string $stack The name of the stack where the button should be moved.
     *
     * @return self
     */
    public function to($stack)
    {
        return $this->stack($stack);
    }

    /**
     * Moves the button to a certain button stack.
     * Alias of stack().
     *
     * @param  string $stack The name of the stack where the button should be moved.
     *
     * @return self
     */
    public function group($stack)
    {
        return $this->stack($stack);
    }

    /**
     * Moves the button to a certain button stack.
     * Alias of stack().
     *
     * @param  string $stack The name of the stack where the button should be moved.
     *
     * @return self
     */
    public function section($stack)
    {
        return $this->stack($stack);
    }

    // -------
    // GETTERS
    // -------

    /**
     * Get the end result that should be displayed to the user.
     * The HTML itself of the button.
     *
     * @param  object|null $entry The eloquent Model for the current entry or null if no current entry.
     *
     * @return HTML
     */
    public function getHtml($entry = null)
    {
        $button = $this;
        $crud = $this->crud();

        if ($this->type == 'model_function') {
            if (is_null($entry)) {
                return $crud->model->{$button->content}($crud);
            }

            return $entry->{$button->content}($crud);
        }

        if ($this->type == 'view') {
            if (view()->exists($button->content)) {
                return view($button->content, compact('button', 'crud', 'entry'));
            } else {
                abort(500, 'Button view "'.$button->content.'" does not exist');
            }
        }

        abort(500, 'Unknown button type');
    }

    /**
     * Get the key for this button in the global buttons collection.
     *
     * @return int
     */
    public function getKey()
    {
        return $this->crud()->getButtonKey($this->name);
    }

    // -----
    // ORDER
    // -----
    // Manipulate the button collection (inside the global CrudPanel object).

    /**
     * Move this button to be the first in the buttons list.
     *
     * @return CrudButton
     */
    public function makeFirst()
    {
        $this->remove();
        $this->collection()->prepend($this);

        return $this;
    }

    /**
     * Move this button to be the last one in the buttons list.
     *
     * @return CrudButton
     */
    public function makeLast()
    {
        $this->remove();
        $this->collection()->push($this);

        return $this;
    }

    /**
     * Move the current filter after another filter.
     *
     * @param  string $destination Name of the destination filter.
     * @return CrudFilter
     */
    public function after($destination)
    {
        $this->crud()->moveButton($this->name, 'after', $destination);

        return $this;
    }

    /**
     * Move the current field before another field.
     *
     * @param  string $destination Name of the destination field.
     * @return CrudFilter
     */
    public function before($destination)
    {
        $this->crud()->moveButton($this->name, 'before', $destination);

        return $this;
    }

    /**
     * Remove the button from the global button collection.
     *
     * @return CrudButton
     */
    public function remove()
    {
        $this->collection()->pull($this->getKey());

        return $this;
    }

    // --------------
    // GLOBAL OBJECTS
    // --------------
    // Access to the objects stored in Laravel's service container.

    /**
     * Access the global collection when all buttons are stored.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->crud()->buttons();
    }

    /**
     * Access the global CrudPanel object.
     *
     * @return \Backpack\CRUD\app\Library\CrudPanel\CrudPanel
     */
    public function crud()
    {
        return app('crud');
    }

    // -----------------
    // DEBUGGING METHODS
    // -----------------

    /**
     * Dump the current object to the screen,
     * so that the developer can see its contents.
     *
     * @return CrudButton
     */
    public function dump()
    {
        dump($this);

        return $this;
    }

    /**
     * Dump and die. Duumps the current object to the screen,
     * so that the developer can see its contents, then stops
     * the execution.
     *
     * @return CrudButton
     */
    public function dd()
    {
        dd($this);

        return $this;
    }

    // ---------------
    // PRIVATE METHODS
    // ---------------

    /**
     * Update the global CrudPanel object with the current button.
     *
     * @return CrudButton
     */
    private function save()
    {
        $itemExists = $this->collection()->contains('name', $this->name);

        if (! $itemExists) {
            if ($this->position == 'beginning') {
                $this->collection()->prepend($this);
            } else {
                $this->collection()->push($this);
            }

            // clear the custom position, so that the next daisy chained method
            // doesn't move it yet again
            $this->position = null;
        } else {
            $this->collection()->replace([$this->getKey() => $this]);
        }

        return $this;
    }
}
