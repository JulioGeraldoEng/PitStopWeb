@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/cliente.css') }}">
    <style>
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .input-error {
            border-color: #dc3545;
        }
        
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
        
        .phone-mask {
            font-family: monospace;
        }
        
        .btn-loading {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>
@endpush

@section('content')
<div class="container">
    <h1>{{ isset($cliente) ? 'Editar Cliente #' . $cliente->id : 'Novo Cliente' }}</h1>
    
    <div id="mensagem" class="alert" style="display: none;"></div>
    
    <form id="clienteForm" method="POST" action="{{ isset($cliente) ? route('clientes.update', $cliente->id) : route('clientes.store') }}">
        @csrf
        @if(isset($cliente))
            @method('PUT')
        @endif
        
        <div class="mb-3 position-relative">
            <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nome" name="nome" 
                   value="{{ old('nome', $cliente->nome ?? '') }}" required autocomplete="off">
            <div id="sugestoes-clientes" class="suggestions-list"></div>
            <div id="error-nome" class="error-message"></div>
        </div>
        
        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" class="form-control phone-mask" id="telefone" name="telefone" 
                   value="{{ old('telefone', isset($cliente) ? $cliente->telefone : '') }}"
                   placeholder="(99) 99999-9999" maxlength="15">
            <small class="form-text text-muted">Formato: (99) 99999-9999 ou (99) 9999-9999</small>
            <div id="error-telefone" class="error-message"></div>
        </div>
        
        <div class="mb-3">
            <label for="observacao" class="form-label">Observação</label>
            <textarea class="form-control" id="observacao" name="observacao" rows="3">{{ old('observacao', $cliente->observacao ?? '') }}</textarea>
        </div>
        
        <div class="mb-3">
            <button type="submit" class="btn btn-primary" id="btnSalvar">
                <i class="fas fa-save"></i> Salvar
            </button>
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>

    @if(!isset($cliente))
    <hr>
    <div class="mt-4">
        <button id="btnClientesCadastrados" class="btn btn-info">
            <i class="fas fa-list"></i> Listar Clientes Cadastrados
        </button>
        <div id="clientesContainer" style="display: none; margin-top: 20px;">
            <h3>Clientes Cadastrados</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tabelaClientes">
                    <thead class="table-dark">
                         Waiter
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Telefone</th>
                            <th>Observação</th>
                            <th width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center">Carregando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal de Confirmação para Edição Rápida -->
<div class="modal fade" id="modalConfirmarEdicao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Edição
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Deseja editar o cliente <strong id="cliente-nome-editar"></strong>?</p>
                <p>Você será redirecionado para o formulário de edição.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <a href="#" id="btnConfirmarEdicao" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação para Exclusão -->
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
    <script>
        // Máscara de telefone
        const phoneInput = document.getElementById('telefone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
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
            
            // Validação em tempo real
            phoneInput.addEventListener('blur', function() {
                const value = this.value.replace(/\D/g, '');
                const errorDiv = document.getElementById('error-telefone');
                
                if (value.length > 0 && value.length !== 10 && value.length !== 11) {
                    errorDiv.textContent = 'Telefone inválido. Use (99) 99999-9999 ou (99) 9999-9999';
                    this.classList.add('input-error');
                } else {
                    errorDiv.textContent = '';
                    this.classList.remove('input-error');
                }
            });
        }
        
        // Validação do formulário antes de enviar
        document.getElementById('clienteForm')?.addEventListener('submit', function(e) {
            let hasError = false;
            
            // Validar nome
            const nome = document.getElementById('nome');
            if (!nome.value.trim()) {
                document.getElementById('error-nome').textContent = 'Nome é obrigatório';
                nome.classList.add('input-error');
                hasError = true;
            } else {
                document.getElementById('error-nome').textContent = '';
                nome.classList.remove('input-error');
            }
            
            // Validar telefone (se preenchido)
            const telefone = document.getElementById('telefone');
            const telefoneValue = telefone.value.replace(/\D/g, '');
            if (telefoneValue.length > 0 && telefoneValue.length !== 10 && telefoneValue.length !== 11) {
                document.getElementById('error-telefone').textContent = 'Telefone inválido';
                telefone.classList.add('input-error');
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
                // Mostrar mensagem de erro
                const msgDiv = document.getElementById('mensagem');
                msgDiv.className = 'alert alert-danger';
                msgDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Por favor, corrija os erros no formulário.';
                msgDiv.style.display = 'block';
                
                // Scroll para o topo
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Auto-esconder após 5 segundos
                setTimeout(() => {
                    msgDiv.style.display = 'none';
                }, 5000);
            } else {
                // Mostrar loading no botão
                const btn = document.getElementById('btnSalvar');
                btn.classList.add('btn-loading');
                btn.innerHTML = '<span class="loading-spinner"></span> Salvando...';
            }
        });
        
        // Limpar mensagens de erro ao digitar
        document.getElementById('nome')?.addEventListener('input', function() {
            this.classList.remove('input-error');
            document.getElementById('error-nome').textContent = '';
        });
        
        document.getElementById('telefone')?.addEventListener('input', function() {
            this.classList.remove('input-error');
            document.getElementById('error-telefone').textContent = '';
        });
        
        // Função para listar clientes (se existir a página de criação)
        const btnListar = document.getElementById('btnClientesCadastrados');
        if (btnListar) {
            btnListar.addEventListener('click', function() {
                const container = document.getElementById('clientesContainer');
                const tabela = document.getElementById('tabelaClientes').querySelector('tbody');
                
                if (container.style.display === 'none') {
                    container.style.display = 'block';
                    tabela.innerHTML = '<tr><td colspan="5" class="text-center"><div class="loading-spinner"></div> Carregando...</td></tr>';
                    
                    // CORREÇÃO 1: Substituir route() por URL direta
                    fetch('/api/clientes')
                        .then(response => response.json())
                        .then(clientes => {
                            if (clientes.length === 0) {
                                tabela.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum cliente cadastrado</td></tr>';
                                return;
                            }
                            
                            let html = '';
                            clientes.forEach(cliente => {
                                html += `
                                    <tr>
                                        <td>${cliente.id}</td>
                                        <td>${escapeHtml(cliente.nome)}</td>
                                        <td>${formatPhone(cliente.telefone) || '-'}</td>
                                        <td>${escapeHtml(cliente.observacao) || '-'}</td>
                                        <td>
                                            <a href="/clientes/${cliente.id}/edit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <button class="btn btn-sm btn-danger btn-excluir" data-id="${cliente.id}" data-nome="${escapeHtml(cliente.nome)}">
                                                <i class="fas fa-trash"></i> Excluir
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                            tabela.innerHTML = html;
                            
                            // Adicionar evento de exclusão
                            document.querySelectorAll('.btn-excluir').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const id = this.dataset.id;
                                    const nome = this.dataset.nome;
                                    document.getElementById('cliente-nome-excluir').textContent = nome;
                                    
                                    const modalExcluir = new bootstrap.Modal(document.getElementById('modalConfirmarExclusao'));
                                    modalExcluir.show();
                                    
                                    document.getElementById('btnConfirmarExclusao').onclick = function() {
                                        // CORREÇÃO 2: URL correta para exclusão
                                        fetch(`/api/clientes/${id}`, {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Content-Type': 'application/json'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                modalExcluir.hide();
                                                // Recarregar lista
                                                btnListar.click();
                                                
                                                // Mostrar mensagem de sucesso
                                                const msgDiv = document.getElementById('mensagem');
                                                msgDiv.className = 'alert alert-success';
                                                msgDiv.innerHTML = '<i class="fas fa-check-circle"></i> Cliente excluído com sucesso!';
                                                msgDiv.style.display = 'block';
                                                setTimeout(() => msgDiv.style.display = 'none', 3000);
                                            } else {
                                                alert('Erro ao excluir: ' + (data.message || 'Erro desconhecido'));
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Erro:', error);
                                            alert('Erro ao excluir cliente');
                                        });
                                    };
                                });
                            });
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            tabela.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erro ao carregar clientes</td></tr>';
                        });
                } else {
                    container.style.display = 'none';
                }
            });
        }
        
        // Funções auxiliares
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatPhone(phone) {
            if (!phone) return '';
            const numbers = phone.replace(/\D/g, '');
            if (numbers.length === 11) {
                return numbers.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (numbers.length === 10) {
                return numbers.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            }
            return phone;
        }
        
        // Loading spinner CSS
        const style = document.createElement('style');
        style.textContent = `
            .loading-spinner {
                display: inline-block;
                width: 16px;
                height: 16px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-right: 8px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
    
    @if(isset($cliente))
    <script>
        // Script específico para edição
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar mensagem de sucesso se houver na sessão
            @if(session('success'))
                const msgDiv = document.getElementById('mensagem');
                msgDiv.className = 'alert alert-success';
                msgDiv.innerHTML = '<i class="fas fa-check-circle"></i> {{ session('success') }}';
                msgDiv.style.display = 'block';
                setTimeout(() => msgDiv.style.display = 'none', 3000);
            @endif
            
            // Mostrar mensagem de erro se houver na sessão
            @if(session('error'))
                const msgDiv = document.getElementById('mensagem');
                msgDiv.className = 'alert alert-danger';
                msgDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> {{ session('error') }}';
                msgDiv.style.display = 'block';
                setTimeout(() => msgDiv.style.display = 'none', 5000);
            @endif
        });
    </script>
    @endif
@endpush