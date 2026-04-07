<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Library\CrudPanel\CrudMetric;
use Backpack\CRUD\app\Library\CrudPanel\Hooks\Facades\LifecycleHook;
use Illuminate\Support\Facades\Route;

trait ReportOperation
{
    /**
     * Define which routes are needed for this operation.
     */
    protected function setupReportRoutes(string $segment, string $routeName, string $controller): void
    {
        Route::get($segment.'/report', [
            'as' => $routeName.'.report',
            'uses' => $controller.'@report',
            'operation' => 'report',
        ]);

        // Per-metric data endpoint. Accepts ?metrics=name1,name2 to resolve a batch (group).
        Route::post($segment.'/report/metric-data', [
            'as' => $routeName.'.reportMetricData',
            'uses' => $controller.'@reportMetricData',
            'operation' => 'report',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupReportDefaults(): void
    {
        $this->crud->allowAccess('report');

        LifecycleHook::hookInto('report:before_setup', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
            $this->setupReportFilters();
        });

        // Add "Report" button to the list operation top bar.
        LifecycleHook::hookInto('list:before_setup', function () {
            $this->crud->addButton('top', 'report', 'view', 'crud::buttons.report', 'end');
        });
    }

    /**
     * Auto-inject the date range and interval filters for the report operation.
     * These are standard CRUD filters, so they render via the shared filters_navbar include.
     * Developers can remove or override them in setupReportOperation().
     */
    protected function setupReportFilters(): void
    {
        // Date range filter — only if not already defined by the developer.
        if (! $this->crud->getFilter('report_date_range')) {
            $this->crud->addFilter(
                [
                    'name' => 'report_date_range',
                    'type' => 'date_range',
                    'label' => 'Date Range',
                ],
                false,
                function ($value) {
                    // The filter logic is applied in reportMetricData(), not here.
                }
            );
        }

        // Interval dropdown filter — only if not already defined.
        if (! $this->crud->getFilter('report_interval')) {
            $this->crud->addFilter(
                [
                    'name' => 'report_interval',
                    'type' => 'dropdown',
                    'label' => 'Interval',
                ],
                [
                    'day' => 'Daily',
                    'week' => 'Weekly',
                    'month' => 'Monthly',
                    'year' => 'Yearly',
                ],
                function ($value) {
                    // Same as above — value is read by JS and sent to the AJAX endpoint.
                }
            );
        }
    }

    // -----------------------
    // Metric Management
    // -----------------------

    /**
     * Add a metric to the report operation.
     */
    public function addMetric(string $name, array $config): self
    {
        $metrics = $this->crud->getOperationSetting('metrics') ?? [];
        $metrics[$name] = new CrudMetric($name, $config);
        $this->crud->setOperationSetting('metrics', $metrics);

        return $this;
    }

    /**
     * Remove a metric by name.
     */
    public function removeMetric(string $name): self
    {
        $metrics = $this->crud->getOperationSetting('metrics') ?? [];
        unset($metrics[$name]);
        $this->crud->setOperationSetting('metrics', $metrics);

        return $this;
    }

    /**
     * Get a single metric by name.
     */
    public function metric(string $name): ?CrudMetric
    {
        $metrics = $this->crud->getOperationSetting('metrics') ?? [];

        return $metrics[$name] ?? null;
    }

    /**
     * Get all registered metrics.
     *
     * @return CrudMetric[]
     */
    public function metrics(): array
    {
        return $this->crud->getOperationSetting('metrics') ?? [];
    }

    /**
     * Modify an existing metric's config.
     */
    public function modifyMetric(string $name, array $config): self
    {
        $metric = $this->metric($name);

        if ($metric) {
            foreach ($config as $key => $value) {
                if (property_exists($metric, $key)) {
                    $metric->{$key} = $value;
                }
            }
            $metrics = $this->crud->getOperationSetting('metrics') ?? [];
            $metrics[$name] = $metric;
            $this->crud->setOperationSetting('metrics', $metrics);
        }

        return $this;
    }

    /**
     * Group metrics so they share a single AJAX request.
     *
     * Usage:
     *   $this->groupMetrics('user_stats', ['total_users', 'new_users']);
     *
     * When grouped, the reportMetricData endpoint resolves all metrics
     * in the group in a single request and returns them together.
     */
    public function groupMetrics(string $groupName, array $metricNames): self
    {
        foreach ($metricNames as $name) {
            $metric = $this->metric($name);
            if ($metric) {
                $metric->group = $groupName;
                $metrics = $this->crud->getOperationSetting('metrics') ?? [];
                $metrics[$name] = $metric;
                $this->crud->setOperationSetting('metrics', $metrics);
            }
        }

        return $this;
    }

    // -----------------------
    // Report Actions
    // -----------------------

    /**
     * Display the report page.
     */
    public function report()
    {
        $this->crud->hasAccessOrFail('report');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural).' Report';
        $this->data['metrics'] = $this->metrics();

        return view($this->crud->get('report.view') ?? 'crud::report', $this->data);
    }

    /**
     * Resolve metric data via AJAX.
     *
     * Accepts POST with:
     *   - metrics: comma-separated metric names (or a single name)
     *   - report_date_range: JSON-encoded {from, to} from the date_range filter
     *   - report_interval: day|week|month|year
     *   - any other filter values
     */
    public function reportMetricData()
    {
        $this->crud->hasAccessOrFail('report');

        $requestedNames = array_filter(explode(',', request()->input('metrics', '')));
        $allMetrics = $this->metrics();

        if (empty($requestedNames)) {
            return response()->json([]);
        }

        // Parse the date range from the CRUD date_range filter format.
        $dateFrom = null;
        $dateTo = null;
        $dateRangeValue = request()->input('report_date_range');
        if ($dateRangeValue) {
            $dates = json_decode($dateRangeValue, true);
            $dateFrom = $dates['from'] ?? null;
            $dateTo = $dates['to'] ?? null;
        }

        $interval = request()->input('report_interval')
            ?: config('backpack.operations.report.defaultInterval', 'day');

        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'interval' => $interval,
        ];

        $results = [];

        foreach ($requestedNames as $name) {
            $metric = $allMetrics[$name] ?? null;
            if (! $metric) {
                continue;
            }

            // Build a fresh query for each metric. Date-range filtering is
            // applied inside CrudMetric::resolve() — after the metric's query
            // closure runs — so that model-swapping closures work correctly.
            $query = $this->crud->model->newQuery();

            $results[$name] = $metric->resolve($query, $filters);
        }

        return response()->json($results);
    }
}
