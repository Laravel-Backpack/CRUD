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
@include('crud::components.dataform.form_content', ['fields' => $crud->fields(), 'action' => 'edit', 'inlineCreate' => true, 'initFields' => false, 'id' => request('_form_id')])
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
    // Define utility functions needed by field initialization scripts
    // These are normally included in form_content.blade.php but missing in AJAX context

    /**
     * Initialize all fields with JavaScript based on their data-init-function attribute
     * @param {jQuery|HTMLElement} container
     */
    function initializeFieldsWithJavascript(container) {
        var selector;
        if (container instanceof jQuery) {
            selector = container;
        } else {
            selector = $(container);
        }
        
        selector.find("[data-init-function]").not("[data-initialized=true]").each(function () {
            var element = $(this);
            var functionName = element.data('init-function');

            if (typeof window[functionName] === "function") {
                window[functionName](element);
                // mark the element as initialized, so that its function is never called again
                element.attr('data-initialized', 'true');
            }
        });
    }

    /**
     * Auto-discover first focusable input
     * @param {jQuery} form
     * @return {jQuery}
     */
    function getFirstFocusableField(form) {
        return form.find('input, select, textarea, button')
            .not('.close')
            .not('[disabled]')
            .filter(':visible:first');
    }

    /**
     *
     * @param {jQuery} firstField
     */
    function triggerFocusOnFirstInputField(firstField) {
        if (firstField.hasClass('select2-hidden-accessible')) {
            return handleFocusOnSelect2Field(firstField);
        }

        firstField.trigger('focus');
    }

    /**
     * 1- Make sure no other select2 input is open in other field to focus on the right one
     * 2- Check until select2 is initialized
     * 3- Open select2
     *
     * @param {jQuery} firstField
     */
    function handleFocusOnSelect2Field(firstField){
        firstField.select2('focus');
    }

    /*
    * Hacky fix for a bug in select2 with jQuery 3.6.0's new nested-focus "protection"
    * see: https://github.com/select2/select2/issues/5993
    * see: https://github.com/jquery/jquery/issues/4382
    *
    */
    $(document).on('select2:open', () => {
        setTimeout(() => document.querySelector('.select2-container--open .select2-search__field').focus(), 100);
    });
</script>
