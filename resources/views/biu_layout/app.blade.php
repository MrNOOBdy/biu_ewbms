<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title', 'BI-U: eWBS')</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4361ee">
    <meta name="description" content="BI-U: eWBS">
    <meta name="author" content="BI-U">

    <link rel="preload" href="{{ asset('css/variables.css') }}" as="style">
    <link rel="preload" href="{{ asset('css/general.css') }}" as="style">
    <link rel="preload" href="/font/Lato-Regular.woff2" as="font" type="font/woff2" crossorigin>
    
    <link rel="preload" 
          href="{{ asset('images/background/adm_background.png') }}" 
          as="image" 
          type="image/png"
          fetchpriority="high"
          media="(min-width: 1px)">

    @if(session('darkModePreference') === 'on')
    <link rel="preload" 
          href="{{ asset('images/background/adm_background_dark.png') }}" 
          as="image" 
          type="image/png"
          fetchpriority="high"
          media="(min-width: 1px)">
    @endif
    
    <link rel="icon" type="image/png" sizes="32x32" href="/fav_ico/favicon-32x32.png">
    
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    @if (app()->environment('production'))
        <link rel="stylesheet" href="{{ asset('css/main.min.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
        <link rel="stylesheet" href="{{ asset('css/general.css') }}">
        <link rel="stylesheet" href="{{ asset('css/navi_bar.css') }}">
        <link rel="stylesheet" href="{{ asset('css/tabs.css') }}">
        <link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
        <link rel="stylesheet" href="{{ asset('css/tab_header.css') }}">
        <link rel="stylesheet" href="{{ asset('css/table.css') }}">
        <link rel="stylesheet" href="{{ asset('css/tab_toolkit.css') }}">
    @endif

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('fonts/fontawesome/css/all.min.css') }}">
    
    <link rel="preload" href="/font/Lato-Bold.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/font/Lato-Italic.woff2" as="font" type="font/woff2" crossorigin>

</head>
<body>
    <!-- @if(!Request::is('admin'))
    <div class="global-dark-mode-toggle">
        <span class="gdm-mode-text">Dark Mode</span>
        <label class="gdm-toggle-switch">
            <input type="checkbox" id="darkModeToggle" class="gdm-toggle-input" 
                {{ (session('darkModePreference', 'system') === 'on' || 
                   (session('darkModePreference', 'system') === 'system' && 
                    request()->header('Sec-CH-Prefers-Color-Scheme') === 'dark')) ? 'checked' : '' }}>
            <span class="gdm-toggle-label"></span>
        </label>
    </div>
    @endif -->

    <div class="content">
        @yield('content')
    </div>
    
    @yield('scripts')
    <script src="{{ asset('js/darkMode.js') }}"></script>
    <script src="{{ asset('js/user_navi.js') }}"></script>
    <script src="{{ asset('js/tab_toolkit.js') }}"></script>
    <script src="{{ asset('js/tab_function.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>   
</body>
</html>
