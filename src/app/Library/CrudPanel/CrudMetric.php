<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

use Backpack\CRUD\app\Library\CrudPanel\Metrics\MetricComparison;
use Backpack\CRUD\app\Library\CrudPanel\Metrics\PreviousPeriod;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class CrudMetric
{
    public string $name;

    public string $type;

    public string $label;

    public ?string $column = null;

    public string $aggregate = 'count';

    public string|false|null $period = null;

    public ?MetricComparison $compare = null;

    public ?string $format = null;

    public array $wrapper = [];

    public ?Closure $query = null;

    public ?Closure $resolve = null;

    public ?string $group = null;

    public array $extra = [];

    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->type = $config['type'] ?? 'stat';
        $this->label = $config['label'] ?? str_replace('_', ' ', mb_ucfirst($name));
        $this->column = $config['column'] ?? null;
        $this->aggregate = $config['aggregate'] ?? 'count';
        $this->period = $config['period'] ?? null;
        $compare = $config['compare'] ?? null;
        $this->compare = match (true) {
            $compare instanceof MetricComparison => $compare,
            $compare === true => new PreviousPeriod(),
            default => null,
        };
        $this->format = $config['format'] ?? null;
        $this->wrapper = $config['wrapper'] ?? [];
        $this->query = $config['query'] ?? null;
        $this->resolve = $config['resolve'] ?? null;
        $this->group = $config['group'] ?? null;

        // Store any extra keys the developer passes (for future metric types).
        $knownKeys = ['type', 'label', 'column', 'aggregate', 'period', 'compare', 'format', 'wrapper', 'query', 'resolve', 'group'];
        $this->extra = array_diff_key($config, array_flip($knownKeys));
    }

    /**
     * Get the default wrapper for this metric type from config.
     */
    public function getWrapper(): array
    {
        if (! empty($this->wrapper)) {
            return $this->wrapper;
        }

        $defaults = config('backpack.operations.report.defaultWrappers', []);

        return $defaults[$this->type] ?? ['class' => 'col-md-6'];
    }

    /**
     * Determine the effective period (date) column for this metric.
     *
     * Returns null when period-based filtering should be skipped — either
     * because the developer explicitly set `'period' => false`, or because
     * the default column is `created_at` and the model has timestamps disabled.
     */
    public function getPeriodColumn(?Builder $query = null): ?string
    {
        // Explicitly disabled by the developer.
        if ($this->period === false) {
            return null;
        }

        // Explicitly set by the developer.
        if ($this->period) {
            return $this->period;
        }

        // Fall back to the config default.
        $default = config('backpack.operations.report.defaultPeriodColumn', 'created_at');

        // When relying on the default 'created_at', skip filtering if the
        // model has timestamps disabled.
        if ($default === 'created_at' && $query && ! $query->getModel()->usesTimestamps()) {
            return null;
        }

        return $default;
    }

    /**
     * Resolve the metric data for the given query and filters.
     */
    public function resolve(Builder $query, array $filters): mixed
    {
        $originalModelClass = get_class($query->getModel());

        // Apply metric-level query modifier first.
        if ($this->query instanceof Closure) {
            $query = ($this->query)($query) ?? $query;
        }

        // If the query closure swapped the underlying model (e.g. via
        // setModel()), rebuild from the new model so that its global scopes
        // (such as SoftDeletes) are applied correctly and stale scopes from
        // the original model are removed.
        if (get_class($query->getModel()) !== $originalModelClass) {
            $freshQuery = $query->getModel()->newQuery();
            if ($this->query instanceof Closure) {
                $freshQuery = ($this->query)($freshQuery) ?? $freshQuery;
            }
            $query = $freshQuery;
        }

        // Apply date-range filtering after the query closure so that
        // model-swapping closures work correctly.
        $periodColumn = $this->getPeriodColumn($query);
        if ($periodColumn) {
            if (! empty($filters['date_from'])) {
                $query->where($periodColumn, '>=', $filters['date_from']);
            }
            if (! empty($filters['date_to'])) {
                $query->where($periodColumn, '<=', $filters['date_to']);
            }
        }

        // If the developer provided a custom resolve closure, use it.
        if ($this->resolve instanceof Closure) {
            return ($this->resolve)($query, $filters);
        }

        return match ($this->type) {
            'stat' => $this->resolveStat($query, $filters),
            'line', 'bar' => $this->resolveTimeSeries($query, $filters),
            default => [],
        };
    }

    /**
     * Resolve a stat metric — a single aggregate value with optional comparison.
     */
    protected function resolveStat(Builder $query, array $filters): array
    {
        $value = $this->runAggregate($query);

        $result = [
            'value' => $value,
            'previous' => null,
            'change' => null,
        ];

        if ($this->compare instanceof MetricComparison) {
            $result = array_merge($result, $this->compare->resolve($this, $query, $filters, $value));
        }

        // Apply format.
        if ($this->format) {
            $result['formatted'] = str_replace(':value', $result['value'], $this->format);
        }

        return $result;
    }

    /**
     * Resolve a time-series metric (line or bar chart).
     */
    protected function resolveTimeSeries(Builder $query, array $filters): array
    {
        $interval = $filters['interval'] ?? config('backpack.operations.report.defaultInterval', 'day');
        $period = $this->getPeriodColumn($query);

        if (! $period) {
            return ['labels' => [], 'data' => []];
        }

        $dateFormat = match ($interval) {
            'day' => '%Y-%m-%d',
            'week' => '%x-W%v',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };

        $selectAggregate = match ($this->aggregate) {
            'count' => 'COUNT(*)',
            'sum' => "SUM({$this->column})",
            'avg' => "AVG({$this->column})",
            'min' => "MIN({$this->column})",
            'max' => "MAX({$this->column})",
            default => 'COUNT(*)',
        };

        $rows = $query
            ->selectRaw("DATE_FORMAT({$period}, ?) as label, {$selectAggregate} as value", [$dateFormat])
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'labels' => $rows->pluck('label')->toArray(),
            'data' => $rows->pluck('value')->map(fn ($v) => (float) $v)->toArray(),
        ];
    }

    /**
     * Run the configured aggregate function on a query.
     */
    public function runAggregate(Builder $query): float
    {
        return (float) match ($this->aggregate) {
            'count' => $query->count(),
            'sum' => $query->sum($this->column),
            'avg' => $query->avg($this->column),
            'min' => $query->min($this->column),
            'max' => $query->max($this->column),
            default => $query->count(),
        };
    }

    /**
     * Convert to array (for Blade views).
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'label' => $this->label,
            'column' => $this->column,
            'aggregate' => $this->aggregate,
            'period' => $this->period,
            'compare' => $this->compare,
            'format' => $this->format,
            'wrapper' => $this->getWrapper(),
            'group' => $this->group,
        ];
    }
}
