@basset('bp-animate-css')
@basset('bp-noty-css')

@basset('bp-lineawesome-css')
@basset('bp-lineawesome-regular-400')
@basset('bp-lineawesome-solid-900')
@basset('bp-lineawesome-brands-400')
@basset('bp-lineawesome-regular-400-woff')
@basset('bp-lineawesome-solid-900-woff')
@basset('bp-lineawesome-brands-400-woff')
@basset('bp-lineawesome-regular-400-ttf')
@basset('bp-lineawesome-solid-900-ttf')
@basset('bp-lineawesome-brands-400-ttf')

@basset(base_path('vendor/backpack/crud/src/resources/assets/css/common.css'))

@if (backpack_theme_config('styles') && count(backpack_theme_config('styles')))
    @foreach (backpack_theme_config('styles') as $path)
        @if(is_array($path))
        @foreach($path as $style)
            @basset($style)
        @endforeach
        @else
            @basset($path)
        @endif
    @endforeach
@endif

@if (backpack_theme_config('mix_styles') && count(backpack_theme_config('mix_styles')))
    @foreach (backpack_theme_config('mix_styles') as $path => $manifest)
        <link rel="stylesheet" type="text/css" href="{{ mix($path, $manifest) }}">
    @endforeach
@endif

@if (backpack_theme_config('vite_styles') && count(backpack_theme_config('vite_styles')))
    @vite(backpack_theme_config('vite_styles'))
@endif
