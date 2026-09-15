<?php

namespace Backpack\CRUD\Tests\config\Models;

use Illuminate\Database\Eloquent\Model;

class Uploader extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['upload', 'upload_multiple', 'extras'];

    protected $casts = [
        'upload_multiple' => 'json',
    ];

    public $timestamps = false;
}
