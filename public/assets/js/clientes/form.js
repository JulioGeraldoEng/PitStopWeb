// ===================== VARIÁVEIS GLOBAIS =====================
const mensagem = document.getElementById('mensagem');

// ===================== FORMATAÇÃO DE TELEFONE =====================
function formatarTelefone(event) {
    const input = event.target;
    let valor = input.value.replace(/\D/g, '');

    if (valor.length > 11) valor = valor.slice(0, 11);

    if (valor.length >= 2 && valor.length <= 6) {
        valor = `(${valor.slice(0, 2)}) ${valor.slice(2)}`;
    } else if (valor.length > 6 && valor.length <= 10) {
        valor = `(${valor.slice(0, 2)}) ${valor.slice(2, 6)}-${valor.slice(6)}`;
    } else if (valor.length === 11) {
        valor = `(${valor.slice(0, 2)}) ${valor.slice(2, 7)}-${valor.slice(7)}`;
    }

    input.value = valor;
}

function formatarTelefoneTexto(telefone) {
    if (!telefone) return '';
    const valor = telefone.replace(/\D/g, '');
    if (valor.length === 11) {
        return `(${valor.slice(0, 2)}) ${valor.slice(2, 7)}-${valor.slice(7)}`;
    }
    if (valor.length === 10) {
        return `(${valor.slice(0, 2)}) ${valor.slice(2, 6)}-${valor.slice(6)}`;
    }
    return telefone;
}

// ===================== AUTOCOMPLETE =====================
function configurarAutocompleteNome(inputNome) {
    console.log('Configurando autocomplete para:', inputNome);
    
    const sugestoes = document.getElementById('sugestoes-clientes');
    
    inputNome.addEventListener('keyup', function() {
        const termo = this.value.trim();
        console.log('Termo digitado:', termo);
        
        if (termo.length < 2) {
            sugestoes.style.display = 'none';
            return;
        }
        
        sugestoes.innerHTML = '<a href="#" class="list-group-item">Carregando...</a>';
        sugestoes.style.display = 'block';
        
        fetch(`/api/clientes/busca?termo=${termo}`)
            .then(response => response.json())
            .then(clientes => {
                console.log('Clientes recebidos:', clientes);
                sugestoes.innerHTML = '';
                
                if (clientes.length === 0) {
                    sugestoes.innerHTML = '<a href="#" class="list-group-item">Nenhum cliente encontrado</a>';
                    return;
                }
                
                clientes.forEach(cliente => {
                    const div = document.createElement('a');
                    div.href = '#';
                    div.className = 'list-group-item list-group-item-action';
                    div.textContent = cliente.nome;
                    div.onclick = (e) => {
                        e.preventDefault();
                        inputNome.value = cliente.nome;
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

// ===================== LISTAGEM DE CLIENTES =====================
async function carregarClientes() {
    const container = document.getElementById('clientesContainer');
    const tbody = document.querySelector('#tabelaClientes tbody');
    
    container.style.display = 'block';
    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Carregando...</td></tr>';

    try {
        const response = await fetch('/api/clientes');
        const clientes = await response.json();

        if (clientes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum cliente cadastrado.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        clientes.forEach(cliente => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${cliente.id}</td>
                <td><input type="text" class="form-control form-control-sm" value="${cliente.nome}" data-original="${cliente.nome}" /></td>
                <td><input type="text" class="form-control form-control-sm telefone-tabela" value="${formatarTelefoneTexto(cliente.telefone)}" data-original="${cliente.telefone}" /></td>
                <td><input type="text" class="form-control form-control-sm" value="${cliente.observacao || ''}" data-original="${cliente.observacao || ''}" /></td>
                <td>
                    <button class="btn btn-sm btn-warning btn-alterar" onclick="atualizarClienteTabela(${cliente.id}, this)"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-remover" onclick="excluirClienteTabela(${cliente.id}, this)"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.querySelectorAll('.telefone-tabela').forEach(input => {
            input.addEventListener('input', formatarTelefone);
        });

    } catch (error) {
        console.error('Erro ao buscar clientes:', error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erro ao carregar clientes.</td></tr>';
    }
}

// ===================== CRUD NA TABELA =====================
async function atualizarClienteTabela(id, btn) {
    const row = btn.closest('tr');
    const nome = row.querySelector('td:nth-child(2) input').value.trim();
    const telefone = row.querySelector('td:nth-child(3) input').value.trim().replace(/\D/g, '');
    const observacao = row.querySelector('td:nth-child(4) input').value.trim();

    try {
        const response = await fetch(`/api/clientes/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ nome, telefone, observacao })
        });

        const resultado = await response.json();
        
        if (resultado.success) {
            mostrarMensagem('Cliente atualizado com sucesso!', 'success');
            row.querySelectorAll('input').forEach(input => {
                input.dataset.original = input.value;
            });
        } else {
            mostrarMensagem(resultado.message || 'Erro ao atualizar cliente.', 'danger');
        }
    } catch (error) {
        console.error('Erro ao atualizar cliente:', error);
        mostrarMensagem('Erro interno ao atualizar.', 'danger');
    }
}

async function excluirClienteTabela(id, btn) {
    if (!confirm('Tem certeza que deseja excluir este cliente?')) return;

    try {
        const response = await fetch(`/api/clientes/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const resultado = await response.json();
        
        if (resultado.success) {
            mostrarMensagem('Cliente excluído com sucesso!', 'success');
            btn.closest('tr').remove();
        } else {
            mostrarMensagem(resultado.message || 'Erro ao excluir cliente.', 'danger');
        }
    } catch (error) {
        console.error('Erro ao excluir cliente:', error);
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
    const form = document.getElementById('clienteForm');
    const inputTelefone = document.getElementById('telefone');
    const inputNome = document.getElementById('nome');
    const btnClientes = document.getElementById('btnClientesCadastrados');

    if (inputTelefone) {
        inputTelefone.addEventListener('input', formatarTelefone);
    }

    if (inputNome) {
        configurarAutocompleteNome(inputNome);
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nome = document.getElementById('nome').value.trim();
            const telefone = document.getElementById('telefone').value.trim().replace(/\D/g, '');
            const observacao = document.getElementById('observacao').value.trim();

            if (!nome) {
                mostrarMensagem('O nome é obrigatório.', 'danger');
                return;
            }

            if (telefone.length < 10) {
                mostrarMensagem('Telefone inválido. Use com DDD.', 'danger');
                return;
            }

            const method = form.querySelector('input[name="_method"]')?.value || 'POST';
            const url = method === 'PUT' 
                ? `/api/clientes/${id}`
                : '/api/clientes';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ nome, telefone, observacao })
                });

                const resultado = await response.json();
                
                if (resultado.success) {
                    mostrarMensagem('Cliente salvo com sucesso!', 'success');
                    if (method === 'POST') {
                        form.reset();
                    } else {
                        setTimeout(() => window.location.href = '{{ route("clientes.index") }}', 1500);
                    }
                } else {
                    mostrarMensagem(resultado.message || 'Erro ao salvar cliente.', 'danger');
                }
            } catch (error) {
                console.error('Erro:', error);
                mostrarMensagem('Erro ao salvar cliente.', 'danger');
            }
        });
    }

    if (btnClientes) {
        btnClientes.addEventListener('click', carregarClientes);
    }
});