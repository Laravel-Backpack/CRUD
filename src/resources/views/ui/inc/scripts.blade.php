@basset('bp-jquery')
@basset('bp-popper-js')
@basset('bp-noty-js')
@basset('bp-sweet-alert-js')

@if (backpack_theme_config('scripts') && count(backpack_theme_config('scripts')))
    @foreach (backpack_theme_config('scripts') as $path)
        @if(is_array($path))
        @foreach($path as $script)
            @basset($script)
        @endforeach
        @else
            @basset($path)
        @endif
    @endforeach
@endif

@if (backpack_theme_config('mix_scripts') && count(backpack_theme_config('mix_scripts')))
    @foreach (backpack_theme_config('mix_scripts') as $path => $manifest)
        <script type="text/javascript" src="{{ mix($path, $manifest) }}"></script>
    @endforeach
@endif

@if (backpack_theme_config('vite_scripts') && count(backpack_theme_config('vite_scripts')))
    @vite(backpack_theme_config('vite_scripts'))
@endif

@include(backpack_view('inc.alerts'))

@if(config('app.debug'))
    @include('crud::inc.ajax_error_frame')
@endif

@push('after_scripts')
    @basset(base_path('vendor/backpack/crud/src/resources/assets/js/common.js'))
@endpush
