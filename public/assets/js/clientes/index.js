// ===================== CLIENTES INDEX =====================
let clienteSelecionadoModal = null;

// ===================== CONFIGURAÇÃO BASE =====================
// Detectar base URL automaticamente
const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -2).join('/');

// Obter token CSRF
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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
    
    // Scroll suave para a mensagem
    mensagem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    setTimeout(() => {
        mensagem.style.display = 'none';
    }, 5000);
}

function mostrarLoading(btn, mostrar) {
    if (!btn) return;
    if (mostrar) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...';
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.getAttribute('data-original-text') || btn.innerHTML;
    }
}

// ===================== VERIFICAR SE CLIENTE JÁ EXISTE =====================
async function verificarClienteExistente(nome, idIgnorar = null) {
    if (!nome || nome.length < 2) return false;
    
    try {
        // Usar URL relativa - mais compatível com diferentes ambientes
        let url = `/api/clientes/verificar?nome=${encodeURIComponent(nome)}`;
        if (idIgnorar) {
            url += `&ignore_id=${idIgnorar}`;
        }
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
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
    
    // Remover event listener anterior se existir
    inputNome.removeEventListener('keyup', handleNomeKeyup);
    inputNome.addEventListener('keyup', handleNomeKeyup);
    
    async function handleNomeKeyup() {
        const termo = inputNome.value.trim();
        sugestoes.innerHTML = '';
        sugestoes.style.display = 'none';
        clienteSelecionadoModal = null;

        if (termo.length < 2) return;

        try {
            const response = await fetch(`/api/clientes/busca?termo=${encodeURIComponent(termo)}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const clientes = await response.json();

            if (!clientes || clientes.length === 0) return;

            clientes.forEach(cliente => {
                const div = document.createElement('div');
                div.className = 'list-group-item list-group-item-action suggestion-item';
                div.textContent = cliente.nome;
                div.style.cursor = 'pointer';
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
    }

    // Fechar sugestões ao clicar fora
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
    
    inputBusca.removeEventListener('keyup', handleBuscaKeyup);
    inputBusca.addEventListener('keyup', handleBuscaKeyup);
    
    async function handleBuscaKeyup() {
        const termo = inputBusca.value.trim();
        sugestoes.innerHTML = '';
        sugestoes.style.display = 'none';

        if (termo.length < 2) return;

        try {
            const response = await fetch(`/api/clientes/busca?termo=${encodeURIComponent(termo)}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const clientes = await response.json();

            if (!clientes || clientes.length === 0) return;

            clientes.forEach(cliente => {
                const div = document.createElement('div');
                div.className = 'list-group-item list-group-item-action suggestion-item';
                div.textContent = cliente.nome;
                div.style.cursor = 'pointer';
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
    }

    document.addEventListener('click', (e) => {
        if (!sugestoes.contains(e.target) && e.target !== inputBusca) {
            sugestoes.style.display = 'none';
        }
    });
}

// ===================== BUSCAR CLIENTES =====================
async function buscarClientes() {
    const nome = document.getElementById('buscar-nome')?.value.trim() || '';
    const btnBuscar = document.getElementById('btnBuscarClientes');
    const originalText = btnBuscar?.innerHTML;
    
    try {
        if (btnBuscar) {
            btnBuscar.setAttribute('data-original-text', originalText);
            btnBuscar.disabled = true;
            btnBuscar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Buscando...';
        }
        
        // URL correta para busca
        const url = nome 
            ? `/api/clientes/busca?termo=${encodeURIComponent(nome)}`
            : '/api/clientes';
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const clientes = await response.json();

        const tbody = document.getElementById('tabela-corpo');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        const tabelaContainer = document.getElementById('tabela-cliente');

        if (!clientes || clientes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Nenhum cliente encontrado.</td></tr>';
            if (tabelaContainer) tabelaContainer.style.display = 'block';
            mostrarMensagem('Nenhum cliente encontrado.', 'warning');
            return;
        }

        clientes.forEach(cliente => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(cliente.nome)}</td>
                <td>${formatarTelefoneTexto(cliente.telefone) || '-'}</td>
                <td>${escapeHtml(cliente.observacao) || '-'}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="/clientes/${cliente.id}/edit" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <button type="button" class="btn btn-danger btn-sm btn-excluir" data-id="${cliente.id}" data-nome="${escapeHtml(cliente.nome)}">
                            <i class="fas fa-trash-alt"></i> Excluir
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
        
        // Adicionar eventos de exclusão
        document.querySelectorAll('.btn-excluir').forEach(btn => {
            btn.removeEventListener('click', handleExclusao);
            btn.addEventListener('click', handleExclusao);
        });

        if (tabelaContainer) {
            tabelaContainer.style.display = 'block';
        }

        mostrarMensagem(`${clientes.length} cliente(s) encontrado(s).`, 'success');

    } catch (error) {
        console.error('Erro ao buscar clientes:', error);
        mostrarMensagem('Erro ao buscar clientes: ' + error.message, 'danger');
    } finally {
        if (btnBuscar) {
            btnBuscar.disabled = false;
            btnBuscar.innerHTML = originalText || '<i class="fas fa-search"></i> Buscar';
        }
    }
}

// ===================== HANDLE EXCLUSÃO =====================
async function handleExclusao(event) {
    const btn = event.currentTarget;
    const id = btn.getAttribute('data-id');
    const nome = btn.getAttribute('data-nome');
    
    if (confirm(`Tem certeza que deseja excluir o cliente "${nome}"?\n\n⚠️ Esta ação não pode ser desfeita!`)) {
        try {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            const response = await fetch(`/api/clientes/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                mostrarMensagem('Cliente excluído com sucesso!', 'success');
                // Recarregar lista
                await buscarClientes();
            } else {
                mostrarMensagem(data.message || 'Erro ao excluir cliente.', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash-alt"></i> Excluir';
            }
        } catch (error) {
            console.error('Erro ao excluir cliente:', error);
            mostrarMensagem('Erro ao excluir cliente: ' + error.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash-alt"></i> Excluir';
        }
    }
}

// ===================== LIMPAR BUSCA =====================
function limparBusca() {
    const inputBusca = document.getElementById('buscar-nome');
    if (inputBusca) inputBusca.value = '';
    
    const tbody = document.getElementById('tabela-corpo');
    if (tbody) tbody.innerHTML = '';
    
    const tabelaContainer = document.getElementById('tabela-cliente');
    if (tabelaContainer) {
        tabelaContainer.style.display = 'none';
    }
    
    mostrarMensagem('Busca limpa.', 'info');
}

// ===================== RESETAR MODAL =====================
function resetarModal() {
    const inputNome = document.getElementById('modal-nome');
    const inputTelefone = document.getElementById('modal-telefone');
    const inputObservacao = document.getElementById('modal-observacao');
    
    if (inputNome) inputNome.value = '';
    if (inputTelefone) inputTelefone.value = '';
    if (inputObservacao) inputObservacao.value = '';
    
    if (inputTelefone) {
        inputTelefone.disabled = false;
        inputTelefone.style.backgroundColor = '';
    }
    if (inputObservacao) {
        inputObservacao.disabled = false;
        inputObservacao.style.backgroundColor = '';
    }
    
    clienteSelecionadoModal = null;
    
    // Limpar mensagens de erro
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
}

// ===================== SALVAR CLIENTE =====================
async function salvarCliente() {
    const nome = document.getElementById('modal-nome')?.value.trim();
    const telefone = document.getElementById('modal-telefone')?.value.trim().replace(/\D/g, '');
    const observacao = document.getElementById('modal-observacao')?.value.trim();
    const btnSalvar = document.getElementById('btnSalvarCliente');
    
    if (!nome) {
        mostrarMensagem('O nome é obrigatório.', 'danger');
        document.getElementById('modal-nome')?.focus();
        return;
    }
    
    // Validar telefone se preenchido
    if (telefone && telefone.length !== 10 && telefone.length !== 11) {
        mostrarMensagem('Telefone inválido. Use o formato (99) 99999-9999 ou (99) 9999-9999', 'danger');
        document.getElementById('modal-telefone')?.focus();
        return;
    }
    
    try {
        if (btnSalvar) {
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Salvando...';
        }
        
        // Verificar se já existe (para SQLite)
        const existe = await verificarClienteExistente(nome);
        
        if (existe) {
            mostrarMensagem('❌ Cliente já cadastrado!', 'danger');
            if (btnSalvar) {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar';
            }
            return;
        }
        
        const response = await fetch('/api/clientes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                nome, 
                telefone: telefone || null, // Enviar null ao invés de string vazia
                observacao: observacao || null 
            })
        });
        
        const resultado = await response.json();
        
        if (resultado.success) {
            mostrarMensagem('Cliente cadastrado com sucesso!', 'success');
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoCliente'));
            if (modal) modal.hide();
            
            // Resetar campos
            resetarModal();
            
            // Atualizar busca se houver termo
            const inputBusca = document.getElementById('buscar-nome');
            if (inputBusca && inputBusca.value) {
                await buscarClientes();
            } else {
                // Opcional: mostrar mensagem para buscar
                mostrarMensagem('Use o campo de busca para listar os clientes.', 'info');
            }
        } else {
            const errors = resultado.errors || {};
            const errorMessages = Object.values(errors).flat().join('\n');
            mostrarMensagem(errorMessages || resultado.message || 'Erro ao cadastrar cliente.', 'danger');
        }
    } catch (error) {
        console.error('Erro ao salvar cliente:', error);
        mostrarMensagem('Erro ao salvar cliente: ' + error.message, 'danger');
    } finally {
        if (btnSalvar) {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar';
        }
    }
}

// ===================== FUNÇÃO AUXILIAR ESCAPE HTML =====================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
        
        // Buscar ao pressionar Enter
        inputBusca.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarClientes();
            }
        });
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
            // Focar no campo nome
            setTimeout(() => {
                document.getElementById('modal-nome')?.focus();
            }, 100);
        });
    }

    // Botão salvar
    if (btnSalvar) {
        btnSalvar.addEventListener('click', salvarCliente);
    }
    
    // Máscara de telefone no modal
    const telefoneInput = document.getElementById('modal-telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', formatarTelefone);
    }
    
    console.log('✅ Cliente JS inicializado para SQLite');
});