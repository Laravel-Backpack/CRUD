<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}" dir="{{ backpack_theme_config('html_direction') }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    @if (backpack_theme_config('meta_robots_content'))
    <meta name="robots" content="{{ backpack_theme_config('meta_robots_content', 'noindex, nofollow') }}">
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}"/> {{-- Encrypted CSRF token for Laravel, in order for Ajax requests to work --}}
    <title>{{ isset($title) ? $title.' :: '.backpack_theme_config('project_name') : backpack_theme_config('project_name') }}</title>

    @yield('before_styles')
    @stack('before_styles')
    @basset('https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css', true, [
        'integrity' => 'sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65',
        'crossorigin' => 'anonymous',
    ])
    @yield('after_styles')
    @stack('after_styles')
</head>

<body class="{{ backpack_theme_config('classes.body') }}" bp-layout="layout">

<div class="page">

    <div class="page-wrapper">

        <div class="page-body">
            <main class="container-xl">

                @yield('before_breadcrumbs_widgets')
                @yield('after_breadcrumbs_widgets')
                @yield('header')

                <div class="container-fluid animated fadeIn">
                    @yield('before_content_widgets')
                    @yield('content')
                    @yield('after_content_widgets')
                </div>
            </main>
        </div>
    </div>
</div>

@yield('before_scripts')
@stack('before_scripts')

@basset('https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js', true, [
    'integrity' => 'sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4',
    'crossorigin' => 'anonymous',
])

@yield('after_scripts')
@stack('after_scripts')
</body>
</html>
