<?php

namespace Backpack\CRUD\app\Models\Traits;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/*
|--------------------------------------------------------------------------
| Methods for working with the Enum column in MySQL.
|--------------------------------------------------------------------------
*/
trait HasEnumFields
{
    public static function getPossibleEnumValues($field_name)
    {
        $default_connection = Config::get('database.default');
        $table_prefix = Config::get('database.connections.'.$default_connection.'.prefix');

        $instance = new static(); // create an instance of the model to be able to get the table name
        $connection = $instance->getConnection();

        // SQLite doesn't support enum
        if ($connection->getConfig('driver') === 'sqlite') {
            return null;
        }

        $type = $connection->select(DB::raw('SHOW COLUMNS FROM `'.$table_prefix.$instance->getTable().'` WHERE Field = "'.$field_name.'"'))[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = [];
        foreach (explode(',', $matches[1]) as $value) {
            $enum[] = trim($value, "'");
        }

        return $enum;
    }

    public static function getEnumValuesAsAssociativeArray($field_name)
    {
        $instance = new static();
        $enum_values = $instance->getPossibleEnumValues($field_name);

        $array = array_flip($enum_values);

        foreach ($array as $key => $value) {
            $array[$key] = $key;
        }

        return $array;
    }
}
