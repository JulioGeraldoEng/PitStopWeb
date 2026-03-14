// ===================== VARIÁVEIS GLOBAIS =====================
let clienteSelecionadoId = null;

// ===================== FUNÇÕES AUXILIARES =====================
function formatarData(data) {
    if (!data) return '-';
    const partes = data.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return data;
}

function formatarMoeda(valor) {
    return parseFloat(valor).toLocaleString('pt-BR', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
}

function mostrarMensagem(texto, tipo) {
    const mensagem = document.createElement('div');
    mensagem.className = `alert alert-${tipo}`;
    mensagem.textContent = texto;
    mensagem.style.position = 'fixed';
    mensagem.style.top = '80px';
    mensagem.style.right = '20px';
    mensagem.style.zIndex = '9999';
    document.body.appendChild(mensagem);
    setTimeout(() => mensagem.remove(), 3000);
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

// ===================== BUSCAR VENDAS COM FILTROS =====================
async function buscarVendas() {
    const filtros = {
        cliente: document.getElementById('cliente')?.value.trim() || '',
        clienteId: clienteSelecionadoId,
        status: document.getElementById('status')?.value || '',
        dataInicio: document.getElementById('dataInicio')?.value || '',
        dataFim: document.getElementById('dataFim')?.value || '',
        vencimentoInicio: document.getElementById('vencimentoInicio')?.value || '',
        vencimentoFim: document.getElementById('vencimentoFim')?.value || ''
    };

    try {
        const btnBuscar = document.getElementById('btnBuscar');
        if (btnBuscar) {
            btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
            btnBuscar.disabled = true;
        }
        
        const response = await fetch(`/api/vendas/busca?${new URLSearchParams(filtros)}`);
        const vendas = await response.json();

        const tbody = document.getElementById('tabela-corpo');
        if (!tbody) return;
        
        tbody.innerHTML = '';

        const tabelaContainer = document.getElementById('tabela-venda');

        if (!vendas || vendas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhuma venda encontrada.</td></tr>';
            if (tabelaContainer) tabelaContainer.style.display = 'block';
            return;
        }

        vendas.forEach(venda => {
            const tr = document.createElement('tr');
            
            // Criar células na ordem correta
            tr.innerHTML = `
                <td>${venda.cliente}</td>
                <td>${formatarData(venda.data)}</td>
                <td>${venda.vencimento ? formatarData(venda.vencimento) : '-'}</td>
                <td>R$ ${formatarMoeda(venda.total)}</td>
            `;
            
            // Criar célula de ações separadamente
            const tdAcoes = document.createElement('td');
            const acoesDiv = document.createElement('div');
            acoesDiv.className = 'acao-container';
            
            // Botão VER
            const btnVer = document.createElement('a');
            btnVer.href = `/vendas/${venda.id}`;
            btnVer.className = 'btn-ver';
            btnVer.innerHTML = '<i class="fas fa-eye"></i> Ver';
            acoesDiv.appendChild(btnVer);
            
            // Botão EDITAR
            const btnEditar = document.createElement('a');
            btnEditar.href = `/vendas/${venda.id}/edit`;
            btnEditar.className = 'btn-editar';
            btnEditar.innerHTML = '<i class="fas fa-edit"></i> Editar';
            acoesDiv.appendChild(btnEditar);
            
            // Formulário EXCLUIR
            const formExcluir = document.createElement('form');
            formExcluir.method = 'POST';
            formExcluir.action = `/vendas/${venda.id}`;
            formExcluir.style.display = 'inline';
            
            // Token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            formExcluir.appendChild(csrfInput);
            
            // Method DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            formExcluir.appendChild(methodInput);
            
            // Botão EXCLUIR
            const btnExcluir = document.createElement('button');
            btnExcluir.type = 'submit';
            btnExcluir.className = 'btn-excluir';
            btnExcluir.innerHTML = '<i class="fas fa-trash-alt"></i> Excluir';
            btnExcluir.onclick = function() {
                return confirm('Tem certeza que deseja excluir esta venda?');
            };
            formExcluir.appendChild(btnExcluir);
            
            acoesDiv.appendChild(formExcluir);
            tdAcoes.appendChild(acoesDiv);
            tr.appendChild(tdAcoes);
            
            tbody.appendChild(tr);
        });

        // MOSTRAR A TABELA
        if (tabelaContainer) {
            tabelaContainer.style.display = 'block';
        }

        mostrarMensagem(`${vendas.length} venda(s) encontrada(s).`, 'success');

    } catch (error) {
        console.error('Erro ao buscar vendas:', error);
        mostrarMensagem('Erro ao buscar vendas.', 'danger');
    } finally {
        const btnBuscar = document.getElementById('btnBuscar');
        if (btnBuscar) {
            btnBuscar.innerHTML = '<i class="fas fa-search"></i> Buscar';
            btnBuscar.disabled = false;
        }
    }
}

// ===================== LIMPAR FILTROS =====================
function limparFiltros() {
    document.getElementById('cliente').value = '';
    document.getElementById('cliente_id').value = '';
    document.getElementById('status').value = '';
    document.getElementById('dataInicio').value = '';
    document.getElementById('dataFim').value = '';
    document.getElementById('vencimentoInicio').value = '';
    document.getElementById('vencimentoFim').value = '';
    clienteSelecionadoId = null;
    
    // Limpar tabela
    const tbody = document.getElementById('tabela-corpo');
    if (tbody) tbody.innerHTML = '';
    
    // Esconder tabela
    const tabelaContainer = document.getElementById('tabela-venda');
    if (tabelaContainer) {
        tabelaContainer.style.display = 'none';
    }
    
    mostrarMensagem('', '');
}

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    // Esconder tabela no carregamento
    const tabelaContainer = document.getElementById('tabela-venda');
    if (tabelaContainer) {
        tabelaContainer.style.display = 'none';
    }
    
    // Configurar autocomplete
    configurarAutocompleteCliente();
    
    // Eventos dos botões
    document.getElementById('btnBuscar')?.addEventListener('click', buscarVendas);
    document.getElementById('btnLimpar')?.addEventListener('click', limparFiltros);
});