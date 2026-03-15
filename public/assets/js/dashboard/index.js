// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', async () => {
    console.log('🚀 Dashboard carregado');
    
    try {
        // Buscar dados do dashboard
        await carregarEstatisticas();
        await carregarTopProdutos();
        await carregarTopClientes();
        await carregarUltimasVendas();
        await carregarStatusVendas();
        await carregarRecebimentosDia();
        
        // Adicionar interatividade
        adicionarTooltips();
        adicionarEventosCards();
        
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
    }
});

// ===================== FUNÇÕES AUXILIARES =====================
function formatarMoeda(valor) {
    return parseFloat(valor).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatarData(data) {
    if (!data) return '-';
    const partes = data.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return data;
}

// ===================== CARREGAR ESTATÍSTICAS =====================
async function carregarEstatisticas() {
    try {
        // Total de vendas
        const responseTotal = await fetch('/api/dashboard/vendas-total');
        const dataTotal = await responseTotal.json();
        document.getElementById('total-vendas').textContent = dataTotal.total || 0;

        // Vendas no mês
        const responseMes = await fetch('/api/dashboard/vendas-mes');
        const dataMes = await responseMes.json();
        document.getElementById('vendas-mes').textContent = dataMes.total || 0;

        // Status das vendas
        const responseStatus = await fetch('/api/dashboard/vendas-por-status');
        const statusData = await responseStatus.json();
        
        document.getElementById('vendas-pendentes').textContent = statusData.pendente || 0;
        document.getElementById('vendas-atrasadas').textContent = statusData.atrasado || 0;

    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
        
        // Valores padrão em caso de erro
        document.getElementById('total-vendas').textContent = '0';
        document.getElementById('vendas-mes').textContent = '0';
        document.getElementById('vendas-pendentes').textContent = '0';
        document.getElementById('vendas-atrasadas').textContent = '0';
    }
}

// ===================== ADICIONAR EVENTOS DE CLIQUE NOS CARDS =====================
function adicionarEventosCards() {
    // Card de Pendentes
    const cardPendentes = document.querySelector('.stat-card.warning');
    if (cardPendentes) {
        cardPendentes.style.cursor = 'pointer';
        cardPendentes.addEventListener('click', () => {
            window.location.href = '/recebimentos?status=pendente';
        });
    }

    // Card de Atrasados
    const cardAtrasados = document.querySelector('.stat-card.danger');
    if (cardAtrasados) {
        cardAtrasados.style.cursor = 'pointer';
        cardAtrasados.addEventListener('click', () => {
            window.location.href = '/recebimentos?status=atrasado';
        });
    }

    // Card de Total de Vendas (leva para vendas)
    const cardTotal = document.querySelector('.stat-card.primary');
    if (cardTotal) {
        cardTotal.style.cursor = 'pointer';
        cardTotal.addEventListener('click', () => {
            window.location.href = '/vendas';
        });
    }

    // Card de Vendas do Mês (leva para vendas com filtro do mês)
    const cardMes = document.querySelector('.stat-card.success');
    if (cardMes) {
        cardMes.style.cursor = 'pointer';
        cardMes.addEventListener('click', () => {
            const hoje = new Date();
            const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
            const ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
            
            const dataInicio = `${primeiroDia.getDate().toString().padStart(2, '0')}/${(primeiroDia.getMonth()+1).toString().padStart(2, '0')}/${primeiroDia.getFullYear()}`;
            const dataFim = `${ultimoDia.getDate().toString().padStart(2, '0')}/${(ultimoDia.getMonth()+1).toString().padStart(2, '0')}/${ultimoDia.getFullYear()}`;
            
            window.location.href = `/vendas?dataInicio=${dataInicio}&dataFim=${dataFim}`;
        });
    }
}

// ===================== ADICIONAR TOOLTIPS =====================
function adicionarTooltips() {
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach(card => {
        card.setAttribute('title', 'Clique para ver detalhes');
    });
}

// ===================== CARREGAR TOP PRODUTOS =====================
async function carregarTopProdutos() {
    const tbody = document.getElementById('top-produtos');
    
    try {
        const response = await fetch('/api/dashboard/top-produtos');
        const produtos = await response.json();

        if (!produtos || produtos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Nenhum produto encontrado</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        produtos.slice(0, 5).forEach(prod => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${prod.nome}</td>
                <td>${prod.quantidade}</td>
                <td>R$ ${formatarMoeda(prod.total)}</td>
            `;
            tbody.appendChild(tr);
        });

    } catch (error) {
        console.error('Erro ao carregar top produtos:', error);
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Erro ao carregar</td></tr>';
    }
}

// ===================== CARREGAR TOP CLIENTES =====================
async function carregarTopClientes() {
    const tbody = document.getElementById('top-clientes');
    
    try {
        const response = await fetch('/api/dashboard/top-clientes');
        const clientes = await response.json();

        if (!clientes || clientes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Nenhum cliente encontrado</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        clientes.slice(0, 5).forEach(cli => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${cli.nome}</td>
                <td>${cli.total_vendas}</td>
                <td>R$ ${formatarMoeda(cli.total_gasto)}</td>
            `;
            tbody.appendChild(tr);
        });

    } catch (error) {
        console.error('Erro ao carregar top clientes:', error);
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Erro ao carregar</td></tr>';
    }
}

// ===================== CARREGAR ÚLTIMAS VENDAS =====================
async function carregarUltimasVendas() {
    const tbody = document.getElementById('ultimas-vendas');
    
    try {
        const response = await fetch('/api/dashboard/ultimas-vendas');
        const vendas = await response.json();

        if (!vendas || vendas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhuma venda encontrada</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        vendas.slice(0, 10).forEach(venda => {
            const tr = document.createElement('tr');
            
            let statusClass = '';
            let statusText = venda.status || 'pendente';
            
            switch(statusText.toLowerCase()) {
                case 'pago':
                    statusClass = 'status-badge pago';
                    statusText = 'Pago';
                    break;
                case 'pendente':
                    statusClass = 'status-badge pendente';
                    statusText = 'Pendente';
                    break;
                case 'atrasado':
                    statusClass = 'status-badge atrasado';
                    statusText = 'Atrasado';
                    break;
                case 'cancelado':
                    statusClass = 'status-badge cancelado';
                    statusText = 'Cancelado';
                    break;
            }
            
            tr.innerHTML = `
                <td>${venda.cliente}</td>
                <td>${formatarData(venda.data)}</td>
                <td>${formatarData(venda.vencimento)}</td>
                <td>R$ ${formatarMoeda(venda.total)}</td>
                <td><span class="${statusClass}">${statusText}</span></td>
            `;
            tbody.appendChild(tr);
        });

    } catch (error) {
        console.error('Erro ao carregar últimas vendas:', error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erro ao carregar</td></tr>';
    }
}

// ===================== CARREGAR STATUS DAS VENDAS =====================
async function carregarStatusVendas() {
    const container = document.getElementById('status-vendas');
    
    try {
        const response = await fetch('/api/dashboard/vendas-por-status');
        const statusData = await response.json();

        container.innerHTML = '';

        const statusList = [
            { label: 'Pagos', value: statusData.pago || 0, icon: 'fa-check-circle', color: '#28a745' },
            { label: 'Pendentes', value: statusData.pendente || 0, icon: 'fa-clock', color: '#ffc107' },
            { label: 'Atrasados', value: statusData.atrasado || 0, icon: 'fa-exclamation-triangle', color: '#dc3545' },
            { label: 'Cancelados', value: statusData.cancelado || 0, icon: 'fa-times-circle', color: '#6c757d' }
        ];

        statusList.forEach(item => {
            const div = document.createElement('div');
            div.className = 'text-center';
            div.innerHTML = `
                <i class="fas ${item.icon}" style="font-size: 2rem; color: ${item.color};"></i>
                <h3 class="mt-2">${item.value}</h3>
                <p class="text-muted">${item.label}</p>
            `;
            container.appendChild(div);
        });

    } catch (error) {
        console.error('Erro ao carregar status:', error);
        container.innerHTML = '<p class="text-danger text-center">Erro ao carregar</p>';
    }
}

// ===================== CARREGAR RECEBIMENTOS DO DIA =====================
async function carregarRecebimentosDia() {
    try {
        const response = await fetch('/api/dashboard/recebimentos-dia');
        const data = await response.json();
        
        document.getElementById('recebimentos-dia').textContent = `R$ ${formatarMoeda(data.total || 0)}`;

    } catch (error) {
        console.error('Erro ao carregar recebimentos do dia:', error);
        document.getElementById('recebimentos-dia').textContent = 'R$ 0,00';
    }
}