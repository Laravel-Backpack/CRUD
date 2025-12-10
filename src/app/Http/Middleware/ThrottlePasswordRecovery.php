<?php

namespace Backpack\CRUD\app\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Validation\ValidationException;

class ThrottlePasswordRecovery extends ThrottleRequests
{
    /**
     * Return a validation exception with a nice message to the user instead of the big fat app error.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  ?callable  $responseCallback
     * @return \Illuminate\Validation\ValidationException
     */
    protected function buildException($request, $key, $maxAttempts, $responseCallback = null)
    {
        return ValidationException::withMessages([
            'email' => [trans('backpack::base.throttled_request')],
        ]);
    }
}
