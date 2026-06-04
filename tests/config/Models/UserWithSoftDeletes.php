<?php

namespace Backpack\CRUD\Tests\Config\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class UserWithSoftDeletes extends User
{
    use SoftDeletes;
}
