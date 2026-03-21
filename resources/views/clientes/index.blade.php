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
        
        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Sugestões melhoradas */
        .suggestions-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: none;
        }
        
        .suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .suggestion-item:hover {
            background-color: #f0f0f0;
        }
        
        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* Máscara de telefone */
        .phone-mask {
            font-family: monospace;
        }
        
        /* Mensagem de erro */
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
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
                        <input type="text" class="form-control" id="buscar-nome" 
                               placeholder="Digite o nome do cliente" autocomplete="off">
                        <div id="sugestoes-busca" class="suggestions-list"></div>
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
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Observação</th>
                        <th width="150">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-corpo">
                    <!-- Conteúdo será carregado via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL NOVO/EDITAR CLIENTE -->
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
                    <input type="hidden" id="cliente-id" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modal-nome" class="form-label">Nome *</label>
                            <div class="form-group position-relative">
                                <input type="text" class="form-control" id="modal-nome" name="nome" required autocomplete="off">
                                <div id="sugestoes-cliente" class="suggestions-list"></div>
                                <div id="error-nome" class="error-message"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="modal-telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control phone-mask" id="modal-telefone" name="telefone" 
                                   placeholder="(99) 99999-9999" maxlength="15">
                            <div id="error-telefone" class="error-message"></div>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="modal-observacao" class="form-label">Observação</label>
                            <textarea class="form-control" id="modal-observacao" name="observacao" rows="3"></textarea>
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

<!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o cliente <strong id="cliente-nome-excluir"></strong>?</p>
                <p class="text-danger">⚠️ Esta ação não poderá ser desfeita!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/clientes/index.js') }}"></script>
    <script>
        // Máscara de telefone
        document.getElementById('modal-telefone')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length >= 11) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            } else if (value.length >= 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
            } else if (value.length >= 2) {
                value = value.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
            }
            
            e.target.value = value;
        });
        
        // Limpar formulário ao abrir modal
        document.getElementById('modalNovoCliente')?.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const clienteId = button?.getAttribute('data-id');
            const clienteNome = button?.getAttribute('data-nome');
            const clienteTelefone = button?.getAttribute('data-telefone');
            const clienteObservacao = button?.getAttribute('data-observacao');
            
            const form = document.getElementById('clienteForm');
            const modalTitle = document.getElementById('modalNovoClienteLabel');
            
            if (clienteId) {
                // Modo edição
                document.getElementById('cliente-id').value = clienteId;
                document.getElementById('modal-nome').value = clienteNome || '';
                document.getElementById('modal-telefone').value = clienteTelefone || '';
                document.getElementById('modal-observacao').value = clienteObservacao || '';
                modalTitle.innerHTML = '<i class="fas fa-user-edit"></i> Editar Cliente';
            } else {
                // Modo criação
                form.reset();
                document.getElementById('cliente-id').value = '';
                modalTitle.innerHTML = '<i class="fas fa-user-plus"></i> Novo Cliente';
            }
            
            // Limpar mensagens de erro
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        });
        
        // Validação em tempo real
        document.getElementById('modal-telefone')?.addEventListener('blur', function() {
            const value = this.value.replace(/\D/g, '');
            const errorDiv = document.getElementById('error-telefone');
            
            if (value.length > 0 && value.length !== 10 && value.length !== 11) {
                errorDiv.textContent = 'Telefone inválido. Use (99) 99999-9999 ou (99) 9999-9999';
            } else {
                errorDiv.textContent = '';
            }
        });
        
        // Prevenir envio com Enter
        document.getElementById('clienteForm')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('btnSalvarCliente')?.click();
            }
        });
    </script>
@endpush