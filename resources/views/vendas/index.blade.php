@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/venda.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>Vendas</h1>
    
    <!-- CARD DE FILTROS -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filtrar Vendas</h5>
            
            <form id="filtrosForm">
                <div class="row g-3">
                    <!-- Cliente e Status na primeira linha -->
                    <div class="col-md-6">
                        <div class="form-group position-relative">  <!-- ADICIONADO position-relative AQUI -->
                            <label for="cliente" class="form-label">Cliente:</label>
                            <input type="text" class="form-control" id="cliente" placeholder="Nome do cliente" autocomplete="off">
                            <input type="hidden" id="cliente_id">
                            <div id="sugestoes-cliente" class="list-group"></div>  <!-- REMOVIDO style inline -->
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status:</label>
                        <select class="form-control" id="status">
                            <option value="">Todos</option>
                            <option value="pendente">Pendente</option>
                            <option value="pago">Pago</option>
                            <option value="atrasado">Atrasado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
                
                <!-- Linha de Datas usando o COMPONENTE REUTILIZÁVEL -->
                <div class="row mt-3">
                    <!-- Data Inicial e Data Final -->
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <x-datepicker id="dataInicio" label="Data Inicial" />
                            </div>
                            
                            <div class="col-md-6">
                                <x-datepicker id="dataFim" label="Data Final" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vencimento Inicial e Vencimento Final -->
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <x-datepicker id="vencimentoInicio" label="Vencimento Inicial" icon="fa-calendar-check" />
                            </div>                       
                            <div class="col-md-6">
                                <x-datepicker id="vencimentoFim" label="Vencimento Final" icon="fa-calendar-check" />
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- BOTÕES -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="{{ route('vendas.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Nova Venda
                            </a>
                            <button type="button" id="btnBuscar" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" id="btnLimpar" class="btn btn-secondary">
                                <i class="fas fa-broom"></i> Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <!-- TABELA DE VENDAS -->
    <div id="tabela-venda" style="display: none;">
        <table class="tabela-venda">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Data da Venda</th>
                    <th>Vencimento</th>
                    <th>Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="tabela-corpo">
                @forelse($vendas as $venda)
                <tr>
                    <td>{{ $venda->cliente->nome }}</td>
                    <td>{{ date('d/m/Y', strtotime($venda->data)) }}</td>
                    <td>{{ $venda->data_vencimento ? date('d/m/Y', strtotime($venda->data_vencimento)) : '-' }}</td>
                    <td>R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                    <td>
                        <div class="acao-container">
                            <a href="{{ route('vendas.show', $venda->id) }}" class="btn-ver">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="{{ route('vendas.edit', $venda->id) }}" class="btn-editar">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="{{ route('vendas.destroy', $venda->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-excluir" onclick="return confirm('Tem certeza?')">
                                    <i class="fas fa-trash-alt"></i> Excluir
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">Nenhuma venda cadastrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/vendas/index.js') }}"></script>
@endpush