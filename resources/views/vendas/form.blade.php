@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/venda.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>{{ isset($venda) ? 'Editar Venda #' . $venda->id : 'Nova Venda' }}</h1>
    
    <div id="mensagem" class="alert" style="display: none;"></div>
    
    <form id="vendaForm" method="POST" action="{{ isset($venda) ? route('vendas.update', $venda->id) : route('vendas.store') }}">
        @csrf
        @if(isset($venda))
            @method('PUT')
        @endif
        
        <!-- Dados do Cliente -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Dados do Cliente</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Cliente -->
                    <div class="col-md-6">
                        <div class="form-group position-relative">
                            <label for="cliente">Cliente:</label>
                            <input type="text" class="form-control" id="cliente" 
                                value="{{ $venda->cliente->nome ?? '' }}" 
                                placeholder="Nome do cliente" autocomplete="off" required>
                            <input type="hidden" id="cliente_id" name="cliente_id" 
                                value="{{ $venda->cliente_id ?? '' }}">
                            <div id="sugestoes-cliente" class="list-group"></div>
                        </div>
                    </div>
                    
                    <!-- Data da Venda com ícone -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="dataVenda" class="form-label">
                                <i class="fas fa-calendar-alt"></i> Data da Venda:
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                <input type="text" class="form-control datepicker-field" id="dataVenda" 
                                    name="data" placeholder="dd/mm/aaaa" maxlength="10"
                                    value="{{ isset($venda) ? date('d/m/Y', strtotime($venda->data)) : '' }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Vencimento com ícone -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="vencimento" class="form-label">
                                <i class="fas fa-calendar-check"></i> Vencimento:
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                <input type="text" class="form-control datepicker-field" id="vencimento" 
                                    name="data_vencimento" placeholder="dd/mm/aaaa" maxlength="10"
                                    value="{{ isset($venda) && $venda->data_vencimento ? date('d/m/Y', strtotime($venda->data_vencimento)) : '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Itens da Venda -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-boxes"></i> Itens da Venda</h5>
            </div>
            <div class="card-body">
                <!-- Linha para adicionar novo item -->
                <div class="row mb-3">
                    <div class="col-md-5">
                        <div class="form-group position-relative">
                            <label>Produto:</label>
                            <input type="text" class="form-control" id="produto" placeholder="Nome do produto" autocomplete="off">
                            <input type="hidden" id="produto_id">
                            <input type="hidden" id="preco_unitario">
                            <div id="sugestoes-produto" class="list-group"></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Quantidade:</label>
                            <input type="number" class="form-control" id="quantidade" min="1" value="1">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Preço Unitário:</label>
                            <input type="text" class="form-control" id="preco_display" readonly placeholder="R$ 0,00">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-success w-100" id="btnAdicionarItem">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>
                </div>

                <!-- Tabela de Itens -->
                <div class="table-responsive mt-3">
                    <table class="table table-bordered" id="tabela-itens">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Preço Unit.</th>
                                <th>Subtotal</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="itens-corpo">
                            <!-- Itens existentes serão carregados aqui via JavaScript -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">TOTAL:</th>
                                <th id="total-venda">R$ 0,00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="d-flex justify-content-center gap-3 mb-4">
            <button type="submit" class="btn btn-primary btn-lg" id="btnSalvar">
                <i class="fas fa-save"></i> {{ isset($venda) ? 'Atualizar Venda' : 'Finalizar Venda' }}
            </button>
            <a href="{{ route('vendas.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>

        
    </form>
</div>
@endsection

@push('scripts')
    <script>
        // Dados da venda para o JavaScript
        window.dadosVenda = {
            clienteId: '{{ $venda->cliente_id ?? '' }}',
            clienteNome: '{{ $venda->cliente->nome ?? '' }}',
            data: '{{ isset($venda) ? date('d/m/Y', strtotime($venda->data)) : '' }}',
            vencimento: '{{ isset($venda) && $venda->data_vencimento ? date('d/m/Y', strtotime($venda->data_vencimento)) : '' }}',
            itens: [
                @if(isset($venda) && $venda->itens->count() > 0)
                    @foreach($venda->itens as $item)
                    {
                        produto_id: {{ $item->produto_id }},
                        nome: '{{ $item->nome_produto }}',
                        quantidade: {{ $item->quantidade }},
                        preco_unitario: {{ $item->preco_unitario }},
                        id: {{ $item->id }}
                    },
                    @endforeach
                @endif
            ]
        };
        console.log('Dados da venda:', window.dadosVenda); // Para debug
    </script>
    <script src="{{ asset('assets/js/vendas/form.js') }}"></script>
@endpush