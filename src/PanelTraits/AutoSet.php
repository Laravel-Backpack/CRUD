<?php

namespace Backpack\CRUD\PanelTraits;

use Backpack\CRUD\DBAL\Type\EnumType;
use Doctrine\DBAL\Types\Type;

trait AutoSet
{
    // ------------------------------------------------------
    // AUTO-SET-FIELDS-AND-COLUMNS FUNCTIONALITY
    // ------------------------------------------------------
    public $labeller = false;

    /**
     * Enable this setting
     * Temporary EnumType will inject into DBAL
     */
    public function enableEnumTypeAutoSet()
    {
        $this->enum_type_auto_set = true;
    }

    /**
     * For a simple CRUD Panel, there should be no need to add/define the fields.
     * The public columns in the database will be converted to be fields.
     *
     * @return void
     */
    public function setFromDb()
    {
        if ($this->enum_type_auto_set) {
            Type::addType('enum', EnumType::class);
            $platform = $this->model->getConnection()->getDoctrineSchemaManager()->getDatabasePlatform();
            $platform->registerDoctrineTypeMapping('enum', 'enum');
        }

        if (! $this->driverIsMongoDb()) {
            $this->setDoctrineTypesMapping();
            $this->getDbColumnTypes();
        }

        array_map(function ($field) {
            $new_field = [
                'name'       => $field,
                'label'      => $this->makeLabel($field),
                'value'      => null,
                'default'    => isset($this->db_column_types[$field]['default']) ? $this->db_column_types[$field]['default'] : null,
                'type'       => $this->getFieldTypeFromDbColumnType($field),
                'values'     => [],
                'attributes' => [],
                'autoset'    => true,
            ];
            if (! isset($this->create_fields[$field])) {
                $this->create_fields[$field] = $new_field;
            }
            if (! isset($this->update_fields[$field])) {
                $this->update_fields[$field] = $new_field;
            }

            if (! in_array($field, $this->model->getHidden()) && ! isset($this->columns[$field])) {
                $this->addColumn([
                    'name'  => $field,
                    'label' => $this->makeLabel($field),
                    'type'  => $this->getFieldTypeFromDbColumnType($field),
                    'autoset' => true,
                ]);
            }
        }, $this->getDbColumnsNames());
    }

    /**
     * Get all columns from the database for that table.
     *
     * @return array
     */
    public function getDbColumnTypes()
    {
        foreach ($this->getDbTableColumns() as $key => $column) {
            $column_type = $column->getType()->getName();
            $this->db_column_types[$column->getName()]['type'] = trim(preg_replace('/\(\d+\)(.*)/i', '', $column_type));
            $this->db_column_types[$column->getName()]['default'] = $column->getDefault();
        }

        return $this->db_column_types;
    }

    /**
     * Get all columns in the database table.
     *
     * @return array
     */
    public function getDbTableColumns()
    {
        if (isset($this->table_columns) && $this->table_columns) {
            return $this->table_columns;
        }

        $conn = $this->model->getConnection();
        $table = $conn->getTablePrefix().$this->model->getTable();
        $columns = $conn->getDoctrineSchemaManager()->listTableColumns($table);

        $this->table_columns = $columns;

        return $this->table_columns;
    }

    /**
     * Intuit a field type, judging from the database column type.
     *
     * @param  string $field Field name.
     *
     * @return string Field type.
     */
    public function getFieldTypeFromDbColumnType($field)
    {
        if (! array_key_exists($field, $this->db_column_types)) {
            return 'text';
        }

        if ($field == 'password') {
            return 'password';
        }

        if ($field == 'email') {
            return 'email';
        }

        switch ($this->db_column_types[$field]['type']) {
            case 'int':
            case 'integer':
            case 'smallint':
            case 'mediumint':
            case 'longint':
                return 'number';
                break;

            case 'string':
            case 'varchar':
            case 'set':
                return 'text';
                break;

            case 'enum':
                return !$this->enum_type_auto_set ? 'text' : 'enum';
            break;

            case 'boolean':
                return 'boolean';
                break;

            case 'text':
            case 'mediumtext':
            case 'longtext':
                return 'textarea';
                break;

            case 'date':
                return 'date';
                break;

            case 'datetime':
            case 'timestamp':
                return 'datetime';
                break;

            case 'time':
                return 'time';
                break;

            default:
                return 'text';
                break;
        }
    }

    // Fix for DBAL not supporting enum
    public function setDoctrineTypesMapping()
    {
        $types = ['enum' => 'string'];
        $platform = $this->getSchema()->getConnection()->getDoctrineConnection()->getDatabasePlatform();
        foreach ($types as $type_key => $type_value) {
            if (! $platform->hasDoctrineTypeMappingFor($type_key)) {
                $platform->registerDoctrineTypeMapping($type_key, $type_value);
            }
        }
    }

    /**
     * Turn a database column name or PHP variable into a pretty label to be shown to the user.
     *
     * @param  string $value The value.
     *
     * @return string The transformed value.
     */
    public function makeLabel($value)
    {
        if ($this->labeller) {
            return ($this->labeller)($value);
        }

        return trim(preg_replace('/(id|at|\[\])$/i', '', mb_ucfirst(str_replace('_', ' ', $value))));
    }

    /**
     * Alias to the makeLabel method.
     */
    public function getLabel($value)
    {
        return $this->makeLabel($value);
    }

    /**
     * Change the way labels are made.
     *
     * @param callable $labeller A function that receives a string and returns the formatted string, after stripping down useless characters.
     *
     * @return self
     */
    public function setLabeller(callable $labeller)
    {
        $this->labeller = $labeller;

        return $this;
    }

    /**
     * Get the database column names, in order to figure out what fields/columns to show in the auto-fields-and-columns functionality.
     *
     * @return array Database column names as an array.
     */
    public function getDbColumnsNames()
    {
        $fillable = $this->model->getFillable();

        if ($this->driverIsMongoDb()) {
            $columns = $fillable;
        } else {
            // Automatically-set columns should be both in the database, and in the $fillable variable on the Eloquent Model
            $columns = $this->model->getConnection()->getSchemaBuilder()->getColumnListing($this->model->getTable());

            if (! empty($fillable)) {
                $columns = array_intersect($columns, $fillable);
            }
        }

        // but not updated_at, deleted_at
        return array_values(array_diff($columns, [$this->model->getKeyName(), $this->model->getCreatedAtColumn(), $this->model->getUpdatedAtColumn(), 'deleted_at']));
    }
}
