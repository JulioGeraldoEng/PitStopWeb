// ===================== VARIÁVEIS GLOBAIS =====================
const mensagem = document.getElementById('mensagem');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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

function desformatarTelefone(telefoneFormatado) {
    if (!telefoneFormatado) return '';
    return telefoneFormatado.replace(/\D/g, '');
}

// ===================== AUTOCOMPLETE =====================
function configurarAutocompleteNome(inputNome) {
    console.log('Configurando autocomplete para:', inputNome);
    
    const sugestoes = document.getElementById('sugestoes-clientes');
    if (!sugestoes) return;
    
    let timeoutId;
    
    inputNome.addEventListener('keyup', function(e) {
        const termo = this.value.trim();
        console.log('Termo digitado:', termo);
        
        // Limpar timeout anterior
        if (timeoutId) clearTimeout(timeoutId);
        
        if (termo.length < 2) {
            sugestoes.style.display = 'none';
            return;
        }
        
        // Debounce para não fazer muitas requisições
        timeoutId = setTimeout(() => {
            sugestoes.innerHTML = '<div class="list-group-item">Carregando...</div>';
            sugestoes.style.display = 'block';
            
            fetch(`/api/clientes/busca?termo=${encodeURIComponent(termo)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(clientes => {
                    console.log('Clientes recebidos:', clientes);
                    sugestoes.innerHTML = '';
                    
                    if (!clientes || clientes.length === 0) {
                        sugestoes.innerHTML = '<div class="list-group-item">Nenhum cliente encontrado</div>';
                        return;
                    }
                    
                    clientes.forEach(cliente => {
                        const div = document.createElement('div');
                        div.className = 'list-group-item list-group-item-action suggestion-item';
                        div.textContent = cliente.nome;
                        div.style.cursor = 'pointer';
                        div.onclick = (e) => {
                            e.preventDefault();
                            inputNome.value = cliente.nome;
                            sugestoes.style.display = 'none';
                            
                            // Opcional: preencher telefone e observação se existirem
                            const telefoneInput = document.getElementById('telefone');
                            const observacaoInput = document.getElementById('observacao');
                            
                            if (telefoneInput && cliente.telefone) {
                                telefoneInput.value = formatarTelefoneTexto(cliente.telefone);
                            }
                            
                            if (observacaoInput && cliente.observacao) {
                                observacaoInput.value = cliente.observacao;
                            }
                        };
                        sugestoes.appendChild(div);
                    });
                })
                .catch(error => {
                    console.error('Erro no fetch:', error);
                    sugestoes.innerHTML = '<div class="list-group-item text-danger">Erro ao buscar clientes</div>';
                });
        }, 300); // Delay de 300ms para debounce
    });
    
    // Fechar sugestões ao clicar fora
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
    
    if (!container || !tbody) return;
    
    if (container.style.display === 'block') {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    tbody.innerHTML = '服务<td colspan="5" class="text-center"><div class="spinner-border spinner-border-sm"></div> Carregando...</td></tr>';

    try {
        const response = await fetch('/api/clientes');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const clientes = await response.json();

        if (!clientes || clientes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum cliente cadastrado.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        clientes.forEach(cliente => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', cliente.id);
            tr.innerHTML = `
                <td>${cliente.id}</td>
                <td>
                    <input type="text" class="form-control form-control-sm campo-nome" 
                           value="${escapeHtml(cliente.nome)}" 
                           data-original="${escapeHtml(cliente.nome)}" />
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm campo-telefone" 
                           value="${escapeHtml(formatarTelefoneTexto(cliente.telefone))}" 
                           data-original="${escapeHtml(cliente.telefone || '')}" />
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm campo-observacao" 
                           value="${escapeHtml(cliente.observacao || '')}" 
                           data-original="${escapeHtml(cliente.observacao || '')}" />
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-warning btn-alterar" data-id="${cliente.id}">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                        <button class="btn btn-danger btn-remover" data-id="${cliente.id}" data-nome="${escapeHtml(cliente.nome)}">
                            <i class="fas fa-trash-alt"></i> Excluir
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Adicionar máscara de telefone nos campos
        document.querySelectorAll('.campo-telefone').forEach(input => {
            input.addEventListener('input', formatarTelefone);
        });
        
        // Adicionar eventos para os botões
        document.querySelectorAll('.btn-alterar').forEach(btn => {
            btn.removeEventListener('click', handleAtualizarCliente);
            btn.addEventListener('click', handleAtualizarCliente);
        });
        
        document.querySelectorAll('.btn-remover').forEach(btn => {
            btn.removeEventListener('click', handleExcluirCliente);
            btn.addEventListener('click', handleExcluirCliente);
        });

    } catch (error) {
        console.error('Erro ao buscar clientes:', error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erro ao carregar clientes. Tente novamente.</td></tr>';
        mostrarMensagem('Erro ao carregar clientes: ' + error.message, 'danger');
    }
}

// ===================== HANDLE ATUALIZAR CLIENTE =====================
async function handleAtualizarCliente(event) {
    const btn = event.currentTarget;
    const row = btn.closest('tr');
    const id = btn.getAttribute('data-id');
    
    // Desabilitar botão durante a operação
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    try {
        const nomeInput = row.querySelector('.campo-nome');
        const telefoneInput = row.querySelector('.campo-telefone');
        const observacaoInput = row.querySelector('.campo-observacao');
        
        const nome = nomeInput?.value.trim();
        const telefone = telefoneInput?.value.trim().replace(/\D/g, '');
        const observacao = observacaoInput?.value.trim();
        
        // Validar nome
        if (!nome) {
            mostrarMensagem('O nome é obrigatório.', 'danger');
            nomeInput?.focus();
            return;
        }
        
        // Validar telefone se preenchido
        if (telefone && telefone.length !== 10 && telefone.length !== 11) {
            mostrarMensagem('Telefone inválido. Use (99) 99999-9999 ou (99) 9999-9999', 'danger');
            telefoneInput?.focus();
            return;
        }
        
        // Verificar se já existe outro cliente com o mesmo nome (para SQLite)
        const existe = await verificarClienteExistente(nome, id);
        if (existe) {
            mostrarMensagem('❌ Já existe um cliente com este nome!', 'danger');
            return;
        }
        
        const response = await fetch(`/api/clientes/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                nome, 
                telefone: telefone || null,
                observacao: observacao || null
            })
        });

        const resultado = await response.json();
        
        if (resultado.success) {
            // Atualizar valores originais
            nomeInput.setAttribute('data-original', nome);
            telefoneInput.setAttribute('data-original', telefone || '');
            observacaoInput.setAttribute('data-original', observacao || '');
            
            mostrarMensagem('Cliente atualizado com sucesso!', 'success');
            
            // Remover classe de modificado se existir
            nomeInput.classList.remove('is-modified');
            telefoneInput.classList.remove('is-modified');
            observacaoInput.classList.remove('is-modified');
        } else {
            const errors = resultado.errors || {};
            const errorMessages = Object.values(errors).flat().join('\n');
            mostrarMensagem(errorMessages || resultado.message || 'Erro ao atualizar cliente.', 'danger');
            
            // Restaurar valores originais
            nomeInput.value = nomeInput.getAttribute('data-original');
            telefoneInput.value = formatarTelefoneTexto(telefoneInput.getAttribute('data-original'));
            observacaoInput.value = observacaoInput.getAttribute('data-original');
        }
    } catch (error) {
        console.error('Erro ao atualizar cliente:', error);
        mostrarMensagem('Erro interno ao atualizar: ' + error.message, 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

// ===================== HANDLE EXCLUIR CLIENTE =====================
async function handleExcluirCliente(event) {
    const btn = event.currentTarget;
    const id = btn.getAttribute('data-id');
    const nome = btn.getAttribute('data-nome');
    
    if (!confirm(`Tem certeza que deseja excluir o cliente "${nome}"?\n\n⚠️ Esta ação não pode ser desfeita!`)) {
        return;
    }
    
    // Desabilitar botão durante a operação
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    try {
        const response = await fetch(`/api/clientes/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const resultado = await response.json();
        
        if (resultado.success) {
            mostrarMensagem('Cliente excluído com sucesso!', 'success');
            btn.closest('tr').remove();
            
            // Se não houver mais clientes, mostrar mensagem
            const tbody = document.querySelector('#tabelaClientes tbody');
            if (tbody && tbody.children.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum cliente cadastrado.</td></tr>';
            }
        } else {
            mostrarMensagem(resultado.message || 'Erro ao excluir cliente. Verifique se não há vendas vinculadas.', 'danger');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    } catch (error) {
        console.error('Erro ao excluir cliente:', error);
        mostrarMensagem('Erro interno ao excluir: ' + error.message, 'danger');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

// ===================== VERIFICAR CLIENTE EXISTENTE =====================
async function verificarClienteExistente(nome, idIgnorar = null) {
    if (!nome || nome.length < 2) return false;
    
    try {
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

// ===================== UTILITÁRIOS =====================
function mostrarMensagem(texto, tipo) {
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

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ===================== VALIDAÇÃO EM TEMPO REAL =====================
function configurarValidacaoTempoReal() {
    const nomeInput = document.getElementById('nome');
    const telefoneInput = document.getElementById('telefone');
    
    if (nomeInput) {
        nomeInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const errorDiv = document.getElementById('error-nome');
            if (errorDiv) errorDiv.textContent = '';
        });
    }
    
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const errorDiv = document.getElementById('error-telefone');
            if (errorDiv) errorDiv.textContent = '';
        });
    }
}

// ===================== SALVAR CLIENTE (FORMULÁRIO TRADICIONAL) =====================
// O formulário já é enviado normalmente via POST
// Esta função é apenas para validação antes do envio
function configurarValidacaoFormulario() {
    const form = document.getElementById('clienteForm');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        const nome = document.getElementById('nome')?.value.trim();
        const telefone = document.getElementById('telefone')?.value.trim().replace(/\D/g, '');
        let hasError = false;
        
        // Validar nome
        if (!nome) {
            e.preventDefault();
            mostrarMensagem('O nome é obrigatório.', 'danger');
            document.getElementById('nome')?.focus();
            hasError = true;
        }
        
        // Validar telefone se preenchido
        if (!hasError && telefone && telefone.length !== 10 && telefone.length !== 11) {
            e.preventDefault();
            mostrarMensagem('Telefone inválido. Use o formato (99) 99999-9999 ou (99) 9999-9999', 'danger');
            document.getElementById('telefone')?.focus();
            hasError = true;
        }
        
        // Verificar duplicidade (apenas se for novo cliente, sem ID)
        if (!hasError && !document.getElementById('cliente-id')?.value) {
            const existe = await verificarClienteExistente(nome);
            if (existe) {
                e.preventDefault();
                mostrarMensagem('❌ Já existe um cliente com este nome!', 'danger');
                document.getElementById('nome')?.focus();
                hasError = true;
            }
        }
        
        if (!hasError) {
            // Mostrar loading no botão
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Salvando...';
            }
        }
    });
}

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    const inputTelefone = document.getElementById('telefone');
    const inputNome = document.getElementById('nome');
    const btnClientes = document.getElementById('btnClientesCadastrados');

    // Máscara de telefone
    if (inputTelefone) {
        inputTelefone.addEventListener('input', formatarTelefone);
    }

    // Autocomplete
    if (inputNome) {
        configurarAutocompleteNome(inputNome);
    }

    // Botão listar clientes
    if (btnClientes) {
        btnClientes.addEventListener('click', carregarClientes);
    }
    
    // Validação em tempo real
    configurarValidacaoTempoReal();
    
    // Validação do formulário
    configurarValidacaoFormulario();
    
    console.log('✅ Cliente form JS inicializado para SQLite');
});