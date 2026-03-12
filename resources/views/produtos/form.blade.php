@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ isset($produto) ? 'Editar' : 'Novo' }} Produto</h1>
    
    <div id="mensagem" class="alert" style="display: none;"></div>
    
    <form id="produtoForm" method="POST">
        @csrf
        @if(isset($produto))
            @method('PUT')
        @endif
        
        <div class="mb-3 position-relative">
            <label for="nome" class="form-label">Nome *</label>
            <input type="text" class="form-control" id="nome" name="nome" 
                   value="{{ $produto->nome ?? '' }}" required autocomplete="off">
            <div id="sugestoes-produtos" class="list-group position-absolute" style="z-index: 1000; width: 100%; display: none;"></div>
        </div>
        
        <div class="mb-3">
            <label for="preco" class="form-label">Preço *</label>
            <input type="number" class="form-control" id="preco" name="preco" 
                   value="{{ $produto->preco ?? '' }}" step="0.01" min="0" required>
        </div>
        
        <div class="mb-3">
            <label for="quantidade" class="form-label">Quantidade *</label>
            <input type="number" class="form-control" id="quantidade" name="quantidade" 
                   value="{{ $produto->quantidade ?? 0 }}" min="0" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>

    @if(!isset($produto))
    <hr>
    <div class="mt-4">
        <button id="btnProdutosCadastrados" class="btn btn-info">Listar Produtos Cadastrados</button>
        <div id="produtosContainer" style="display: none; margin-top: 20px;">
            <h3>Produtos Cadastrados</h3>
            <table class="table table-striped" id="tabelaProdutos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// ===================== VARIÁVEIS GLOBAIS =====================
const mensagem = document.getElementById('mensagem');

// ===================== FORMATAÇÃO =====================
function formatarPreco(valor) {
    return parseFloat(valor).toFixed(2);
}

// ===================== AUTOCOMPLETE =====================
function configurarAutocompleteProduto(inputNome) {
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
                        sugestoes.style.display = 'none';
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
                <td><input type="number" class="form-control form-control-sm" value="${produto.preco}" step="0.01" data-original="${produto.preco}" /></td>
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
    const preco = parseFloat(row.querySelector('td:nth-child(3) input').value);
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

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('produtoForm');
    const inputNome = document.getElementById('nome');
    const btnProdutos = document.getElementById('btnProdutosCadastrados');

    if (inputNome) {
        configurarAutocompleteProduto(inputNome);
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

            const method = form.querySelector('input[name="_method"]')?.value || 'POST';
            const url = method === 'PUT' 
                ? `/api/produtos/${id}`
                : '/api/produtos';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ nome, preco, quantidade })
                });

                const resultado = await response.json();
                
                if (resultado.success) {
                    mostrarMensagem('Produto salvo com sucesso!', 'success');
                    if (method === 'POST') {
                        form.reset();
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
</script>
@endpush