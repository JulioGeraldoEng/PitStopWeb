@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Relatório de Vendas</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <form id="filtrosForm" class="row g-3">
                <div class="col-md-4">
                    <label for="cliente" class="form-label">Cliente:</label>
                    <input type="text" class="form-control" id="cliente" placeholder="Nome do cliente" autocomplete="off">
                    <input type="hidden" id="cliente_id">
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
                
                <div class="col-md-3">
                    <label for="dataInicio" class="form-label">Data Inicial:</label>
                    <input type="text" class="form-control" id="dataInicio" placeholder="dd/mm/aaaa" maxlength="10">
                </div>
                
                <div class="col-md-3">
                    <label for="dataFim" class="form-label">Data Final:</label>
                    <input type="text" class="form-control" id="dataFim" placeholder="dd/mm/aaaa" maxlength="10">
                </div>
                
                <div class="col-md-3">
                    <label for="vencimentoInicio" class="form-label">Vencimento Inicial:</label>
                    <input type="text" class="form-control" id="vencimentoInicio" placeholder="dd/mm/aaaa" maxlength="10">
                </div>
                
                <div class="col-md-3">
                    <label for="vencimentoFim" class="form-label">Vencimento Final:</label>
                    <input type="text" class="form-control" id="vencimentoFim" placeholder="dd/mm/aaaa" maxlength="10">
                </div>
                
                <div class="col-md-12 d-flex align-items-end gap-2">
                    <button type="button" id="btnBuscar" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <button type="button" id="btnLimpar" class="btn btn-secondary">
                        <i class="fas fa-broom"></i> Limpar
                    </button>
                    <button type="button" id="btnExportarPDF" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </button>
                    <button type="button" id="btnCompartilharWhatsApp" class="btn btn-success">
                        <i class="fab fa-whatsapp"></i> Compartilhar WhatsApp
                    </button>
                    <a href="{{ route('vendas.create') }}" class="btn btn-info">
                        <i class="fas fa-plus"></i> Nova Venda
                    </a>
                    <a href="{{ route('recebimentos.index') }}" class="btn btn-warning">
                        <i class="fas fa-money-bill"></i> Recebimentos
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div id="mensagem" class="alert" style="display: none;"></div>

    <div id="tabela-relatorio" style="display: none;">
        <h3>Resultados</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="tabela-vendas">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Data da Venda</th>
                        <th>Observação</th>
                        <th>Vencimento</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Produto</th>
                        <th>Qtd</th>
                        <th>Preço Unit.</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        
        <div id="resumo-relatorio" class="card mt-3" style="display: none;">
            <div class="card-body">
                <h5 class="card-title">Resumo</h5>
                <p class="card-text" id="totalRelatorio"></p>
            </div>
        </div>
    </div>

    <!-- Template oculto para PDF -->
    <div id="pdf-content" style="display: none;">
        <h1 style="text-align: center;">PitStop - Relatório de Vendas</h1>
        <div id="pdf-filtros" style="margin-bottom: 20px;"></div>
        <table style="width: 100%; border-collapse: collapse;" border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Data da Venda</th>
                    <th>Vencimento</th>
                    <th>Total</th>
                    <th>Produto</th>
                    <th>Qtd</th>
                    <th>Preço Unit.</th>
                </tr>
            </thead>
            <tbody id="pdf-tbody"></tbody>
        </table>
        <div id="pdf-total" style="margin-top: 20px; font-weight: bold;"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ===================== VARIÁVEIS GLOBAIS =====================
let clienteSelecionadoId = null;
let dadosUltimoRelatorio = [];

const mensagem = document.getElementById('mensagem');
const inputCliente = document.getElementById('cliente');
const selectStatus = document.getElementById('status');
const dataInicio = document.getElementById('dataInicio');
const dataFim = document.getElementById('dataFim');
const vencimentoInicio = document.getElementById('vencimentoInicio');
const vencimentoFim = document.getElementById('vencimentoFim');
const btnBuscar = document.getElementById('btnBuscar');
const btnLimpar = document.getElementById('btnLimpar');
const btnExportarPDF = document.getElementById('btnExportarPDF');
const btnWhatsApp = document.getElementById('btnCompartilharWhatsApp');
const tabelaContainer = document.getElementById('tabela-relatorio');
const tbody = document.querySelector('#tabela-vendas tbody');
const resumoDiv = document.getElementById('resumo-relatorio');
const totalRelatorio = document.getElementById('totalRelatorio');

// ===================== FUNÇÕES AUXILIARES =====================
function formatarData(data) {
    if (!data) return '-';
    const partes = data.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return data;
}

function formatarDataISO(dataBr) {
    if (!dataBr || dataBr.length !== 10) return null;
    const partes = dataBr.split('/');
    return `${partes[2]}-${partes[1]}-${partes[0]}`;
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

// Máscara de data
function mascaraData(input) {
    input.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, '');
        if (valor.length > 2 && valor.length <= 4) {
            valor = valor.replace(/(\d{2})(\d{1,2})/, '$1/$2');
        } else if (valor.length > 4) {
            valor = valor.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
        }
        this.value = valor.substring(0, 10);
    });
}

// Aplicar máscaras
mascaraData(dataInicio);
mascaraData(dataFim);
mascaraData(vencimentoInicio);
mascaraData(vencimentoFim);

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

// ===================== BUSCAR VENDAS =====================
async function buscarVendas() {
    const filtros = {
        cliente: inputCliente.value.trim(),
        clienteId: clienteSelecionadoId,
        status: selectStatus.value,
        dataInicio: dataInicio.value,
        dataFim: dataFim.value,
        vencimentoInicio: vencimentoInicio.value,
        vencimentoFim: vencimentoFim.value
    };

    try {
        btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
        btnBuscar.disabled = true;
        
        const response = await fetch(`/api/relatorios/vendas?${new URLSearchParams(filtros)}`);
        const vendas = await response.json();

        tbody.innerHTML = '';

        if (vendas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">Nenhuma venda encontrada.</td></tr>';
            tabelaContainer.style.display = 'block';
            resumoDiv.style.display = 'none';
            dadosUltimoRelatorio = [];
            return;
        }

        let totalGeral = 0;

        vendas.forEach(venda => {
            const valor = parseFloat(venda.total_venda);
            totalGeral += valor;

            // Linha da venda (cabeçalho)
            const trVenda = document.createElement('tr');
            trVenda.classList.add('venda-principal', 'table-primary');
            trVenda.innerHTML = `
                <td>${venda.venda_id}</td>
                <td>${venda.cliente}</td>
                <td>${formatarData(venda.data)}</td>
                <td>${venda.observacao || '-'}</td>
                <td>${formatarData(venda.vencimento)}</td>
                <td>R$ ${formatarMoeda(valor)}</td>
                <td>${venda.status_pagamento}</td>
                <td></td>
                <td></td>
                <td></td>
            `;
            tbody.appendChild(trVenda);

            // Linhas dos itens
            if (venda.itens && venda.itens.length > 0) {
                venda.itens.forEach(item => {
                    const trItem = document.createElement('tr');
                    trItem.classList.add('item-venda');
                    trItem.innerHTML = `
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>${item.nome_produto}</td>
                        <td>${item.quantidade}</td>
                        <td>R$ ${formatarMoeda(item.preco_unitario)}</td>
                    `;
                    tbody.appendChild(trItem);
                });
            } else {
                const trItem = document.createElement('tr');
                trItem.classList.add('item-venda', 'text-muted');
                trItem.innerHTML = `
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="3">Nenhum item cadastrado</td>
                `;
                tbody.appendChild(trItem);
            }
        });

        tabelaContainer.style.display = 'block';
        resumoDiv.style.display = 'block';
        totalRelatorio.textContent = `Total Geral: R$ ${formatarMoeda(totalGeral)}`;
        mostrarMensagem(`${vendas.length} venda(s) encontrada(s).`, 'success');

        // Salvar dados para PDF/WhatsApp
        dadosUltimoRelatorio = vendas;

    } catch (error) {
        console.error('Erro ao buscar vendas:', error);
        mostrarMensagem('Erro ao buscar vendas.', 'danger');
    } finally {
        btnBuscar.innerHTML = '<i class="fas fa-search"></i> Buscar';
        btnBuscar.disabled = false;
    }
}

// ===================== EXPORTAR PDF =====================
async function exportarPDF() {
    if (dadosUltimoRelatorio.length === 0) {
        mostrarMensagem('Nenhum dado para exportar. Faça uma busca primeiro.', 'warning');
        return;
    }

    try {
        btnExportarPDF.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';
        btnExportarPDF.disabled = true;

        // Preparar dados para o PDF
        const pdfTbody = document.getElementById('pdf-tbody');
        pdfTbody.innerHTML = '';

        let totalGeral = 0;

        dadosUltimoRelatorio.forEach(venda => {
            const valor = parseFloat(venda.total_venda);
            totalGeral += valor;

            if (venda.itens && venda.itens.length > 0) {
                venda.itens.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    if (index === 0) {
                        tr.innerHTML = `
                            <td>${venda.venda_id}</td>
                            <td>${venda.cliente}</td>
                            <td>${formatarData(venda.data)}</td>
                            <td>${formatarData(venda.vencimento)}</td>
                            <td>R$ ${formatarMoeda(valor)}</td>
                            <td>${item.nome_produto}</td>
                            <td>${item.quantidade}</td>
                            <td>R$ ${formatarMoeda(item.preco_unitario)}</td>
                        `;
                    } else {
                        tr.innerHTML = `
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>${item.nome_produto}</td>
                            <td>${item.quantidade}</td>
                            <td>R$ ${formatarMoeda(item.preco_unitario)}</td>
                        `;
                    }
                    pdfTbody.appendChild(tr);
                });
            }
        });

        document.getElementById('pdf-total').textContent = `Total Geral: R$ ${formatarMoeda(totalGeral)}`;

        // Capturar o HTML do PDF
        const pdfContent = document.getElementById('pdf-content').innerHTML;

        // Enviar para o backend gerar o PDF
        const response = await fetch('/api/relatorios/pdf', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ html: pdfContent })
        });

        const resultado = await response.json();

        if (resultado.success) {
            mostrarMensagem('PDF gerado com sucesso!', 'success');
            // Abrir o PDF em nova aba
            window.open(`/api/relatorios/pdf/${resultado.arquivo}`, '_blank');
        } else {
            mostrarMensagem(resultado.message || 'Erro ao gerar PDF.', 'danger');
        }

    } catch (error) {
        console.error('Erro ao gerar PDF:', error);
        mostrarMensagem('Erro ao gerar PDF.', 'danger');
    } finally {
        btnExportarPDF.innerHTML = '<i class="fas fa-file-pdf"></i> Exportar PDF';
        btnExportarPDF.disabled = false;
    }
}

// ===================== COMPARTILHAR WHATSAPP =====================
function compartilharWhatsApp() {
    if (dadosUltimoRelatorio.length === 0) {
        mostrarMensagem('Nenhum dado para compartilhar. Faça uma busca primeiro.', 'warning');
        return;
    }

    // Agrupar vendas por cliente
    const vendasPorCliente = {};
    
    dadosUltimoRelatorio.forEach(venda => {
        const telefone = venda.telefone;
        if (!telefone) return;
        
        if (!vendasPorCliente[telefone]) {
            vendasPorCliente[telefone] = {
                cliente: venda.cliente,
                vendas: []
            };
        }
        vendasPorCliente[telefone].vendas.push(venda);
    });

    // Criar mensagens
    Object.entries(vendasPorCliente).forEach(([telefone, dados]) => {
        let mensagem = `Olá ${dados.cliente}, tudo bem?\n\nSegue resumo das suas compras na PitStop:\n\n`;
        let totalGeral = 0;

        dados.vendas.forEach(venda => {
            mensagem += `🧾 Venda #${venda.venda_id}\n`;
            mensagem += `📅 Data: ${formatarData(venda.data)}\n`;
            mensagem += `📅 Vencimento: ${formatarData(venda.vencimento)}\n`;
            mensagem += `💳 Status: ${venda.status_pagamento}\n`;
            mensagem += `📦 Produtos:\n`;

            venda.itens.forEach(item => {
                mensagem += `   • ${item.nome_produto} - ${item.quantidade}x R$ ${formatarMoeda(item.preco_unitario)}\n`;
            });

            mensagem += `💰 Total da venda: R$ ${formatarMoeda(venda.total_venda)}\n\n`;
            totalGeral += parseFloat(venda.total_venda);
        });

        mensagem += `💵 Total Geral: R$ ${formatarMoeda(totalGeral)}\n\n`;
        mensagem += `Agradecemos a preferência!\nPitStop`;

        // Abrir WhatsApp
        const mensagemCodificada = encodeURIComponent(mensagem);
        const link = `https://wa.me/55${telefone}?text=${mensagemCodificada}`;
        window.open(link, '_blank');
    });

    mostrarMensagem('Mensagens preparadas para envio!', 'success');
}

// ===================== EVENTOS =====================
document.addEventListener('DOMContentLoaded', () => {
    configurarAutocompleteCliente();

    btnBuscar.addEventListener('click', buscarVendas);
    
    btnLimpar.addEventListener('click', () => {
        inputCliente.value = '';
        document.getElementById('cliente_id').value = '';
        selectStatus.value = '';
        dataInicio.value = '';
        dataFim.value = '';
        vencimentoInicio.value = '';
        vencimentoFim.value = '';
        clienteSelecionadoId = null;
        tbody.innerHTML = '';
        tabelaContainer.style.display = 'none';
        resumoDiv.style.display = 'none';
        dadosUltimoRelatorio = [];
        mostrarMensagem('', '');
    });

    btnExportarPDF.addEventListener('click', exportarPDF);
    btnWhatsApp.addEventListener('click', compartilharWhatsApp);
});
</script>
@endpush