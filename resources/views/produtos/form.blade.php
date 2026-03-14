@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produto.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>{{ isset($produto) ? 'Editar' : 'Novo' }} Produto</h1>
    
    <div id="mensagem" class="alert" style="display: none;"></div>
    
    <form id="produtoForm" method="POST">
        @csrf
        @if(isset($produto))
            @method('PUT')
        @endif
        
        <div class="mb-3 position-relative">
            <label for="nome" class="form-label">Nome *</label>
            <input type="text" class="form-control" id="nome" name="nome" 
                   value="{{ $produto->nome ?? '' }}" required autocomplete="off">
            <div id="sugestoes-produtos" class="list-group position-absolute" style="z-index: 1000; width: 100%; display: none;"></div>
        </div>
        
        <div class="mb-3">
            <label for="preco" class="form-label">Preço *</label>
            <input type="number" class="form-control" id="preco" name="preco" 
                   value="{{ $produto->preco ?? '' }}" step="0.01" min="0" required>
        </div>
        
        <div class="mb-3">
            <label for="quantidade" class="form-label">Quantidade *</label>
            <input type="number" class="form-control" id="quantidade" name="quantidade" 
                   value="{{ $produto->quantidade ?? 0 }}" min="0" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>

    @if(!isset($produto))
    <hr>
    <div class="mt-4">
        <button id="btnProdutosCadastrados" class="btn btn-info">Listar Produtos Cadastrados</button>
        <div id="produtosContainer" style="display: none; margin-top: 20px;">
            <h3>Produtos Cadastrados</h3>
            <table class="table table-striped" id="tabelaProdutos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
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
    <script src="{{ asset('assets/js/produtos/form.js') }}"></script>
@endpush