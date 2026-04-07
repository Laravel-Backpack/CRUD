<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Metrics;

use Backpack\CRUD\app\Library\CrudPanel\CrudMetric;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class PreviousPeriod implements MetricComparison
{
    /**
     * Compare the current value against the same metric in the previous period
     * of equal duration. E.g. if the selected range is Jan 10–20 (10 days),
     * the previous period is Dec 31–Jan 9.
     */
    public function resolve(CrudMetric $metric, Builder $query, array $filters, float $currentValue): array
    {
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;
        $periodColumn = $metric->getPeriodColumn($query);

        if (! $from || ! $to || ! $periodColumn) {
            return ['previous' => null, 'change' => null];
        }

        $diff = strtotime($to) - strtotime($from);
        $previousFrom = date('Y-m-d', strtotime($from) - $diff - 86400);
        $previousTo = date('Y-m-d', strtotime($from) - 86400);

        // Build a fresh query for the previous period from the (possibly
        // swapped) model so global scopes are correct.
        $previousQuery = $query->getModel()->newQuery();
        if ($metric->query instanceof Closure) {
            $previousQuery = ($metric->query)($previousQuery) ?? $previousQuery;
        }

        $previousQuery->where($periodColumn, '>=', $previousFrom)
                       ->where($periodColumn, '<=', $previousTo);

        $previous = $metric->runAggregate($previousQuery);

        $change = $previous != 0
            ? round((($currentValue - $previous) / abs($previous)) * 100, 1)
            : ($currentValue != 0 ? 100.0 : 0.0);

        return [
            'previous' => $previous,
            'change' => $change,
        ];
    }
}
