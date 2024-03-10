@include('crud::fields.text')

{{-- FIELD CSS - will be loaded in the after_styles section --}}
@push('crud_fields_styles')
    {{-- include jQuery UI --}}
    @loadOnce('packages/jquery-ui-dist/jquery-ui.css')
@endpush

{{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
    {{-- include jQuery UI --}}
    @loadOnce('packages/jquery-ui-dist/jquery-ui.min.js')

    <script>
        $(function () {
            var availableOptions = {!! json_encode(($field['options'] ?? [])) !!};

            $("input[name='{{ $field['name'] }}']").autocomplete({
                source: availableOptions
            });
        });
    </script>
@endpush
