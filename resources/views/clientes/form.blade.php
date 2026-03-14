@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/cliente.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>{{ isset($cliente) ? 'Editar Cliente #' . $cliente->id : 'Novo Cliente' }}</h1>
    
    <div id="mensagem" class="alert" style="display: none;"></div>
    
    <form id="clienteForm" method="POST" action="{{ isset($cliente) ? route('clientes.update', $cliente->id) : route('clientes.store') }}">
        @csrf
        @if(isset($cliente))
            @method('PUT')
        @endif
        
        <div class="mb-3 position-relative">
            <label for="nome" class="form-label">Nome *</label>
            <input type="text" class="form-control" id="nome" name="nome" 
                   value="{{ $cliente->nome ?? '' }}" required autocomplete="off">
            <div id="sugestoes-clientes" class="list-group position-absolute" style="z-index: 1000; width: 100%; display: none;"></div>
        </div>
        
        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" class="form-control" id="telefone" name="telefone" 
                   value="{{ isset($cliente) ? \App\Helpers\FormatarTelefone::formatar($cliente->telefone) : '' }}">
        </div>
        
        <div class="mb-3">
            <label for="observacao" class="form-label">Observação</label>
            <textarea class="form-control" id="observacao" name="observacao" rows="3">{{ $cliente->observacao ?? '' }}</textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Salvar
        </button>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancelar
        </a>
    </form>

    @if(!isset($cliente))
    <hr>
    <div class="mt-4">
        <button id="btnClientesCadastrados" class="btn btn-info">Listar Clientes Cadastrados</button>
        <div id="clientesContainer" style="display: none; margin-top: 20px;">
            <h3>Clientes Cadastrados</h3>
            <table class="table table-striped" id="tabelaClientes">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Observação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/clientes/form.js') }}"></script>
@endpush