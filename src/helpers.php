<?php

use Backpack\Basset\Facades\Basset;
use Creativeorange\Gravatar\Facades\Gravatar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

if (! function_exists('backpack_url')) {
    /**
     * Appends the configured backpack prefix and returns
     * the URL using the standard Laravel helpers.
     *
     * @param  $path
     * @return string
     */
    function backpack_url($path = null, $parameters = [], $secure = null)
    {
        $path = ! $path || (substr($path, 0, 1) == '/') ? $path : '/'.$path;

        return url(config('backpack.base.route_prefix', 'admin').$path, $parameters, $secure);
    }
}

if (! function_exists('backpack_authentication_column')) {
    /**
     * Return the username column name.
     * The Laravel default (and Backpack default) is 'email'.
     *
     * @return string
     */
    function backpack_authentication_column()
    {
        return config('backpack.base.authentication_column', 'email');
    }
}

if (! function_exists('backpack_email_column')) {
    /**
     * Return the email column name.
     * The Laravel default (and Backpack default) is 'email'.
     *
     * @return string
     */
    function backpack_email_column()
    {
        return config('backpack.base.email_column', 'email');
    }
}

if (! function_exists('backpack_form_input')) {
    /**
     * Parse the submitted input in request('form') to an usable array.
     * Joins the multiple[] fields in a single key and transform the dot notation fields into arrayed ones.
     *
     *
     * @return array
     */
    function backpack_form_input()
    {
        $input = request('form') ?? [];
        $result = [];

        foreach ($input as $row) {
            $repeatableRowKey = null;

            // regular fields don't need any additional parsing
            if (strpos($row['name'], '[') === false) {
                $result[$row['name']] = $row['value'];

                continue;
            }

            $isMultiple = substr($row['name'], -2, 2) === '[]';

            if ($isMultiple && substr_count($row['name'], '[') === 1) {
                $result[substr($row['name'], 0, -2)][] = $row['value'];
                continue;
            }

            // dot notation fields
            if (substr_count($row['name'], '[') === 1) {
                // start in the first occurrence since it's HasOne/MorphOne with dot notation (address[street] in request) to get the input name (address)
                $inputNameStart = strpos($row['name'], '[') + 1;
            } else {
                // repeatable fields, we need to get the input name and the row number
                // start on the second occurrence since it's a repeatable and we want to bypass the row number (repeatableName[rowNumber][inputName])
                $inputNameStart = strpos($row['name'], '[', strpos($row['name'], '[') + 1) + 1;

                // get the array key (aka repeatable row) from field name
                $startKey = strpos($row['name'], '[') + 1;
                $endKey = strpos($row['name'], ']', $startKey);
                $lengthKey = $endKey - $startKey;
                $repeatableRowKey = substr($row['name'], $startKey, $lengthKey);
            }

            $inputNameEnd = strpos($row['name'], ']', $inputNameStart);
            $inputNameLength = $inputNameEnd - $inputNameStart;
            $inputName = substr($row['name'], $inputNameStart, $inputNameLength);
            $parentInputName = substr($row['name'], 0, strpos($row['name'], '['));

            if (isset($repeatableRowKey)) {
                if ($isMultiple) {
                    $result[$parentInputName][$repeatableRowKey][$inputName][] = $row['value'];
                    continue;
                }

                $result[$parentInputName][$repeatableRowKey][$inputName] = $row['value'];

                continue;
            }

            if ($isMultiple) {
                $result[$parentInputName][$inputName][] = $row['value'];
                continue;
            }
            $result[$parentInputName][$inputName] = $row['value'];
        }

        return $result;
    }
}

if (! function_exists('backpack_users_have_email')) {
    /**
     * Check if the email column is present on the user table.
     *
     * @return string
     */
    function backpack_users_have_email()
    {
        $user_model_fqn = config('backpack.base.user_model_fqn');
        $user = new $user_model_fqn();

        return \Schema::hasColumn($user->getTable(), config('backpack.base.email_column') ?? 'email');
    }
}

if (! function_exists('backpack_avatar_url')) {
    /**
     * Returns the avatar URL of a user.
     *
     * @param  $user
     * @return string
     */
    function backpack_avatar_url($user)
    {
        switch (config('backpack.base.avatar_type')) {
            case 'gravatar':
                if (backpack_users_have_email() && ! empty($user->email)) {
                    $avatarLink = Gravatar::fallback(config('backpack.base.gravatar_fallback'))->get($user->email, ['size' => 80]);

                    // if we can save it locally, for safer loading, let's do it
                    if (in_array(Basset::basset($avatarLink, false)->name, ['INTERNALIZED', 'IN_CACHE', 'LOADED'])) {
                        return Basset::getUrl($avatarLink);
                    }

                    return $avatarLink;
                }
                break;
            default:
                return method_exists($user, config('backpack.base.avatar_type')) ? $user->{config('backpack.base.avatar_type')}() : $user->{config('backpack.base.avatar_type')};
                break;
        }
    }
}

if (! function_exists('backpack_middleware')) {
    /**
     * Return the key of the middleware used across Backpack.
     * That middleware checks if the visitor is an admin.
     *
     * @param  $path
     * @return string
     */
    function backpack_middleware()
    {
        return config('backpack.base.middleware_key', 'admin');
    }
}

if (! function_exists('backpack_guard_name')) {
    /*
     * Returns the name of the guard defined
     * by the application config
     */
    function backpack_guard_name()
    {
        return config('backpack.base.guard', config('auth.defaults.guard'));
    }
}

if (! function_exists('backpack_auth')) {
    /*
     * Returns the user instance if it exists
     * of the currently authenticated admin
     * based off the defined guard.
     */
    function backpack_auth()
    {
        return \Auth::guard(backpack_guard_name());
    }
}

if (! function_exists('backpack_user')) {
    /*
     * Returns back a user instance without
     * the admin guard, however allows you
     * to pass in a custom guard if you like.
     */
    function backpack_user()
    {
        return backpack_auth()->user();
    }
}

if (! function_exists('mb_ucfirst')) {
    /**
     * Capitalize the first letter of a string,
     * even if that string is multi-byte (non-latin alphabet).
     *
     * @param  string  $string  String to have its first letter capitalized.
     * @param  encoding  $encoding  Character encoding
     * @return string String with first letter capitalized.
     */
    function mb_ucfirst($string, $encoding = false)
    {
        $encoding = $encoding ? $encoding : mb_internal_encoding();

        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);

        return mb_strtoupper($firstChar, $encoding).$then;
    }
}

if (! function_exists('backpack_view')) {
    /**
     * Returns a new displayable view path, based on the configured backpack view namespace.
     * If that view doesn't exist, it falls back to the fallback namespace.
     * If that view doesn't exist, it falls back to the one from the Backpack UI directory.
     *
     * @param string (see config/backpack/base.php)
     * @return string
     */
    function backpack_view($view)
    {
        $viewPaths = [
            config('backpack.ui.view_namespace').$view,
            backpack_theme_config('view_namespace_fallback').$view,
            'backpack.ui::'.$view,
        ];

        foreach ($viewPaths as $view) {
            if (view()->exists($view)) {
                return $view;
            }
        }

        $errorMessage = 'The view: ['.$view.'] was not found in any of the following view paths: ['.implode(' ], [ ', $viewPaths).']';

        $errorDetails = (function () {
            if (env('APP_ENV') === 'production' || ! env('APP_DEBUG')) {
                return '';
            }

            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2) ?? [];
            $functionCaller = $backtrace[1] ?? [];
            $functionLine = $functionCaller['line'] ?? 'N/A';
            $functionFile = $functionCaller['file'] ?? 'N/A';

            return '- Called in: '.Str::after($functionFile, base_path()).' on line: '.$functionLine;
        })();

        abort(500, $errorMessage.$errorDetails, ['developer-error-exception']);
    }
}

if (! function_exists('backpack_theme_config')) {
    /**
     * Returns a config value from the current theme's config file.
     * It assumes the theme's config namespace is the same as the view namespace.
     *
     * @param string
     * @return string
     */
    function backpack_theme_config($key)
    {
        $namespacedKey = config('backpack.ui.view_namespace').$key;
        $namespacedKey = str_replace('::', '.', $namespacedKey);

        // if the config exists in the theme config file, use it
        if (config()->has($namespacedKey)) {
            return config($namespacedKey);
        }

        // if not, fall back to a general the config in the fallback theme
        $namespacedKey = config('backpack.ui.view_namespace_fallback').$key;
        $namespacedKey = str_replace('::', '.', $namespacedKey);

        if (config()->has($namespacedKey)) {
            return config($namespacedKey);
        }

        // if not, fall back to the config in ui
        $namespacedKey = 'backpack.ui.'.$key;

        if (config()->has($namespacedKey)) {
            return config($namespacedKey);
        }

        Log::error('Could not find config key: '.$key.'. Neither in the Backpack theme, nor in the fallback theme, nor in ui.');

        return null;
    }
}

if (! function_exists('square_brackets_to_dots')) {
    /**
     * Turns a string from bracket-type array to dot-notation array.
     * Ex: array[0][property] turns into array.0.property.
     *
     * @param  $path
     * @return string
     */
    function square_brackets_to_dots($string)
    {
        $string = str_replace(['[', ']'], ['.', ''], $string);

        return $string;
    }
}

if (! function_exists('old_empty_or_null')) {
/**
 * This method is an alternative to Laravel's old() helper, which mistakenly
 * returns NULL it two cases:
 * - if there is an old value, and it was empty or null
 * - if there is no old value
 * (this is because of the ConvertsEmptyStringsToNull middleware).
 *
 * In contrast, this method will return:
 * - the old value, if there actually is an old value for that key;
 * - the second parameter, if there is no old value for that key, but it was empty string or null;
 * - null, if there is no old value at all for that key;
 *
 * This version is form-aware to prevent old values from bleeding across multiple forms.
 *
 * @param  string  $key
 * @param  array|string  $empty_value
 * @return mixed
 */    function old_empty_or_null($key, $empty_value = '')
    {
        $key = square_brackets_to_dots($key);
        $old_inputs = session()->getOldInput();

        // Check if we have a form ID in the old inputs to determine if this is form-specific
        $submittedFormId = data_get($old_inputs, '_form_id');

        if ($submittedFormId) {
            // Check if we're currently rendering a DataForm with a specific ID
            // Use Laravel's service container to get the current form context
            $currentFormId = app()->bound('backpack.current_form_id') ? app('backpack.current_form_id') : null;

            // If we can determine the current form ID and it doesn't match the submitted form ID,
            // don't return old values to prevent bleeding across forms
            if ($currentFormId && $currentFormId !== $submittedFormId) {
                return null;
            }
        }

        // if the input name is present in the old inputs we need to return earlier and not in a coalescing chain
        // otherwise `null` aka empty will not pass the condition and the field value would be returned.
        if (\Illuminate\Support\Arr::has($old_inputs, $key)) {
            return \Illuminate\Support\Arr::get($old_inputs, $key) ?? $empty_value;
        }

        return null;
    }
}

if (! function_exists('is_multidimensional_array')) {
    /**
     * Check if the array is multidimensional.
     *
     * If $strict is enabled, the array is considered multidimensional only if all elements of the array are arrays.
     */
    function is_multidimensional_array(array $array, bool $strict = false): bool
    {
        foreach ($array as $item) {
            if ($strict && ! is_array($item)) {
                return false;
            }
            if (! $strict && is_array($item)) {
                return true;
            }
        }

        return $strict;
    }
}

if (! function_exists('backpack_pro')) {
    /**
     * Check if the backpack/pro package is installed.
     *
     * @return bool
     */
    function backpack_pro()
    {
        if (app()->runningUnitTests()) {
            return true;
        }
        if (! \Composer\InstalledVersions::isInstalled('backpack/pro')) {
            return false;
        }

        return \Composer\InstalledVersions::getVersion('backpack/pro');
    }
}
