<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">{{ $crud->getHeading() ?? $crud->entity_name_plural }}</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">{{ $crud->getSubheading() ?? $subtitle_fallback }}.</p>
    @if ($crud->hasAccess('list'))
        <p class="mb-0 ms-2 ml-2" bp-section="page-subheading-back-button">
            <small><a href="{{ url($crud->route) }}" class="d-print-none font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
        </p>
    @endif
</section>
