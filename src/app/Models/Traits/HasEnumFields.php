<?php

namespace Backpack\CRUD\app\Models\Traits;

use Illuminate\Support\Collection;

/*
|--------------------------------------------------------------------------
| Methods for working with the Enum column in MySQL.
|--------------------------------------------------------------------------
*/
trait HasEnumFields
{
    public static function getPossibleEnumValues($field_name)
    {
        $instance = new static(); // create an instance of the model to be able to get the table name

        $connection = $instance->getConnection();
        $table = $instance->getTable();

        $type = $connection->getSchemaBuilder()->getColumnType($table, $field_name, true);

        if (preg_match('/^enum\((.*)\)$/', $type, $matches) !== 1) {
            abort(500, 'Could not deduce enum values - note this only works on selected engines (e.g. MySQL). Please use select_from_array instead.', ['developer-error-exception']);
        }

        return (new Collection(explode(',', $matches[1])))
            ->map(static fn (string $value) => trim($value, "'"))
            ->values()
            ->toArray();
    }

    public static function getEnumValuesAsAssociativeArray($field_name)
    {
        return (new Collection(static::getPossibleEnumValues($field_name)))
            ->mapWithKeys(static fn (string $value) => [$value => $value])
            ->toArray();
    }
}
