// ===================== VARIÁVEIS GLOBAIS =====================
let clienteSelecionadoId = null;

// ===================== MÁSCARA MONETÁRIA =====================
function mascaraMoeda(input) {
    input.addEventListener('input', function(e) {
        let valor = this.value;
        
        // Remove tudo que não for número
        valor = valor.replace(/\D/g, '');
        
        // Se não tiver valor, mostra 0,00
        if (valor.length === 0) {
            this.value = '0,00';
            return;
        }
        
        // Converte para centavos (ex: 1 -> 0.01, 12 -> 0.12, 125 -> 1.25)
        let valorEmCentavos = parseInt(valor) / 100;
        
        // Formata para o padrão brasileiro
        this.value = valorEmCentavos.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    });
    
    // Garantir que ao perder o foco, tenha 2 casas decimais
    input.addEventListener('blur', function() {
        let valor = this.value.replace(/\D/g, '');
        if (valor.length === 0) {
            this.value = '0,00';
        } else {
            let valorEmCentavos = parseInt(valor) / 100;
            this.value = valorEmCentavos.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    });
}

// ===================== COLOCAR CURSOR NO FINAL =====================
function colocarCursorNoFinal(input) {
    input.addEventListener('click', function() {
        // Coloca o cursor no final do texto
        this.setSelectionRange(this.value.length, this.value.length);
    });
    
    input.addEventListener('focus', function() {
        this.setSelectionRange(this.value.length, this.value.length);
    });
}

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
    const mensagem = document.getElementById('mensagem');
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

// ===================== BUSCAR RECEBIMENTOS =====================
async function buscarRecebimentos() {
    const filtros = {
        cliente: document.getElementById('cliente').value.trim(),
        clienteId: clienteSelecionadoId,
        status: document.getElementById('status').value,
        dataInicio: document.getElementById('dataVendaInicio').value,
        dataFim: document.getElementById('dataVendaFim').value,
        vencimentoInicio: document.getElementById('vencimentoInicio').value,
        vencimentoFim: document.getElementById('vencimentoFim').value
    };

    try {
        const btnBuscar = document.getElementById('btnBuscar');
        btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
        btnBuscar.disabled = true;
        
        const response = await fetch(`/api/recebimentos/busca?${new URLSearchParams(filtros)}`);
        const recebimentos = await response.json();

        const tbody = document.getElementById('tabela-corpo');
        tbody.innerHTML = '';

        if (recebimentos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">Nenhum recebimento encontrado.</td></tr>';
            document.getElementById('tabela-recebimentos').style.display = 'block';
            return;
        }

        recebimentos.forEach(rec => {
            const tr = document.createElement('tr');
            tr.dataset.id = rec.id;
            tr.dataset.valorTotal = rec.valor_total;
            tr.innerHTML = `
                <td>${rec.venda_id}</td>
                <td>${rec.cliente}</td>
                <td>${formatarData(rec.data_venda)}</td>
                <td>${formatarData(rec.data_vencimento)}</td>
                <td>R$ ${formatarMoeda(rec.valor_total)}</td>
                <td class="valor-pago-cell">R$ ${formatarMoeda(rec.valor_pago)}</td>
                <td class="data-pagamento-cell">${formatarData(rec.data_pagamento)}</td>
                <td class="status-cell">${rec.status}</td>
                <td class="forma-pagamento-cell">${rec.forma_pagamento || '-'}</td>
                <td>
                    <button class="btn btn-primary btn-sm btn-acao-modal" 
                            data-id="${rec.id}"
                            data-venda="${rec.venda_id}"
                            data-cliente="${rec.cliente}"
                            data-data-venda="${formatarData(rec.data_venda)}"
                            data-valor-total="${rec.valor_total}"
                            data-status="${rec.status}"
                            data-valor-pago="${rec.valor_pago}"
                            data-data-pagamento="${rec.data_pagamento || ''}"
                            data-forma-pagamento="${rec.forma_pagamento || ''}">
                        <i class="fas fa-edit"></i> Ações
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('tabela-recebimentos').style.display = 'block';
        mostrarMensagem(`${recebimentos.length} recebimento(s) encontrado(s).`, 'success');

    } catch (error) {
        console.error('Erro ao buscar recebimentos:', error);
        mostrarMensagem('Erro ao buscar recebimentos.', 'danger');
    } finally {
        document.getElementById('btnBuscar').innerHTML = '<i class="fas fa-search"></i> Buscar';
        document.getElementById('btnBuscar').disabled = false;
    }
}

// ===================== LIMPAR FILTROS =====================
function limparFiltros() {
    document.getElementById('cliente').value = '';
    document.getElementById('status').value = '';
    document.getElementById('dataVendaInicio').value = '';
    document.getElementById('dataVendaFim').value = '';
    document.getElementById('vencimentoInicio').value = '';
    document.getElementById('vencimentoFim').value = '';
    clienteSelecionadoId = null;
    
    document.getElementById('tabela-corpo').innerHTML = '';
    document.getElementById('tabela-recebimentos').style.display = 'none';
    mostrarMensagem('', '');
}

// ===================== ABRIR MODAL DE EDIÇÃO =====================
function abrirModalEdicao(btn) {
    // Pegar dados do botão
    const id = btn.dataset.id;
    const vendaId = btn.dataset.venda;
    const cliente = btn.dataset.cliente;
    const dataVenda = btn.dataset.dataVenda;
    const valorTotal = parseFloat(btn.dataset.valorTotal);
    const status = btn.dataset.status;
    const valorPago = parseFloat(btn.dataset.valorPago);
    const dataPagamento = btn.dataset.dataPagamento;
    const formaPagamento = btn.dataset.formaPagamento;
    
    // Preencher informações fixas
    document.getElementById('modal-venda-id').textContent = vendaId;
    document.getElementById('modal-cliente').textContent = cliente;
    document.getElementById('modal-data-venda').textContent = dataVenda;
    document.getElementById('modal-valor-total').textContent = `R$ ${formatarMoeda(valorTotal)}`;
    
    // Preencher campos editáveis
    document.getElementById('modal-status').value = status;
    document.getElementById('modal-valor-pago').value = valorPago.toFixed(2).replace('.', ',');
    document.getElementById('modal-data-pagamento').value = dataPagamento !== 'null' && dataPagamento ? formatarData(dataPagamento) : '';
    document.getElementById('modal-forma-pagamento').value = formaPagamento || '';
    
    // Aplicar máscara no campo valor pago
    const valorPagoInput = document.getElementById('modal-valor-pago');
    mascaraMoeda(valorPagoInput);
    colocarCursorNoFinal(valorPagoInput);
    
    // Guardar ID para usar no salvamento
    document.getElementById('btn-salvar-modal').dataset.recebimentoId = id;
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalEdicaoRecebimento'));
    modal.show();
}

// ===================== SALVAR MODAL =====================
document.getElementById('btn-salvar-modal').addEventListener('click', async function() {
    const id = this.dataset.recebimentoId;
    
    const valorPagoTexto = document.getElementById('modal-valor-pago').value;
    const novoValorPago = parseFloat(valorPagoTexto.replace(/\./g, '').replace(',', '.'));
    const novaDataPagamento = document.getElementById('modal-data-pagamento').value;
    
    const dados = {
        status: document.getElementById('modal-status').value,
        valor_pago: novoValorPago,
        data_pagamento: novaDataPagamento,
        forma_pagamento: document.getElementById('modal-forma-pagamento').value
    };
    
    // Validar se status pago tem data e forma
    if (dados.status === 'pago') {
        if (!dados.data_pagamento || !dados.forma_pagamento) {
            mostrarMensagem('Para status PAGO, informe data e forma de pagamento.', 'warning');
            return;
        }
    }
    
    // Converter data para ISO se preenchida
    if (dados.data_pagamento && dados.data_pagamento.length === 10) {
        const partes = dados.data_pagamento.split('/');
        dados.data_pagamento = `${partes[2]}-${partes[1]}-${partes[0]}`;
    }
    
    try {
        const response = await fetch(`/api/recebimentos/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(dados)
        });

        const resultado = await response.json();

        if (resultado.success) {
            // Fechar modal
            bootstrap.Modal.getInstance(document.getElementById('modalEdicaoRecebimento')).hide();
            mostrarMensagem('Recebimento atualizado com sucesso!', 'success');
            buscarRecebimentos(); // Recarrega a lista
        } else {
            mostrarMensagem(resultado.message || 'Erro ao atualizar recebimento.', 'danger');
        }
    } catch (error) {
        console.error('Erro ao atualizar recebimento:', error);
        mostrarMensagem('Erro ao atualizar recebimento.', 'danger');
    }
});

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    configurarAutocompleteCliente();
    
    document.getElementById('btnBuscar').addEventListener('click', buscarRecebimentos);
    document.getElementById('btnLimpar').addEventListener('click', limparFiltros);
    
    // Delegação de eventos para botões do modal
    document.getElementById('tabela-corpo').addEventListener('click', (e) => {
        const btnModal = e.target.closest('.btn-acao-modal');
        if (btnModal) {
            abrirModalEdicao(btnModal);
        }
    });

    // Verificar se veio com status da URL
    const urlParams = new URLSearchParams(window.location.search);
    const statusParam = urlParams.get('status');
    if (statusParam) {
        document.getElementById('status').value = statusParam;
        // Disparar busca automaticamente
        setTimeout(() => {
            document.getElementById('btnBuscar').click();
        }, 500);
    }
});