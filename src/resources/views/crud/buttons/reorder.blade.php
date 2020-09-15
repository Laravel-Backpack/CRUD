@if ($crud->get('reorder.enabled') && $crud->hasAccess('reorder'))
  <a href="{{ url($crud->route.'/reorder') }}" class="btn btn-outline-primary" data-style="zoom-in"><span class="ladda-label"><i class="la la-arrows"></i> {{ trans('backpack::crud.reorder') }} {{ $crud->entity_pronoun_plural_definite.$crud->entity_name_plural }}</span></a>
@endif