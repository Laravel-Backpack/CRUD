<?php

namespace Backpack\CRUD\Tests\config\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class SchemaTestModel extends Model
{
    use CrudTrait;

    protected $table = 'schema_test_models';
}
