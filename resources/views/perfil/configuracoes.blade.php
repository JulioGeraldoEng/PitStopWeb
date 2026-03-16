@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/perfil.css') }}">
    <style>
        /* Estilos adicionais para a área de backup */
        .backup-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px;
        }
        
        .backup-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.3s ease;
        }
        
        .backup-item:last-child {
            border-bottom: none;
        }
        
        .backup-item:hover {
            background-color: var(--hover-bg);
        }
        
        .backup-info {
            display: flex;
            flex-direction: column;
        }
        
        .backup-date {
            font-weight: 600;
            color: var(--text-color);
        }
        
        .backup-details {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        
        .backup-actions {
            display: flex;
            gap: 5px;
        }
        
        .btn-backup-action {
            padding: 5px 10px;
            border-radius: 20px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        
        .btn-download {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
        }
        
        .btn-restore {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: #212529;
        }
        
        .btn-restore:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
            color: white;
        }
        
        .progress-bar {
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        /* Estilo para seções de notificações */
        .notificacao-secao {
            background: rgba(102, 126, 234, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .notificacao-secao h6 {
            color: var(--text-color);
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }

        .alert-warning a {
            color: #856404;
            font-weight: 600;
            text-decoration: underline;
        }
    </style>
@endpush

@section('content')
<div class="container perfil-container">
    <h1 class="mb-4"><i class="fas fa-cog"></i> Configurações</h1>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- FORMULÁRIO PRINCIPAL -->
    <form action="{{ route('configuracoes.update') }}" method="POST" id="formConfiguracoes">
        @csrf
        @method('PUT')

        <!-- CARD DE CONFIGURAÇÕES GERAIS -->
        <div class="perfil-card">
            <h5><i class="fas fa-palette"></i> Aparência</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tema">Tema</label>
                        <select class="form-control" id="tema" name="tema">
                            <option value="claro" {{ $settings->tema == 'claro' ? 'selected' : '' }}>Claro</option>
                            <option value="escuro" {{ $settings->tema == 'escuro' ? 'selected' : '' }}>Escuro</option>
                            <option value="auto" {{ $settings->tema == 'auto' ? 'selected' : '' }}>Automático</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="idioma">Idioma</label>
                        <select class="form-control" id="idioma" name="idioma">
                            <option value="pt-BR" {{ $settings->idioma == 'pt-BR' ? 'selected' : '' }}>Português (Brasil)</option>
                            <option value="en" {{ $settings->idioma == 'en' ? 'selected' : '' }}>English</option>
                            <option value="es" {{ $settings->idioma == 'es' ? 'selected' : '' }}>Español</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD DE NOTIFICAÇÕES -->
        <div class="perfil-card">
            <h5><i class="fas fa-bell"></i> Notificações</h5>
            
            <!-- AVISO SOBRE TELEFONE -->
            @if(!Auth::user()->telefone)
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Atenção:</strong> Para receber notificações no WhatsApp, 
                    <a href="{{ route('perfil.edit') }}" class="alert-link">cadastre seu telefone no perfil</a>.
                </div>
            @else
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle"></i>
                    <strong>WhatsApp cadastrado:</strong> {{ Auth::user()->telefone_formatado ?? Auth::user()->telefone }}
                </div>
            @endif

            <!-- CANAIS DE RECEBIMENTO -->
            <div class="notificacao-secao">
                <h6><i class="fas fa-broadcast-tower"></i> Canais de recebimento</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notificacoes_sistema" name="notificacoes_sistema" value="on" {{ $settings->notificacoes_sistema ? 'checked' : '' }}>
                            <label class="form-check-label" for="notificacoes_sistema">
                                <i class="fas fa-desktop"></i> Sistema
                                <small class="d-block text-muted">Notificações dentro do sistema</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notificacoes_whatsapp" name="notificacoes_whatsapp" value="on" {{ $settings->notificacoes_whatsapp ? 'checked' : '' }} {{ !Auth::user()->telefone ? 'disabled' : '' }}>
                            <label class="form-check-label" for="notificacoes_whatsapp">
                                <i class="fab fa-whatsapp text-success"></i> WhatsApp
                                <small class="d-block text-muted">
                                    @if(Auth::user()->telefone)
                                        {{ Auth::user()->telefone_formatado ?? Auth::user()->telefone }}
                                    @else
                                        <span class="text-muted">Nenhum telefone cadastrado</span>
                                    @endif
                                </small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notificacoes_email" name="notificacoes_email" value="on" {{ $settings->notificacoes_email ? 'checked' : '' }}>
                            <label class="form-check-label" for="notificacoes_email">
                                <i class="fas fa-envelope"></i> E-mail
                                <small class="d-block text-muted">{{ Auth::user()->email }}</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FREQUÊNCIA WHATSAPP (aparece apenas se WhatsApp estiver marcado) -->
            <div id="whatsappFrequency" style="display: {{ $settings->notificacoes_whatsapp ? 'block' : 'none' }};">
                <div class="notificacao-secao">
                    <h6><i class="fas fa-clock"></i> Frequência WhatsApp</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <select class="form-control" id="frequencia_whatsapp" name="frequencia_whatsapp" {{ !Auth::user()->telefone ? 'disabled' : '' }}>
                                    <option value="diario" {{ ($settings->frequencia_whatsapp ?? 'diario') == 'diario' ? 'selected' : '' }}>Diário (resumo às 08:00)</option>
                                    <option value="tempo_real" {{ ($settings->frequencia_whatsapp ?? '') == 'tempo_real' ? 'selected' : '' }}>Tempo real</option>
                                </select>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle"></i>
                                    As notificações serão enviadas para {{ Auth::user()->telefone_formatado ?? Auth::user()->telefone ?? 'o número cadastrado' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TIPOS DE NOTIFICAÇÃO - RECEBIMENTOS -->
            <div class="notificacao-secao">
                <h6><i class="fas fa-hand-holding-usd"></i> Recebimentos</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notif_atrasados" name="notif_atrasados" value="on" {{ $settings->notif_atrasados ? 'checked' : '' }}>
                            <label class="form-check-label" for="notif_atrasados">
                                <strong>Contas atrasadas</strong>
                                <small class="d-block text-muted">Alertas de contas em atraso</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notif_pendentes" name="notif_pendentes" value="on" {{ $settings->notif_pendentes ? 'checked' : '' }}>
                            <label class="form-check-label" for="notif_pendentes">
                                <strong>Contas pendentes</strong>
                                <small class="d-block text-muted">Contas a vencer (opcional)</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TIPOS DE NOTIFICAÇÃO - ESTOQUE -->
            <div class="notificacao-secao">
                <h6><i class="fas fa-boxes"></i> Estoque</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notif_estoque_baixo" name="notif_estoque_baixo" value="on" {{ $settings->notif_estoque_baixo ? 'checked' : '' }}>
                            <label class="form-check-label" for="notif_estoque_baixo">
                                <strong>Estoque baixo</strong>
                                <small class="d-block text-muted">Produtos com menos de 5 unidades</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notif_produto_zerado" name="notif_produto_zerado" value="on" {{ $settings->notif_produto_zerado ? 'checked' : '' }}>
                            <label class="form-check-label" for="notif_produto_zerado">
                                <strong>Produto esgotado</strong>
                                <small class="d-block text-muted">Quando estoque chegar a zero</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTÃO DE TESTE EM TEMPO REAL -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="fas fa-flask"></i> Teste de Notificações</h6>
                            <div class="row">
                                <div class="col-md-8">
                                    <select class="form-control" id="teste_tipo">
                                        <option value="atrasados">📢 Contas atrasadas</option>
                                        <option value="pendentes">⏳ Contas pendentes</option>
                                        <option value="estoque">📦 Estoque baixo</option>
                                        <option value="zerados">❌ Produtos zerados</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn-perfil btn-perfil-primary w-100" onclick="testarNotificacao()">
                                        <i class="fab fa-whatsapp"></i> Enviar teste agora
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle"></i>
                                Envia uma notificação de teste AGORA para validar se o WhatsApp está funcionando
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD DE BACKUP AUTOMÁTICO -->
        <div class="perfil-card">
            <h5><i class="fas fa-database"></i> Backup</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="backup_automatico" name="backup_automatico" value="on" {{ $settings->backup_automatico ? 'checked' : '' }}>
                        <label class="form-check-label" for="backup_automatico">
                            <strong>Backup automático diário</strong>
                            <small class="d-block text-muted">Todos os dias às 02:00 da manhã</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="perfil-card">
        <!-- BOTÕES DE AÇÃO GLOBAIS -->
        <h5><i class="fas fa-save"></i> Salvar Configurações</h5>
        <div class="text-end mt-3">
            <button type="submit" class="btn-perfil btn-perfil-primary">
                <i class="fas fa-save"></i> Salvar configurações
            </button>
        </div>
    </div>
    

    <!-- CARD DE BACKUP E IMPORTAÇÃO (FORA DO FORM) -->
    <div class="perfil-card">
        <h5><i class="fas fa-archive"></i> Backup Manual e Restauração</h5>

        <!-- Backup Manual e Importação -->
        <div class="row mb-4">
            <div class="col-md-6">
                <button type="button" class="btn-perfil btn-perfil-secondary mb-3" onclick="fazerBackupManual()" id="btnBackup">
                    <i class="fas fa-download"></i> Fazer backup agora
                </button>
                <p class="small text-muted">Baixar uma cópia completa do banco de dados</p>
                
                <div id="backupProgress" style="display: none;">
                    <div class="progress-bar" style="width: 0%;"></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <form action="{{ route('configuracoes.importar-backup') }}" method="POST" enctype="multipart/form-data" id="formImportar">
                    @csrf
                    <div class="mb-3">
                        <label for="backup_file" class="form-label">Importar backup</label>
                        <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql,.zip" required>
                        <small class="text-muted">Arquivos .sql ou .zip (máx. 100MB)</small>
                    </div>
                    <button type="submit" class="btn-perfil btn-perfil-primary" onclick="return confirm('A importação substituirá os dados atuais. Deseja continuar?')">
                        <i class="fas fa-upload"></i> Importar dados
                    </button>
                </form>
            </div>
        </div>

        <!-- Lista de Últimos Backups -->
        <div class="mt-4">
            <h6 class="mb-3">
                <i class="fas fa-history"></i> 
                Histórico de Backups
                <small class="text-muted">(últimos 20)</small>
            </h6>
            <div id="lista-backups" class="backup-list">
                <div class="text-center text-muted py-3">
                    <i class="fas fa-spinner fa-spin"></i> Carregando backups...
                </div>
            </div>
        </div>
    </div>

    <!-- CARD DE PERIGO (ZONA VERMELHA) -->
    <div class="perfil-card" style="border-left: 4px solid #dc3545;">
        <h5 style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Zona de Perigo</h5>
        
        <div class="row">
            <div class="col-md-6">
                <button type="button" class="btn-perfil btn-perfil-danger" onclick="excluirConta()">
                    <i class="fas fa-trash-alt"></i> Excluir conta
                </button>
            </div>
            <div class="col-md-6">
                <button type="button" class="btn-perfil btn-perfil-warning" onclick="exportarDados()">
                    <i class="fas fa-download"></i> Exportar dados
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Arquivo de configurações externo -->
    <script src="{{ asset('assets/js/configuracoes/configuracoes.js') }}?v={{ time() }}"></script>
@endpush