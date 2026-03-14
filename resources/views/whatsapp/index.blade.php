@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/whatsapp.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>Integração com WhatsApp</h1>
    
    <!-- CARD DE STATUS -->
    <div class="status-card" id="status-card">
        <h5>
            <i class="fab fa-whatsapp"></i> Status da Conexão
        </h5>
        <div class="status-info">
            <span id="status-text">Verificando conexão...</span>
            <span class="status-badge" id="status-badge">
                <i class="fas fa-spinner fa-spin"></i> Conectando...
            </span>
        </div>
    </div>

    <!-- CARD DO QR CODE -->
    <div class="qrcode-card" id="qrcode-card">
        <h5><i class="fas fa-qrcode"></i> QR Code de Conexão</h5>
        <div class="qrcode-container" id="qrcode-container">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Aguardando QR Code...</p>
            </div>
        </div>
    </div>

    <!-- CARD DE AÇÕES -->
    <div class="actions-card">
        <h5><i class="fas fa-cog"></i> Ações</h5>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <button type="button" id="btnConectar" class="btn-whatsapp btn-whatsapp-success">
                <i class="fas fa-plug"></i> Conectar
            </button>
            <button type="button" id="btnReiniciar" class="btn-whatsapp btn-whatsapp-warning">
                <i class="fas fa-sync-alt"></i> Reiniciar
            </button>
            <!-- Botão Desconectar (vermelho) -->
            <button type="button" id="btnLogout" class="btn-whatsapp btn-whatsapp-danger">
                <i class="fas fa-sign-out-alt"></i> Desconectar
            </button>

            <!-- Botão Enviar Atrasados (azul) -->
            <button type="button" id="btnEnviar" class="btn-whatsapp btn-whatsapp-primary">
                <i class="fab fa-whatsapp"></i> Enviar Atrasados
            </button>
        </div>
    </div>

    <div id="mensagem" class="alert mensagem" style="display: none;"></div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script src="{{ asset('assets/js/whatsapp/index.js') }}"></script>
@endpush