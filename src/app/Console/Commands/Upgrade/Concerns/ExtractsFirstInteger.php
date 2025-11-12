<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\Concerns;

trait ExtractsFirstInteger
{
    /**
     * Extract the first integer occurrence from the given string.
     */
    protected function extractFirstInteger(string $value): ?int
    {
        if (preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
