@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produto.css') }}">
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
    <h1>Produtos</h1>
    
    <!-- CARD DE BUSCA -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Buscar Produtos</h5>
            
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="buscar-nome" class="form-label">Nome:</label>
                    <div class="form-group position-relative">
                        <input type="text" class="form-control" id="buscar-nome" placeholder="Digite o nome do produto">
                        <div id="sugestoes-busca" class="list-group"></div>
                    </div>
                </div>
            
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <button type="button" id="btnBuscarProdutos" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" id="btnLimparBusca" class="btn btn-secondary">
                                <i class="fas fa-broom"></i> Limpar
                            </button>
                            <button type="button" id="btnNovoProduto" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovoProduto">
                                <i class="fas fa-plus-circle"></i> Novo
                            </button>
                        </div>
                    </div>
                </div>
            
            </div>
        </div>
    </div>

    <div id="mensagem" class="alert" style="display: none;"></div>

    <!-- TABELA DE PRODUTOS -->
    <div id="tabela-produto" style="display: none;">
        <table class="tabela-produto">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Quantidade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="tabela-corpo">
                <!-- Conteúdo será carregado via JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL NOVO PRODUTO -->
<div class="modal fade" id="modalNovoProduto" tabindex="-1" aria-labelledby="modalNovoProdutoLabel" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovoProdutoLabel">
                    <i class="fas fa-box"></i> Novo Produto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="produtoForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modal-nome" class="form-label">Nome *</label>
                            <div class="form-group position-relative">
                                <input type="text" class="form-control" id="modal-nome" name="nome" required autocomplete="off">
                                <div id="sugestoes-produto" class="list-group"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="modal-preco" class="form-label">Preço (R$) *</label>
                            <input type="text" class="form-control" id="modal-preco" name="preco" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="modal-quantidade" class="form-label">Quantidade *</label>
                            <input type="number" class="form-control" id="modal-quantidade" name="quantidade" min="0" value="0" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btnLimparModal">
                    <i class="fas fa-broom"></i> Limpar
                </button>
                <button type="button" class="btn btn-primary" id="btnSalvarProduto">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/produtos/index.js') }}"></script>
@endpush