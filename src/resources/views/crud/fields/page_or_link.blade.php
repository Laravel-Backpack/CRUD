<!-- PAGE OR LINK field -->
<!-- Used in Backpack\MenuCRUD -->

<?php
    $field['options'] = [
        'page_link'     => trans('backpack::crud.page_link'),
        'internal_link' => trans('backpack::crud.internal_link'),
        'external_link' => trans('backpack::crud.external_link'),
    ];
    $field['allows_null'] = false;
    $page_model = $field['page_model'];
    $active_pages = $page_model::all();
?>

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    <div class="row" data-init-function="bpFieldInitPageOrLinkElement">
        <div class="col-sm-3">
            <select
                data-identifier="page_or_link_select"
                name="type"
                @include('crud::fields.inc.attributes')
                >

                @if (isset($field['allows_null']) && $field['allows_null']==true)
                    <option value="">-</option>
                @endif

                    @if (count($field['options']))
                        @foreach ($field['options'] as $key => $value)
                            <option value="{{ $key }}"
                                @if (isset($crud->entry) && $key==$crud->entry->type)
                                     selected
                                @endif
                            >{{ $value }}</option>
                        @endforeach
                    @endif
            </select>
        </div>
        <div class="col-sm-9">
            <!-- external link input -->
              <div class="page_or_link_value page_or_link_external_link <?php if (! isset($entry) || $entry->type != 'external_link') {
    echo 'd-none';
} ?>">
                <input
                    type="url"
                    class="form-control"
                    name="link"
                    placeholder="{{ trans('backpack::crud.page_link_placeholder') }}"

                    @if (!isset($entry) || $entry->type!='external_link')
                        disabled="disabled"
                      @endif

                    @if (isset($entry) && $entry->type=='external_link' && isset($entry->link) && $entry->link!='')
                        value="{{ $entry->link }}"
                    @endif
                    >
              </div>
              <!-- internal link input -->
              <div class="page_or_link_value page_or_link_internal_link <?php if (! isset($entry) || $entry->type != 'internal_link') {
    echo 'd-none';
} ?>">
                <input
                    type="text"
                    class="form-control"
                    name="link"
                    placeholder="{{ trans('backpack::crud.internal_link_placeholder', ['url', url(config('backpack.base.route_prefix').'/page')]) }}"

                    @if (!isset($entry) || $entry->type!='internal_link')
                        disabled="disabled"
                      @endif

                    @if (isset($entry) && $entry->type=='internal_link' && isset($entry->link) && $entry->link!='')
                        value="{{ $entry->link }}"
                    @endif
                    >
              </div>
              <!-- page slug input -->
              <div class="page_or_link_value page_or_link_page <?php if (isset($entry) && $entry->type != 'page_link') {
    echo 'd-none';
} ?>">
                <select
                    class="form-control"
                    name="page_id"
                    >
                    @if (!count($active_pages))
                        <option value="">-</option>
                    @else
                        @foreach ($active_pages as $key => $page)
                            <option value="{{ $page->id }}"
                                @if (isset($entry) && isset($entry->page_id) && $page->id==$entry->page_id)
                                     selected
                                @endif
                            >{{ $page->name }}</option>
                        @endforeach
                    @endif

                </select>
              </div>
        </div>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        @loadOnce('bpFieldInitPageOrLinkElement')
        <script>
            function bpFieldInitPageOrLinkElement(element) {
                $wrapper = element;

                $wrapper.find('[data-identifier=page_or_link_select]').change(function(e) {
                    $wrapper.find(".page_or_link_value input").attr('disabled', 'disabled');
                    $wrapper.find(".page_or_link_value select").attr('disabled', 'disabled');
                    $wrapper.find(".page_or_link_value").removeClass("d-none").addClass("d-none");

                    switch($(this).val()) {
                        case 'external_link':
                            $wrapper.find(".page_or_link_external_link input").removeAttr('disabled');
                            $wrapper.find(".page_or_link_external_link").removeClass('d-none');
                            break;

                        case 'internal_link':
                            $wrapper.find(".page_or_link_internal_link input").removeAttr('disabled');
                            $wrapper.find(".page_or_link_internal_link").removeClass('d-none');
                            break;

                        default: // page_link
                            $wrapper.find(".page_or_link_page select").removeAttr('disabled');
                            $wrapper.find(".page_or_link_page").removeClass('d-none');
                    }
                });
            }
        </script>
        @endLoadOnce
    @endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
