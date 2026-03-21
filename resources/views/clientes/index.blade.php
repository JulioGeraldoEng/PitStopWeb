@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/cliente.css') }}">
    <style>
        /* Estilo para o modal */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        .modal-body {
            padding: 25px;
        }
        .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 15px 25px;
        }
    </style>
@endpush

@section('content')
<div class="container">
    <h1>Clientes</h1>
    
    <!-- CARD DE BUSCA -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Buscar Clientes</h5>
            
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="buscar-nome" class="form-label">Nome:</label>
                    <div class="form-group position-relative">
                        <input type="text" class="form-control" id="buscar-nome" placeholder="Digite o nome do cliente">
                        <div id="sugestoes-busca" class="list-group"></div>
                    </div>
                </div>
            
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <button type="button" id="btnBuscarClientes" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" id="btnLimparBusca" class="btn btn-secondary">
                                <i class="fas fa-broom"></i> Limpar
                            </button>
                            <button type="button" id="btnNovoCliente" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovoCliente">
                                <i class="fas fa-plus-circle"></i> Novo
                            </button>
                        </div>
                    </div>
                </div>
            
            </div>
        </div>
    </div>

    <div id="mensagem" class="alert" style="display: none;"></div>

    <!-- TABELA DE CLIENTES -->
    <div id="tabela-cliente" style="display: none;">
        <table class="tabela-cliente">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Observação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="tabela-corpo">
                <!-- Conteúdo será carregado via JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL NOVO CLIENTE -->
<div class="modal fade" id="modalNovoCliente" tabindex="-1" aria-labelledby="modalNovoClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovoClienteLabel">
                    <i class="fas fa-user-plus"></i> Novo Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="clienteForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modal-nome" class="form-label">Nome *</label>
                            <div class="form-group position-relative">
                                <input type="text" class="form-control" id="modal-nome" name="nome" required autocomplete="off">
                                <div id="sugestoes-cliente" class="list-group"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="modal-telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="modal-telefone" name="telefone">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="modal-observacao" class="form-label">Observação</label>
                            <input type="text" class="form-control" id="modal-observacao" name="observacao">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnSalvarCliente">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/clientes/index.js') }}"></script>
@endpush