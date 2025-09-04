@php
\Alert::flush();
@endphp 
 <form method="post"
        action="#"
>
{!! csrf_field() !!}
@include('crud::form_content', ['fields' => $crud->fields(), 'action' => 'edit', 'inlineCreate' => true, 'initFields' => false])
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


@stack('after_styles')
@stack('after_scripts')