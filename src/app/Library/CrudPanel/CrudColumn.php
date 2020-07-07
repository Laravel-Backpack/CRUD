<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

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
 */
class CrudColumn
{
    protected $attributes;

    public function __construct($name)
    {
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

        // guess all attributes that weren't explicitly defined
        $this->attributes = $this->crud()->makeSureColumnHasNeededAttributes($this->attributes);

        $this->save();
    }

    public function crud()
    {
        return app()->make('crud');
    }

    /**
     * Create a CrudColumn object with the parameter as its name.
     *
     * @param  string $name Name of the column in the db, or model attribute.
     * @return CrudColumn
     */
    public static function name($name)
    {
        return new static($name);
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
     * @param  string $attribute  Name of the attribute being removed
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
     * @param  string $destinationColumn Name of the destination column.
     * @return CrudColumn
     */
    public function after($destinationColumn)
    {
        $this->crud()->removeColumn($this->attributes['name']);
        $this->crud()->addColumn($this->attributes)->afterColumn($destinationColumn);

        return $this;
    }

    /**
     * Move the current column before another column.
     *
     * @param  string $destinationColumn Name of the destination column.
     * @return CrudColumn
     */
    public function before($destinationColumn)
    {
        $this->crud()->removeColumn($this->attributes['name']);
        $this->crud()->addColumn($this->attributes)->beforeColumn($destinationColumn);

        return $this;
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
     * @return CrudColumn
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
     * @return CrudColumn
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
     * Set the value for a certain attribute on the CrudColumn object.
     *
     * @param string $attribute Name of the attribute.
     * @param mixed $value     Value of that attribute.
     */
    private function setAttributeValue($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * Replace all column attributes on the CrudColumn object
     * with the given array of attribute-value pairs.
     *
     * @param array $array Array of attributes and their values.
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
        }

        return $this;
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
     * @param  string $method     The method being called that doesn't exist.
     * @param  array $parameters  The arguments when that method was called.
     *
     * @return CrudColumn
     */
    public function __call($method, $parameters)
    {
        $this->setAttributeValue($method, $parameters[0]);

        return $this->save();
    }
}
