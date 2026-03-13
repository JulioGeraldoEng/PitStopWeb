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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cliente">Cliente:</label>
                            <input type="text" class="form-control" id="cliente" 
                                   value="{{ $venda->cliente->nome ?? '' }}" 
                                   placeholder="Nome do cliente" autocomplete="off" required>
                            <input type="hidden" id="cliente_id" name="cliente_id" 
                                   value="{{ $venda->cliente_id ?? '' }}">
                            <div id="sugestoes-cliente" class="list-group position-absolute" 
                                 style="z-index: 1000; width: 90%; display: none;"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="dataVenda">Data da Venda:</label>
                            <input type="text" class="form-control datepicker-field" id="dataVenda" 
                                   name="data" placeholder="dd/mm/aaaa" maxlength="10"
                                   value="{{ isset($venda) ? date('d/m/Y', strtotime($venda->data)) : '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="vencimento">Vencimento:</label>
                            <input type="text" class="form-control datepicker-field" id="vencimento" 
                                   name="data_vencimento" placeholder="dd/mm/aaaa" maxlength="10"
                                   value="{{ isset($venda) && $venda->data_vencimento ? date('d/m/Y', strtotime($venda->data_vencimento)) : '' }}">
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
                        <label>Produto:</label>
                        <input type="text" class="form-control" id="produto" placeholder="Nome do produto" autocomplete="off">
                        <input type="hidden" id="produto_id">
                        <input type="hidden" id="preco_unitario">
                        <div id="sugestoes-produto" class="list-group position-absolute" style="z-index: 1000; width: 90%; display: none;"></div>
                    </div>
                    <div class="col-md-2">
                        <label>Quantidade:</label>
                        <input type="number" class="form-control" id="quantidade" min="1" value="1">
                    </div>
                    <div class="col-md-3">
                        <label>Preço Unitário:</label>
                        <input type="text" class="form-control" id="preco_display" readonly placeholder="R$ 0,00">
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
// ===================== VARIÁVEIS GLOBAIS =====================
let itensVenda = [];
let clienteSelecionadoId = '{{ $venda->cliente_id ?? '' }}';
let produtoSelecionadoId = null;
let precoSelecionado = null;

// Carregar itens existentes se for edição
@if(isset($venda) && $venda->itens->count() > 0)
    itensVenda = [
        @foreach($venda->itens as $item)
        {
            produto_id: {{ $item->produto_id }},
            nome: '{{ $item->nome_produto }}',
            quantidade: {{ $item->quantidade }},
            preco_unitario: {{ $item->preco_unitario }},
            id: {{ $item->id }}
        },
        @endforeach
    ];
@endif

// ===================== FUNÇÕES AUXILIARES =====================
function formatarMoeda(valor) {
    return parseFloat(valor).toFixed(2).replace('.', ',');
}

function calcularTotal() {
    let total = itensVenda.reduce((sum, item) => sum + (item.quantidade * item.preco_unitario), 0);
    document.getElementById('total-venda').textContent = `R$ ${formatarMoeda(total)}`;
}

function atualizarTabelaItens() {
    const tbody = document.getElementById('itens-corpo');
    tbody.innerHTML = '';

    itensVenda.forEach((item, index) => {
        const subtotal = item.quantidade * item.preco_unitario;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.nome}</td>
            <td>${item.quantidade}</td>
            <td>R$ ${formatarMoeda(item.preco_unitario)}</td>
            <td>R$ ${formatarMoeda(subtotal)}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removerItem(${index})">
                    <i class="fas fa-trash"></i> Remover
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    calcularTotal();
}

function removerItem(index) {
    itensVenda.splice(index, 1);
    atualizarTabelaItens();
    mostrarMensagem('Item removido da venda.', 'success');
}

function mostrarMensagem(texto, tipo) {
    const mensagem = document.getElementById('mensagem');
    mensagem.textContent = texto;
    mensagem.className = `alert alert-${tipo}`;
    mensagem.style.display = 'block';
    setTimeout(() => {
        mensagem.style.display = 'none';
    }, 3000);
}

// ===================== AUTOCOMPLETE CLIENTE =====================
function configurarAutocompleteCliente() {
    const inputCliente = document.getElementById('cliente');
    const sugestoes = document.getElementById('sugestoes-cliente');
    
    inputCliente.addEventListener('keyup', async () => {
        const termo = inputCliente.value.trim();
        sugestoes.innerHTML = '';
        sugestoes.style.display = 'none';

        if (termo.length < 2) return;

        try {
            const response = await fetch(`/api/clientes/busca?termo=${termo}`);
            const clientes = await response.json();

            if (clientes.length === 0) return;

            clientes.forEach(cliente => {
                const div = document.createElement('a');
                div.href = '#';
                div.className = 'list-group-item list-group-item-action';
                div.textContent = cliente.nome;
                div.onclick = (e) => {
                    e.preventDefault();
                    inputCliente.value = cliente.nome;
                    document.getElementById('cliente_id').value = cliente.id;
                    clienteSelecionadoId = cliente.id;
                    sugestoes.style.display = 'none';
                };
                sugestoes.appendChild(div);
            });

            sugestoes.style.display = 'block';
        } catch (error) {
            console.error('Erro ao buscar clientes:', error);
        }
    });

    document.addEventListener('click', (e) => {
        if (!sugestoes.contains(e.target) && e.target !== inputCliente) {
            sugestoes.style.display = 'none';
        }
    });
}

// ===================== AUTOCOMPLETE PRODUTO =====================
function configurarAutocompleteProduto() {
    const inputProduto = document.getElementById('produto');
    const sugestoes = document.getElementById('sugestoes-produto');
    const precoDisplay = document.getElementById('preco_display');
    
    inputProduto.addEventListener('keyup', async () => {
        const termo = inputProduto.value.trim();
        sugestoes.innerHTML = '';
        sugestoes.style.display = 'none';

        if (termo.length < 2) return;

        try {
            const response = await fetch(`/api/produtos/busca?termo=${termo}`);
            const produtos = await response.json();

            if (produtos.length === 0) return;

            produtos.forEach(produto => {
                const div = document.createElement('a');
                div.href = '#';
                div.className = 'list-group-item list-group-item-action';
                div.textContent = `${produto.nome} - R$ ${parseFloat(produto.preco).toFixed(2).replace('.', ',')}`;
                div.onclick = (e) => {
                    e.preventDefault();
                    inputProduto.value = produto.nome;
                    document.getElementById('produto_id').value = produto.id;
                    document.getElementById('preco_unitario').value = produto.preco;
                    precoSelecionado = parseFloat(produto.preco);
                    precoDisplay.value = `R$ ${parseFloat(produto.preco).toFixed(2).replace('.', ',')}`;
                    sugestoes.style.display = 'none';
                };
                sugestoes.appendChild(div);
            });

            sugestoes.style.display = 'block';
        } catch (error) {
            console.error('Erro ao buscar produtos:', error);
        }
    });

    document.addEventListener('click', (e) => {
        if (!sugestoes.contains(e.target) && e.target !== inputProduto) {
            sugestoes.style.display = 'none';
        }
    });
}

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    configurarAutocompleteCliente();
    configurarAutocompleteProduto();
    
    // Carregar itens existentes na tabela
    atualizarTabelaItens();
    
    // Evento do botão adicionar item
    document.getElementById('btnAdicionarItem').addEventListener('click', () => {
        const produtoId = document.getElementById('produto_id').value;
        const produtoNome = document.getElementById('produto').value.trim();
        const quantidade = parseInt(document.getElementById('quantidade').value);
        const preco = parseFloat(document.getElementById('preco_unitario').value);

        if (!produtoId || !produtoNome || isNaN(quantidade) || quantidade < 1 || isNaN(preco) || preco <= 0) {
            mostrarMensagem('Selecione um produto e informe quantidade válida.', 'danger');
            return;
        }

        // Verificar se produto já existe na lista
        const itemExistente = itensVenda.findIndex(item => item.produto_id === parseInt(produtoId));
        
        if (itemExistente !== -1) {
            // Se existe, pergunta se quer substituir
            if (confirm('Este produto já está na lista. Deseja substituir a quantidade?')) {
                itensVenda[itemExistente].quantidade = quantidade;
                itensVenda[itemExistente].preco_unitario = preco;
            }
        } else {
            // Se não existe, adiciona novo
            itensVenda.push({
                produto_id: parseInt(produtoId),
                nome: produtoNome,
                quantidade: quantidade,
                preco_unitario: preco
            });
        }

        atualizarTabelaItens();
        mostrarMensagem('Item adicionado com sucesso!', 'success');

        // Limpar campos do produto
        document.getElementById('produto').value = '';
        document.getElementById('produto_id').value = '';
        document.getElementById('preco_unitario').value = '';
        document.getElementById('preco_display').value = '';
        document.getElementById('quantidade').value = '1';
        produtoSelecionadoId = null;
        precoSelecionado = null;
    });

    // Validação antes de enviar o formulário
    document.getElementById('vendaForm').addEventListener('submit', (e) => {
        const clienteId = document.getElementById('cliente_id').value;
        
        if (!clienteId) {
            e.preventDefault();
            mostrarMensagem('Selecione um cliente.', 'danger');
            return;
        }

        if (itensVenda.length === 0) {
            e.preventDefault();
            mostrarMensagem('Adicione pelo menos um item à venda.', 'danger');
            return;
        }

        // Adicionar os itens ao formulário como campos hidden
        itensVenda.forEach((item, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `itens[${index}][produto_id]`;
            input.value = item.produto_id;
            document.getElementById('vendaForm').appendChild(input);

            const input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = `itens[${index}][nome]`;
            input2.value = item.nome;
            document.getElementById('vendaForm').appendChild(input2);

            const input3 = document.createElement('input');
            input3.type = 'hidden';
            input3.name = `itens[${index}][quantidade]`;
            input3.value = item.quantidade;
            document.getElementById('vendaForm').appendChild(input3);

            const input4 = document.createElement('input');
            input4.type = 'hidden';
            input4.name = `itens[${index}][preco_unitario]`;
            input4.value = item.preco_unitario;
            document.getElementById('vendaForm').appendChild(input4);
        });
    });
});
</script>
@endpush