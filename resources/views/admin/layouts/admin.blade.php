<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - PitStop</title>
    
    <!-- Favicon (Ícone da aba) -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/icon/pitstop_icon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/icon/pitstop_icon.ico') }}">
    
    <!-- Para dispositivos Apple (iPhone, iPad) -->
    <link rel="apple-touch-icon" href="{{ asset('assets/icon/pitstop_icon.ico') }}">
    
    <!-- Para Android Chrome -->
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/icon/pitstop_icon.ico') }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v={{ time() }}">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar animate__animated animate__fadeInLeft">
        <div class="sidebar-header">
            <h3>PitStop</h3>
            <small>Painel Administrativo</small>
        </div>
        
        <div class="sidebar-menu">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Usuários
            </a>
            <a href="{{ route('dashboard') }}">
                <i class="fas fa-external-link-alt"></i> Ir para o sistema
            </a>
        </div>
    </div>

    <!-- Conteúdo principal -->
    <div class="main-content">
        <!-- Top navbar -->
        <div class="top-navbar animate__animated animate__fadeInDown">
            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
            <div class="user-info">
                <div class="user-avatar">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="user-details">
                    <span class="user-name">{{ Auth::user()->name }}</span>
                    <span class="user-role">{{ Auth::user()->tipo === 'admin' ? 'Administrador' : 'Usuário' }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        @if(session('success'))
            <div class="alert alert-success animate__animated animate__fadeInDown">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger animate__animated animate__fadeInDown">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Conteúdo da página -->
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Adicionar classe active ao link atual
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const links = document.querySelectorAll('.sidebar-menu a');
            
            links.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>