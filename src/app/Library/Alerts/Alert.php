<?php

namespace Backpack\CRUD\app\Library\Alerts;

use Illuminate\Support\Facades\Facade;

/**
 * @method static ToastMessage success(string $message)
 * @method static ToastMessage error(string $message)
 * @method static ToastMessage warning(string $message)
 * @method static ToastMessage info(string $message)
 * @method static ToastMessage add(string|AlertType $type, string $message)
 * @method static void flash()
 * @method static void flush(bool $withSession = true)
 * @method static array getMessages()
 * @method static bool has()
 * @method static int count()
 *
 * @see \Backpack\CRUD\app\Library\Alerts\AlertsMessageBag
 */
class Alert extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'alerts';
    }
}
