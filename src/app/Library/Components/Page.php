<?php

namespace Backpack\CRUD\app\Library\Components;

/**
 * This is a test class.
 */
class Page
{
    public static function button(string|array $name)
    {
    }

    /**
     * Undocumented function.
     *
     * @param  string|array  $input
     * @return Field
     */
    public static function field(string|array $input)
    {
        return new BaseField($input);
    }
}
