<?php

namespace Backpack\CRUD\Tests\config\Models;

use Illuminate\Notifications\Notifiable;

class NotifiableUser extends User
{
    use Notifiable;
}
