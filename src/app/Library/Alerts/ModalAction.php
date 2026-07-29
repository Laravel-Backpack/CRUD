<?php

namespace Backpack\CRUD\app\Library\Alerts;

enum ModalAction: string
{
    case Confirm = 'confirm';
    case Prompt  = 'prompt';
    case Alert   = 'alert';
}
