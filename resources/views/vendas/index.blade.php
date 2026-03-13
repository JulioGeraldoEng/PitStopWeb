@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/venda.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>Vendas</h1>
    
    <!-- CARD DE FILTROS -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filtrar Vendas</h5>
            
            <form id="filtrosForm">
                <div class="row g-3">
                    <!-- Cliente e Status na primeira linha -->
                    <div class="col-md-6">
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
                </div>
                
                <!-- Linha de Datas usando o COMPONENTE REUTILIZÁVEL -->
                <div class="row mt-3">
                    <!-- Data Inicial e Data Final -->
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <x-datepicker id="dataInicio" label="Data Inicial" />
                            </div>
                            
                            <div class="col-md-6">
                                <x-datepicker id="dataFim" label="Data Final" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vencimento Inicial e Vencimento Final -->
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <x-datepicker id="vencimentoInicio" label="Vencimento Inicial" icon="fa-calendar-check" />
                            </div>
                            
                            <div class="col-md-6">
                                <x-datepicker id="vencimentoFim" label="Vencimento Final" icon="fa-calendar-check" />
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- BOTÕES -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="{{ route('vendas.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Nova Venda
                            </a>
                            <button type="button" id="btnBuscar" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" id="btnLimpar" class="btn btn-secondary">
                                <i class="fas fa-broom"></i> Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <!-- TABELA DE VENDAS -->
    <div id="tabela-venda">
        <table class="tabela-venda">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Data da Venda</th>
                    <th>Vencimento</th>
                    <th>Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="tabela-corpo">
                @forelse($vendas as $venda)
                <tr>
                    <td>{{ $venda->cliente->nome }}</td>
                    <td>{{ date('d/m/Y', strtotime($venda->data)) }}</td>
                    <td>{{ $venda->data_vencimento ? date('d/m/Y', strtotime($venda->data_vencimento)) : '-' }}</td>
                    <td>R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                    <td>
                        <div class="acao-container">
                            <a href="{{ route('vendas.show', $venda->id) }}" class="btn-ver">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="{{ route('vendas.edit', $venda->id) }}" class="btn-editar">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="{{ route('vendas.destroy', $venda->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-excluir" onclick="return confirm('Tem certeza?')">
                                    <i class="fas fa-trash-alt"></i> Excluir
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">Nenhuma venda cadastrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ===================== VARIÁVEIS GLOBAIS =====================
let clienteSelecionadoId = null;

// ===================== FUNÇÕES AUXILIARES =====================
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
        cliente: document.getElementById('cliente').value.trim(),
        clienteId: clienteSelecionadoId,
        status: document.getElementById('status').value,
        dataInicio: document.getElementById('dataInicio').value,
        dataFim: document.getElementById('dataFim').value,
        vencimentoInicio: document.getElementById('vencimentoInicio').value,
        vencimentoFim: document.getElementById('vencimentoFim').value
    };

    try {
        const btnBuscar = document.getElementById('btnBuscar');
        btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
        btnBuscar.disabled = true;
        
        const response = await fetch(`/api/vendas/busca?${new URLSearchParams(filtros)}`);
        const vendas = await response.json();

        const tbody = document.getElementById('tabela-corpo');
        tbody.innerHTML = '';

        if (vendas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhuma venda encontrada.</td></tr>';
            return;
        }

        vendas.forEach(venda => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${venda.cliente}</td>
                <td>${formatarData(venda.data)}</td>
                <td>${venda.vencimento ? formatarData(venda.vencimento) : '-'}</td>
                <td>R$ ${formatarMoeda(venda.total)}</td>
                <td>
                    <a href="/vendas/${venda.id}" class="btn-ver">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <a href="/vendas/${venda.id}/edit" class="btn-editar">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="/vendas/${venda.id}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-excluir" onclick="return confirm('Tem certeza?')">
                            <i class="fas fa-trash-alt"></i> Excluir
                        </button>
                    </form>
                </td>
            `;
            tbody.appendChild(tr);
        });

        mostrarMensagem(`${vendas.length} venda(s) encontrada(s).`, 'success');

    } catch (error) {
        console.error('Erro ao buscar vendas:', error);
        mostrarMensagem('Erro ao buscar vendas.', 'danger');
    } finally {
        document.getElementById('btnBuscar').innerHTML = '<i class="fas fa-search"></i> Buscar';
        document.getElementById('btnBuscar').disabled = false;
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
    
    // Recarregar a página para mostrar todas as vendas
    window.location.reload();
}

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    // Configurar autocomplete
    configurarAutocompleteCliente();
    
    // Eventos dos botões
    document.getElementById('btnBuscar').addEventListener('click', buscarVendas);
    document.getElementById('btnLimpar').addEventListener('click', limparFiltros);
});
</script>
@endpush