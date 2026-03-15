@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@section('content')
<div class="container">
    <!-- CABEÇALHO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <div class="text-muted">
            <i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::now('America/Sao_Paulo')->format('d/m/Y') }}
        </div>
    </div>

    <!-- CARDS DE ESTATÍSTICAS -->
    <div class="row stats-row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card primary animate-card">
                <div class="stat-icon primary">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3 id="total-vendas">0</h3>
                    <p>Total de Vendas</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card success animate-card">
                <div class="stat-icon success">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3 id="vendas-mes">0</h3>
                    <p>Vendas no Mês</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card warning animate-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="vendas-pendentes">0</h3>
                    <p>Pendentes</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card danger animate-card">
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="vendas-atrasadas">0</h3>
                    <p>Atrasadas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- SEGUNDA LINHA DE CARDS -->
    <div class="row g-4">
        <!-- TOP PRODUTOS -->
        <div class="col-md-6">
            <div class="content-card animate-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-chart-line"></i> Top 5 Produtos Mais Vendidos</h5>
                    <span class="badge">MÊS</span>
                </div>
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Qtd</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="top-produtos">
                        <tr>
                            <td colspan="3" class="text-center text-muted">Carregando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TOP CLIENTES -->
        <div class="col-md-6">
            <div class="content-card animate-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-users"></i> Top 5 Clientes</h5>
                    <span class="badge">TOTAL</span>
                </div>
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Compras</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="top-clientes">
                        <tr>
                            <td colspan="3" class="text-center text-muted">Carregando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ÚLTIMAS VENDAS -->
        <div class="col-12">
            <div class="content-card animate-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-history"></i> Últimas Vendas</h5>
                    <span class="badge">RECENTES</span>
                </div>
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Vencimento</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="ultimas-vendas">
                        <tr>
                            <td colspan="5" class="text-center text-muted">Carregando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- STATUS DAS VENDAS -->
        <div class="col-md-6">
            <div class="content-card animate-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-chart-pie"></i> Status das Vendas</h5>
                </div>
                <div id="status-vendas" class="d-flex justify-content-around flex-wrap">
                    <!-- Será preenchido via JavaScript -->
                </div>
            </div>
        </div>

        <!-- RECEBIMENTOS DO DIA -->
        <div class="col-md-6">
            <div class="content-card animate-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-hand-holding-usd"></i> Recebimentos do Dia</h5>
                </div>
                <div class="text-center">
                    <h2 id="recebimentos-dia" class="display-4">R$ 0,00</h2>
                    <p class="text-muted">{{ now()->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- AÇÕES RÁPIDAS -->
    <div class="quick-actions">
        <a href="{{ route('vendas.create') }}" class="quick-action-btn success">
            <i class="fas fa-plus-circle"></i> Nova Venda
        </a>
        <a href="{{ route('clientes.create') }}" class="quick-action-btn primary">
            <i class="fas fa-user-plus"></i> Novo Cliente
        </a>
        <a href="{{ route('produtos.create') }}" class="quick-action-btn info">
            <i class="fas fa-box"></i> Novo Produto
        </a>
        <a href="{{ route('recebimentos.index') }}" class="quick-action-btn warning">
            <i class="fas fa-hand-holding-usd"></i> Recebimentos
        </a>
        <a href="{{ route('relatorios.index') }}" class="quick-action-btn danger">
            <i class="fas fa-chart-line"></i> Relatórios
        </a>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/dashboard/index.js') }}"></script>
@endpush