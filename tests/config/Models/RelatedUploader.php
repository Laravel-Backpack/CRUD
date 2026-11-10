<?php

namespace Backpack\CRUD\Tests\config\Models;

use Illuminate\Database\Eloquent\Model;

class RelatedUploader extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;

    protected $fillable = ['upload', 'image'];

    public $timestamps = false;
}
