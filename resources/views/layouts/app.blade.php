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
    <link rel="stylesheet" href="{{ asset('assets/css/top-bar.css') }}?v={{ time() }}">
    
    <!-- Script de inicialização do tema (executa ANTES da renderização) -->
    <script>
        (function() {
            // Tentar obter o tema da sessão ou configurar padrão
            let tema = '{{ session('tema', Auth::user()->settings->tema ?? 'claro') }}';
            const html = document.documentElement;
            
            // Remover qualquer classe de tema existente
            html.classList.remove('dark', 'light', 'auto');
            
            // Aplicar tema
            if (tema === 'escuro') {
                html.classList.add('dark');
            } else if (tema === 'auto') {
                // Detectar preferência do sistema
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    html.classList.add('dark');
                } else {
                    html.classList.add('light');
                }
            } else {
                // tema claro (padrão)
                html.classList.add('light');
            }
            
            // Log para debug (opcional)
            console.log('🎨 Tema aplicado:', tema, '→', html.classList.contains('dark') ? 'Escuro' : 'Claro');
        })();
    </script>
    
    @stack('styles')
</head>
<body>
    <!-- Top Bar Moderna -->
    @if(!request()->routeIs('login') && !request()->routeIs('register'))
        <div id="top-bar-container">
            <div class="top-bar">
                <!-- Logo -->
                <div class="logo-area">
                    <div class="logo-icon" style="background: transparent; box-shadow: none;">
                        <img src="{{ asset('assets/icon/pitstop_icon.ico') }}" alt="PitStop" style="width: 45px; height: 45px;">
                    </div>
                    <span class="logo-text">PitStop</span>
                </div>

                <!-- Menu Central -->
                <div class="menu-center">
                    <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Início</span>
                    </a>
                    <a href="{{ route('vendas.index') }}" class="menu-item {{ request()->routeIs('vendas.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Vendas</span>
                    </a>
                    <a href="{{ route('recebimentos.index') }}" class="menu-item {{ request()->routeIs('recebimentos.*') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Recebimentos</span>
                    </a>
                    <a href="{{ route('relatorios.index') }}" class="menu-item {{ request()->routeIs('relatorios.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Relatórios</span>
                    </a>
                    <a href="{{ route('clientes.index') }}" class="menu-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Clientes</span>
                    </a>
                    <a href="{{ route('produtos.index') }}" class="menu-item {{ request()->routeIs('produtos.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>Produtos</span>
                    </a>
                    <a href="{{ route('whatsapp.index') }}" class="menu-item {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
                        <i class="fab fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </a>
                    <a href="{{ route('sobre.index') }}" class="menu-item {{ request()->routeIs('sobre.*') ? 'active' : '' }}">
                        <i class="fas fa-info-circle"></i>
                        <span>Sobre</span>
                    </a>
                </div>

                <!-- Dropdown do usuário -->
                <div class="user-area" id="user-area">
                    <div class="user-dropdown-btn" id="userDropdownBtn">
                        <div class="user-avatar">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                        <span class="user-name">{{ Auth::user()->name ?? 'Usuário' }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <div class="user-dropdown-header">
                            <strong>{{ Auth::user()->name ?? 'Usuário' }}</strong>
                            <br>
                            <small>{{ Auth::user()->email ?? 'email@exemplo.com' }}</small>
                        </div>
                        
                        <!-- PERFIL E CONFIGURAÇÕES -->
                        <a href="{{ route('perfil.edit') }}" class="user-dropdown-item">
                            <i class="fas fa-user"></i> Meu Perfil
                        </a>
                        <a href="{{ route('configuracoes.index') }}" class="user-dropdown-item">
                            <i class="fas fa-cog"></i> Configurações
                        </a>
                        
                        <!-- MODO ESCURO (já existente) -->
                        <div class="user-dropdown-divider"></div>
                        <div class="user-dropdown-item" id="theme-toggle" style="cursor: pointer;">
                            <i class="fas {{ session('theme') == 'dark' ? 'fa-sun' : 'fa-moon' }}"></i>
                            <span>{{ session('theme') == 'dark' ? 'Modo Claro' : 'Modo Escuro' }}</span>
                            <div class="ms-auto form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="darkModeSwitch" style="cursor: pointer;" {{ session('theme') == 'dark' ? 'checked' : '' }}>
                            </div>
                        </div>
                        
                        <!-- 👇 NOVO: SEÇÃO DE IDIOMAS -->
                        <div class="user-dropdown-divider"></div>
                        <div class="user-dropdown-header">
                            <strong>Idioma / Language</strong>
                        </div>
                        <a href="{{ route('language.switch', 'pt-BR') }}" class="user-dropdown-item {{ session('locale') == 'pt-BR' ? 'active' : '' }}">
                            <img src="{{ asset('assets/img/br.svg') }}" style="width: 20px; height: 15px; margin-right: 8px;">
                            Português
                        </a>
                        <a href="{{ route('language.switch', 'en') }}" class="user-dropdown-item {{ session('locale') == 'en' ? 'active' : '' }}">
                            <img src="{{ asset('assets/img/us.svg') }}" style="width: 20px; height: 15px; margin-right: 8px;">
                            English
                        </a>
                        <a href="{{ route('language.switch', 'es') }}" class="user-dropdown-item {{ session('locale') == 'es' ? 'active' : '' }}">
                            <img src="{{ asset('assets/img/es.svg') }}" style="width: 20px; height: 15px; margin-right: 8px;">
                            Español
                        </a>
                        
                        <!-- SAIR (já existente) -->
                        <div class="user-dropdown-divider"></div>
                        <a href="#" class="user-dropdown-item danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Ajuste no main para não ficar atrás da top-bar -->
    <main style="padding-top: 70px;">
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>
    <script src="{{ asset('assets/js/global-scripts.js') }}" defer></script>
    <script src="{{ asset('assets/js/datepicker-init.js') }}"></script>
    
    <!-- Script da Top Bar -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userBtn = document.getElementById('userDropdownBtn');
            const userDropdown = document.getElementById('userDropdown');
            
            if (userBtn && userDropdown) {
                userBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('show');
                });
                
                document.addEventListener('click', function() {
                    userDropdown.classList.remove('show');
                });
                
                userDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>