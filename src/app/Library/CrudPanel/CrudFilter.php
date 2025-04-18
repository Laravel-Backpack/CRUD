<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;
use Backpack\CRUD\ViewNamespaces;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Symfony\Component\HttpFoundation\ParameterBag;

class CrudFilter
{
    use Conditionable;

    public $name; // the name of the filtered variable (db column name)

    public $type = 'select2'; // the name of the filter view that will be loaded

    public $key; //camelCased version of filter name to use in internal ids, js functions and css classes.

    public $label;

    public $placeholder;

    public $values;

    public $options;

    public $logic;

    public $fallbackLogic;

    public $currentValue;

    public $view;

    public $viewNamespace = 'crud::filters';

    public $applied = false;

    public function __construct($options, $values, $logic, $fallbackLogic)
    {
        if (! backpack_pro()) {
            throw new BackpackProRequiredException('Filter');
        }
        // if filter exists
        if ($this->crud()->hasFilterWhere('name', $options['name'])) {
            $properties = get_object_vars($this->crud()->firstFilterWhere('name', $options['name']));
            foreach ($properties as $property => $value) {
                $this->{$property} = $value;
            }
        } else {
            // it means we're creating the filter now,
            $this->name = $options['name'];
            $this->key = Str::camel($options['name']);
            $this->type = $options['type'] ?? $this->type;
            $this->label = $options['label'] ?? $this->crud()->makeLabel($this->name);
            $this->viewNamespace = $options['viewNamespace'] ?? $options['view_namespace'] ?? $this->viewNamespace;
            $this->view = $this->type;
            $this->placeholder = $options['placeholder'] ?? '';

            $this->values = is_callable($values) ? $values() : $values;
            $this->options = $options;
            $this->logic = $logic;
            $this->fallbackLogic = $fallbackLogic;
        }

        if ($this->crud()->getRequest()->has($this->name)) {
            $this->currentValue = $this->crud()->getRequest()->input($this->name);
        }
    }

    /**
     * Check if the field is currently active. This happens when there's a GET parameter
     * in the current request with the same name as the name of the field.
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->crud()->getRequest()->has($this->name)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the filter has already had the apply method called on it.
     *
     * @return bool
     */
    public function wasApplied()
    {
        return $this->applied;
    }

    /**
     * Check if the filter has not had the apply method called on it yet.
     * This is the inverse of the wasApplied() method.
     *
     * @return bool
     */
    public function wasNotApplied()
    {
        return ! $this->applied;
    }

    /**
     * Run the filter logic, default logic and/or fallback logic so that from this point on
     * the CRUD query has its results filtered, according to the Request.
     *
     * @param  array  $input  The GET parameters for which the filter should be applied.
     * @return void
     */
    public function apply($input = null)
    {
        // mark the field as already applied
        $this->applied(true);

        if (is_array($input)) {
            $input = new ParameterBag($input);
        }

        $input = $input ?? new ParameterBag($this->crud()->getRequest()->all());

        if (! $input->has($this->name)) {
            // if fallback logic was supplied and is a closure
            if (is_callable($this->fallbackLogic)) {
                return ($this->fallbackLogic)();
            }

            return;
        }

        // if a closure was passed as "filterLogic"
        if (is_callable($this->logic)) {
            return ($this->logic)($input->get($this->name));
        } else {
            return $this->applyDefaultLogic($this->name, false);
        }
    }

    /**
     * Get the full path of the filter view, including the view namespace.
     *
     * @return string
     */
    public function getViewWithNamespace()
    {
        return $this->viewNamespace.'.'.$this->view;
    }

    /**
     * Get an array of full paths to the filter view, including fallbacks
     * as configured in the backpack/config/crud.php file.
     *
     * @return array
     */
    public function getNamespacedViewWithFallbacks()
    {
        $type = $this->type;
        $namespaces = ViewNamespaces::getFor('filters');

        if ($this->viewNamespace != 'crud::filters') {
            $namespaces = array_merge([$this->viewNamespace], $namespaces);
        }

        return array_map(function ($item) use ($type) {
            return $item.'.'.$type;
        }, $namespaces);
    }

    // ---------------------
    // FLUENT SYNTAX METHODS
    // ---------------------

    /**
     * Create a CrudFilter object with the parameter as its name.
     *
     * @param  string  $name  Name of the column in the db, or model attribute.
     * @return CrudPanel
     */
    public static function name($name)
    {
        $filter = new static(compact('name'), null, null, null);

        return $filter->save();
    }

    /**
     * Remove the current filter from the current operation.
     *
     * @return void
     */
    public function remove()
    {
        $this->crud()->removeFilter($this->name);
    }

    /**
     * Remove an attribute from the current filter definition array.
     *
     * @param  string  $attribute  Name of the attribute being removed.
     * @return CrudFilter
     */
    public function forget($attribute)
    {
        if (property_exists($this, $attribute)) {
            $this->{$attribute} = false;
        }

        if (isset($this->options[$attribute])) {
            unset($this->options[$attribute]);
        }

        $this->crud()->replaceFilter($this->name, $this);

        return $this;
    }

    /**
     * Remove an attribute from one field's definition array.
     * (unused function).
     *
     * @param  string  $field  The name of the field.
     * @param  string  $attribute  The name of the attribute being removed.
     *
     * @codeCoverageIgnore
     *
     * @deprecated
     */
    public function removeFilterAttribute($filter, $attribute)
    {
        $fields = $this->fields();

        unset($fields[$field][$attribute]);

        $this->setOperationSetting('fields', $fields);
    }

    /**
     * Move the current filter after another filter.
     *
     * @param  string  $destination  Name of the destination filter.
     * @return CrudFilter
     */
    public function after($destination)
    {
        $this->crud()->moveFilter($this->name, 'after', $destination);

        return $this;
    }

    /**
     * Move the current field before another field.
     *
     * @param  string  $destination  Name of the destination field.
     * @return CrudFilter
     */
    public function before($destination)
    {
        $this->crud()->moveFilter($this->name, 'before', $destination);

        return $this;
    }

    /**
     * Make the current field the first one in the fields list.
     *
     * @return CrudPanel
     */
    public function makeFirst()
    {
        $this->crud()->moveFilter($this->name, 'before', $this->crud()->filters()->first()->name);

        return $this;
    }

    /**
     * Make the current field the last one in the fields list.
     *
     * @return CrudPanel
     */
    public function makeLast()
    {
        $this->crud()->removeFilter($this->name);
        $this->crud()->addCrudFilter($this);

        return $this;
    }

    // -----------------------
    // FILTER-SPECIFIC SETTERS
    // -----------------------

    /**
     * Set the type of the filter.
     *
     * @param  string  $value  Name of blade view that shows the field.
     * @return CrudFilter
     */
    public function type($value)
    {
        $this->type = $value;
        $this->view = $value;

        return $this->save();
    }

    /**
     * Set the label of the filter - the element that the end-user can see and click
     * to activate the filter or an input that will activate the filter.
     *
     * @param  string  $value  A name for this filter that the end-user will understand.
     * @return CrudFilter
     */
    public function label($value)
    {
        $this->label = $value;

        return $this->save();
    }

    /**
     * Set the values for the current filter, for the filters who need values.
     * For example, the dropdown, select2 and select2 filters let the user select
     * pre-determined values to filter with. This is how to set those values that will be picked up.
     *
     * @param  array|string|function  $value  Key-value array with values for the user to pick from, or a function which also return a Key-value array.
     * @return CrudFilter
     */
    public function values($value)
    {
        $this->values = (! is_string($value) && is_callable($value)) ? $value() : $value;

        return $this->save();
    }

    /**
     * Set the values for the current filter, for the filters who need values. For example
     * the dropdown, select2 and select2 filters let the user select pre-determined
     * values to filter with.
     *
     * Alias of the values() method.
     *
     * @param  array|string|function  $value  Key-value array with values for the user to pick from, or a function which also return a Key-value array.
     * @return CrudFilter
     */
    public function options($value)
    {
        return $this->values($value);
    }

    /**
     * Set the blade view that will be used by the filter.
     * Should NOT include the namespace, that's defined separately using 'viewNamespace'.
     *
     * @param  string  $value  Path to the blade file, after the view namespace.
     * @return CrudFilter
     */
    public function view($value)
    {
        $this->view = $value;
        $this->options['view'] = $value;

        return $this->save();
    }

    /**
     * The path to the blade views directory where the filter file will be found. Ex: 'crud::filters'
     * Useful to load filters from a different package or directory.
     *
     * @param  string  $value  Blade path to the directory.
     * @return CrudFilter
     */
    public function viewNamespace($value)
    {
        $this->viewNamespace = $value;

        return $this->save();
    }

    /**
     * Define what happens when the filter is active, through a closure.
     *
     * @param  Closure  $value  Closure that will be called when Request has this name as GET parameter.
     * @return CrudFilter
     */
    public function logic($value)
    {
        $this->logic = $value;

        return $this->save();
    }

    /**
     * Define what happens when the filter is NOT active, through a closure.
     *
     * @param  Closure  $value  Closure that will be called when Request does NOT have this name as GET parameter.
     * @return CrudFilter
     */
    public function fallbackLogic($value)
    {
        $this->fallbackLogic = $value;

        return $this->save();
    }

    /**
     * Define if the filter has already been applied (logic or fallbackLogic called).
     *
     * @param  bool  $value  Whether the filter has been run.
     * @return CrudFilter
     */
    public function applied($value)
    {
        $this->applied = $value;

        return $this->save();
    }

    /**
     * Aliases of the logic() method.
     */
    public function whenActive($value)
    {
        return $this->logic($value);
    }

    public function ifActive($value)
    {
        return $this->logic($value);
    }

    /**
     * Alises of the fallbackLogic() method.
     */
    public function whenInactive($value)
    {
        return $this->fallbackLogic($value);
    }

    public function whenNotActive($value)
    {
        return $this->fallbackLogic($value);
    }

    public function ifInactive($value)
    {
        return $this->fallbackLogic($value);
    }

    public function ifNotActive($value)
    {
        return $this->fallbackLogic($value);
    }

    public function else($value)
    {
        return $this->fallbackLogic($value);
    }

    // ---------------
    // PRIVATE METHODS
    // ---------------

    private function crud()
    {
        return app()->make('crud');
    }

    /**
     * Set the value for a certain attribute on the CrudFilter object.
     *
     * @param  string  $attribute  Name of the attribute.
     * @param  string  $value  Value of that attribute.
     */
    private function setOptionValue($attribute, $value)
    {
        $this->options[$attribute] = $value;
    }

    /**
     * Replace all field options on the CrudFilter object
     * with the given array of attribute-value pairs.
     *
     * @param  array  $array  Array of options and their values.
     */
    private function setAllOptionsValues($array)
    {
        $this->options = $array;
    }

    /**
     * Update the global CrudPanel object with the current field options.
     *
     * @return CrudFilter
     */
    private function save()
    {
        $key = $this->name;

        if ($this->crud()->hasFilterWhere('name', $key)) {
            $this->crud()->modifyFilter($key, (array) $this);
        } else {
            $this->crud()->addCrudFilter($this);
        }

        return $this;
    }

    /**
     * @param  string  $name
     * @param  string  $operator
     * @param  array  $input
     */
    private function applyDefaultLogic($name, $operator, $input = null)
    {
        $input = $input ?? $this->crud()->getRequest()->all();

        // if this filter is active (the URL has it as a GET parameter)
        switch ($operator) {
            // if no operator was passed, just use the equals operator
            case false:
                $this->crud()->addClause('where', $name, $input[$name]);
                break;

            case 'scope':
                $this->crud()->addClause($operator);
                break;

                // TODO:
                // whereBetween
                // whereNotBetween
                // whereIn
                // whereNotIn
                // whereNull
                // whereNotNull
                // whereDate
                // whereMonth
                // whereDay
                // whereYear
                // whereColumn
                // like

                // sql comparison operators
            case '=':
            case '<=>':
            case '<>':
            case '!=':
            case '>':
            case '>=':
            case '<':
            case '<=':
                $this->crud()->addClause('where', $name, $operator, $input[$name]);
                break;

            default:
                abort(500, 'Unknown filter operator.', ['developer-error-exception']);
                break;
        }
    }

    // -----------------
    // DEBUGGING METHODS
    // -----------------

    /**
     * Dump the current object to the screen,
     * so that the developer can see its contents.
     *
     * @codeCoverageIgnore
     *
     * @return CrudFilter
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
     * @codeCoverageIgnore
     *
     * @return CrudFilter
     */
    public function dd()
    {
        dd($this);

        return $this;
    }

    // -------------
    // MAGIC METHODS
    // -------------

    /**
     * If a developer calls a method that doesn't exist, assume they want:
     * - $this->options['whatever'] to be set to that value;
     * - that filter be updated inside the global CrudPanel object;.
     *
     * Eg: type('number') will set the "type" attribute to "number"
     *
     * @param  string  $method  The method being called that doesn't exist.
     * @param  array  $parameters  The arguments when that method was called.
     * @return CrudFilter
     */
    public function __call($method, $parameters)
    {
        $this->setOptionValue($method, $parameters[0]);

        return $this->save();
    }
}
