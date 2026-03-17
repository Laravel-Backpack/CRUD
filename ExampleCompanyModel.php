<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use CrudTrait;
    use HasTranslations;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'email',
        'status',
    ];

    protected $translatable = [
        'name',
        'description',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isActive()
    {
        return $this->status === true;
    }
}
