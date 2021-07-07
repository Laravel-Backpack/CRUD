{{-- PAGE OR LINK field --}}
{{-- Used in Backpack\MenuCRUD --}}

<?php
    $field['options'] = [
        'page_link'     => trans('backpack::crud.page_link'),
        'internal_link' => trans('backpack::crud.internal_link'),
        'external_link' => trans('backpack::crud.external_link'),
    ];
    $field['allows_null'] = $field['allows_null'] ?? false;

    $pages = $field['page_model']::all();

    $nameType = $field['name']['type'] ?? $field['name'][0] ?? 'type';
    $nameLink = $field['name']['link'] ?? $field['name'][1] ?? 'link';
    $namePageId = $field['name']['page_id'] ?? $field['name'][2] ?? 'page_id';
?>

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    <div class="row" data-init-function="bpFieldInitPageOrLinkElement">
        {{-- hidden placeholders for content --}}
        <input type="hidden" value="{{ $entry->$namePageId }}" name="{{ $namePageId }}" />
        <input type="hidden" value="{{ $entry->$nameLink }}" name="{{ $nameLink }}" />

        <div class="col-sm-3">
            {{-- type select --}}
            <select
                data-identifier="page_or_link_select"
                name="{!! $nameType !!}"
                @include('crud::fields.inc.attributes')
                >

                @if ($field['allows_null'])
                    <option value="">-</option>
                @endif

                @foreach ($field['options'] as $key => $value)
                    <option value="{{ $key }}"
                        @if ($key === $entry->$nameType)
                            selected
                        @endif
                    >{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-9">
            {{-- page slug input --}}
            <div class="page_or_link_value page_link {{ $entry->$nameType === 'page_link' || (!$entry->$nameType && !$field['allows_null']) ? '' : 'd-none' }}">
                <select
                    class="form-control"
                    for="{{ $namePageId }}"
                    required
                    >
                    @foreach ($pages as $page)
                        <option value="{{ $page->id }}"
                            @if ($page->id === $entry->$namePageId)
                                selected
                            @endif
                        >{{ $page->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- internal link input --}}
            <div class="page_or_link_value internal_link {{ $entry->$nameType === 'internal_link' ? '' : 'd-none' }}">
                <input
                    type="text"
                    class="form-control"
                    placeholder="{{ trans('backpack::crud.internal_link_placeholder', ['url', url(config('backpack.base.route_prefix').'/page')]) }}"
                    for="{{ $nameLink }}"
                    required

                    @if ($entry->$nameType !== 'internal_link')
                        disabled="disabled"
                    @endif

                    @if ($entry->$nameType === 'internal_link' && $entry->$nameLink)
                        value="{{ $entry->$nameLink }}"
                    @endif
                    >
            </div>

            {{-- external link input --}}
            <div class="page_or_link_value external_link {{ $entry->$nameType === 'external_link' ? '' : 'd-none' }}">
                <input
                    type="url"
                    class="form-control"
                    placeholder="{{ trans('backpack::crud.page_link_placeholder') }}"
                    for="{{ $nameLink }}"
                    required

                    @if ($entry->$nameType !== 'external_link')
                        disabled="disabled"
                    @endif

                    @if ($entry->$nameType === 'external_link' && $entry->$nameLink)
                        value="{{ $entry->$nameLink }}"
                    @endif
                    >
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
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script>
        function bpFieldInitPageOrLinkElement(element) {
            element = element[0]; // jQuery > Vanilla

            const select = element.querySelector('select[data-identifier=page_or_link_select]');
            const values = element.querySelectorAll('.page_or_link_value');

            // updates hidden fields
            const updateHidden = () => {
                let selectedInput = select.value && element.querySelector(`.${select.value}`).firstElementChild;
                element.querySelectorAll(`input[type="hidden"]`).forEach(hidden => {
                    hidden.value = selectedInput && hidden.getAttribute('name') === selectedInput.getAttribute('for') ? selectedInput.value : '';
                });
            }

            // save input changes to hidden placeholders
            values.forEach(value => value.firstElementChild.addEventListener('input', updateHidden));

            // main select change
            select.addEventListener('change', () => {
                values.forEach(value => {
                    let isSelected = value.classList.contains(select.value);

                    // toggle visibility and disabled
                    value.classList.toggle('d-none', !isSelected);
                    value.firstElementChild.toggleAttribute('disabled', !isSelected);
                });

                // updates hidden fields
                updateHidden();
            });
        }
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}