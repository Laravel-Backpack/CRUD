<?php

namespace Backpack\CRUD\app\Library\Alerts;

enum AlertType: string
{
    case Success = 'success';
    case Error = 'error';
    case Warning = 'warning';
    case Info = 'info';
}
