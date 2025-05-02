<h1 
    class="text-capitalize mb-0" 
    bp-section="page-heading">
    {!! $crud->getHeading() ?? trans('backpack::crud.page_heading', [
        'singular' => $singular, 
        'plural' => $plural,
        'action' => $action
        ]) 
    !!}
</h1>
<p 
    class="ms-2 ml-2 mb-0" 
    bp-section="page-subheading">
    {!! $crud->getSubheading() ?? trans('backpack::crud.page_subheading', [
        'singular' => $singular, 
        'plural' => $plural,
        'action' => $action
        ]) 
    !!}
</p>
@if ($backButton ?? true && $crud->hasAccess('list'))
    <p class="mb-0 ms-2 ml-2" bp-section="page-subheading-back-button">
        <small>
            <a href="{{ url($crud->route) }}" class="d-print-none font-sm">
                <span><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></span>
            </a>
        </small>
    </p>
@endif