<?php

namespace Backpack\CRUD\app\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Traversable;

/*
|--------------------------------------------------------------------------
| Methods for Fake Fields functionality (used in PageManager).
|--------------------------------------------------------------------------
*/
trait HasFakeFields
{
    /**
     * Add fake fields as regular attributes, even though they are stored as JSON.
     *
     * @param  array  $columns  - the database columns that contain the JSONs
     */
    public function addFakes($columns = ['extras'])
    {
        foreach ($columns as $key => $column) {
            if (! isset($this->attributes[$column])) {
                continue;
            }

            $column_contents = $this->{$column};

            if ($this->shouldDecodeFake($column)) {
                $column_contents = json_decode($column_contents);
            }

            if (is_array($column_contents) || is_object($column_contents) || $column_contents instanceof Traversable) {
                foreach ($column_contents as $fake_field_name => $fake_field_value) {
                    $this->setAttribute($fake_field_name, $fake_field_value);
                }
            }
        }
    }

    /**
     * Return the entity with fake fields as attributes.
     *
     * @param  array  $columns  - the database columns that contain the JSONs
     * @return Model
     */
    public function withFakes($columns = [])
    {
        $model = '\\'.get_class($this);

        $columnCount = ((is_array($columns) || $columns instanceof Countable) ? count($columns) : 0);

        if ($columnCount == 0) {
            $columns = (property_exists($model, 'fakeColumns')) ? $this->fakeColumns : ['extras'];
        }

        $this->addFakes($columns);

        return $this;
    }

    /**
     * Determine if this fake column should be json_decoded.
     *
     * @param  $column  string fake column name
     * @return bool
     */
    public function shouldDecodeFake($column)
    {
        return ! in_array($column, array_keys($this->casts));
    }

    /**
     * Determine if this fake column should get json_encoded or not.
     *
     * @param  $column  string fake column name
     * @return bool
     */
    public function shouldEncodeFake($column)
    {
        return ! in_array($column, array_keys($this->casts));
    }
}
