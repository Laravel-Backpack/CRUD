<?php

namespace Backpack\CRUD\app\Models\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Arr;

trait HasIdentifiableAttribute
{
    /**
     * Get the name of the attribute that best defines the entry, from the user perspective.
     *
     * Rephrased: In most cases a user will NOT identify an Article because its ID is "4", but
     * because its name is "10 Ways to Burn Fat". This method returns the column in the database
     * that represents what is better to show to the user as an identifier rather than the ID.
     * Ex: name, title, label, description etc.
     *
     * @return string The name of the column that best defines this entry from the user perspective.
     */
    public function identifiableAttribute()
    {
        if (property_exists($this, 'identifiableAttribute')) {
            return $this->identifiableAttribute;
        }

        return static::guessIdentifiableColumnName();
    }

    /**
     * Get the most likely column in the db table that could be used as an identifiable attribute.
     *
     * @return string The name of the column in the database that is most likely to be a good indentifying attribute.
     */
    private static function guessIdentifiableColumnName()
    {
        $instance = new static();

        $conn = $instance->getConnectionWithExtraTypeMappings();

        // column listing is not available in non-sql databases. In this scenario we infer
        // the identifiable attribute from the model `fillable` attributes
        if (! in_array($conn->getConfig()['driver'], CRUD::getSqlDriverList())) {
            return $instance->inferIdentifiableAttributeFromModelFillable();
        }

        $table = $instance->getTableWithPrefix();
        $columns = $conn->getDoctrineSchemaManager()->listTableColumns($table);
        $indexes = $conn->getDoctrineSchemaManager()->listTableIndexes($table);
        $columnsNames = array_keys($columns);

        // if any of the sensibleDefaultNames column exists
        // that's probably a good choice
        foreach ($instance->getSensibleDefaultNames() as $defaultName) {
            if (in_array($defaultName, $columnsNames)) {
                return $defaultName;
            }
        }

        // get indexed columns in database table
        $indexedColumns = [];
        foreach ($indexes as $index) {
            $indexColumns = $index->getColumns();
            foreach ($indexColumns as $ic) {
                array_push($indexedColumns, $ic);
            }
        }

        // if none of the sensible defaults exists
        // we get the first column from database
        // that is NOT indexed (usually primary, foreign keys)
        foreach ($columns as $columnName => $columnProperties) {
            if (! in_array($columnName, $indexedColumns)) {

                //check for convention "field<_id>" in case developer didn't add foreign key constraints.
                if (strpos($columnName, '_id') !== false) {
                    continue;
                }

                return $columnName;
            }
        }

        // in case everything fails we just return the first column in database
        return Arr::first($columnsNames);
    }

    /**
     * Infer the identifiable attribute from model fillable when getting the columns listing is not available.
     *
     * @return void
     */
    public function inferIdentifiableAttributeFromModelFillable()
    {
        $fillableFields = $this->getFillable();
        if (! empty($fillableFields)) {
            $matchedAttributeNames = array_intersect($this->getSensibleDefaultNames(), $fillableFields);
            if (! empty($matchedAttributeNames)) {
                return reset($matchedAttributeNames);
            }

            return reset($fillableFields);
        }

        abort(500, 'Impossible to determine the identifiable attribute. Add it manually to your model or in your field definition.');
    }

    /**
     * Returns a list of sensible default names to be shown to endusers.
     *
     * @return array
     */
    public function getSensibleDefaultNames()
    {
        return ['name', 'title', 'description', 'label'];
    }
}
