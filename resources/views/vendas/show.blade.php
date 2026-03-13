@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/venda.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detalhes da Venda #{{ $venda->id }}</h1>
        <a href="{{ route('vendas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
    
    <div class="row">
        <!-- Card de Informações da Venda -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Informações da Venda</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">ID da Venda:</th>
                            <td>#{{ $venda->id }}</td>
                        </tr>
                        <tr>
                            <th>Data da Venda:</th>
                            <td>{{ date('d/m/Y', strtotime($venda->data)) }}</td>
                        </tr>
                        <tr>
                            <th>Vencimento:</th>
                            <td>{{ $venda->data_vencimento ? date('d/m/Y', strtotime($venda->data_vencimento)) : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Total da Venda:</th>
                            <td class="font-weight-bold text-success">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Card de Informações do Cliente -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Informações do Cliente</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Nome:</th>
                            <td>{{ $venda->cliente->nome }}</td>
                        </tr>
                        <tr>
                            <th>Telefone:</th>
                            <td>{{ $venda->cliente->telefone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Observação:</th>
                            <td>{{ $venda->cliente->observacao ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card de Itens da Venda -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-boxes"></i> Itens da Venda</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($venda->itens as $item)
                        <tr>
                            <td>{{ $item->nome_produto }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td>R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item->quantidade * $item->preco_unitario, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Nenhum item encontrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="3" class="text-right">TOTAL:</td>
                            <td>R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Card de Status do Recebimento -->
    @if($venda->recebimento)
    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Status do Recebimento</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th>Status:</th>
                            <td>
                                @php
                                    $status = $venda->recebimento->status;
                                    $badgeClass = match($status) {
                                        'pago' => 'badge-success',
                                        'pendente' => 'badge-warning',
                                        'atrasado' => 'badge-danger',
                                        'cancelado' => 'badge-secondary',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} p-2">{{ strtoupper($status) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Valor Total:</th>
                            <td>R$ {{ number_format($venda->recebimento->valor_total, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th>Valor Pago:</th>
                            <td>R$ {{ number_format($venda->recebimento->valor_pago, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Data Pagamento:</th>
                            <td>{{ $venda->recebimento->data_pagamento ? date('d/m/Y', strtotime($venda->recebimento->data_pagamento)) : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Forma Pagamento:</th>
                            <td>{{ $venda->recebimento->forma_pagamento ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection