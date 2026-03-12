@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/venda.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>Vendas</h1>
    <a href="{{ route('vendas.create') }}" class="btn btn-primary mb-3">Nova Venda</a>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Data</th>
                <th>Vencimento</th>
                <th>Total</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas as $venda)
            <tr>
                <td>{{ $venda->id }}</td>
                <td>{{ $venda->cliente->nome }}</td>
                <td>{{ date('d/m/Y', strtotime($venda->data)) }}</td>
                <td>{{ $venda->data_vencimento ? date('d/m/Y', strtotime($venda->data_vencimento)) : '-' }}</td>
                <td>R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                <td>
                    <a href="{{ route('vendas.show', $venda->id) }}" class="btn btn-sm btn-info">Ver</a>
                    <a href="{{ route('vendas.edit', $venda->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('vendas.destroy', $venda->id) }}" method="POST" style="display:inline">
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