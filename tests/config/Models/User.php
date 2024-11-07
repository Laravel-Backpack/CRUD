<?php

namespace Backpack\CRUD\Tests\Config\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use CrudTrait;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'extras', 'bang_relation_field'];

    public function identifiableAttribute()
    {
        return 'name';
    }

    /**
     * Get the account details associated with the user.
     */
    public function accountDetails()
    {
        return $this->hasOne('Backpack\CRUD\Tests\config\Models\AccountDetails');
    }

    /**
     * Get the articles for this user.
     */
    public function articles()
    {
        return $this->hasMany('Backpack\CRUD\Tests\config\Models\Article');
    }

    /**
     * Get the user roles.
     */
    public function roles()
    {
        return $this->belongsToMany('Backpack\CRUD\Tests\config\Models\Role', 'user_role');
    }

    public function getNameComposedAttribute()
    {
        return $this->name.'++';
    }

    public function comment()
    {
        return $this->morphOne('Backpack\CRUD\Tests\config\Models\Comment', 'commentable');
    }

    public function recommends()
    {
        return $this->morphToMany('Backpack\CRUD\Tests\config\Models\Recommend', 'recommendable')->withPivot('text');
    }

    public function recommendsDuplicate()
    {
        return $this->morphToMany('Backpack\CRUD\Tests\config\Models\Recommend', 'recommendable')->withPivot(['text', 'id']);
    }

    public function bills()
    {
        return $this->morphToMany('Backpack\CRUD\Tests\config\Models\Bill', 'billable');
    }

    public function stars()
    {
        return $this->morphMany('Backpack\CRUD\Tests\config\Models\Star', 'starable');
    }

    public function superArticles()
    {
        return $this->belongsToMany('Backpack\CRUD\Tests\config\Models\Article', 'articles_user')->withPivot(['notes', 'start_date', 'end_date']);
    }

    public function superArticlesDuplicates()
    {
        return $this->belongsToMany('Backpack\CRUD\Tests\config\Models\Article', 'articles_user')
                        ->withPivot(['notes', 'start_date', 'end_date', 'id'])
                        ->using('Backpack\CRUD\Tests\config\Models\SuperArticlePivot');
    }

    public function universes()
    {
        return $this->hasMany('Backpack\CRUD\Tests\config\Models\Universe');
    }

    public function planets()
    {
        return $this->hasMany('Backpack\CRUD\Tests\config\Models\Planet');
    }

    public function planetsNonNullable()
    {
        return $this->hasMany('Backpack\CRUD\Tests\config\Models\PlanetNonNullable');
    }

    public function comets()
    {
        return $this->hasMany('Backpack\CRUD\Tests\config\Models\Comet');
    }

    public function bang()
    {
        return $this->belongsTo('Backpack\CRUD\Tests\config\Models\Bang', 'bang_relation_field');
    }

    public function incomes()
    {
        return $this->hasMany('Backpack\CRUD\Tests\config\Models\Transaction')->ofType('income');
    }

    public function expenses()
    {
        return $this->hasMany('Backpack\CRUD\Tests\config\Models\Transaction')->ofType('expense');
    }

    protected function isNotRelation()
    {
        return false;
    }

    public function isNotRelationPublic($arg)
    {
        return false;
    }
}
