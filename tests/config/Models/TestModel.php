<?php

namespace Backpack\CRUD\Tests\config\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;

class TestModel extends \Illuminate\Database\Eloquent\Model
{
    use CrudTrait;

    protected $casts = [
        'arrayCast' => 'array',
        'jsonCast' => 'json',
        'dateCast' => 'date',
        'booleanCast' => 'boolean',
        'datetimeCast' => 'datetime',
        'numberCast' => 'timestamp',
    ];

    protected $dates = [
        'someDate',
    ];

    public function buttonModelFunction()
    {
        return 'model function button test';
    }

    public function article()
    {
        return $this->belongsTo('Backpack\CRUD\Tests\Config\Models\Article');
    }
}
