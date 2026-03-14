@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@section('content')
<div class="main-content">
    <header class="header">
        <div class="user-info">
            Usuário logado: <span id="loggedUser" class="user-badge">{{ Auth::user()->name ?? 'Usuário' }}</span>
        </div>
    </header>

    <main>
        <h1>Bem-vindo ao Sistema de Vendas PitStop</h1>
        <p>Escolha uma das opções no menu acima.</p>

        <div class="dashboard-info">
            <div class="info-card">
                <h3>Vendas Hoje</h3>
                <p id="vendas-hoje">0</p>
            </div>
            <div class="info-card">
                <h3>Vendas no Mês</h3>
                <p id="vendas-mes">0</p>
            </div>
            <div class="info-card">
                <h3>Total de Vendas</h3>
                <p id="vendas-total">0</p>
            </div>
        </div>
        
        <div class="status-vendas-container">
            <h2>Status das Vendas</h2>
            <div id="status-vendas" class="cards-status-container"></div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
@endpush