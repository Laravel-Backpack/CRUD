<?php

namespace Backpack\CRUD\app\Library\Attributes;

use Attribute;

/**
 * There is a new behavior defined for this method. We use this attribute to provide backwards compatibility
 * We plan to remove this function in a future version.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class DeprecatedIgnoreOnRuntime
{
    public function __construct(protected string $reason = '') {}
}
