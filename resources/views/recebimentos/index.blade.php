@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Recebimentos</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <form id="filtrosForm" class="row g-3">
                <div class="col-md-4">
                    <label for="cliente" class="form-label">Cliente:</label>
                    <input type="text" class="form-control" id="cliente" placeholder="Nome do cliente" autocomplete="off">
                    <div id="sugestoes-cliente" class="list-group position-absolute" style="z-index: 1000; width: 90%; display: none;"></div>
                </div>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Status:</label>
                    <select class="form-control" id="status">
                        <option value="">Todos</option>
                        <option value="pendente">Pendente</option>
                        <option value="pago">Pago</option>
                        <option value="atrasado">Atrasado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="btnBuscar" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <button type="button" id="btnLimpar" class="btn btn-secondary">
                        <i class="fas fa-broom"></i> Limpar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="mensagem" class="alert" style="display: none;"></div>

    <div id="tabela-recebimentos" style="display: none;">
        <h3>Contas a Receber</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="tabela-contas-receber">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Venda</th>
                        <th>Cliente</th>
                        <th>Data da Venda</th>
                        <th>Vencimento</th>
                        <th>Valor Total</th>
                        <th>Valor Pago</th>
                        <th>Data Pagamento</th>
                        <th>Status</th>
                        <th>Forma Pagamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ===================== VARIÁVEIS GLOBAIS =====================
let clienteSelecionadoId = null;
const mensagem = document.getElementById('mensagem');
const inputCliente = document.getElementById('cliente');
const selectStatus = document.getElementById('status');
const btnBuscar = document.getElementById('btnBuscar');
const btnLimpar = document.getElementById('btnLimpar');
const tabelaContainer = document.getElementById('tabela-recebimentos');
const tbody = document.querySelector('#tabela-contas-receber tbody');

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
    mensagem.textContent = texto;
    mensagem.className = `alert alert-${tipo}`;
    mensagem.style.display = 'block';
    setTimeout(() => {
        mensagem.style.display = 'none';
    }, 3000);
}

// ===================== AUTOCOMPLETE CLIENTE =====================
function configurarAutocompleteCliente() {
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
        cliente: inputCliente.value.trim(),
        clienteId: clienteSelecionadoId,
        status: selectStatus.value
    };

    try {
        btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
        btnBuscar.disabled = true;
        
        const response = await fetch(`/api/recebimentos/busca?${new URLSearchParams(filtros)}`);
        const recebimentos = await response.json();

        tbody.innerHTML = '';

        if (recebimentos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center">Nenhum recebimento encontrado.</td></tr>';
            tabelaContainer.style.display = 'block';
            return;
        }

        recebimentos.forEach(rec => {
            const tr = document.createElement('tr');
            tr.dataset.id = rec.id;
            tr.dataset.valorTotal = rec.valor_total;
            tr.innerHTML = `
                <td>${rec.id}</td>
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
                    <button class="btn btn-sm btn-primary btn-acao" data-id="${rec.id}">
                        <i class="fas fa-edit"></i> Ações
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tabelaContainer.style.display = 'block';
        mostrarMensagem(`${recebimentos.length} recebimento(s) encontrado(s).`, 'success');

    } catch (error) {
        console.error('Erro ao buscar recebimentos:', error);
        mostrarMensagem('Erro ao buscar recebimentos.', 'danger');
    } finally {
        btnBuscar.innerHTML = '<i class="fas fa-search"></i> Buscar';
        btnBuscar.disabled = false;
    }
}

// ===================== ATUALIZAR RECEBIMENTO =====================
async function atualizarRecebimento(id, dados) {
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
            mostrarMensagem('Recebimento atualizado com sucesso!', 'success');
            buscarRecebimentos(); // Recarrega a lista
        } else {
            mostrarMensagem(resultado.message || 'Erro ao atualizar recebimento.', 'danger');
        }
    } catch (error) {
        console.error('Erro ao atualizar recebimento:', error);
        mostrarMensagem('Erro ao atualizar recebimento.', 'danger');
    }
}

// ===================== MODO DE EDIÇÃO =====================
function entrarModoEdicao(btn) {
    const row = btn.closest('tr');
    const id = row.dataset.id;
    const valorTotal = parseFloat(row.dataset.valorTotal);
    
    const statusCell = row.querySelector('.status-cell');
    const valorPagoCell = row.querySelector('.valor-pago-cell');
    const dataPagamentoCell = row.querySelector('.data-pagamento-cell');
    const formaPagamentoCell = row.querySelector('.forma-pagamento-cell');
    const acoesCell = row.querySelector('td:last-child');

    // Salvar valores originais
    const statusOriginal = statusCell.textContent.trim();
    const valorPagoOriginal = parseFloat(valorPagoCell.textContent.replace('R$', '').replace('.', '').replace(',', '.').trim());
    const dataPagamentoOriginal = dataPagamentoCell.textContent.trim();
    const formaPagamentoOriginal = formaPagamentoCell.textContent.trim();

    // Criar inputs
    statusCell.innerHTML = `
        <select class="form-control form-control-sm status-select">
            <option value="pendente" ${statusOriginal === 'pendente' ? 'selected' : ''}>Pendente</option>
            <option value="pago" ${statusOriginal === 'pago' ? 'selected' : ''}>Pago</option>
            <option value="atrasado" ${statusOriginal === 'atrasado' ? 'selected' : ''}>Atrasado</option>
            <option value="cancelado" ${statusOriginal === 'cancelado' ? 'selected' : ''}>Cancelado</option>
        </select>
    `;

    valorPagoCell.innerHTML = `
        <input type="number" class="form-control form-control-sm valor-pago-input" 
               value="${valorPagoOriginal}" step="0.01" min="0">
    `;

    dataPagamentoCell.innerHTML = `
        <input type="text" class="form-control form-control-sm data-pagamento-input" 
               value="${dataPagamentoOriginal !== '-' ? dataPagamentoOriginal : ''}" 
               placeholder="dd/mm/aaaa" maxlength="10">
    `;

    formaPagamentoCell.innerHTML = `
        <select class="form-control form-control-sm forma-pagamento-select">
            <option value="">Selecione</option>
            <option value="dinheiro" ${formaPagamentoOriginal === 'dinheiro' ? 'selected' : ''}>Dinheiro</option>
            <option value="cartao_credito" ${formaPagamentoOriginal === 'cartao_credito' ? 'selected' : ''}>Cartão de Crédito</option>
            <option value="cartao_debito" ${formaPagamentoOriginal === 'cartao_debito' ? 'selected' : ''}>Cartão de Débito</option>
            <option value="pix" ${formaPagamentoOriginal === 'pix' ? 'selected' : ''}>PIX</option>
            <option value="boleto" ${formaPagamentoOriginal === 'boleto' ? 'selected' : ''}>Boleto</option>
        </select>
    `;

    acoesCell.innerHTML = `
        <button class="btn btn-sm btn-success btn-salvar" data-id="${id}">
            <i class="fas fa-save"></i>
        </button>
        <button class="btn btn-sm btn-secondary btn-cancelar">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Máscara para data
    const dataInput = row.querySelector('.data-pagamento-input');
    if (dataInput) {
        dataInput.addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            if (valor.length > 2 && valor.length <= 4) {
                valor = valor.replace(/(\d{2})(\d{1,2})/, '$1/$2');
            } else if (valor.length > 4) {
                valor = valor.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
            }
            this.value = valor.substring(0, 10);
        });
    }

    // Botão salvar
    row.querySelector('.btn-salvar').addEventListener('click', () => {
        const novoStatus = row.querySelector('.status-select').value;
        const novoValorPago = parseFloat(row.querySelector('.valor-pago-input').value);
        const novaDataPagamento = row.querySelector('.data-pagamento-input').value;
        const novaFormaPagamento = row.querySelector('.forma-pagamento-select').value;

        // Validar se status pago tem data e forma
        if (novoStatus === 'pago') {
            if (!novaDataPagamento || !novaFormaPagamento) {
                mostrarMensagem('Para status PAGO, informe data e forma de pagamento.', 'warning');
                return;
            }
        }

        // Converter data para ISO se preenchida
        let dataPagamentoISO = null;
        if (novaDataPagamento && novaDataPagamento.length === 10) {
            const partes = novaDataPagamento.split('/');
            dataPagamentoISO = `${partes[2]}-${partes[1]}-${partes[0]}`;
        }

        atualizarRecebimento(id, {
            status: novoStatus,
            valor_pago: novoValorPago,
            data_pagamento: dataPagamentoISO,
            forma_pagamento: novaFormaPagamento
        });
    });

    // Botão cancelar
    row.querySelector('.btn-cancelar').addEventListener('click', () => {
        buscarRecebimentos(); // Recarrega a lista
    });
}

// ===================== EVENTOS =====================
document.addEventListener('DOMContentLoaded', () => {
    configurarAutocompleteCliente();

    btnBuscar.addEventListener('click', buscarRecebimentos);

    btnLimpar.addEventListener('click', () => {
        inputCliente.value = '';
        selectStatus.value = '';
        clienteSelecionadoId = null;
        tbody.innerHTML = '';
        tabelaContainer.style.display = 'none';
        mostrarMensagem('', '');
    });

    // Delegação de eventos para botões de ação
    tbody.addEventListener('click', (e) => {
        const btnAcao = e.target.closest('.btn-acao');
        if (btnAcao) {
            entrarModoEdicao(btnAcao);
        }
    });
});
</script>
@endpush