// ===================== RELATÓRIOS INDEX =====================
// Variáveis globais
let clienteSelecionadoId = null;
let dadosUltimoRelatorio = [];

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
    if (!mensagem) return;
    
    mensagem.textContent = texto;
    mensagem.className = `alert alert-${tipo}`;
    mensagem.style.display = 'block';
    setTimeout(() => {
        mensagem.style.display = 'none';
    }, 3000);
}

// ===================== CARREGAR LOGO BASE64 =====================
async function carregarLogoBase64() {
    try {
        const response = await fetch('/assets/icon/pitstop_icon.b64');
        if (!response.ok) {
            console.warn('Logo não encontrado, usando texto apenas');
            return '';
        }
        const base64String = await response.text();
        const base64Limpo = base64String.trim();
        return `data:image/png;base64,${base64Limpo}`;
    } catch (error) {
        console.error('Erro ao carregar logo:', error);
        return '';
    }
}

// ===================== AUTOCOMPLETE CLIENTE =====================
function configurarAutocompleteCliente() {
    const inputCliente = document.getElementById('cliente');
    if (!inputCliente) return;
    
    const sugestoes = document.createElement('div');
    sugestoes.id = 'sugestoes-cliente';
    sugestoes.className = 'list-group';
    sugestoes.style.position = 'absolute';
    sugestoes.style.zIndex = '999999';
    sugestoes.style.display = 'none';
    sugestoes.style.width = '100%';
    sugestoes.style.maxHeight = '300px';
    sugestoes.style.overflowY = 'auto';
    sugestoes.style.backgroundColor = 'white';
    sugestoes.style.border = '2px solid #0078d7';
    sugestoes.style.borderRadius = '8px';
    sugestoes.style.boxShadow = '0 10px 30px rgba(0,0,0,0.3)';
    
    inputCliente.parentNode.style.position = 'relative';
    inputCliente.parentNode.appendChild(sugestoes);
    
    inputCliente.addEventListener('keyup', async () => {
        const termo = inputCliente.value.trim();
        sugestoes.innerHTML = '';
        sugestoes.style.display = 'none';

        if (termo.length < 2) return;

        try {
            const response = await fetch(`/api/clientes/busca?termo=${termo}`);
            const clientes = await response.json();

            if (clientes.length === 0) {
                const div = document.createElement('div');
                div.className = 'list-group-item';
                div.textContent = 'Nenhum cliente encontrado';
                sugestoes.appendChild(div);
                sugestoes.style.display = 'block';
                return;
            }

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
        
        const response = await fetch(`/api/relatorios/vendas?${new URLSearchParams(filtros)}`);
        const vendas = await response.json();

        const tbody = document.getElementById('tabela-corpo');
        if (!tbody) return;
        
        tbody.innerHTML = '';

        const tabelaContainer = document.getElementById('tabela-relatorio');

        if (!vendas || vendas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">Nenhuma venda encontrada.</td></tr>';
            if (tabelaContainer) tabelaContainer.style.display = 'block';
            dadosUltimoRelatorio = [];
            return;
        }

        let totalGeral = 0;

        vendas.forEach(venda => {
            const valor = parseFloat(venda.total_venda);
            totalGeral += valor;
            
            let clienteTotal = 0;

            // LINHA DO CLIENTE
            const trCliente = document.createElement('tr');
            trCliente.classList.add('cliente-row');
            
            trCliente.innerHTML = `
                <td><strong>${venda.cliente || 'N/A'}</strong></td>
                <td>${formatarData(venda.data)}</td>
                <td>${formatarData(venda.vencimento)}</td>
                <td>${venda.status_pagamento?.toUpperCase() || ''}</td>
                <td></td>
                <td></td>
                <td></td>
            `;
            tbody.appendChild(trCliente);

            // LINHAS DOS PRODUTOS
            if (venda.itens && venda.itens.length > 0) {
                venda.itens.forEach(item => {
                    clienteTotal += item.quantidade * item.preco_unitario;
                    
                    const trItem = document.createElement('tr');
                    trItem.classList.add('produto-row');
                    trItem.innerHTML = `
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
                trItem.classList.add('produto-row', 'text-muted');
                trItem.innerHTML = `
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="3">Nenhum produto nesta venda</td>
                `;
                tbody.appendChild(trItem);
            }

            // LINHA DE TOTAL DO CLIENTE
            if (clienteTotal > 0) {
                const trTotalCliente = document.createElement('tr');
                trTotalCliente.classList.add('cliente-total-row');
                trTotalCliente.innerHTML = `
                    <td colspan="4"></td>
                    <td><strong>TOTAL DO CLIENTE</strong></td>
                    <td></td>
                    <td><strong>R$ ${formatarMoeda(clienteTotal)}</strong></td>
                `;
                tbody.appendChild(trTotalCliente);
            }
        });

        // LINHA DE TOTAL GERAL
        const trTotalGeral = document.createElement('tr');
        trTotalGeral.classList.add('total-geral');
        trTotalGeral.innerHTML = `
            <td colspan="6" style="text-align: right;"><strong>TOTAL GERAL:</strong></td>
            <td><strong>R$ ${formatarMoeda(totalGeral)}</strong></td>
        `;
        tbody.appendChild(trTotalGeral);

        if (tabelaContainer) tabelaContainer.style.display = 'block';
        
        mostrarMensagem(`${vendas.length} venda(s) encontrada(s).`, 'success');
        dadosUltimoRelatorio = vendas;

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
    
    const tbody = document.getElementById('tabela-corpo');
    if (tbody) tbody.innerHTML = '';
    
    const tabelaContainer = document.getElementById('tabela-relatorio');
    if (tabelaContainer) tabelaContainer.style.display = 'none';
    
    dadosUltimoRelatorio = [];
    mostrarMensagem('', '');
}

// ===================== EXPORTAR PDF =====================
async function exportarPDF() {
    if (dadosUltimoRelatorio.length === 0) {
        mostrarMensagem('Nenhum dado para exportar. Faça uma busca primeiro.', 'warning');
        return;
    }

    try {
        const btnExportar = document.getElementById('btnExportarPDF');
        if (btnExportar) {
            btnExportar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';
            btnExportar.disabled = true;
        }

        // Carregar logo
        const logoSrc = await carregarLogoBase64();

        const dataAtual = new Date().toLocaleDateString('pt-BR');
        const horaAtual = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

        let htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; margin: 30px; }
                    .header { 
                        display: flex; 
                        align-items: center; 
                        justify-content: space-between; 
                        border-bottom: 2px solid #0078d7; 
                        padding-bottom: 15px; 
                        margin-bottom: 20px; 
                    }
                    .logo-area { 
                        display: flex; 
                        flex-direction: column; 
                        align-items: center; 
                        text-align: center; 
                    }
                    .empresa-info { 
                        text-align: right; 
                        line-height: 1.5; 
                    }
                    .filtros { 
                        background: #f5f5f5; 
                        padding: 15px; 
                        border-radius: 5px; 
                        margin-bottom: 20px; 
                        border-left: 4px solid #0078d7; 
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        font-size: 11px; 
                    }
                    th { 
                        background: #0078d7; 
                        color: white; 
                        padding: 10px; 
                    }
                    td { 
                        border: 1px solid #ddd; 
                        padding: 8px; 
                        text-align: center; 
                    }
                    .cliente-row { 
                        background: #e6f3ff; 
                        font-weight: bold; 
                    }
                    .cliente-total-row { 
                        background: #d4edda; 
                        font-weight: bold; 
                        border-top: 2px solid #28a745; 
                    }
                    .total-geral { 
                        background: #d4edda; 
                        font-weight: bold; 
                        font-size: 13px; 
                        border-top: 3px solid #28a745; 
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <div class="logo-area">
                        <div class="logo-container">
                            ${logoSrc ? `<img src="${logoSrc}" alt="PitStop Logo" style="height: 50px;">` : ''}
                        </div>
                        <div class="logo-nome" style="font-size: 16px; font-weight: bold; color: #0078d7; margin-top: 5px;">PitStop</div>
                    </div>
                    <div class="empresa-info">
                        <div style="font-size: 14px; font-weight: bold; color: #333;">JG Soluções Tecnológicas</div>
                        <div style="font-size: 12px; color: #666;">contato@pitstop.com.br</div>
                        <div style="font-size: 12px; color: #666;">(18) 99798-7391</div>
                    </div>
                </div>
                
                <h2 style="text-align: center;">Relatório de Vendas</h2>
                
                <div class="filtros">
                    <p><strong>Gerado em:</strong> ${dataAtual} às ${horaAtual}</p>
                    <p><strong>Cliente:</strong> ${document.getElementById('cliente')?.value || 'Todos'}</p>
                    <p><strong>Status:</strong> ${document.getElementById('status')?.value || 'Todos'}</p>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>CLIENTE</th>
                            <th>DATA</th>
                            <th>VENCIMENTO</th>
                            <th>STATUS</th>
                            <th>PRODUTO</th>
                            <th>QTD</th>
                            <th>PREÇO</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        let totalGeral = 0;

        dadosUltimoRelatorio.forEach(venda => {
            const valor = parseFloat(venda.total_venda);
            totalGeral += valor;
            let clienteTotal = 0;

            htmlContent += `<tr class="cliente-row"><td>${venda.cliente}</td><td>${formatarData(venda.data)}</td><td>${formatarData(venda.vencimento)}</td><td>${venda.status_pagamento?.toUpperCase()}</td><td></td><td></td><td></td></tr>`;

            if (venda.itens && venda.itens.length > 0) {
                venda.itens.forEach(item => {
                    clienteTotal += item.quantidade * item.preco_unitario;
                    htmlContent += `<tr><td></td><td></td><td></td><td></td><td>${item.nome_produto}</td><td>${item.quantidade}</td><td>R$ ${formatarMoeda(item.preco_unitario)}</td></tr>`;
                });
            }

            if (clienteTotal > 0) {
                htmlContent += `<tr class="cliente-total-row"><td colspan="4"></td><td>TOTAL DO CLIENTE</td><td></td><td>R$ ${formatarMoeda(clienteTotal)}</td></tr>`;
            }
        });

        htmlContent += `<tr class="total-geral"><td colspan="6" style="text-align: right;">TOTAL GERAL:</td><td>R$ ${formatarMoeda(totalGeral)}</td></tr>`;
        htmlContent += `</tbody></table></body></html>`;

        const response = await fetch('/api/relatorios/pdf', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ html: htmlContent })
        });

        const resultado = await response.json();

        if (resultado.success) {
            mostrarMensagem('PDF gerado com sucesso!', 'success');
            window.open(`/api/relatorios/pdf/${resultado.arquivo}`, '_blank');
        } else {
            mostrarMensagem(resultado.message || 'Erro ao gerar PDF.', 'danger');
        }

    } catch (error) {
        console.error('Erro ao gerar PDF:', error);
        mostrarMensagem('Erro ao gerar PDF.', 'danger');
    } finally {
        const btnExportar = document.getElementById('btnExportarPDF');
        if (btnExportar) {
            btnExportar.innerHTML = '<i class="fas fa-file-pdf"></i> Exportar PDF';
            btnExportar.disabled = false;
        }
    }
}

// ===================== COMPARTILHAR WHATSAPP =====================
function compartilharWhatsApp() {
    if (dadosUltimoRelatorio.length === 0) {
        mostrarMensagem('Nenhum dado para compartilhar. Faça uma busca primeiro.', 'warning');
        return;
    }

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

    Object.entries(vendasPorCliente).forEach(([telefone, dados]) => {
        let mensagem = `Olá ${dados.cliente}, tudo bem?\n\nSegue resumo das suas compras na PitStop:\n\n`;
        let totalGeral = 0;

        dados.vendas.forEach(venda => {
            mensagem += `📅 Data: ${formatarData(venda.data)}\n`;
            mensagem += `📅 Vencimento: ${formatarData(venda.vencimento)}\n`;
            mensagem += `💳 Status: ${venda.status_pagamento}\n`;
            mensagem += `📦 Produtos:\n`;

            venda.itens.forEach(item => {
                mensagem += `   • ${item.nome_produto} - ${item.quantidade}x R$ ${formatarMoeda(item.preco_unitario)}\n`;
            });

            mensagem += `💰 Total: R$ ${formatarMoeda(venda.total_venda)}\n\n`;
            totalGeral += parseFloat(venda.total_venda);
        });

        mensagem += `💵 Total Geral: R$ ${formatarMoeda(totalGeral)}\n\n`;
        mensagem += `Agradecemos a preferência!\nPitStop`;

        window.open(`https://wa.me/55${telefone}?text=${encodeURIComponent(mensagem)}`, '_blank');
    });

    mostrarMensagem('Mensagens preparadas para envio!', 'success');
}

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    configurarAutocompleteCliente();
    
    document.getElementById('btnBuscar')?.addEventListener('click', buscarVendas);
    document.getElementById('btnLimpar')?.addEventListener('click', limparFiltros);
    document.getElementById('btnExportarPDF')?.addEventListener('click', exportarPDF);
    document.getElementById('btnCompartilharWhatsApp')?.addEventListener('click', compartilharWhatsApp);
});