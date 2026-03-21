// ===================== CLIENTES INDEX =====================
let clienteSelecionadoModal = null;

// ===================== MÁSCARA DE TELEFONE =====================
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

// ===================== VERIFICAR SE CLIENTE JÁ EXISTE =====================
async function verificarClienteExistente(nome) {
    try {
        const response = await fetch(`/api/clientes/verificar?nome=${encodeURIComponent(nome)}`);
        const data = await response.json();
        return data.existe;
    } catch (error) {
        console.error('Erro ao verificar cliente:', error);
        return false;
    }
}

// ===================== AUTOCOMPLETE PARA MODAL =====================
function configurarAutocompleteModal() {
    const inputNome = document.getElementById('modal-nome');
    const inputTelefone = document.getElementById('modal-telefone');
    const inputObservacao = document.getElementById('modal-observacao');
    const sugestoes = document.getElementById('sugestoes-cliente');
    
    if (!inputNome || !sugestoes) return;
    
    inputNome.addEventListener('keyup', async () => {
        const termo = inputNome.value.trim();
        sugestoes.innerHTML = '';
        sugestoes.style.display = 'none';
        clienteSelecionadoModal = null;

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
                    inputNome.value = cliente.nome;
                    clienteSelecionadoModal = cliente;
                    
                    // Preencher telefone se existir
                    if (cliente.telefone) {
                        inputTelefone.value = formatarTelefoneTexto(cliente.telefone);
                        inputTelefone.disabled = true;
                        inputTelefone.style.backgroundColor = '#f0f0f0';
                    } else {
                        inputTelefone.value = '';
                        inputTelefone.disabled = false;
                        inputTelefone.style.backgroundColor = '';
                    }
                    
                    // Preencher observação se existir
                    if (cliente.observacao) {
                        inputObservacao.value = cliente.observacao;
                        inputObservacao.disabled = true;
                        inputObservacao.style.backgroundColor = '#f0f0f0';
                    } else {
                        inputObservacao.value = '';
                        inputObservacao.disabled = false;
                        inputObservacao.style.backgroundColor = '';
                    }
                    
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
        if (!sugestoes.contains(e.target) && e.target !== inputNome) {
            sugestoes.style.display = 'none';
        }
    });
}

// ===================== AUTOCOMPLETE PARA BUSCA =====================
function configurarAutocompleteBusca(inputBusca) {
    const sugestoes = document.getElementById('sugestoes-busca');
    if (!sugestoes) return;
    
    inputBusca.addEventListener('keyup', async () => {
        const termo = inputBusca.value.trim();
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
                    inputBusca.value = cliente.nome;
                    sugestoes.style.display = 'none';
                    document.getElementById('btnBuscarClientes')?.click();
                };
                sugestoes.appendChild(div);
            });

            sugestoes.style.display = 'block';
        } catch (error) {
            console.error('Erro ao buscar clientes:', error);
        }
    });

    document.addEventListener('click', (e) => {
        if (!sugestoes.contains(e.target) && e.target !== inputBusca) {
            sugestoes.style.display = 'none';
        }
    });
}

// ===================== BUSCAR CLIENTES =====================
async function buscarClientes() {
    const nome = document.getElementById('buscar-nome')?.value.trim() || '';
    
    try {
        const btnBuscar = document.getElementById('btnBuscarClientes');
        if (btnBuscar) {
            btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
            btnBuscar.disabled = true;
        }
        
        const response = await fetch(`/api/clientes/busca-clientes?nome=${encodeURIComponent(nome)}`);
        const clientes = await response.json();

        const tbody = document.getElementById('tabela-corpo');
        if (!tbody) return;
        
        tbody.innerHTML = '';

        const tabelaContainer = document.getElementById('tabela-cliente');

        if (!clientes || clientes.length === 0) {
            tbody.innerHTML = '时任<td colspan="4" class="text-center">Nenhum cliente encontrado. </td>';
            if (tabelaContainer) tabelaContainer.style.display = 'block';
            return;
        }

        clientes.forEach(cliente => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${cliente.nome}</td>
                <td>${formatarTelefoneTexto(cliente.telefone)}</td>
                <td>${cliente.observacao || '-'}</td>
                <td>
                    <div class="acao-container">
                        <a href="/clientes/${cliente.id}/edit" class="btn-editar">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form action="/clientes/${cliente.id}" method="POST" style="display:inline;">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn-excluir" onclick="return confirm('Tem certeza?')">
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

        mostrarMensagem(`${clientes.length} cliente(s) encontrado(s).`, 'success');

    } catch (error) {
        console.error('Erro ao buscar clientes:', error);
        mostrarMensagem('Erro ao buscar clientes.', 'danger');
    } finally {
        const btnBuscar = document.getElementById('btnBuscarClientes');
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
    
    const tabelaContainer = document.getElementById('tabela-cliente');
    if (tabelaContainer) {
        tabelaContainer.style.display = 'none';
    }
    
    mostrarMensagem('', '');
}

// ===================== RESETAR MODAL =====================
function resetarModal() {
    const inputNome = document.getElementById('modal-nome');
    const inputTelefone = document.getElementById('modal-telefone');
    const inputObservacao = document.getElementById('modal-observacao');
    
    inputNome.value = '';
    inputTelefone.value = '';
    inputObservacao.value = '';
    
    inputTelefone.disabled = false;
    inputObservacao.disabled = false;
    inputTelefone.style.backgroundColor = '';
    inputObservacao.style.backgroundColor = '';
    
    clienteSelecionadoModal = null;
}

// ===================== SALVAR CLIENTE =====================
async function salvarCliente() {
    const nome = document.getElementById('modal-nome').value.trim();
    const telefone = document.getElementById('modal-telefone').value.trim().replace(/\D/g, '');
    const observacao = document.getElementById('modal-observacao').value.trim();
    
    if (!nome) {
        mostrarMensagem('O nome é obrigatório.', 'danger');
        return;
    }
    
    // Verificar se já existe
    const existe = await verificarClienteExistente(nome);
    
    if (existe) {
        mostrarMensagem('❌ Cliente já cadastrado!', 'danger');
        return;
    }
    
    try {
        const response = await fetch('/api/clientes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            // CORREÇÃO SQLITE: enviar null para campos vazios
            body: JSON.stringify({ 
                nome, 
                telefone: telefone || null, 
                observacao: observacao || null 
            })
        });
        
        const resultado = await response.json();
        
        if (resultado.success) {
            mostrarMensagem('Cliente cadastrado com sucesso!', 'success');
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoCliente'));
            modal.hide();
            
            // Resetar campos
            resetarModal();
            
            // Atualizar busca se necessário
            if (document.getElementById('buscar-nome').value) {
                buscarClientes();
            }
        } else {
            mostrarMensagem(resultado.message || 'Erro ao cadastrar cliente.', 'danger');
        }
    } catch (error) {
        console.error('Erro ao salvar cliente:', error);
        mostrarMensagem('Erro ao salvar cliente.', 'danger');
    }
}

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    const inputBusca = document.getElementById('buscar-nome');
    const btnBuscar = document.getElementById('btnBuscarClientes');
    const btnLimpar = document.getElementById('btnLimparBusca');
    const btnSalvar = document.getElementById('btnSalvarCliente');
    const modalElement = document.getElementById('modalNovoCliente');

    // Autocomplete na busca
    if (inputBusca) {
        configurarAutocompleteBusca(inputBusca);
    }

    // Botões de busca
    if (btnBuscar) {
        btnBuscar.addEventListener('click', buscarClientes);
    }

    if (btnLimpar) {
        btnLimpar.addEventListener('click', limparBusca);
    }

    // Configurar autocomplete no modal quando abrir
    if (modalElement) {
        modalElement.addEventListener('shown.bs.modal', () => {
            configurarAutocompleteModal();
            resetarModal();
        });
    }

    // Botão salvar
    if (btnSalvar) {
        btnSalvar.addEventListener('click', salvarCliente);
    }
});