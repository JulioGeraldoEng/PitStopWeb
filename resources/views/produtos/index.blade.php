@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produto.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>Produtos</h1>
    <a href="{{ route('produtos.create') }}" class="btn btn-primary mb-3">Novo Produto</a>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Quantidade</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $produto)
            <tr>
                <td>{{ $produto->id }}</td>
                <td>{{ $produto->nome }}</td>
                <td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                <td>{{ $produto->quantidade }}</td>
                <td>
                    <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('produtos.destroy', $produto->id) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/produtos/index.js') }}"></script>
@endpush