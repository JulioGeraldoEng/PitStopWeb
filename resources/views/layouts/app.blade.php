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
                <!-- Logo e Botão Mobile -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <!-- Botão do Menu Mobile (hambúrguer) -->
                    <button class="menu-mobile-btn" id="menuMobileBtn" style="background: rgba(255,255,255,0.2); border: none; border-radius: 8px; padding: 8px 12px; cursor: pointer; color: white;">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <!-- Logo -->
                    <div class="logo-area">
                        <div class="logo-icon" style="background: transparent; box-shadow: none;">
                            <img src="{{ asset('assets/icon/pitstop_icon.ico') }}" alt="PitStop" style="width: 45px; height: 45px;">
                        </div>
                        <span class="logo-text">PitStop</span>
                    </div>
                </div>

                <!-- Menu Central (desktop) -->
                <div class="menu-center">
                    <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>{{ __('messages.inicio') }}</span>
                    </a>
                    <a href="{{ route('vendas.index') }}" class="menu-item {{ request()->routeIs('vendas.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span>{{ __('messages.vendas') }}</span>
                    </a>
                    <a href="{{ route('recebimentos.index') }}" class="menu-item {{ request()->routeIs('recebimentos.*') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>{{ __('messages.recebimentos') }} </span>
                    </a>
                    <a href="{{ route('relatorios.index') }}" class="menu-item {{ request()->routeIs('relatorios.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>{{ __('messages.relatorios') }}</span>
                    </a>
                    <a href="{{ route('clientes.index') }}" class="menu-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>{{ __('messages.clientes') }} </span>
                    </a>
                    <a href="{{ route('produtos.index') }}" class="menu-item {{ request()->routeIs('produtos.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>{{ __('messages.produtos') }}</span>
                    </a>
                    <a href="{{ route('whatsapp.index') }}" class="menu-item {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
                        <i class="fab fa-whatsapp"></i>
                        <span>{{ __('messages.whatsapp') }}</span>
                    </a>
                    <a href="{{ route('sobre.index') }}" class="menu-item {{ request()->routeIs('sobre.*') ? 'active' : '' }}">
                        <i class="fas fa-info-circle"></i>
                        <span>{{ __('messages.sobre') }}</span>
                    </a>
                    @if(Auth::user()->tipo === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                            <i class="fas fa-shield-alt"></i>
                            <span>{{ __('messages.admin') }}</span>
                        </a>
                    @endif
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

                        <!-- DROPDOWN DO USUÁRIO - ADICIONAR APÓS CONFIGURAÇÕES -->
                        @if(Auth::user()->tipo === 'admin')
                            <div class="user-dropdown-divider"></div>
                            <a href="{{ route('admin.dashboard') }}" class="user-dropdown-item">
                                <i class="fas fa-shield-alt"></i> Área Admin
                            </a>
                        @endif
                        
                        <!-- MODO ESCURO (já existente) -->
                        <div class="user-dropdown-divider"></div>
                        <div class="user-dropdown-item" id="theme-toggle" style="cursor: pointer;">
                            <i class="fas {{ session('theme') == 'dark' ? 'fa-sun' : 'fa-moon' }}"></i>
                            <span>{{ session('theme') == 'dark' ? 'Modo Claro' : 'Modo Escuro' }}</span>
                            <div class="ms-auto form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="darkModeSwitch" style="cursor: pointer;" {{ session('theme') == 'dark' ? 'checked' : '' }}>
                            </div>
                        </div>
                        
                        <!-- SEÇÃO DE IDIOMAS -->
                        <!-- SEÇÃO DE IDIOMAS -->
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
                        
                        <!-- SAIR -->
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

        <!-- Overlay do Menu Mobile -->
        <div class="menu-mobile-overlay" id="menuMobileOverlay"></div>

        <!-- Menu Mobile (side menu) -->
        <div class="menu-mobile" id="menuMobile">
            <div class="menu-mobile-header">
                <h3>Menu</h3>
            </div>
            <div class="menu-mobile-items">
                <a href="{{ route('dashboard') }}" class="menu-mobile-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Início</span>
                </a>
                <a href="{{ route('vendas.index') }}" class="menu-mobile-item {{ request()->routeIs('vendas.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Vendas</span>
                </a>
                <a href="{{ route('recebimentos.index') }}" class="menu-mobile-item {{ request()->routeIs('recebimentos.*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Recebimentos</span>
                </a>
                <a href="{{ route('relatorios.index') }}" class="menu-mobile-item {{ request()->routeIs('relatorios.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Relatórios</span>
                </a>
                <a href="{{ route('clientes.index') }}" class="menu-mobile-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
                <a href="{{ route('produtos.index') }}" class="menu-mobile-item {{ request()->routeIs('produtos.*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i>
                    <span>Produtos</span>
                </a>
                <a href="{{ route('whatsapp.index') }}" class="menu-mobile-item {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
                <a href="{{ route('sobre.index') }}" class="menu-mobile-item {{ request()->routeIs('sobre.*') ? 'active' : '' }}">
                    <i class="fas fa-info-circle"></i>
                    <span>Sobre</span>
                </a>
                @if(Auth::user()->tipo === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="menu-mobile-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <i class="fas fa-shield-alt"></i>
                        <span>Admin</span>
                    </a>
                @endif
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
    
    <!-- Script da Top Bar e Menu Mobile -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dropdown do usuário
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
            
            // ========== MENU MOBILE MELHORADO ==========
            const menuMobileBtn = document.getElementById('menuMobileBtn');
            const menuMobile = document.getElementById('menuMobile');
            const menuMobileOverlay = document.getElementById('menuMobileOverlay');
            
            // Verificar se todos os elementos existem
            if (menuMobileBtn && menuMobile && menuMobileOverlay) {
                
                // Função para abrir o menu
                function openMenu() {
                    menuMobile.classList.add('open');
                    menuMobileOverlay.style.display = 'block';
                    // Impedir scroll da página de forma mais eficaz
                    document.body.style.overflow = 'hidden';
                    document.body.style.position = 'fixed';
                    document.body.style.width = '100%';
                    document.body.style.top = `-${window.scrollY}px`;
                    
                    // Guardar a posição do scroll para restaurar depois
                    window.scrollYPosition = window.scrollY;
                }
                
                // Função para fechar o menu
                function closeMenu() {
                    menuMobile.classList.remove('open');
                    menuMobileOverlay.style.display = 'none';
                    // Restaurar scroll
                    document.body.style.overflow = '';
                    document.body.style.position = '';
                    document.body.style.width = '';
                    
                    // Restaurar a posição do scroll
                    if (window.scrollYPosition !== undefined) {
                        window.scrollTo(0, window.scrollYPosition);
                    }
                }
                
                // Função para alternar o menu
                function toggleMenu(e) {
                    if (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    
                    if (menuMobile.classList.contains('open')) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                }
                
                // Evento de clique no botão hambúrguer
                menuMobileBtn.addEventListener('click', toggleMenu);
                
                // Evento de toque no botão para celular (garantir que funciona)
                menuMobileBtn.addEventListener('touchstart', function(e) {
                    // Não faz nada, só garante que o evento de clique vai funcionar
                    // Isso ajuda em alguns dispositivos móveis
                });
                
                // Evento de clique no overlay (fundo escuro)
                menuMobileOverlay.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeMenu();
                });
                
                // Fechar menu ao clicar em qualquer link do menu mobile
                const mobileLinks = document.querySelectorAll('.menu-mobile-item');
                mobileLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        closeMenu();
                    });
                });
                
                // Fechar menu ao redimensionar a tela para desktop (acima de 768px)
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768 && menuMobile.classList.contains('open')) {
                        closeMenu();
                    }
                });
                
                // Impedir que o menu feche ao clicar dentro dele
                menuMobile.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
                
                // Fechar menu ao pressionar a tecla ESC
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && menuMobile.classList.contains('open')) {
                        closeMenu();
                    }
                });
                
                // Debug: mostrar no console que o menu foi inicializado
                console.log('✅ Menu mobile inicializado com sucesso');
            } else {
                console.error('❌ Elementos do menu não encontrados');
                console.log('menuMobileBtn:', menuMobileBtn);
                console.log('menuMobile:', menuMobile);
                console.log('menuMobileOverlay:', menuMobileOverlay);
            }
            
            // ========== CORREÇÃO DO PADDING DO MAIN ==========
            function adjustMainPadding() {
                const topBar = document.getElementById('top-bar-container');
                const mainContent = document.querySelector('main');
                
                if (topBar && mainContent) {
                    const topBarHeight = topBar.offsetHeight;
                    mainContent.style.paddingTop = topBarHeight + 'px';
                }
            }
            
            // Ajustar padding ao carregar e ao redimensionar
            adjustMainPadding();
            window.addEventListener('resize', adjustMainPadding);
            
            // Observar mudanças no DOM (caso a top bar mude de altura)
            const observer = new MutationObserver(adjustMainPadding);
            const topBarContainer = document.getElementById('top-bar-container');
            if (topBarContainer) {
                observer.observe(topBarContainer, { attributes: true, childList: true, subtree: true });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>