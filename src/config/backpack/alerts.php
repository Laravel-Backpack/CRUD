<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    | The session key used to flash alert messages to the frontend.
    | Kept as 'alert_messages' for backward compatibility with existing
    | theme templates that read from this key.
    */
    'session_key' => 'alert_messages',

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    | Default values applied to new AlertMessage instances.
    | Can be overridden per-message via modifiers.
    */
    'defaults' => [
        'timeout' => 2500,          // ms, 0 = sticky
        'dismissible' => true,
    ],

];
