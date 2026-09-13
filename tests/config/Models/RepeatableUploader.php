<?php

namespace Backpack\CRUD\Tests\config\Models;

use Illuminate\Database\Eloquent\Model;

class RepeatableUploader extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;

    protected $table = 'uploaders';

    protected $fillable = ['upload', 'upload_multiple', 'image', 'repeatable'];

    protected $casts = [
        'upload_multiple' => 'json',
        'repeatable' => 'json',
    ];

    public $timestamps = false;
}
