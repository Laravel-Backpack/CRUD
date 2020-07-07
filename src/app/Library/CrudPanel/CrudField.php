<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

/**
 * Adds fluent syntax to Backpack CRUD Fields.
 *
 * In addition to the existing:
 * - CRUD::addField(['name' => 'price', 'type' => 'number']);
 *
 * Developers can also do:
 * - CRUD::field('price')->type('number');
 *
 * And if the developer uses CrudField as Field in their CrudController:
 * - Field::name('price')->type('number');
 *
 * @method self type(string $value)
 * @method self label(string $value)
 * @method self tab(string $value)
 * @method self prefix(string $value)
 * @method self suffix(string $value)
 * @method self default(mixed $value)
 * @method self hint(string $value)
 * @method self attributes(array $value)
 * @method self wrapper(array $value)
 * @method self fake(bool $value)
 * @method self store_in(string $value)
 */
class CrudField
{
    protected $attributes;

    public function __construct($name)
    {
        $field = $this->crud()->firstFieldWhere('name', $name);

        // if field exists
        if ((bool) $field) {
            // use all existing attributes
            $this->setAllAttributeValues($field);
        } else {
            // it means we're creating the field now,
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
     * Create a CrudField object with the parameter as its name.
     *
     * @param  string $name Name of the column in the db, or model attribute.
     * @return CrudField
     */
    public static function name($name)
    {
        return new static($name);
    }

    /**
     * Remove the current field from the current operation.
     *
     * @return void
     */
    public function remove()
    {
        $this->crud()->removeField($this->attributes['name']);
    }

    /**
     * Remove an attribute from the current field definition array.
     *
     * @param  string $attribute Name of the attribute being removed.
     * @return CrudField
     */
    public function forget($attribute)
    {
        $this->crud()->removeFieldAttribute($this->attributes['name'], $attribute);

        return $this;
    }

    /**
     * Move the current field after another field.
     *
     * @param  string $destinationField Name of the destination field.
     * @return CrudField
     */
    public function after($destinationField)
    {
        $this->crud()->removeField($this->attributes['name']);
        $this->crud()->addField($this->attributes)->afterField($destinationField);

        return $this;
    }

    /**
     * Move the current field before another field.
     *
     * @param  string $destinationField Name of the destination field.
     * @return CrudField
     */
    public function before($destinationField)
    {
        $this->crud()->removeField($this->attributes['name']);
        $this->crud()->addField($this->attributes)->beforeField($destinationField);

        return $this;
    }

    /**
     * Make the current field the first one in the fields list.
     *
     * @return CrudField
     */
    public function makeFirst()
    {
        $this->crud()->removeField($this->attributes['name']);
        $this->crud()->addField($this->attributes)->makeFirstField();

        return $this;
    }

    /**
     * Make the current field the last one in the fields list.
     *
     * @return CrudField
     */
    public function makeLast()
    {
        $this->crud()->removeField($this->attributes['name']);
        $this->crud()->addField($this->attributes);

        return $this;
    }

    // -------------------
    // CONVENIENCE METHODS
    // -------------------
    // These methods don't do exactly what advertised by their name.
    // They exist because the original syntax was too long.

    /**
     * Set the wrapper width at this many number of columns.
     * For example, to set a field wrapper to span across 6 columns, you can do both:
     * ->wrapper(['class' => 'form-group col-md-6'])
     * ->size(6).
     *
     * @param  int $numberOfColumns How many columns should this field span across (1-12)?
     * @return CrudField
     */
    public function size($numberOfColumns)
    {
        $this->attributes['wrapper']['class'] = 'form-group col-md-'.$numberOfColumns;

        return $this->save();
    }

    // ---------------
    // PRIVATE METHODS
    // ---------------

    /**
     * Set the value for a certain attribute on the CrudField object.
     *
     * @param string $attribute Name of the attribute.
     * @param mixed $value     Value of that attribute.
     */
    private function setAttributeValue($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * Replace all field attributes on the CrudField object
     * with the given array of attribute-value pairs.
     *
     * @param array $array Array of attributes and their values.
     */
    private function setAllAttributeValues($array)
    {
        $this->attributes = $array;
    }

    /**
     * Update the global CrudPanel object with the current field attributes.
     *
     * @return CrudField
     */
    private function save()
    {
        $key = $this->attributes['name'];

        if ($this->crud()->hasFieldWhere('name', $key)) {
            $this->crud()->modifyField($key, $this->attributes);
        } else {
            $this->crud()->addField($this->attributes);
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
     * @return CrudField
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
     * @return CrudField
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
     * - the CrudField object to have an attribute with that value;
     * - that field be updated inside the global CrudPanel object;.
     *
     * Eg: type('number') will set the "type" attribute to "number"
     *
     * @param  string $method     The method being called that doesn't exist.
     * @param  array $parameters  The arguments when that method was called.
     *
     * @return CrudField
     */
    public function __call($method, $parameters)
    {
        $this->setAttributeValue($method, $parameters[0]);

        return $this->save();
    }
}
