@php
\Alert::flush();

$loadedAssets = json_decode($parentLoadedAssets ?? '[]', true);

//mark parent crud assets as loaded.
foreach($loadedAssets as $asset) {
    Basset::markAsLoaded($asset);
}

@endphp 

 <form method="post"
        action="#"
>
{!! csrf_field() !!}
@include('crud::components.dataform.form_content', ['fields' => $crud->fields(), 'action' => 'edit', 'inlineCreate' => true, 'initFields' => false, 'id' => (request('_modal_form_id') ?? request('_form_id'))])
        {{-- This makes sure that all field assets are loaded. --}}
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

@stack('before_scripts')

@stack('crud_fields_scripts')

@stack('crud_fields_styles')

@stack('after_scripts')

@stack('after_styles')

<script>
    @include('crud::components.dataform.common_js')
</script>
