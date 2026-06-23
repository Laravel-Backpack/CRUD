# Report Operation

## About

Adds a report/dashboard page to any CrudController with metrics (stats, charts) about the entity's data. Each metric fetches its data independently via AJAX.

Metric types: `stat`, `line`, `bar`, `stacked-bar`, `stacked-line`, `pie`, `table`, `view`, plus custom registered types.

## Requirements

- Backpack\CRUD ^7.0
- Backpack\PRO (for the `date_range` filter type)
- Chart.js (loaded automatically via `@basset`)

## Installation

```bash
composer require backpack/report-operation
```

Or from GitHub:

```bash
composer config repositories.report-operation vcs https://github.com/Laravel-Backpack/report-operation
composer require backpack/report-operation:dev-main
```

Publish config (optional):

```bash
php artisan vendor:publish --tag=backpack-report-config
```

## How to Use

Use the trait and define metrics in `setupReportOperation()`:

```php
use \Backpack\ReportOperation\Http\Controllers\Operations\ReportOperation;

protected function setupReportOperation()
{
    $this->addMetric('total_orders', [
        'type'      => 'stat',
        'label'     => 'Total Orders',
        'aggregate' => 'count',
        'period'    => 'created_at',
        'compare'   => true,
    ]);

    $this->addMetric('orders_over_time', [
        'type'      => 'line',
        'label'     => 'Orders Over Time',
        'aggregate' => 'count',
        'period'    => 'created_at',
    ]);
}
```

A **Report** button appears in the List operation top bar.

## How It Works

Routes:
- `GET  /{segment}/report` — renders the report page with metric placeholders
- `POST /{segment}/report/metric-data` — resolves one or more metrics, returns JSON

On page load, the JS reads metric widgets from the DOM and builds a load plan: ungrouped metrics fire individual requests, grouped metrics share a single request. Stat cards update with values and chart canvases render via Chart.js.

Two CRUD filters are auto-injected (before `setupReportOperation()`):
- `report_date_range` — `date_range` filter for time window
- `report_interval` — `dropdown` filter for chart grouping (Daily / Weekly / Monthly / Yearly)

Two sections:
- **Static** (`'section' => 'static'`) — above filters, loaded once, never reloaded, unfiltered dataset
- **Dynamic** (default) — below filters, re-fetch on filter change

## Metric Types

### Stat

Single-value card showing an aggregate number.

```php
$this->addMetric('total_products', [
    'type'        => 'stat',          // required
    'label'       => 'Total Products',
    'description' => 'All products currently in the catalogue.',
    'aggregate'   => 'count',         // count | sum | avg | min | max
    'column'      => 'price',         // required for sum, avg, min, max
    'period'      => 'created_at',    // date column for filtering & comparison
    'compare'     => true,            // show % change vs previous period
    'format'      => '$:value',       // format the displayed value
]);
```

### Line

Time-series line chart.

```php
$this->addMetric('revenue_over_time', [
    'type'        => 'line',
    'label'       => 'Revenue Over Time',
    'column'      => 'amount',
    'aggregate'   => 'sum',        // count | sum | avg | min | max
    'period'      => 'created_at', // date column for grouping & filtering
]);
```

### Bar

Same as `line` but renders as a bar chart. Use `'type' => 'bar'`.

### Stacked Bar

Time-series broken down by a category column. Each unique value in `stack_by` becomes a separate dataset.

```php
$this->addMetric('orders_by_status', [
    'type'      => 'stacked-bar',
    'label'     => 'Orders by Status',
    'aggregate' => 'count',
    'period'    => 'created_at',
    'stack_by'  => 'status',
    'colors'    => [                   // optional: label => color
        'pending'   => 'rgba(255, 206, 86, 0.8)',
        'completed' => 'rgba(0, 200, 83, 0.8)',
        'cancelled' => 'rgba(255, 99, 132, 0.8)',
    ],
]);
```

When `colors` is an associative array, its key order determines the series order. Unlisted values get default palette colors.

### Stacked Line

Same as `stacked-bar` but stacked area chart. Use `'type' => 'stacked-line'`.

```php
$this->addMetric('revenue_by_category', [
    'type'      => 'stacked-line',
    'label'     => 'Revenue by Category',
    'column'    => 'amount',
    'aggregate' => 'sum',
    'period'    => 'created_at',
    'stack_by'  => 'category',
]);
```

### Pie

Categorical breakdown as a pie chart. Groups rows by a column.

```php
// Count by status (aggregate defaults to 'count')
$this->addMetric('products_by_status', [
    'type'   => 'pie',
    'label'  => 'Products by Status',
    'column' => 'status',
]);

// Sum by category, with different group column
$this->addMetric('revenue_by_category', [
    'type'      => 'pie',
    'label'     => 'Revenue by Category',
    'column'    => 'price',
    'aggregate' => 'sum',
    'group_by'  => 'category_id',
]);
```

When `aggregate` is `count` (default), `column` is the grouping column. For `sum`/`avg`/`min`/`max`, use `group_by` for the grouping column and `column` for the value column.

### Table

Sorted, paginated data table with per-column aggregates.

```php
$this->addMetric('top_categories', [
    'type'     => 'table',
    'label'    => 'Top Categories',
    'group_by' => 'category_name',
    'columns'  => [
        'category_name' => 'Category',                                              // display column
        'total_orders'  => ['label' => 'Orders',  'aggregate' => 'count'],
        'revenue'       => ['label' => 'Revenue', 'aggregate' => 'sum', 'column' => 'price', 'format' => '$:value'],
    ],
    'order_by' => ['revenue', 'desc'],  // optional, defaults to first aggregate desc
    'limit'    => 5,                    // optional, defaults to 5
]);
```

Each `columns` entry is either a string (display-only, value from `group_by`) or an array with `label`, `aggregate`, optional `column`, optional `format`.

With a `resolve` closure, `group_by` is not needed:

```php
$this->addMetric('recent_orders', [
    'type'    => 'table',
    'label'   => 'Recent Orders',
    'columns' => [
        'id'       => '#',
        'customer' => 'Customer',
        'total'    => ['label' => 'Total', 'format' => '$:value'],
        'status'   => 'Status',
    ],
    'resolve' => function ($query, $filters) {
        $rows = $query->latest()->limit(5)->get(['id', 'customer', 'total', 'status']);
        return [
            'columns' => [
                ['key' => 'id', 'label' => '#'],
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'total', 'label' => 'Total'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'rows' => $rows->map(fn ($r) => [
                'id'       => $r->id,
                'customer' => $r->customer,
                'total'    => '$'.number_format($r->total, 2),
                'status'   => $r->status,
            ])->toArray(),
        ];
    },
]);
```

Defaults to `col-md-12` (full width). Override with `wrapper`.

### View

Custom Blade view rendered server-side, returned as HTML via AJAX.

```php
$this->addMetric('top_products', [
    'type'    => 'view',
    'label'   => 'Top Products',
    'view'    => 'admin.metrics.top_products',
    'wrapper' => ['class' => 'col-md-12'],
]);
```

Blade view receives:

| Variable   | Type     | Description |
|------------|----------|-------------|
| `$metric`  | `CrudMetric` | The metric instance |
| `$query`   | `Builder`    | Eloquent query, scoped by `query` closure and date-range filters |
| `$filters` | `array`      | Active filter values: `date_from`, `date_to`, `interval` |

Example view (`resources/views/admin/metrics/top_products.blade.php`):

```blade
@php
    $products = $query->clone()
        ->orderByDesc('price')
        ->limit(5)
        ->get(['name', 'price', 'status']);
@endphp

<table class="table table-sm table-striped mb-0">
    <thead>
        <tr>
            <th>Product</th>
            <th class="text-end">Price</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td class="text-end">${{ number_format($product->price, 2) }}</td>
                <td>{{ $product->status }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-secondary text-center">No data</td></tr>
        @endforelse
    </tbody>
</table>
```

HTML is injected via `innerHTML` — `<script>` tags are not executed. Use Alpine.js directives, inline event handlers, or the [JavaScript events](#javascript-events) system for interactivity.

## Examples

Drop-in `setupReportOperation()` using `created_at`, `updated_at`, and `deleted_at` timestamps:

```php
protected function setupReportOperation()
{
    // STATIC SECTION
    $this->addMetric('active_entries', [
        'type'    => 'stat',
        'label'   => 'Active Entries',
        'section' => 'static',
        'wrapper' => ['class' => 'col-md-3'],
    ]);

    $this->addMetric('all_time_created', [
        'type'    => 'stat',
        'label'   => 'All Time Created',
        'section' => 'static',
        'query'   => fn ($q) => $q->withTrashed(),
        'wrapper' => ['class' => 'col-md-3'],
    ]);

    $this->addMetric('all_time_updated', [
        'type'    => 'stat',
        'label'   => 'All Time Updated',
        'section' => 'static',
        'query'   => fn ($q) => $q->withTrashed()->whereColumn('updated_at', '!=', 'created_at'),
        'wrapper' => ['class' => 'col-md-3'],
    ]);

    $this->addMetric('all_time_deleted', [
        'type'    => 'stat',
        'label'   => 'All Time Deleted',
        'section' => 'static',
        'query'   => fn ($q) => $q->onlyTrashed(),
        'wrapper' => ['class' => 'col-md-3'],
    ]);

    // ROW 1 — Period stats
    $this->addMetricGroup(['class' => 'row'], function () {
        $this->addMetric('created', [
            'type'    => 'stat', 'label' => 'Created', 'period' => 'created_at',
            'compare' => true, 'wrapper' => ['class' => 'col-md-4'],
        ]);
        $this->addMetric('modified', [
            'type'    => 'stat', 'label' => 'Modified', 'period' => 'updated_at',
            'compare' => true, 'wrapper' => ['class' => 'col-md-4'],
            'query'   => fn ($q) => $q->whereColumn('updated_at', '!=', 'created_at'),
        ]);
        $this->addMetric('deleted', [
            'type'    => 'stat', 'label' => 'Deleted', 'period' => 'deleted_at',
            'compare' => true, 'wrapper' => ['class' => 'col-md-4'],
            'query'   => fn ($q) => $q->onlyTrashed(),
        ]);
    });

    // ROW 2 — Trends
    $this->addMetricGroup(['class' => 'row mt-2'], function () {
        $this->addMetric('creations_over_time', [
            'type' => 'line', 'label' => 'New Records Over Time',
            'period' => 'created_at', 'wrapper' => ['class' => 'col-md-4'],
        ]);
        $this->addMetric('updates_over_time', [
            'type' => 'bar', 'label' => 'Edits Over Time',
            'period' => 'updated_at', 'wrapper' => ['class' => 'col-md-4'],
            'query' => fn ($q) => $q->whereColumn('updated_at', '!=', 'created_at'),
        ]);
        $this->addMetric('deletions_over_time', [
            'type' => 'bar', 'label' => 'Deletions Over Time',
            'period' => 'deleted_at', 'wrapper' => ['class' => 'col-md-4'],
            'query' => fn ($q) => $q->onlyTrashed(),
        ]);
    });
}
```

## Metric Options Reference

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `type` | `string` | `'stat'` | `stat`, `line`, `bar`, `stacked-bar`, `stacked-line`, `pie`, `table`, `view` (or custom registered type) |
| `label` | `string` | Auto from name | Display label |
| `description` | `string\|null` | `null` | Text below the value (stat) or chart title |
| `column` | `string\|null` | `null` | DB column for `sum`/`avg`/`min`/`max` aggregates |
| `aggregate` | `string` | `'count'` | `count`, `sum`, `avg`, `min`, `max` |
| `period` | `string\|null` | Config default | Date column for filtering and chart grouping |
| `compare` | `bool\|MetricComparison\|null` | `null` | `true` for previous-period comparison, or `MetricComparison` instance |
| `format` | `string\|null` | `null` | Format string for `stat`. `:value` is replaced with the value (e.g. `'$:value'`) |
| `wrapper` | `array` | From config | HTML attributes for the wrapper `<div>` (e.g. `['class' => 'col-md-4']`) |
| `query` | `Closure\|null` | `null` | Modify the query before aggregation. Receives `$query`, returns `$query` |
| `resolve` | `Closure\|null` | `null` | Full custom resolution. Receives `($query, $filters)`, returns an array |
| `group` | `string\|null` | `null` | Group name for batching AJAX requests |
| `group_by` | `string\|null` | `null` | *(Pie/Table)* Column to group by |
| `stack_by` | `string\|null` | `null` | *(Stacked)* Column whose values become separate datasets |
| `colors` | `array\|null` | `null` | *(Pie/Stacked)* Associative array mapping labels to colors; key order = series order |
| `columns` | `array\|null` | `null` | *(Table)* Column definitions: string (display) or array with `label`, `aggregate`, `column`, `format` |
| `order_by` | `array\|null` | First agg. desc | *(Table)* `['column_key', 'asc'\|'desc']` |
| `limit` | `int` | `5` | *(Table)* Max rows |
| `view` | `string\|null` | `null` | *(View)* Blade view name (e.g. `'admin.metrics.top_products'`) |
| `section` | `string` | `'dynamic'` | `'static'` (above filters, never reloads) or `'dynamic'` (below filters) |
| `refreshInterval` | `int\|false` | `false` | Auto-refresh interval in seconds (e.g. `60`) |

## Static Metrics

| Section | Position | Behavior |
|---------|----------|----------|
| Static | Above filters | Loaded once. Never reloaded. No date-range constraints. |
| Dynamic *(default)* | Below filters | Re-fetches on filter change. Date-range constraints applied. |

```php
$this->addMetric('total_customers_ever', [
    'type'    => 'stat',
    'label'   => 'Total Customers (All Time)',
    'section' => 'static',
]);

$this->addMetric('new_customers', [
    'type'    => 'stat',
    'label'   => 'New Customers',
    'compare' => true,  // dynamic by default
]);
```

When using `addMetricGroup()`, static and dynamic metrics are split into their respective sections automatically.

## Auto-Refresh (Polling)

```php
$this->addMetric('active_users', [
    'type'            => 'stat',
    'label'           => 'Active Users',
    'aggregate'       => 'count',
    'query'           => fn ($q) => $q->where('last_seen_at', '>=', now()->subMinutes(5)),
    'refreshInterval' => 30, // re-fetch every 30 seconds
]);
```

Behavior:
- Disabled by default (`false`). Set to seconds (e.g. `60`) to enable.
- Grouped metrics use the lowest interval among them.
- In-flight requests are skipped if a polling tick fires while a previous request is still pending.
- Polling pauses when browser tab is hidden, resumes on focus.
- Filter changes reset the timer and trigger immediate re-fetch.

Static metrics can also poll without filter parameters:

```php
$this->addMetric('server_uptime', [
    'type'            => 'stat',
    'label'           => 'Server Uptime',
    'section'         => 'static',
    'refreshInterval' => 10,
    'resolve'         => function ($query, $filters) {
        return ['value' => getServerUptime(), 'formatted' => ':value%'];
    },
]);
```

## Customizing Queries

### Scoping with `query`

Add conditions to the base query before aggregation:

```php
$this->addMetric('active_users', [
    'type'      => 'stat',
    'label'     => 'Active Users',
    'aggregate' => 'count',
    'query'     => fn ($query) => $query->where('status', 'active'),
]);
```

### Full Control with `resolve`

The closure receives the query (scoped by date range if `period` is set) and the filters array:

```php
$this->addMetric('unique_categories', [
    'type'    => 'stat',
    'label'   => 'Unique Categories',
    'resolve' => function ($query, $filters) {
        return ['value' => $query->distinct('category_id')->count('category_id')];
    },
]);
```

For chart metrics, return `['labels' => [...], 'data' => [...]]` (or `'datasets'` for stacked):

```php
$this->addMetric('custom_chart', [
    'type'    => 'line',
    'label'   => 'Custom Chart',
    'resolve' => function ($query, $filters) {
        $rows = $query->selectRaw('MONTH(created_at) as m, COUNT(*) as c')
                      ->groupBy('m')->orderBy('m')->get();
        return [
            'labels' => $rows->pluck('m')->toArray(),
            'data'   => $rows->pluck('c')->toArray(),
        ];
    },
]);
```

## Custom Comparisons

The `compare` option accepts `true` (previous-period comparison) or any class implementing `MetricComparison`.

### MetricComparison Interface

```php
use Backpack\ReportOperation\CrudMetric;
use Illuminate\Database\Eloquent\Builder;

interface MetricComparison
{
    public function resolve(CrudMetric $metric, Builder $query, array $filters, float $currentValue): array;
}
```

Must return:

```php
return [
    'previous' => 42.0,   // comparison value
    'change'   => 15.3,   // percentage change
];
```

### Built-in: PreviousPeriod

`'compare' => true` is equivalent to `new PreviousPeriod()`, which computes the same aggregate for a period of equal duration immediately before the selected date range.

### Custom Comparison Example

Compare against the same date range in the previous year:

```php
namespace App\Metrics;

use Backpack\ReportOperation\CrudMetric;
use Backpack\ReportOperation\MetricComparison;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class PreviousYear implements MetricComparison
{
    public function resolve(CrudMetric $metric, Builder $query, array $filters, float $currentValue): array
    {
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;
        $periodColumn = $metric->getPeriodColumn($query);

        if (! $from || ! $to || ! $periodColumn) {
            return ['previous' => null, 'change' => null];
        }

        $previousFrom = date('Y-m-d', strtotime($from . ' -1 year'));
        $previousTo = date('Y-m-d', strtotime($to . ' -1 year'));

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

        return ['previous' => $previous, 'change' => $change];
    }
}
```

Usage:

```php
use App\Metrics\PreviousYear;

$this->addMetric('total_orders', [
    'type'    => 'stat',
    'label'   => 'Total Orders',
    'compare' => new PreviousYear(),
]);
```

## Custom Metric Types

Both PHP and JS use a registry pattern — you can add new metric types without modifying core files.

**1. PHP resolver** — implement `MetricType`:

```php
namespace App\Metrics;

use Backpack\ReportOperation\CrudMetric;
use Backpack\ReportOperation\MetricType;
use Illuminate\Database\Eloquent\Builder;

class FunnelType implements MetricType
{
    public function resolve(CrudMetric $metric, Builder $query, array $filters): mixed
    {
        return [
            'stages' => ['Visited', 'Signed Up', 'Purchased'],
            'values' => [1000, 350, 80],
        ];
    }
}
```

**2. Register the PHP type:**

```php
use Backpack\ReportOperation\CrudMetric;
use App\Metrics\FunnelType;

CrudMetric::registerType('funnel', FunnelType::class);
```

**3. Blade view** — `resources/views/vendor/backpack-report/metrics/funnel.blade.php`:

```blade
<div class="card" data-metric="{{ $metric->name }}" data-metric-type="{{ $metric->type }}">
    <div class="card-body">
        <div data-metric-placeholder>Loading...</div>
        <div data-metric-content style="display:none;"></div>
    </div>
</div>

@pushOnce('after_scripts')
<script>
BackpackReportMetrics.register('funnel', function(widget, data) {
    var placeholder = widget.querySelector('[data-metric-placeholder]');
    var content = widget.querySelector('[data-metric-content]');
    if (placeholder) placeholder.style.display = 'none';
    content.style.display = '';
    // Render custom visualization with `data`
});
</script>
@endPushOnce
```

**4. Use it:**

```php
$this->addMetric('purchase_funnel', [
    'type'  => 'funnel',
    'label' => 'Purchase Funnel',
]);
```

You can even override built-in types: `CrudMetric::registerType('stat', YourCustomStatType::class)`.

## Grouping Metrics (AJAX Batching)

Batch metrics into a single AJAX request:

```php
$this->addMetric('total_orders', [
    'type' => 'stat',
    'aggregate' => 'count',
]);

$this->addMetric('avg_order_value', [
    'type' => 'stat',
    'column' => 'total',
    'aggregate' => 'avg',
    'format' => '$:value',
]);

$this->groupMetrics('order_stats', ['total_orders', 'avg_order_value']);
```

Each metric still runs its own query — grouping only affects the AJAX transport.

## Metric Groups (Visual Layout)

Use `addMetricGroup()` to organize metrics into separate visual containers:

```php
protected function setupReportOperation()
{
    $this->addMetricGroup(['class' => 'row'], function () {
        $this->addMetric('total_orders', [
            'type' => 'stat', 'label' => 'Total Orders',
            'aggregate' => 'count', 'compare' => true,
            'wrapper' => ['class' => 'col-md-3'],
        ]);
        $this->addMetric('total_revenue', [
            'type' => 'stat', 'label' => 'Total Revenue',
            'column' => 'total', 'aggregate' => 'sum',
            'format' => '$:value', 'compare' => true,
            'wrapper' => ['class' => 'col-md-3'],
        ]);
    });

    $this->addMetricGroup(['class' => 'row mt-4'], function () {
        $this->addMetric('orders_over_time', [
            'type' => 'line', 'label' => 'Orders Over Time',
            'aggregate' => 'count', 'period' => 'created_at',
        ]);
        $this->addMetric('orders_by_status', [
            'type' => 'pie', 'label' => 'Orders by Status',
            'column' => 'status',
        ]);
    });
}
```

### Group Attributes

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `element` | `string` | `'div'` | HTML tag for the container |
| `class` | `string` | — | CSS classes |
| `id` | `string` | — | HTML id attribute |
| *any* | `string` | — | Any valid HTML attribute |

Metrics outside `addMetricGroup()` are auto-wrapped in `<div class="row">`.

## Wrapper / Layout

Control each metric's wrapper `<div>` attributes via the `wrapper` option:

```php
$this->addMetric('big_chart', [
    'type'    => 'line',
    'label'   => 'Revenue',
    'wrapper' => ['class' => 'col-md-12'],
]);

$this->addMetric('small_stat', [
    'type'    => 'stat',
    'label'   => 'Users',
    'wrapper' => ['class' => 'col-md-3', 'style' => 'min-height: 120px;'],
]);
```

Default wrappers per type are defined in the config file.

## Filters

Auto-injected filters (added before `setupReportOperation()`):
- `report_date_range` — `date_range` filter
- `report_interval` — `dropdown` filter (Daily, Weekly, Monthly, Yearly)

Remove or override them:

```php
protected function setupReportOperation()
{
    $this->crud->removeFilter('report_interval');

    $this->crud->removeFilter('report_date_range');
    $this->crud->addFilter([
        'name' => 'report_date_range',
        'type' => 'date_range',
        'label' => 'Period',
    ], false, function ($value) {
        // custom logic
    });

    // ... add metrics
}
```

## Metrics API

Methods available via the `ReportOperation` trait:

| Method | Description |
|--------|-------------|
| `$this->addMetric(string $name, array $config)` | Add a metric |
| `$this->removeMetric(string $name)` | Remove a metric by name |
| `$this->metric(string $name)` | Get a single `CrudMetric` instance |
| `$this->metrics()` | Get all metrics as flat associative array |
| `$this->metricGroups()` | Get all metric groups for rendering |
| `$this->modifyMetric(string $name, array $config)` | Update properties of an existing metric |
| `$this->addMetricGroup(array $attributes, Closure $callback)` | Add a visual metric group |
| `$this->groupMetrics(string $groupName, array $metricNames)` | Batch metrics into a single AJAX request |

```php
$this->modifyMetric('total_orders', [
    'label' => 'All Orders',
    'wrapper' => ['class' => 'col-md-6'],
]);

$this->removeMetric('avg_order_value');
```

## Configuration

Publish:

```bash
php artisan vendor:publish --tag=backpack-report-config
```

Config file `config/backpack/operations/report.php`:

```php
return [
    'contentClass' => 'col-md-12',

    'defaultPeriodColumn' => 'created_at',

    'defaultInterval' => 'day',   // day | week | month | year

    'defaultWrappers' => [
        'stat'         => ['class' => 'col-md-3'],
        'line'         => ['class' => 'col-md-6'],
        'bar'          => ['class' => 'col-md-6'],
        'stacked-bar'  => ['class' => 'col-md-6'],
        'stacked-line' => ['class' => 'col-md-6'],
        'pie'          => ['class' => 'col-md-6'],
        'view'         => ['class' => 'col-md-12'],
        'table'        => ['class' => 'col-md-12'],
    ],
];
```

Override per-controller:

```php
$this->crud->setOperationSetting('contentClass', 'col-md-10 mx-auto');
```

## Overriding Views

Create override files in `resources/views/vendor/backpack-report/`:

| View | Purpose |
|------|---------|
| `report.blade.php` | Main report page layout |
| `metrics/stat.blade.php` | Stat card template |
| `metrics/line.blade.php` | Line chart (delegates to `chart.blade.php`) |
| `metrics/bar.blade.php` | Bar chart (delegates to `chart.blade.php`) |
| `metrics/pie.blade.php` | Pie chart template |
| `metrics/stacked-bar.blade.php` | Stacked bar (delegates to `stacked-chart.blade.php`) |
| `metrics/stacked-line.blade.php` | Stacked line (delegates to `stacked-chart.blade.php`) |
| `metrics/stacked-chart.blade.php` | Shared stacked chart container |
| `metrics/chart.blade.php` | Shared chart container (canvas + Chart.js) |
| `metrics/view.blade.php` | View metric card |
| `metrics/inc/report_scripts.blade.php` | JS for AJAX fetching and metric dispatch |
| `buttons/report.blade.php` | Report button in List operation |

## Full Example

```php
<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\ReportOperation\Http\Controllers\Operations\ReportOperation;

class OrderCrudController extends CrudController
{
    use ListOperation;
    use ReportOperation;

    protected function setupReportOperation()
    {
        // Stat cards
        $this->addMetricGroup(['class' => 'row'], function () {
            $this->addMetric('total_orders', [
                'type' => 'stat', 'label' => 'Total Orders',
                'aggregate' => 'count', 'period' => 'created_at',
                'compare' => true, 'wrapper' => ['class' => 'col-md-4'],
            ]);
            $this->addMetric('total_revenue', [
                'type' => 'stat', 'label' => 'Total Revenue',
                'column' => 'total', 'aggregate' => 'sum',
                'format' => '$:value', 'period' => 'created_at',
                'compare' => true, 'wrapper' => ['class' => 'col-md-4'],
            ]);
            $this->addMetric('avg_order', [
                'type' => 'stat', 'label' => 'Avg Order Value',
                'column' => 'total', 'aggregate' => 'avg',
                'format' => '$:value', 'wrapper' => ['class' => 'col-md-4'],
            ]);
        });

        // Charts
        $this->addMetricGroup(['class' => 'row mt-2'], function () {
            $this->addMetric('orders_over_time', [
                'type' => 'line', 'label' => 'Orders Over Time',
                'aggregate' => 'count', 'period' => 'created_at',
            ]);
            $this->addMetric('revenue_over_time', [
                'type' => 'bar', 'label' => 'Revenue Over Time',
                'column' => 'total', 'aggregate' => 'sum',
                'period' => 'created_at',
            ]);
        });

        // Pie chart
        $this->addMetricGroup(['class' => 'row mt-2'], function () {
            $this->addMetric('orders_by_status', [
                'type' => 'pie', 'label' => 'Orders by Status',
                'column' => 'status',
            ]);
        });

        // Stacked bar
        $this->addMetricGroup(['class' => 'row mt-2'], function () {
            $this->addMetric('orders_stacked_by_status', [
                'type' => 'stacked-bar', 'label' => 'Orders by Status Over Time',
                'aggregate' => 'count', 'period' => 'created_at',
                'stack_by' => 'status',
                'colors' => [
                    'pending' => 'rgba(255, 206, 86, 0.8)',
                    'completed' => 'rgba(0, 200, 83, 0.8)',
                    'cancelled' => 'rgba(255, 99, 132, 0.8)',
                ],
            ]);
        });

        // Batch stat cards into one AJAX request
        $this->groupMetrics('stats', ['total_orders', 'total_revenue', 'avg_order']);
    }
}
```

## JavaScript Events

DOM events dispatched on every metric refresh:

| Event | Target | Detail |
|-------|--------|--------|
| `backpack:metric:updated` | Widget element (bubbles) | `{ name, type, data, widget }` |
| `backpack:metric:updated:{name}` | `document` | `{ name, type, data, widget }` |
| `backpack:metric:error` | Widget element (bubbles) | `{ name }` |
| `backpack:metric:error:{name}` | `document` | `{ name }` |

Listen to a specific metric:

```js
document.addEventListener('backpack:metric:updated:total_orders', function(e) {
    console.log('Total orders:', e.detail.data.value);
});
```

Listen to all metrics from a parent container:

```js
document.getElementById('report-metrics').addEventListener('backpack:metric:updated', function(e) {
    console.log(e.detail.name, 'refreshed with', e.detail.data);
});
```

Error handling:

```js
document.addEventListener('backpack:metric:error:total_orders', function(e) {
    console.warn('Failed to load total_orders metric');
});
```

Events fire for all metric types (stat, line, bar, pie, view, and custom types). This is the recommended way to add JS behavior to `view` metrics, since `<script>` tags inside view metric HTML are not executed.

## Troubleshooting

**Chart spinner stays visible / chart doesn't render:**
Clear basset cache: `php artisan basset:clear`, then hard-refresh. Check browser console for JS errors (`Chart is not defined` means Chart.js CDN failed to load — check network/CSP).

**Stat shows "—":**
The AJAX request failed. Check the Network tab for errors on the `metric-data` POST request. Common causes: invalid `column` name, missing `period` column, query error.

**Date range filter has no effect:**
Ensure `period` is set on the metric and points to a valid date/datetime column. Metrics without `period` are not filtered by date range.

**Previous period comparison always shows 100%:**
Requires both `period` and `compare => true` on a `stat` metric, plus a selected date range.

**`DATE_FORMAT` errors on SQLite or PostgreSQL:**
Default time-series resolution uses MySQL's `DATE_FORMAT()`. For other databases, use a custom `resolve` closure with appropriate date functions.

**Filters not showing:**
Auto-injected filters are added during the `report:before_setup` lifecycle hook. Ensure you haven't called `removeAllFilters()` or removed them in `setupReportOperation()`.
