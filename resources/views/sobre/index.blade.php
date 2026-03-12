@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/sobre.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>Sobre o Aplicativo Pitstop</h1>
    <p class="about-text">O Pitstop é uma solução completa para gerenciar seu negócio de forma eficiente. Ele foi projetado para simplificar suas tarefas diárias, desde o registro de informações importantes até a análise de dados para tomada de decisões.</p>

    <h2>Principais Características:</h2>
    <ul class="lista-features-sobre">
        <li>
            <span class="feature-toggle" data-target="details-cadastro-clientes">Cadastro de Clientes</span>
            <div class="feature-details" id="details-cadastro-clientes">
                <p>Esta seção permite que você mantenha um registro organizado de todos os seus clientes...</p>
            </div>
        </li>
        <!-- Adicione os outros itens conforme seu sobre.html -->
    </ul>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.feature-toggle');
        toggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const detailsElement = document.getElementById(targetId);
                if (detailsElement) {
                    this.classList.toggle('open');
                    if (detailsElement.style.display === 'block') {
                        detailsElement.style.display = 'none';
                    } else {
                        detailsElement.style.display = 'block';
                    }
                }
            });
        });
    });
</script>
@endpush