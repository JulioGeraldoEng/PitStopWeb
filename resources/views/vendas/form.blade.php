@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/venda.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>{{ isset($venda) ? 'Editar' : 'Nova' }} Venda</h1>
    
    <div id="mensagem" class="alert" style="display: none;"></div>
    
    <form id="vendaForm" method="POST">
        @csrf
        @if(isset($venda))
            @method('PUT')
        @endif
        
        <div class="form-linha">
            <div class="campo">
                <label for="cliente">Cliente:</label>
                <input type="text" id="cliente" placeholder="Nome do cliente" autocomplete="off" 
                       value="{{ $venda->cliente->nome ?? '' }}">
                <input type="hidden" id="cliente_id" name="cliente_id" value="{{ $venda->cliente_id ?? '' }}">
                <div id="sugestoes-cliente" class="list-group position-absolute" style="z-index: 1000; width: 100%; display: none;"></div>
            </div>

            <div class="campo">
                <label for="dataVenda">Data da Venda:</label>
                <input type="text" id="dataVenda" placeholder="dd/mm/aaaa" maxlength="10" 
                       value="{{ isset($venda) ? date('d/m/Y', strtotime($venda->data)) : '' }}">
            </div>
        </div>

        <hr>
        <h3>Produtos</h3>
        
        <div class="form-linha">
            <div class="campo">
                <label for="produto">Produto:</label>
                <input type="text" id="produto" placeholder="Nome do produto" autocomplete="off">
                <input type="hidden" id="produto_id">
                <input type="hidden" id="preco_unitario">
                <div id="sugestoes-produto" class="list-group position-absolute" style="z-index: 1000; width: 100%; display: none;"></div>
            </div>

            <div class="campo">
                <label for="quantidade">Quantidade:</label>
                <input type="number" id="quantidade" min="1" value="1">
            </div>

            <div class="campo">
                <label for="vencimento">Vencimento:</label>
                <input type="text" id="vencimento" placeholder="dd/mm/aaaa" maxlength="10">
            </div>

            <div class="campo botao">
                <label>&nbsp;</label>
                <button type="button" id="adicionar" class="btn btn-success">
                    <i class="fas fa-plus"></i> Adicionar Item
                </button>
            </div>
        </div>

        <div id="tabela-venda" style="display: none; margin-top: 20px;">
            <h4>Itens da Venda</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Preço Unit.</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="itens-venda"></tbody>
            </table>
        </div>

        <div class="resumo-venda" style="display: none; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
            <h4 class="text-right">Total: <span id="totalVenda">R$ 0,00</span></h4>
            <p id="vencimentoDisplay" class="text-muted"></p>
        </div>

        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary" id="finalizar">
                <i class="fas fa-save"></i> Finalizar Venda
            </button>
            <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ===================== VARIÁVEIS GLOBAIS =====================
let itensVenda = [];
let clienteSelecionadoId = document.getElementById('cliente_id').value || null;
let produtoSelecionadoId = null;
let precoSelecionado = null;

const mensagem = document.getElementById('mensagem');
const inputCliente = document.getElementById('cliente');
const inputProduto = document.getElementById('produto');
const inputQuantidade = document.getElementById('quantidade');
const inputVencimento = document.getElementById('vencimento');
const inputDataVenda = document.getElementById('dataVenda');
const btnAdicionar = document.getElementById('adicionar');
const btnFinalizar = document.getElementById('finalizar');
const itensTbody = document.getElementById('itens-venda');
const tabelaVenda = document.getElementById('tabela-venda');
const resumoVenda = document.querySelector('.resumo-venda');
const totalVendaSpan = document.getElementById('totalVenda');
const vencimentoDisplay = document.getElementById('vencimentoDisplay');

// ===================== MÁSCARAS =====================
function mascaraData(input) {
    input.addEventListener('input', () => {
        let valor = input.value.replace(/\D/g, '');
        if (valor.length > 2 && valor.length <= 4) {
            valor = valor.replace(/(\d{2})(\d{1,2})/, '$1/$2');
        } else if (valor.length > 4) {
            valor = valor.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
        }
        input.value = valor.substring(0, 10);
    });
}

mascaraData(inputDataVenda);
mascaraData(inputVencimento);

// ===================== AUTOCOMPLETE CLIENTE =====================
function configurarAutocompleteCliente() {
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
    const sugestoes = document.getElementById('sugestoes-produto');
    
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
                    produtoSelecionadoId = produto.id;
                    precoSelecionado = parseFloat(produto.preco);
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

// ===================== ATUALIZAR LISTA DE ITENS =====================
function atualizarLista() {
    itensTbody.innerHTML = '';
    
    if (itensVenda.length === 0) {
        tabelaVenda.style.display = 'none';
        resumoVenda.style.display = 'none';
        return;
    }

    let total = 0;

    itensVenda.forEach((item, index) => {
        const subtotal = item.quantidade * item.preco_unitario;
        total += subtotal;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.nome}</td>
            <td>R$ ${item.preco_unitario.toFixed(2).replace('.', ',')}</td>
            <td>${item.quantidade}</td>
            <td>R$ ${subtotal.toFixed(2).replace('.', ',')}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removerItem(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        itensTbody.appendChild(tr);
    });

    totalVendaSpan.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    vencimentoDisplay.textContent = inputVencimento.value ? `Vencimento: ${inputVencimento.value}` : '';

    tabelaVenda.style.display = 'block';
    resumoVenda.style.display = 'block';
}

// ===================== REMOVER ITEM =====================
window.removerItem = function(index) {
    itensVenda.splice(index, 1);
    atualizarLista();
    mostrarMensagem('Item removido da venda.', 'success');
};

// ===================== MOSTRAR MENSAGEM =====================
function mostrarMensagem(texto, tipo) {
    mensagem.textContent = texto;
    mensagem.className = `alert alert-${tipo}`;
    mensagem.style.display = 'block';
    setTimeout(() => {
        mensagem.style.display = 'none';
    }, 3000);
}

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    configurarAutocompleteCliente();
    configurarAutocompleteProduto();

    // Adicionar item
    btnAdicionar.addEventListener('click', () => {
        const produtoId = document.getElementById('produto_id').value;
        const produtoNome = inputProduto.value.trim();
        const quantidade = parseInt(inputQuantidade.value);
        const preco = parseFloat(document.getElementById('preco_unitario').value);

        if (!produtoId || !produtoNome || isNaN(quantidade) || quantidade < 1 || isNaN(preco) || preco <= 0) {
            mostrarMensagem('Selecione um produto e informe quantidade válida.', 'danger');
            return;
        }

        itensVenda.push({
            produto_id: produtoId,
            nome: produtoNome,
            quantidade: quantidade,
            preco_unitario: preco
        });

        atualizarLista();
        mostrarMensagem('Item adicionado com sucesso!', 'success');

        // Limpar campos do produto
        inputProduto.value = '';
        document.getElementById('produto_id').value = '';
        document.getElementById('preco_unitario').value = '';
        inputQuantidade.value = '1';
        produtoSelecionadoId = null;
        precoSelecionado = null;
    });

    // Finalizar venda
    btnFinalizar.addEventListener('click', async (e) => {
        e.preventDefault();

        const clienteId = document.getElementById('cliente_id').value;
        const dataVenda = inputDataVenda.value;
        const vencimento = inputVencimento.value;

        if (!clienteId) {
            mostrarMensagem('Selecione um cliente.', 'danger');
            return;
        }

        if (itensVenda.length === 0) {
            mostrarMensagem('Adicione pelo menos um item à venda.', 'danger');
            return;
        }

        const total = itensVenda.reduce((sum, item) => sum + (item.quantidade * item.preco_unitario), 0);

        // Converter data de dd/mm/aaaa para yyyy-mm-dd
        function converterData(dataBr) {
            const partes = dataBr.split('/');
            if (partes.length !== 3) return null;
            return `${partes[2]}-${partes[1]}-${partes[0]}`;
        }

        const dadosVenda = {
            cliente_id: clienteId,
            data: converterData(dataVenda),
            data_vencimento: vencimento ? converterData(vencimento) : null,
            total: total,
            itens: itensVenda
        };

        try {
            const response = await fetch('/api/vendas', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(dadosVenda)
            });

            const resultado = await response.json();

            if (resultado.success) {
                mostrarMensagem('Venda registrada com sucesso!', 'success');
                
                // Resetar formulário
                inputCliente.value = '';
                document.getElementById('cliente_id').value = '';
                inputDataVenda.value = '';
                inputVencimento.value = '';
                itensVenda = [];
                atualizarLista();
                
                setTimeout(() => {
                    window.location.href = '{{ route("vendas.index") }}';
                }, 2000);
            } else {
                mostrarMensagem(resultado.message || 'Erro ao registrar venda.', 'danger');
            }
        } catch (error) {
            console.error('Erro ao registrar venda:', error);
            mostrarMensagem('Erro interno ao registrar venda.', 'danger');
        }
    });
});
</script>
@endpush