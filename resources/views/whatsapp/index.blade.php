@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/whatsapp.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>Integração com WhatsApp</h1>

    <form class="formulario">
        <div class="form-linha">
            <div class="campo botao">
                <label>&nbsp;</label>
                <div class="buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" id="btnWhatsapp">
                        <i class="fab fa-whatsapp"></i> Conectar WhatsApp
                    </button>
                    <button type="button" id="btnRestartSession">
                        <i class="fas fa-sync-alt"></i> Reiniciar Sessão
                    </button>
                    <button type="button" id="btnSendLateSales">
                        <i class="fas fa-paper-plane"></i> Enviar Vendas Atrasadas
                    </button>
                </div>
            </div>
        </div>

        <div id="loadingMessage" style="display: none; margin-top: 15px;">
            Carregando QR Code...
        </div>

        <div id="qrcode-container" style="display: none; margin-top: 15px;"></div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
    // Seu código do whatsapp.js aqui
</script>
@endpush