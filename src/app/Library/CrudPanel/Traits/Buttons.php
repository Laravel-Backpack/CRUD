<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudButton;
use Illuminate\Support\Collection;

trait Buttons
{
    // ------------
    // BUTTONS
    // ------------

    /**
     * Order the CRUD buttons. If certain button names are missing from the given order array
     * they will be pushed to the end of the button collection.
     *
     * @param  string  $stack  Stack where the buttons belongs. Options: top, line, bottom.
     * @param  array  $order  Ordered names of the buttons. ['update', 'delete', 'show']
     */
    public function orderButtons(string $stack, array $order)
    {
        $newButtons = collect([]);
        $otherButtons = collect([]);

        // we get the buttons that belong to the specified stack
        $stackButtons = $this->buttons()->reject(function ($item) use ($stack, $otherButtons) {
            if ($item->stack != $stack) {
                // if the button does not belong to this stack we just add it for merging later
                $otherButtons->push($item);

                return true;
            }

            return false;
        });

        // we parse the ordered buttons
        collect($order)->each(function ($btnKey) use ($newButtons, $stackButtons) {
            if (! $button = $stackButtons->where('name', $btnKey)->first()) {
                abort(500, 'Button name [«'.$btnKey.'»] not found.', ['developer-error-exception']);
            }
            $newButtons->push($button);
        });

        // if the ordered buttons are less than the total number of buttons in the stack
        // we add the remaining buttons to the end of the ordered ones
        if (count($newButtons) < count($stackButtons)) {
            foreach ($stackButtons as $button) {
                if (! $newButtons->where('name', $button->name)->first()) {
                    $newButtons->push($button);
                }
            }
        }

        $this->setOperationSetting('buttons', $newButtons->merge($otherButtons));
    }

    /**
     * Add a button to the CRUD table view.
     *
     * @param  string  $stack  Where should the button be visible? Options: top, line, bottom.
     * @param  string  $name  The name of the button. Unique.
     * @param  string  $type  Type of button: view or model_function.
     * @param  string  $content  The HTML for the button.
     * @param  bool|string  $position  Position on the stack: beginning or end. If false, the position will be
     *                                 'beginning' for the line stack or 'end' otherwise.
     * @param  bool  $replaceExisting  True if a button with the same name on the given stack should be replaced.
     * @return \Backpack\CRUD\app\Library\CrudPanel\CrudButton The new CRUD button.
     */
    public function addButton($stack, $name, $type, $content, $position = false, $replaceExisting = true)
    {
        if ($replaceExisting) {
            $this->removeButton($name, $stack);
        }

        return new CrudButton($name, $stack, $type, $content, $position);
    }

    public function addCrudButton(CrudButton $crudButton)
    {
        $this->setOperationSetting('buttons', $this->buttons()->push($crudButton));
    }

    public function addButtonFromModelFunction($stack, $name, $model_function_name, $position = false)
    {
        $this->addButton($stack, $name, 'model_function', $model_function_name, $position);
    }

    public function addButtonFromView($stack, $name, $view, $position = false)
    {
        $view = 'crud::buttons.'.$view;

        $this->addButton($stack, $name, 'view', $view, $position);
    }

    /**
     * @return Collection
     */
    public function buttons()
    {
        return $this->getOperationSetting('buttons') ?? collect();
    }

    /**
     * Modify the attributes of a button.
     *
     * @param  string  $name  The button name.
     * @param  array  $modifications  The attributes and their new values.
     * @return CrudButton The button that has suffered the changes, for daisychaining methods.
     */
    public function modifyButton($name, $modifications = null)
    {
        /**
         * @var ?CrudButton
         */
        $button = $this->buttons()->firstWhere('name', $name);

        if (! $button) {
            abort(500, 'CRUD Button "'.$name.'" not found. Please ensure the button exists before you modify it.', ['developer-error-exception']);
        }

        if (is_array($modifications)) {
            foreach ($modifications as $key => $value) {
                $button->{$key} = $value;
            }
        }

        return $button;
    }

    /**
     * Remove a button from the CRUD panel.
     *
     * @param  string  $name  Button name.
     * @param  string  $stack  Optional stack name.
     */
    public function removeButton($name, $stack = null)
    {
        $this->setOperationSetting('buttons', $this->buttons()->reject(function ($button) use ($name, $stack) {
            return $stack == null ? $button->name == $name : ($button->stack == $stack) && ($button->name == $name);
        }));
    }

    /**
     * @param  array  $names  Button names
     * @param  ?string  $stack  Optional stack name.
     */
    public function removeButtons($names, $stack = null)
    {
        if (! empty($names)) {
            foreach ($names as $name) {
                $this->removeButton($name, $stack);
            }
        }
    }

    public function removeAllButtons()
    {
        $this->setOperationSetting('buttons', collect());
    }

    public function removeAllButtonsFromStack($stack)
    {
        $this->setOperationSetting('buttons', $this->buttons()->reject(function ($button) use ($stack) {
            return $button->stack == $stack;
        }));
    }

    public function removeButtonFromStack($name, $stack)
    {
        $this->setOperationSetting('buttons', $this->buttons()->reject(function ($button) use ($name, $stack) {
            return $button->name == $name && $button->stack == $stack;
        }));
    }

    /**
     * Move the most recently added button before or after the given target button. Default is before.
     *
     * @param  string|array  $target  The target button name or array.
     * @param  string|array  $where  Move 'before' or 'after' the target.
     * @param  string|array  $destination  The destination button name or array.
     * @param  bool  $before  If true, the button will be moved before the target button, otherwise it will be moved after it.
     */
    public function moveButton($target, $where, $destination)
    {
        $targetButton = $this->firstButtonWhere('name', $target);

        $destinationButton = $this->firstButtonWhere('name', $destination);
        $destinationKey = $this->getButtonKey($destination);
        $newDestinationKey = ($where == 'before' ? $destinationKey : $destinationKey + 1);

        $newButtons = $this->buttons()->filter(function ($value, $key) use ($target) {
            return $value->name != $target;
        });

        if (! $targetButton) {
            return;
        }

        if (! $destinationButton) {
            return;
        }

        $firstSlice = $newButtons->slice(0, $newDestinationKey);
        $lastSlice = $newButtons->slice($newDestinationKey, null);

        $newButtons = $firstSlice->push($targetButton);

        $lastSlice->each(function ($item, $key) use ($newButtons) {
            $newButtons->push($item);
        });

        $this->setOperationSetting('buttons', $newButtons);
    }

    /**
     * Check if a button exists, by any given attribute.
     *
     * @param  string  $attribute  Attribute name on that button definition array.
     * @param  string  $value  Value of that attribute on that button definition array.
     * @return bool
     */
    public function hasButtonWhere($attribute, $value)
    {
        return $this->buttons()->contains($attribute, $value);
    }

    /**
     * Get the first button where a given attribute has the given value.
     *
     * @param  string  $attribute  Attribute name on that button definition array.
     * @param  string  $value  Value of that attribute on that button definition array.
     * @return bool
     */
    public function firstButtonWhere($attribute, $value)
    {
        return $this->buttons()->firstWhere($attribute, $value);
    }

    /**
     * Get button key from its name.
     *
     * @param  string  $buttonName  Button name.
     * @return string
     */
    public function getButtonKey($name)
    {
        $array = $this->buttons()->toArray();

        foreach ($array as $key => $value) {
            if ((is_object($value) ? $value->name : $value['name']) === $name) {
                return $key;
            }
        }
    }

    /**
     * Return the buttons for a given stack.
     */
    public function getButtonsForStack(string $stack): Collection
    {
        return $this->buttons()->where('stack', $stack);
    }

    /**
     * Add a new button to the current CRUD operation.
     */
    public function button(string|array $nameOrAttributes): CrudButton
    {
        return new CrudButton($nameOrAttributes);
    }
}
