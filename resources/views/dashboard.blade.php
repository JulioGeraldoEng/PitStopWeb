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
        <p>Escolha uma das opções no menu à esquerda.</p>

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
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        // Carregar dados do dashboard via API
        try {
            const responseHoje = await fetch('/api/dashboard/vendas-hoje');
            const vendasHoje = await responseHoje.json();
            document.getElementById('vendas-hoje').textContent = vendasHoje.total || 0;

            const responseMes = await fetch('/api/dashboard/vendas-mes');
            const vendasMes = await responseMes.json();
            document.getElementById('vendas-mes').textContent = vendasMes.total || 0;

            const responseTotal = await fetch('/api/dashboard/vendas-total');
            const vendasTotal = await responseTotal.json();
            document.getElementById('vendas-total').textContent = vendasTotal.total || 0;

            const responseStatus = await fetch('/api/dashboard/vendas-por-status');
            const statusData = await responseStatus.json();
            
            const container = document.getElementById('status-vendas');
            container.innerHTML = '';
            
            statusData.forEach(({ status, total }) => {
                const icones = {
                    pago: '<i class="fas fa-check-circle" style="color: #28a745;"></i>',
                    pendente: '<i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i>',
                    cancelado: '<i class="fas fa-times-circle" style="color: #dc3545;"></i>',
                    atrasado: '<i class="fas fa-clock" style="color: #e4751b;"></i>'
                };

                const card = document.createElement('div');
                card.className = `card-status ${status}`;
                card.innerHTML = `
                    <h4>${icones[status] || ''} ${status.charAt(0).toUpperCase() + status.slice(1)}</h4>
                    <p>${total} venda(s)</p>
                `;
                container.appendChild(card);
            });
        } catch (error) {
            console.error('Erro ao carregar dashboard:', error);
        }
    });
</script>
@endpush