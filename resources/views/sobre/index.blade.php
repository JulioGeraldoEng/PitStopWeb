@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/sobre.css') }}">
@endpush

@section('content')
<div class="container">
    <!-- CABEÇALHO -->
    <div class="sobre-header animate-card">
        <h1><i class="fas fa-info-circle"></i> Sobre o PitStop</h1>
        <p>Sistema completo de gerenciamento para oficinas e pequenos negócios</p>
        <div class="versao-badge">
            <i class="fas fa-tag"></i> Versão 2.0.0
        </div>
    </div>

    <!-- CARDS DE INFORMAÇÕES -->
    <div class="row g-4">
        <!-- Card: Sobre o Sistema -->
        <div class="col-md-6">
            <div class="info-card animate-card">
                <h5><i class="fas fa-rocket"></i> Sobre o Sistema</h5>
                <p>O PitStop é uma solução completa para gerenciamento de oficinas e pequenos negócios. Desenvolvido com as melhores tecnologias do mercado, oferece uma experiência fluida e intuitiva para gerenciar clientes, produtos, vendas e recebimentos.</p>
                <div class="mt-3">
                    <span class="tech-badge"><i class="fab fa-laravel"></i> Laravel 12</span>
                    <span class="tech-badge"><i class="fab fa-js"></i> JavaScript</span>
                    <span class="tech-badge"><i class="fas fa-database"></i> PostgreSQL</span>
                    <span class="tech-badge"><i class="fab fa-bootstrap"></i> Bootstrap 5</span>
                </div>
            </div>
        </div>

        <!-- Card: Funcionalidades -->
        <div class="col-md-6">
            <div class="info-card animate-card">
                <h5><i class="fas fa-cogs"></i> Funcionalidades</h5>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Cadastro de Clientes com autocomplete</li>
                    <li><i class="fas fa-check-circle"></i> Controle de Produtos e Estoque</li>
                    <li><i class="fas fa-check-circle"></i> Registro de Vendas com carrinho</li>
                    <li><i class="fas fa-check-circle"></i> Controle de Recebimentos</li>
                    <li><i class="fas fa-check-circle"></i> Relatórios com exportação PDF</li>
                    <li><i class="fas fa-check-circle"></i> Integração com WhatsApp</li>
                    <li><i class="fas fa-check-circle"></i> Dashboard com estatísticas</li>
                    <li><i class="fas fa-check-circle"></i> Autenticação de usuários</li>
                </ul>
            </div>
        </div>

        <!-- Card: Tecnologias Utilizadas -->
        <div class="col-md-6">
            <div class="info-card animate-card">
                <h5><i class="fas fa-code"></i> Tecnologias</h5>
                <div class="mb-3">
                    <strong>Backend:</strong>
                    <div class="mt-2">
                        <span class="tech-badge"><i class="fab fa-laravel"></i> Laravel 12</span>
                        <span class="tech-badge"><i class="fas fa-database"></i> PostgreSQL</span>
                        <span class="tech-badge"><i class="fas fa-lock"></i> Sanctum</span>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Frontend:</strong>
                    <div class="mt-2">
                        <span class="tech-badge"><i class="fab fa-js"></i> JavaScript</span>
                        <span class="tech-badge"><i class="fab fa-bootstrap"></i> Bootstrap 5</span>
                        <span class="tech-badge"><i class="fas fa-wifi"></i> WebSocket</span>
                    </div>
                </div>
                <div>
                    <strong>Infraestrutura:</strong>
                    <div class="mt-2">
                        <span class="tech-badge"><i class="fas fa-server"></i> WPPConnect</span>
                        <span class="tech-badge"><i class="fab fa-node"></i> Node.js</span>
                        <span class="tech-badge"><i class="fas fa-code-branch"></i> Git</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Estatísticas -->
        <div class="col-md-6">
            <div class="info-card animate-card">
                <h5><i class="fas fa-chart-bar"></i> Estatísticas</h5>
                <ul>
                    <li><i class="fas fa-users"></i> <strong>Clientes:</strong> {{ \App\Models\Cliente::count() }}</li>
                    <li><i class="fas fa-boxes"></i> <strong>Produtos:</strong> {{ \App\Models\Produto::count() }}</li>
                    <li><i class="fas fa-shopping-cart"></i> <strong>Vendas:</strong> {{ \App\Models\Venda::count() }}</li>
                    <li><i class="fas fa-money-bill-wave"></i> <strong>Recebimentos:</strong> {{ \App\Models\Recebimento::count() }}</li>
                    <li><i class="fas fa-user-check"></i> <strong>Usuários:</strong> {{ \App\Models\User::count() }}</li>
                    <li><i class="fas fa-code-branch"></i> <strong>Branches:</strong> 15</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- CARD DE CONTATO -->
    <div class="contato-card animate-card">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-envelope"></i> Contato</h5>
                <div class="contato-item">
                    <i class="fas fa-user"></i>
                    <span>JG Soluções Tecnológicas</span>
                </div>
                <div class="contato-item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:juliogeraldo.eng@gmail.com">juliogeraldo.eng@gmail.com</a>
                </div>
                <div class="contato-item">
                    <i class="fas fa-phone"></i>
                    <a href="tel:5518997987391">(18) 99798-7391</a>
                </div>
                <div class="contato-item">
                    <i class="fab fa-whatsapp"></i>
                    <a href="https://wa.me/5518997987391" target="_blank">WhatsApp</a>
                </div>
            </div>
            <div class="col-md-6">
                <h5><i class="fas fa-link"></i> Links Úteis</h5>
                <div class="contato-item">
                    <i class="fab fa-github"></i>
                    <a href="https://github.com/JulioGeraldoEng/PitStopWeb" target="_blank">GitHub</a>
                </div>
                <div class="contato-item">
                    <i class="fas fa-book"></i>
                    <a href="#">Documentação</a>
                </div>
                <div class="contato-item">
                    <i class="fas fa-question-circle"></i>
                    <a href="#">Suporte</a>
                </div>
                <div class="contato-item">
                    <i class="fas fa-history"></i>
                    <a href="#">Changelog</a>
                </div>
            </div>
        </div>
        <div class="text-center mt-4 pt-3 border-top border-white border-opacity-25">
            <p class="mb-0">© {{ date('Y') }} PitStop - Todos os direitos reservados</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/sobre/index.js') }}"></script>
@endpush