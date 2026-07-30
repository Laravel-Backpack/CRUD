<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    | The session key used to flash alert messages to the frontend.
    */
    'session_key' => 'alert_messages',

    /*
    |--------------------------------------------------------------------------
    | Toast Defaults
    |--------------------------------------------------------------------------
    | Default configuration for toast notifications rendered via the Noty shim.
    | Can be overridden per-call via new Noty({...}) options.
    */
    'toast' => [

        /*
        |----------------------------------------------------------------------
        | Container CSS Classes
        |----------------------------------------------------------------------
        | Bootstrap 5 positioning classes for the toast container.
        | BS5 supports 9 placements out of the box:
        |   top-0 start-0                         — Top left
        |   top-0 start-50 translate-middle-x     — Top center
        |   top-0 end-0                           — Top right (default)
        |   top-50 start-0 translate-middle-y     — Middle left
        |   top-50 start-50 translate-middle      — Middle center
        |   top-50 end-0 translate-middle-y       — Middle right
        |   bottom-0 start-0                      — Bottom left
        |   bottom-0 start-50 translate-middle-x  — Bottom center
        |   bottom-0 end-0                        — Bottom right
        |
        | Or set any custom CSS classes. No limitations.
        */
        'container_class' => 'backpack-toast-container position-fixed top-0 end-0 p-3',

        /*
        |----------------------------------------------------------------------
        | Position Map
        |----------------------------------------------------------------------
        | Maps legacy Noty layout names to BS5 container positioning classes.
        | Used by the Noty shim for backward compatibility when setting position
        | programmatically. Users can add custom keys.
        */
        'positions' => [
            'topRight'      => 'top-0 end-0',
            'topLeft'       => 'top-0 start-0',
            'bottomRight'   => 'bottom-0 end-0',
            'bottomLeft'    => 'bottom-0 start-0',
            'topCenter'     => 'top-0 start-50 translate-middle-x',
            'bottomCenter'  => 'bottom-0 start-50 translate-middle-x',
            'center'        => 'top-50 start-50 translate-middle',
        ],

        /*
        |----------------------------------------------------------------------
        | Default Behavior
        |----------------------------------------------------------------------
        */
        'default_timeout' => 2500,          // ms, 0 = sticky (data-bs-autohide="false")
        'dismissible' => true,              // show close button
        'animation' => true,                // BS5 CSS fade transition
        'close_on_click' => true,           // clicking the toast dismisses it
    ],

];
