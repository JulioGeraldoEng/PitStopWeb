@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/recebimento.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/recebimentos/index.js') }}"></script>
@endpush

@section('content')
<div class="container">
    <h1>Recebimentos</h1>
    
    <!-- CARD DE FILTROS -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filtrar Recebimentos</h5>
            
            <form id="filtrosForm">
                <div class="row g-3">
                    <!-- Cliente -->
                    <div class="col-md-6">
                        <div class="form-group position-relative"> 
                                <label for="cliente" class="form-label">Cliente:</label>
                                <input type="text" class="form-control" id="cliente" placeholder="Nome do cliente" autocomplete="off">
                                <div id="sugestoes-cliente" class="list-group position-absolute" style="z-index: 1000; width: 90%; display: none;"></div>
                    
                        </div>
                    </div>
                    
                    <!-- Status -->
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
                
                <!-- Linha de Datas -->
                <div class="row mt-3">
                    <!-- Data da Venda -->
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <x-datepicker id="dataVendaInicio" label="Data Venda Inicial" />
                            </div>
                            <div class="col-md-6">
                                <x-datepicker id="dataVendaFim" label="Data Venda Final" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vencimento -->
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
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <button type="button" id="btnBuscar" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" id="btnLimpar" class="btn btn-secondary">
                                <i class="fas fa-broom"></i> Limpar
                            </button>
                            <a href="{{ route('vendas.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Nova Venda
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="mensagem" class="alert" style="display: none;"></div>

    <!-- TABELA DE RECEBIMENTOS -->
    <div id="tabela-recebimentos" style="display: none;">
        <h3>Contas a Receber</h3>
        <div class="table-responsive">
            <table class="tabela-recebimento">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Data Venda</th>
                        <th>Vencimento</th>
                        <th>Valor Total</th>
                        <th>Valor Pago</th>
                        <th>Data Pagamento</th>
                        <th>Status</th>
                        <th>Forma Pagamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-corpo"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL DE EDIÇÃO DE RECEBIMENTO -->
<div class="modal fade" id="modalEdicaoRecebimento" tabindex="-1" aria-labelledby="modalEdicaoRecebimentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEdicaoRecebimentoLabel">
                    <i class="fas fa-edit"></i> Editar Recebimento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Informações fixas (não editáveis) -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <i class="fas fa-info-circle"></i> Informações da Venda
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Venda:</th>
                                        <td id="modal-venda-id"></td>
                                    </tr>
                                    <tr>
                                        <th>Cliente:</th>
                                        <td id="modal-cliente"></td>
                                    </tr>
                                    <tr>
                                        <th>Data Venda:</th>
                                        <td id="modal-data-venda"></td>
                                    </tr>
                                    <tr>
                                        <th>Valor Total:</th>
                                        <td id="modal-valor-total"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campos editáveis -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-pen"></i> Dados do Pagamento
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Status:</label>
                                    <select class="form-control" id="modal-status">
                                        <option value="pendente">⏳ Pendente</option>
                                        <option value="pago">✅ Pago</option>
                                        <option value="atrasado">⚠️ Atrasado</option>
                                        <option value="cancelado">❌ Cancelado</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Valor Pago:</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="text" class="form-control" id="modal-valor-pago" value="0,00">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Data Pagamento:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                        <input type="text" class="form-control datepicker-field" id="modal-data-pagamento" placeholder="dd/mm/aaaa">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Forma Pagamento:</label>
                                    <select class="form-control" id="modal-forma-pagamento">
                                        <option value="">Selecione</option>
                                        <option value="dinheiro">💵 Dinheiro</option>
                                        <option value="cartao_credito">💳 Crédito</option>
                                        <option value="cartao_debito">💳 Débito</option>
                                        <option value="pix">📱 PIX</option>
                                        <option value="boleto">📄 Boleto</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btn-salvar-modal">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </div>
    </div>
</div>
@endsection