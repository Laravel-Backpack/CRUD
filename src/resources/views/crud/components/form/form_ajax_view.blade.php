 <form method="post"
        action="{{ url($crud->route) }}"
        onsubmit="return false"
        @if ($crud->hasUploadFields('create'))
        enctype="multipart/form-data"
        @endif
>
{!! csrf_field() !!}
@include('crud::form_content', ['fields' => $crud->fields(), 'action' => 'create', 'inlineCreate' => true])
<div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>
 </form>
 

@foreach (app('widgets')->toArray() as $currentWidget)
@php
    $currentWidget = \Backpack\CRUD\app\Library\Widget::add($currentWidget);
@endphp
    @if($currentWidget->getAttribute('inline'))
        @include($currentWidget->getFinalViewPath(), ['widget' => $currentWidget->toArray()])
    @endif
@endforeach

@stack('crud_fields_styles')
@stack('crud_fields_scripts')
@stack('after_styles')
@stack('after_scripts')