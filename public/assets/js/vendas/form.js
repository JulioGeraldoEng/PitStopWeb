// ===================== VENDAS FORM =====================
// Variáveis globais (recebidas do window)
let itensVenda = window.dadosVenda?.itens || [];
let clienteSelecionadoId = window.dadosVenda?.clienteId || '';
let produtoSelecionadoId = null;
let precoSelecionado = null;

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
    if (!tbody) return;
    
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
    if (!mensagem) return;
    
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
    if (!inputCliente || !sugestoes) return;
    
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
    if (!inputProduto || !sugestoes) return;
    
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
                    if (precoDisplay) {
                        precoDisplay.value = `R$ ${parseFloat(produto.preco).toFixed(2).replace('.', ',')}`;
                    }
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
    document.getElementById('btnAdicionarItem')?.addEventListener('click', () => {
        const produtoId = document.getElementById('produto_id')?.value;
        const produtoNome = document.getElementById('produto')?.value.trim();
        const quantidade = parseInt(document.getElementById('quantidade')?.value);
        const preco = parseFloat(document.getElementById('preco_unitario')?.value);

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
        if (precoDisplay) precoDisplay.value = '';
        document.getElementById('quantidade').value = '1';
        produtoSelecionadoId = null;
        precoSelecionado = null;
    });

    // Validação antes de enviar o formulário
    document.getElementById('vendaForm')?.addEventListener('submit', (e) => {
        const clienteId = document.getElementById('cliente_id')?.value;
        
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

        // Adicionar o total como campo hidden
        const totalInput = document.createElement('input');
        totalInput.type = 'hidden';
        totalInput.name = 'total';
        totalInput.value = itensVenda.reduce((sum, item) => sum + (item.quantidade * item.preco_unitario), 0);
        document.getElementById('vendaForm').appendChild(totalInput);
    });
});