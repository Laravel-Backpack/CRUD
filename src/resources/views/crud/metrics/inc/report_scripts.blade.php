@basset('https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js')

@bassetBlock('backpack/crud/report/report-scripts.js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('report-metrics');
    if (!container) return;

    var dataUrl = container.dataset.reportUrl;
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    var chartInstances = {};

    function getFilterParams() {
        // Read filter state from the navbar's stored params rather than the URL.
        // This supports pages with multiple filter navbars where the URL is not updated.
        var section = container.closest('[bp-section]');
        var navbar = section
            ? section.querySelector('.navbar-filters')
            : document.querySelector('.navbar-filters');

        if (navbar && navbar.hasAttribute('data-filter-params')) {
            return new URLSearchParams(navbar.getAttribute('data-filter-params'));
        }

        return new URLSearchParams(window.location.search);
    }

    function fetchMetrics(metricNames) {
        var params = getFilterParams();
        params.set('metrics', metricNames.join(','));

        return fetch(dataUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: params.toString(),
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Report data request failed');
            return response.json();
        })
        .then(function(data) {
            Object.keys(data).forEach(function(name) {
                updateMetricWidget(name, data[name]);
            });
        })
        .catch(function(error) {
            console.error('Report metric error:', error);
            metricNames.forEach(function(name) { showMetricError(name); });
        });
    }

    function updateMetricWidget(name, data) {
        var widget = container.querySelector('[data-metric="' + name + '"]');
        if (!widget) return;

        var type = widget.dataset.metricType;

        if (type === 'stat') {
            updateStatWidget(widget, data);
        } else if (type === 'line' || type === 'bar') {
            updateChartWidget(widget, name, type, data);
        }
    }

    function updateStatWidget(widget, data) {
        var valueEl = widget.querySelector('[data-metric-value]');
        var changeEl = widget.querySelector('[data-metric-change]');

        if (valueEl) {
            valueEl.textContent = data.formatted ?? data.value;
        }

        if (changeEl && data.change !== null && data.change !== undefined) {
            var isPositive = data.change >= 0;
            var arrow = isPositive ? 'la-arrow-up' : 'la-arrow-down';
            var color = isPositive ? 'text-success' : 'text-danger';
            changeEl.innerHTML = '<span class="' + color + ' fw-semibold small"><i class="la ' + arrow + '"></i> ' + Math.abs(data.change) + '%</span>';
        }
    }

    function updateChartWidget(widget, name, type, data) {
        var placeholder = widget.querySelector('[data-metric-placeholder]');
        var canvas = widget.querySelector('[data-metric-canvas]');

        if (!canvas) return;

        if (placeholder) placeholder.classList.add('d-none');
        canvas.classList.remove('d-none');
        canvas.style.display = '';

        if (chartInstances[name]) {
            chartInstances[name].data.labels = data.labels;
            chartInstances[name].data.datasets[0].data = data.data;
            chartInstances[name].update();
            return;
        }

        if (typeof Chart === 'undefined') {
            setTimeout(function() { updateChartWidget(widget, name, type, data); }, 200);
            return;
        }

        var ctx = canvas.getContext('2d');
        var chartLabel = widget.querySelector('.card-title')?.textContent || name;

        chartInstances[name] = new Chart(ctx, {
            type: type === 'bar' ? 'bar' : 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: chartLabel,
                    data: data.data,
                    borderColor: 'rgb(32, 107, 196)',
                    backgroundColor: type === 'bar' ? 'rgba(32, 107, 196, 0.5)' : 'rgba(32, 107, 196, 0.1)',
                    borderWidth: 2,
                    fill: type === 'line',
                    tension: 0.3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });
    }

    function showMetricError(name) {
        var widget = container.querySelector('[data-metric="' + name + '"]');
        if (!widget) return;

        var type = widget.dataset.metricType;
        if (type === 'stat') {
            var valueEl = widget.querySelector('[data-metric-value]');
            if (valueEl) valueEl.innerHTML = '<span class="text-danger">&mdash;</span>';
        } else {
            var placeholder = widget.querySelector('[data-metric-placeholder]');
            if (placeholder) placeholder.innerHTML = '<span class="text-danger small">Failed to load</span>';
        }
    }

    function buildLoadPlan() {
        var widgets = container.querySelectorAll('[data-metric]');
        var groups = {};
        var singles = [];

        widgets.forEach(function(widget) {
            var name = widget.dataset.metric;
            var wrapperEl = widget.closest('[data-metric-group]');
            var group = wrapperEl ? wrapperEl.dataset.metricGroup : null;

            if (group) {
                if (!groups[group]) groups[group] = [];
                groups[group].push(name);
            } else {
                singles.push(name);
            }
        });

        var plan = [];
        Object.values(groups).forEach(function(names) { plan.push(names); });
        singles.forEach(function(name) { plan.push([name]); });

        return plan;
    }

    function fetchAllMetrics() {
        var plan = buildLoadPlan();
        plan.forEach(function(metricNames) { fetchMetrics(metricNames); });
    }

    fetchAllMetrics();

    document.addEventListener('backpack:filter:changed', function () {
        clearTimeout(window._reportRefreshTimer);
        window._reportRefreshTimer = setTimeout(fetchAllMetrics, 300);
    });

    document.addEventListener('backpack:filters:cleared', function () {
        fetchAllMetrics();
    });
});
</script>
@endBassetBlock
