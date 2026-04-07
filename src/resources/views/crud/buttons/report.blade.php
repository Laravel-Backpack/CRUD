@if ($crud->hasAccess('report'))
    <a href="{{ url($crud->route.'/report') }}" class="btn btn-outline-info" bp-button="report">
        <i class="la la-chart-bar"></i> <span>Report</span>
    </a>
@endif
