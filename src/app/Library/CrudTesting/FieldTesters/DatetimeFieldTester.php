<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Tester for datetime picker fields.
 */
class DatetimeFieldTester extends DateFieldTester
{
    /**
     * {@inheritdoc}
     */
    protected function getDateFormat(): string
    {
        return $this->field['datetime_format'] ?? 'Y-m-d H:i:s';
    }
}
