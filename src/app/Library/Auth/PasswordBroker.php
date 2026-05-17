<?php

namespace Backpack\CRUD\app\Library\Auth;

use Backpack\CRUD\app\Notifications\ResetPasswordNotification;
use Closure;
use Illuminate\Auth\Passwords\PasswordBroker as OriginalPasswordBroker;

/**
 * Overwrite of Illuminate\Auth\Passwords\PasswordBroker in order to send
 * a custom notification for Backpack admins, not the default password reset notification.
 */
class PasswordBroker extends OriginalPasswordBroker
{
    public const RESET_THROTTLED = 'backpack::base.throttled';

    /**
     * Send a password reset link to a user.
     *
     * @param  array  $credentials
     * @return string
     */
    public function sendResetLink(array $credentials, ?Closure $callback = null)
    {
        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        if (method_exists($this->tokens, 'recentlyCreatedToken') &&
            $this->tokens->recentlyCreatedToken($user)) {
            return static::RESET_THROTTLED;
        }

        // Once we have the reset token, we are ready to send the message out to this
        // user with a link to reset their password. We will then redirect back to
        // the current URI having nothing set in the session to indicate errors.
        $user->notify(new ResetPasswordNotification($this->tokens->create($user), $user->email));

        return static::RESET_LINK_SENT;
    }
}
