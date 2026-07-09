<?php

namespace Backpack\CRUD\Tests\config\Models;

use Backpack\CRUD\app\Models\Traits\HasEnumFields;
use Illuminate\Database\Eloquent\Model;

/**
 * Model used exclusively by the MySQL integration tests for HasEnumFields.
 * It connects to a real MySQL database to exercise actual ENUM columns.
 */
class EnumTestModel extends Model
{
    use HasEnumFields;

    // Set dynamically by the test via getEnvironmentSetUp().
    protected $connection = 'testing_mysql';

    protected $table = 'crud_enum_test';
}
