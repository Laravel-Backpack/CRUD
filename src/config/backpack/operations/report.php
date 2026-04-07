<?php

/**
 * Configurations for Backpack's ReportOperation.
 *
 * These defaults apply to ALL CrudControllers that use ReportOperation.
 * Override per-controller via $this->crud->setOperationSetting('key', 'value')
 * inside setupReportOperation().
 */

return [
    // The CSS class for the report content container.
    'contentClass' => 'col-md-12',

    // Default date column to use for time-series metrics when no 'period' is specified.
    'defaultPeriodColumn' => 'created_at',

    // Default time-series interval: day | week | month | year
    'defaultInterval' => 'day',

    // Default wrapper classes per metric type (controls the grid column width).
    'defaultWrappers' => [
        'stat' => ['class' => 'col-md-3'],
        'line' => ['class' => 'col-md-6'],
        'bar'  => ['class' => 'col-md-6'],
        'pie'  => ['class' => 'col-md-6'],
        'table' => ['class' => 'col-md-12'],
    ],

    // The chart library adapter to use. Only 'chartjs' is supported for now.
    'chartLibrary' => 'chartjs',
];
