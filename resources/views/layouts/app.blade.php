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

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    
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

    <!-- jQuery (obrigatório para o Datepicker) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap 5 JS (necessário para o modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>
    
    <!-- Scripts Globais -->
    <script src="{{ asset('assets/js/global-scripts.js') }}" defer></script>
    
    <!-- Seu arquivo de inicialização do Datepicker -->
    <script src="{{ asset('assets/js/datepicker-init.js') }}"></script>
    
    @stack('scripts')
</body>
</html>