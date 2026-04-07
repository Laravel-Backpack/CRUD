<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Metrics;

use Backpack\CRUD\app\Library\CrudPanel\CrudMetric;
use Illuminate\Database\Eloquent\Builder;

interface MetricComparison
{
    /**
     * Compute comparison data for a stat metric.
     *
     * @return array{previous: float|null, change: float|null}
     */
    public function resolve(CrudMetric $metric, Builder $query, array $filters, float $currentValue): array;
}
