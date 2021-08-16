<?php

namespace Backpack\CRUD\app\Models\Traits;

use Illuminate\Support\Str;

/**
 * Generates a UUID on model creation accoriding to the public $uuidColumn parameter
 */
	
trait CanGenerateUuid{
    protected static function bootCanGenerateUuid()
    {
        static::creating(function ($model) {
            if ($model->uuidColumn != null) {
                $model->{$model->uuidColumn} = (string) Str::uuid();
            }
        });
    }

}