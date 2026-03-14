// ===================== PRODUTOS INDEX =====================
let produtoSelecionadoModal = null;

// ===================== VERIFICAR SE PRODUTO JÁ EXISTE =====================
async function verificarProdutoExistente(nome) {
    try {
        const response = await fetch(`/api/produtos/verificar?nome=${encodeURIComponent(nome)}`);
        const data = await response.json();
        return data.existe;
    } catch (error) {
        console.error('Erro ao verificar produto:', error);
        return false;
    }
}

// ===================== MÁSCARA DE PREÇO =====================
function formatarPreco(event) {
    const input = event.target;
    let valor = input.value.replace(/\D/g, '');
    
    if (valor.length === 0) {
        input.value = '';
        return;
    }
    
    valor = (parseInt(valor) / 100).toFixed(2);
    input.value = valor.replace('.', ',');
}

function formatarPrecoModal(event) {
    const input = event.target;
    let valor = input.value.replace(/\D/g, '');
    
    if (valor.length === 0) {
        input.value = '';
        return;
    }
    
    valor = (parseInt(valor) / 100).toFixed(2);
    input.value = valor.replace('.', ',');
}

function formatarPrecoParaSalvar(valor) {
    return parseFloat(valor.replace(/\./g, '').replace(',', '.'));
}

function formatarPrecoParaExibir(valor) {
    return parseFloat(valor).toFixed(2).replace('.', ',');
}

// ===================== FUNÇÕES AUXILIARES =====================
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

// ... (resto do código permanece igual)

// ===================== AUTOCOMPLETE PARA BUSCA =====================
function configurarAutocompleteBusca(inputBusca) {
    const sugestoes = document.getElementById('sugestoes-busca');
    if (!sugestoes) return;
    
    // Remover eventos anteriores para evitar duplicação
    inputBusca.removeEventListener('keyup', handleKeyUp);
    inputBusca.addEventListener('keyup', handleKeyUp);
    
    async function handleKeyUp() {
        const termo = inputBusca.value.trim();
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
                div.textContent = produto.nome;
                div.onclick = (e) => {
                    e.preventDefault();
                    inputBusca.value = produto.nome;
                    sugestoes.style.display = 'none';
                    document.getElementById('btnBuscarProdutos')?.click();
                };
                sugestoes.appendChild(div);
            });

            sugestoes.style.display = 'block';
        } catch (error) {
            console.error('Erro ao buscar produtos:', error);
        }
    }

    document.addEventListener('click', (e) => {
        if (!sugestoes.contains(e.target) && e.target !== inputBusca) {
            sugestoes.style.display = 'none';
        }
    });
}

// ===================== AUTOCOMPLETE PARA MODAL =====================
function configurarAutocompleteModal() {
    const inputNome = document.getElementById('modal-nome');
    const inputPreco = document.getElementById('modal-preco');
    const inputQuantidade = document.getElementById('modal-quantidade');
    const sugestoes = document.getElementById('sugestoes-produto');
    
    if (!inputNome || !sugestoes) return;
    
    inputNome.removeEventListener('keyup', handleKeyUp);
    inputNome.addEventListener('keyup', handleKeyUp);
    
    async function handleKeyUp() {
        const termo = inputNome.value.trim();
        sugestoes.innerHTML = '';
        sugestoes.style.display = 'none';
        produtoSelecionadoModal = null;

        if (termo.length < 2) return;

        try {
            const response = await fetch(`/api/produtos/busca?termo=${termo}`);
            const produtos = await response.json();

            if (produtos.length === 0) return;

            produtos.forEach(produto => {
                const div = document.createElement('a');
                div.href = '#';
                div.className = 'list-group-item list-group-item-action';
                div.textContent = produto.nome;
                div.onclick = (e) => {
                    e.preventDefault();
                    inputNome.value = produto.nome;
                    produtoSelecionadoModal = produto;
                    
                    // Preencher preço se existir e BLOQUEAR
                    if (produto.preco && inputPreco) {
                        inputPreco.value = produto.preco;
                        inputPreco.disabled = true;
                        inputPreco.style.backgroundColor = '#f0f0f0';
                    } else if (inputPreco) {
                        inputPreco.value = '';
                        inputPreco.disabled = false;
                        inputPreco.style.backgroundColor = '';
                    }
                    
                    // Preencher quantidade se existir e BLOQUEAR
                    if (produto.quantidade !== undefined && inputQuantidade) {
                        inputQuantidade.value = produto.quantidade;
                        inputQuantidade.disabled = true;
                        inputQuantidade.style.backgroundColor = '#f0f0f0';
                    } else if (inputQuantidade) {
                        inputQuantidade.value = 0;
                        inputQuantidade.disabled = false;
                        inputQuantidade.style.backgroundColor = '';
                    }
                    
                    sugestoes.style.display = 'none';
                };
                sugestoes.appendChild(div);
            });

            sugestoes.style.display = 'block';
        } catch (error) {
            console.error('Erro ao buscar produtos:', error);
        }
    }

    document.addEventListener('click', (e) => {
        if (!sugestoes.contains(e.target) && e.target !== inputNome) {
            sugestoes.style.display = 'none';
        }
    });
}

// ===================== RESETAR MODAL =====================
function resetarModal() {
    const inputNome = document.getElementById('modal-nome');
    const inputPreco = document.getElementById('modal-preco');
    const inputQuantidade = document.getElementById('modal-quantidade');
    
    inputNome.value = '';
    inputPreco.value = '';
    inputQuantidade.value = '0';
    
    // Reativar campos
    inputPreco.disabled = false;
    inputQuantidade.disabled = false;
    inputPreco.style.backgroundColor = '';
    inputQuantidade.style.backgroundColor = '';
    
    produtoSelecionadoModal = null;
}

// ===================== BUSCAR PRODUTOS =====================
async function buscarProdutos() {
    const nome = document.getElementById('buscar-nome')?.value.trim() || '';
    
    try {
        const btnBuscar = document.getElementById('btnBuscarProdutos');
        if (btnBuscar) {
            btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
            btnBuscar.disabled = true;
        }
        
        const response = await fetch(`/api/produtos/busca-produtos?nome=${encodeURIComponent(nome)}`);
        const produtos = await response.json();

        const tbody = document.getElementById('tabela-corpo');
        if (!tbody) return;
        
        tbody.innerHTML = '';

        const tabelaContainer = document.getElementById('tabela-produto');

        if (!produtos || produtos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Nenhum produto encontrado.</td></tr>';
            if (tabelaContainer) tabelaContainer.style.display = 'block';
            return;
        }

        produtos.forEach(produto => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${produto.nome}</td>
                <td>R$ ${formatarPrecoParaExibir(produto.preco)}</td>
                <td>${produto.quantidade}</td>
                <td>
                    <div class="acao-container">
                        <a href="/produtos/${produto.id}/edit" class="btn-editar">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form action="/produtos/${produto.id}" method="POST" style="display:inline;">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                <i class="fas fa-trash-alt"></i> Excluir
                            </button>
                        </form>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (tabelaContainer) {
            tabelaContainer.style.display = 'block';
        }

        mostrarMensagem(`${produtos.length} produto(s) encontrado(s).`, 'success');

    } catch (error) {
        console.error('Erro ao buscar produtos:', error);
        mostrarMensagem('Erro ao buscar produtos.', 'danger');
    } finally {
        const btnBuscar = document.getElementById('btnBuscarProdutos');
        if (btnBuscar) {
            btnBuscar.innerHTML = '<i class="fas fa-search"></i> Buscar';
            btnBuscar.disabled = false;
        }
    }
}

// ===================== LIMPAR BUSCA =====================
function limparBusca() {
    document.getElementById('buscar-nome').value = '';
    
    const tbody = document.getElementById('tabela-corpo');
    if (tbody) tbody.innerHTML = '';
    
    const tabelaContainer = document.getElementById('tabela-produto');
    if (tabelaContainer) {
        tabelaContainer.style.display = 'none';
    }
    
    mostrarMensagem('', '');
}

// ===================== LIMPAR MODAL =====================
function limparModal() {
    const inputNome = document.getElementById('modal-nome');
    const inputPreco = document.getElementById('modal-preco');
    const inputQuantidade = document.getElementById('modal-quantidade');
    const sugestoes = document.getElementById('sugestoes-produto');
    
    // Limpar campos
    inputNome.value = '';
    inputPreco.value = '';
    inputQuantidade.value = '0';
    
    // Reativar campos
    inputPreco.disabled = false;
    inputQuantidade.disabled = false;
    inputPreco.style.backgroundColor = '';
    inputQuantidade.style.backgroundColor = '';
    
    // Limpar sugestões
    sugestoes.innerHTML = '';
    sugestoes.style.display = 'none';
    
    // Limpar seleção
    produtoSelecionadoModal = null;
    
    // Focar no campo nome
    inputNome.focus();
}

// ===================== RESETAR MODAL (quando abre) =====================
function resetarModal() {
    const inputNome = document.getElementById('modal-nome');
    const inputPreco = document.getElementById('modal-preco');
    const inputQuantidade = document.getElementById('modal-quantidade');
    
    inputNome.value = '';
    inputPreco.value = '';
    inputQuantidade.value = '0';
    
    // Reativar campos
    inputPreco.disabled = false;
    inputQuantidade.disabled = false;
    inputPreco.style.backgroundColor = '';
    inputQuantidade.style.backgroundColor = '';
    
    produtoSelecionadoModal = null;
}

// ===================== SALVAR PRODUTO =====================
async function salvarProduto() {
    const nome = document.getElementById('modal-nome').value.trim();
    const preco = document.getElementById('modal-preco').value.trim();
    const quantidade = parseInt(document.getElementById('modal-quantidade').value) || 0;
    
    console.log('Salvando produto:', { nome, preco, quantidade }); // DEBUG
    
    if (!nome) {
        mostrarMensagem('O nome é obrigatório.', 'danger');
        return;
    }
    
    if (!preco) {
        mostrarMensagem('O preço é obrigatório.', 'danger');
        return;
    }
    
    // Converter preço para formato numérico (ex: "1.234,56" -> 1234.56)
    const precoNumerico = parseFloat(preco.replace(/\./g, '').replace(',', '.'));
    
    if (isNaN(precoNumerico) || precoNumerico <= 0) {
        mostrarMensagem('Preço inválido.', 'danger');
        return;
    }
    
    // Verificar se já existe (apenas se não for um produto selecionado no autocomplete)
    if (!produtoSelecionadoModal) {
        const existe = await verificarProdutoExistente(nome);
        if (existe) {
            mostrarMensagem('❌ Produto já cadastrado!', 'danger');
            return;
        }
    }
    
    try {
        const response = await fetch('/api/produtos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                nome, 
                preco: precoNumerico, 
                quantidade 
            })
        });
        
        const resultado = await response.json();
        console.log('Resposta da API:', resultado); // DEBUG
        
        if (resultado.success) {
            mostrarMensagem('Produto cadastrado com sucesso!', 'success');
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoProduto'));
            modal.hide();
            
            // Resetar campos
            resetarModal();
            
            // Atualizar busca se necessário
            if (document.getElementById('buscar-nome').value) {
                buscarProdutos();
            }
        } else {
            mostrarMensagem(resultado.message || 'Erro ao cadastrar produto.', 'danger');
        }
    } catch (error) {
        console.error('Erro ao salvar produto:', error);
        mostrarMensagem('Erro ao salvar produto.', 'danger');
    }
}

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    const inputBusca = document.getElementById('buscar-nome');
    const inputPreco = document.getElementById('modal-preco');
    const btnBuscar = document.getElementById('btnBuscarProdutos');
    const btnLimpar = document.getElementById('btnLimparBusca');
    const btnSalvar = document.getElementById('btnSalvarProduto');
    const btnLimparModal = document.getElementById('btnLimparModal');
    const modalElement = document.getElementById('modalNovoProduto');

    // Máscara de preço no modal
    const inputPrecoModal = document.getElementById('modal-preco');

    if (inputPrecoModal) {
        inputPrecoModal.addEventListener('input', formatarPrecoModal);
    }

    // Máscara de preço
    if (inputPreco) {
        inputPreco.addEventListener('input', formatarPreco);
    }

    // Autocomplete na busca
    if (inputBusca) {
        configurarAutocompleteBusca(inputBusca);
    }

    // Botões de busca
    if (btnBuscar) {
        btnBuscar.addEventListener('click', buscarProdutos);
    }

    if (btnLimpar) {
        btnLimpar.addEventListener('click', limparBusca);
    }

    // Configurar autocomplete do modal UMA ÚNICA VEZ
    configurarAutocompleteModal();

    // Quando o modal abrir, resetar campos
    if (modalElement) {
        modalElement.addEventListener('shown.bs.modal', resetarModal);
    }

    // Botão limpar modal
    if (btnLimparModal) {
        btnLimparModal.addEventListener('click', limparModal);
    }

    // Botão salvar
    if (btnSalvar) {
        btnSalvar.addEventListener('click', salvarProduto);
    }
});