// ===================== VARIÁVEIS GLOBAIS =====================
const mensagem = document.getElementById('mensagem');
let produtoSelecionado = null;

// ===================== FORMATAÇÃO =====================
function formatarPreco(valor) {
    return parseFloat(valor).toFixed(2);
}

function formatarPrecoParaExibir(valor) {
    return parseFloat(valor).toFixed(2).replace('.', ',');
}

function formatarPrecoParaSalvar(valor) {
    return parseFloat(valor.toString().replace(',', '.'));
}

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

// ===================== AUTOCOMPLETE =====================
function configurarAutocompleteProduto(inputNome, inputPreco, inputQuantidade) {
    console.log('Configurando autocomplete para produtos:', inputNome);
    
    const sugestoes = document.getElementById('sugestoes-produtos');
    
    inputNome.addEventListener('keyup', function() {
        const termo = this.value.trim();
        console.log('Termo digitado:', termo);
        
        if (termo.length < 2) {
            sugestoes.style.display = 'none';
            return;
        }
        
        sugestoes.innerHTML = '<a href="#" class="list-group-item">Carregando...</a>';
        sugestoes.style.display = 'block';
        
        fetch(`/api/produtos/busca?termo=${termo}`)
            .then(response => response.json())
            .then(produtos => {
                console.log('Produtos recebidos:', produtos);
                sugestoes.innerHTML = '';
                
                if (produtos.length === 0) {
                    sugestoes.innerHTML = '<a href="#" class="list-group-item">Nenhum produto encontrado</a>';
                    return;
                }
                
                produtos.forEach(produto => {
                    const div = document.createElement('a');
                    div.href = '#';
                    div.className = 'list-group-item list-group-item-action';
                    div.textContent = produto.nome;
                    div.onclick = (e) => {
                        e.preventDefault();
                        inputNome.value = produto.nome;
                        produtoSelecionado = produto;
                        sugestoes.style.display = 'none';
                        
                        // Preencher preço se existir
                        if (produto.preco && inputPreco) {
                            inputPreco.value = produto.preco;
                            inputPreco.disabled = true;
                            inputPreco.style.backgroundColor = '#f0f0f0';
                        }
                        
                        // Preencher quantidade se existir
                        if (produto.quantidade !== undefined && inputQuantidade) {
                            inputQuantidade.value = produto.quantidade;
                            inputQuantidade.disabled = true;
                            inputQuantidade.style.backgroundColor = '#f0f0f0';
                        }
                    };
                    sugestoes.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Erro no fetch:', error);
                sugestoes.innerHTML = '<a href="#" class="list-group-item text-danger">Erro ao buscar</a>';
            });
    });
    
    document.addEventListener('click', (e) => {
        if (!sugestoes.contains(e.target) && e.target !== inputNome) {
            sugestoes.style.display = 'none';
        }
    });
}

// ===================== LISTAGEM DE PRODUTOS =====================
async function carregarProdutosNaTabela() {
    const container = document.getElementById('produtosContainer');
    const tbody = document.querySelector('#tabelaProdutos tbody');
    
    if (!container || !tbody) return;
    
    container.style.display = 'block';
    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Carregando...</td></tr>';

    try {
        const response = await fetch('/api/produtos');
        const produtos = await response.json();

        if (produtos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum produto cadastrado.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        produtos.forEach(produto => {
            const tr = document.createElement('tr');
            tr.dataset.id = produto.id;
            tr.innerHTML = `
                <td>${produto.id}</td>
                <td><input type="text" class="form-control form-control-sm" value="${produto.nome}" data-original="${produto.nome}" /></td>
                <td><input type="text" class="form-control form-control-sm" value="${formatarPrecoParaExibir(produto.preco)}" data-original="${produto.preco}" /></td>
                <td><input type="number" class="form-control form-control-sm" value="${produto.quantidade}" data-original="${produto.quantidade}" /></td>
                <td>
                    <button class="btn btn-sm btn-warning btn-alterar" onclick="atualizarProdutoTabela(${produto.id}, this)"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-remover" onclick="excluirProdutoTabela(${produto.id}, this)"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });

    } catch (error) {
        console.error('Erro ao buscar produtos:', error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erro ao carregar produtos.</td></tr>';
    }
}

// ===================== CRUD NA TABELA =====================
async function atualizarProdutoTabela(id, btn) {
    const row = btn.closest('tr');
    const nome = row.querySelector('td:nth-child(2) input').value.trim();
    const preco = parseFloat(row.querySelector('td:nth-child(3) input').value.replace(',', '.'));
    const quantidade = parseInt(row.querySelector('td:nth-child(4) input').value);

    if (!nome || isNaN(preco) || preco <= 0 || isNaN(quantidade) || quantidade < 0) {
        mostrarMensagem('Preencha os campos corretamente.', 'danger');
        return;
    }

    try {
        const response = await fetch(`/api/produtos/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ nome, preco, quantidade })
        });

        const resultado = await response.json();
        
        if (resultado.success) {
            mostrarMensagem('Produto atualizado com sucesso!', 'success');
            row.querySelectorAll('input').forEach(input => {
                input.dataset.original = input.value;
            });
        } else {
            mostrarMensagem(resultado.message || 'Erro ao atualizar produto.', 'danger');
        }
    } catch (error) {
        console.error('Erro ao atualizar produto:', error);
        mostrarMensagem('Erro interno ao atualizar.', 'danger');
    }
}

async function excluirProdutoTabela(id, btn) {
    if (!confirm('Tem certeza que deseja excluir este produto?')) return;

    try {
        const response = await fetch(`/api/produtos/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const resultado = await response.json();
        
        if (resultado.success) {
            mostrarMensagem('Produto excluído com sucesso!', 'success');
            btn.closest('tr').remove();
        } else {
            mostrarMensagem(resultado.message || 'Erro ao excluir produto.', 'danger');
        }
    } catch (error) {
        console.error('Erro ao excluir produto:', error);
        mostrarMensagem('Erro interno ao excluir.', 'danger');
    }
}

// ===================== UTILITÁRIOS =====================
function mostrarMensagem(texto, tipo) {
    mensagem.textContent = texto;
    mensagem.className = `alert alert-${tipo}`;
    mensagem.style.display = 'block';
    setTimeout(() => {
        mensagem.style.display = 'none';
    }, 3000);
}

// ===================== RESETAR CAMPOS =====================
function resetarCampos() {
    const inputNome = document.getElementById('nome');
    const inputPreco = document.getElementById('preco');
    const inputQuantidade = document.getElementById('quantidade');
    
    inputPreco.disabled = false;
    inputQuantidade.disabled = false;
    inputPreco.style.backgroundColor = '';
    inputQuantidade.style.backgroundColor = '';
    produtoSelecionado = null;
}

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('produtoForm');
    const inputNome = document.getElementById('nome');
    const inputPreco = document.getElementById('preco');
    const inputQuantidade = document.getElementById('quantidade');
    const btnProdutos = document.getElementById('btnProdutosCadastrados');
    const urlParams = new URLSearchParams(window.location.search);
    const isEdit = window.location.pathname.includes('/edit');

    if (inputNome && inputPreco && inputQuantidade) {
        configurarAutocompleteProduto(inputNome, inputPreco, inputQuantidade);
        
        // Se for edição, não bloquear campos
        if (isEdit) {
            inputPreco.disabled = false;
            inputQuantidade.disabled = false;
            inputPreco.style.backgroundColor = '';
            inputQuantidade.style.backgroundColor = '';
        }
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nome = document.getElementById('nome').value.trim();
            const preco = parseFloat(document.getElementById('preco').value);
            const quantidade = parseInt(document.getElementById('quantidade').value);

            if (!nome || isNaN(preco) || preco <= 0 || isNaN(quantidade) || quantidade < 0) {
                mostrarMensagem('Preencha os campos corretamente.', 'danger');
                return;
            }

            // Verificar se é edição (PUT) ou criação (POST)
            const method = document.querySelector('input[name="_method"]')?.value || 'POST';
            
            // Se for criação, verificar se já existe
            if (method === 'POST') {
                const existe = await verificarProdutoExistente(nome);
                if (existe) {
                    mostrarMensagem('❌ Produto já cadastrado!', 'danger');
                    return;
                }
            }

            // Usar a action do formulário
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }

                const resultado = await response.json();
                
                if (resultado.success) {
                    mostrarMensagem('Produto salvo com sucesso!', 'success');
                    if (method === 'POST') {
                        form.reset();
                        resetarCampos();
                    } else {
                        setTimeout(() => window.location.href = '{{ route("produtos.index") }}', 1500);
                    }
                } else {
                    mostrarMensagem(resultado.message || 'Erro ao salvar produto.', 'danger');
                }
            } catch (error) {
                console.error('Erro:', error);
                mostrarMensagem('Erro ao salvar produto.', 'danger');
            }
        });
    }

    if (btnProdutos) {
        btnProdutos.addEventListener('click', carregarProdutosNaTabela);
    }
});