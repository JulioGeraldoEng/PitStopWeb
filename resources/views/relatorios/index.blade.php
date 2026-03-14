@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/relatorio.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>Relatório de Vendas</h1>
    
    <!-- CARD DE FILTROS -->
    <div class="card mb-4">  <!-- Corrigido: mb-6 não existe no Bootstrap -->
        <div class="card-body">
            <h5 class="card-title">Filtrar Vendas</h5>
            
            <form id="filtrosForm">
                <!-- Primeira linha: Cliente e Status -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="cliente" class="form-label">Cliente:</label>
                        <div class="form-group position-relative">
                            <input type="text" class="form-control" id="cliente" placeholder="Nome do cliente" autocomplete="off">
                            <input type="hidden" id="cliente_id">
                            <div id="sugestoes-cliente" class="list-group"></div>
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
                
                <!-- Datas lado a lado -->
                <div class="row mt-3">
                    <!-- Datas da Venda -->
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
                    
                    <!-- Datas de Vencimento -->
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
                
                <!-- Botões -->
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
                            <button type="button" id="btnExportarPDF" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button type="button" id="btnCompartilharWhatsApp" class="btn btn-whatsapp">
                                <i class="fab fa-whatsapp"></i> Compartilhar WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="mensagem" class="alert" style="display: none;"></div>

    <!-- TABELA DE RESULTADOS -->
    <div id="tabela-relatorio" style="display: none;">
        <h3>Resultados</h3>
        <div class="table-responsive">
            <table class="tabela-venda" id="tabela-vendas">  <!-- Corrigido: usar classe personalizada -->
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Data Venda</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                        <th>Produto</th>
                        <th>Qtd</th>
                        <th>Preço Unit.</th>
                    </tr>
                </thead>
                <tbody id="tabela-corpo"></tbody>
            </table>
        </div>

        <!-- TOTAL GERAL FORA DA TABELA -->
        <div id="total-geral-container" class="text-end mt-4 p-3 rounded" style="display: none; background-color: #d4edda; border-left: 5px solid #28a745;">
            <h4 id="total-geral" class="text-success fw-bold mb-0"></h4>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/relatorios/index.js') }}"></script>
@endpush