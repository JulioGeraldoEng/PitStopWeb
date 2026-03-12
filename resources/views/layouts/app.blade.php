<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PitStop')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/icon/pitstop_icon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/icon/pitstop_icon.ico') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Seus estilos personalizados -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Top Bar - Só aparece se NÃO for página de login -->
    @if(!request()->routeIs('login') && !request()->routeIs('register'))
        <div id="top-bar-container"></div>
    @endif

    <!-- Conteúdo Principal -->
    <main>
        @yield('content')
    </main>

    <!-- Scripts Globais -->
    <script src="{{ asset('assets/js/global-scripts.js') }}" defer></script>
    @stack('scripts')
</body>
</html>