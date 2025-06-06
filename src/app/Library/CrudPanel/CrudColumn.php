<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

use Backpack\CRUD\app\Library\CrudPanel\Traits\Support\MacroableWithAttributes;
use Illuminate\Support\Traits\Conditionable;

/**
 * Adds fluent syntax to Backpack CRUD Columns.
 *
 * In addition to the existing:
 * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
 *
 * Developers can also do:
 * - CRUD::column('price')->type('number');
 *
 * And if the developer uses CrudColumn as Column in their CrudController:
 * - Column::name('price')->type('number');
 *
 * @method self type(string $value)
 * @method self label(string $value)
 * @method self searchLogic(mixed $value)
 * @method self orderLogic(callable $value)
 * @method self orderable(bool $value)
 * @method self wrapper(array $value)
 * @method self visibleInTable(bool $value)
 * @method self visibleInModal(bool $value)
 * @method self visibleInExport(bool $value)
 * @method self visibleInShow(bool $value)
 * @method self priority(int $value)
 * @method self key(string $value)
 * @method self upload(bool $value)
 * @method self linkTo(string $routeName, ?array $parameters = [])
 */
class CrudColumn
{
    use Conditionable;
    use MacroableWithAttributes;

    protected $attributes;

    public function __construct($nameOrDefinitionArray)
    {
        if (is_array($nameOrDefinitionArray)) {
            $column = $this->crud()->addAndReturnColumn($nameOrDefinitionArray);
            $name = $column->getAttributes()['name'];
        } else {
            $name = $nameOrDefinitionArray;
        }

        $column = $this->crud()->firstColumnWhere('name', $name);

        // if column exists
        if ((bool) $column) {
            // use all existing attributes
            $this->setAllAttributeValues($column);
        } else {
            // it means we're creating the column now,
            // so at the very least set the name attribute
            $this->setAttributeValue('name', $name);
        }

        $this->save();
    }

    public function crud()
    {
        return app()->make('crud');
    }

    /**
     * Create a CrudColumn object with the parameter as its name.
     *
     * @param  string  $name  Name of the column in the db, or model attribute.
     * @return CrudColumn
     */
    public static function name($name)
    {
        return new static($name);
    }

    /**
     * Change the CrudColumn key.
     *
     * @param  string  $key  New key for the column
     * @return CrudColumn
     */
    public function key(string $key)
    {
        if (! isset($this->attributes['name'])) {
            abort(500, 'Column name must be defined before changing the key.', ['developer-error-exception']);
        }

        $columns = $this->crud()->columns();

        $searchKey = $this->attributes['key'];
        $column = $this->attributes;

        if (isset($columns[$searchKey])) {
            unset($columns[$searchKey]);
            $column['key'] = $key;
        }
        $this->attributes = $column;
        $this->crud()->setOperationSetting('columns', array_merge($columns, [$key => $column]));

        return $this;
    }

    /**
     * Remove the current column from the current operation.
     *
     * @return void
     */
    public function remove()
    {
        $this->crud()->removeColumn($this->attributes['name']);
    }

    /**
     * Remove an attribute from the column definition array.
     *
     * @param  string  $attribute  Name of the attribute being removed
     * @return CrudColumn
     */
    public function forget($attribute)
    {
        $this->crud()->removeColumnAttribute($this->attributes['name'], $attribute);

        return $this;
    }

    /**
     * Move the current column after another column.
     *
     * @param  string  $destinationColumn  Name of the destination column.
     * @return CrudColumn
     */
    public function after($destinationColumn)
    {
        $this->crud()->removeColumn($this->attributes['name']);
        $this->crud()->addColumn($this->attributes)->afterColumn($destinationColumn);

        return $this;
    }

    /** Alias of after() */
    public function afterColumn(string $destinationColumn)
    {
        $this->after($destinationColumn);
    }

    /**
     * Move the current column before another column.
     *
     * @param  string  $destinationColumn  Name of the destination column.
     * @return CrudColumn
     */
    public function before($destinationColumn)
    {
        $this->crud()->removeColumn($this->attributes['name']);
        $this->crud()->addColumn($this->attributes)->beforeColumn($destinationColumn);

        return $this;
    }

    public function upload($upload = true)
    {
        $this->attributes['upload'] = $upload;

        return $this->save();
    }

    /**
     * When subfields are defined, pass them through the guessing function
     * so that they have label, relationship attributes, etc.
     *
     * @param  array  $subfields  Subfield definition array
     * @return self
     */
    public function subfields($subfields)
    {
        $callAttributeMacro = ! isset($this->attributes['subfields']);
        $this->attributes['subfields'] = $subfields;
        $this->attributes = $this->crud()->makeSureColumnHasNeededAttributes($this->attributes);
        if ($callAttributeMacro) {
            $this->callRegisteredAttributeMacros();
        }

        return $this->save();
    }

    /**
     * Make the current column the first one in the columns list.
     *
     * @return CrudColumn
     */
    public function makeFirst()
    {
        $this->crud()->removeColumn($this->attributes['name']);
        $this->crud()->addColumn($this->attributes)->makeFirstColumn();

        return $this;
    }

    /**
     * Make the current column the last one in the columns list.
     *
     * @return CrudColumn
     */
    public function makeLast()
    {
        $this->crud()->removeColumn($this->attributes['name']);
        $this->crud()->addColumn($this->attributes);

        return $this;
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
     * @return CrudColumn
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
     * @return CrudColumn
     */
    public function dd()
    {
        dd($this);

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    // ---------------
    // PRIVATE METHODS
    // ---------------

    /**
     * Set the value for a certain attribute on the CrudColumn object.
     *
     * @param  string  $attribute  Name of the attribute.
     * @param  mixed  $value  Value of that attribute.
     */
    private function setAttributeValue($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * Replace all column attributes on the CrudColumn object
     * with the given array of attribute-value pairs.
     *
     * @param  array  $array  Array of attributes and their values.
     */
    private function setAllAttributeValues($array)
    {
        $this->attributes = $array;
    }

    /**
     * Update the global CrudPanel object with the current column attributes.
     *
     * @return CrudColumn
     */
    private function save()
    {
        $key = $this->attributes['key'] ?? $this->attributes['name'];
        if ($this->crud()->hasColumnWhere('key', $key)) {
            $this->crud()->setColumnDetails($key, $this->attributes);
        } else {
            $this->crud()->addColumn($this->attributes);
            $this->attributes = $this->getFreshAttributes();
        }

        return $this;
    }

    /**
     * Get the fresh attributes for the current column.
     *
     * @return array
     */
    private function getFreshAttributes()
    {
        $key = isset($this->attributes['key']) ? 'key' : 'name';
        $search = $this->attributes['key'] ?? $this->attributes['name'];

        return $this->crud()->firstColumnWhere($key, $search);
    }

    // -------------
    // MAGIC METHODS
    // -------------

    /**
     * If a developer calls a method that doesn't exist, assume they want:
     * - the CrudColumn object to have an attribute with that value;
     * - that column be updated inside the global CrudPanel object;.
     *
     * Eg: type('number') will set the "type" attribute to "number"
     *
     * @param  string  $method  The method being called that doesn't exist.
     * @param  array  $parameters  The arguments when that method was called.
     * @return CrudColumn
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        $this->setAttributeValue($method, $parameters[0]);

        return $this->save();
    }
}
