<?php

namespace Backpack\CRUD\Tests\Unit\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use CrudTrait;

    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];

    /**
     * Get the account details associated with the user.
     */
    public function accountDetails()
    {
        return $this->hasOne('Backpack\CRUD\Tests\Unit\Models\AccountDetails');
    }

    /**
     * Get the articles for this user.
     */
    public function articles()
    {
        return $this->hasMany('Backpack\CRUD\Tests\Unit\Models\Article');
    }

    /**
     * Get the user roles.
     */
    public function roles()
    {
        return $this->belongsToMany('Backpack\CRUD\Tests\Unit\Models\Role', 'user_role');
    }

    public function getNameComposedAttribute()
    {
        return $this->name.'++';
    }
}
