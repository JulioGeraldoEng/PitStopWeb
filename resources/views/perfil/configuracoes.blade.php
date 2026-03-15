@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/perfil.css') }}">
@endpush

@section('content')
<div class="container perfil-container">
    <h1 class="mb-4"><i class="fas fa-cog"></i> Configurações</h1>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
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

    <!-- CARD DE CONFIGURAÇÕES GERAIS -->
    <div class="perfil-card">
        <h5><i class="fas fa-palette"></i> Aparência</h5>
        
        <form action="{{ route('configuracoes.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tema">Tema</label>
                        <select class="form-control" id="tema" name="tema">
                            <option value="claro" {{ old('tema', 'claro') == 'claro' ? 'selected' : '' }}>Claro</option>
                            <option value="escuro" {{ old('tema') == 'escuro' ? 'selected' : '' }}>Escuro</option>
                            <option value="auto" {{ old('tema') == 'auto' ? 'selected' : '' }}>Automático</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="idioma">Idioma</label>
                        <select class="form-control" id="idioma" name="idioma">
                            <option value="pt-BR" {{ old('idioma', 'pt-BR') == 'pt-BR' ? 'selected' : '' }}>Português (Brasil)</option>
                            <option value="en" {{ old('idioma') == 'en' ? 'selected' : '' }}>English</option>
                            <option value="es" {{ old('idioma') == 'es' ? 'selected' : '' }}>Español</option>
                        </select>
                    </div>
                </div>
            </div>
    </div>

    <!-- CARD DE NOTIFICAÇÕES -->
    <div class="perfil-card">
        <h5><i class="fas fa-bell"></i> Notificações</h5>
        
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="notificacoes_email" name="notificacoes_email" {{ old('notificacoes_email', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notificacoes_email">Receber notificações por e-mail</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="notificacoes_sistema" name="notificacoes_sistema" {{ old('notificacoes_sistema', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notificacoes_sistema">Notificações no sistema</label>
                    </div>
                </div>
            </div>
    </div>

    <!-- CARD DE BACKUP -->
    <div class="perfil-card">
        <h5><i class="fas fa-database"></i> Backup</h5>
        
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="backup_automatico" name="backup_automatico" {{ old('backup_automatico', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="backup_automatico">Backup automático diário</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn-perfil btn-perfil-secondary" onclick="alert('Backup manual iniciado!')">
                        <i class="fas fa-sync-alt"></i> Fazer backup agora
                    </button>
                </div>
            </div>
    </div>

    <!-- CARD DE SEGURANÇA -->
    <div class="perfil-card">
        <h5><i class="fas fa-shield-alt"></i> Segurança</h5>
        
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="two_factor" name="two_factor">
                        <label class="form-check-label" for="two_factor">Autenticação de dois fatores (2FA)</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn-perfil btn-perfil-secondary" onclick="alert('Sessões encerradas!')">
                        <i class="fas fa-sign-out-alt"></i> Encerrar todas as sessões
                    </button>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="btn-perfil btn-perfil-primary">
                    <i class="fas fa-save"></i> Salvar configurações
                </button>
            </div>
        </form>
    </div>

    <!-- CARD DE PERIGO (ZONA VERMELHA) -->
    <div class="perfil-card" style="border-left: 4px solid #dc3545;">
        <h5 style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Zona de Perigo</h5>
        
        <div class="row">
            <div class="col-md-6">
                <button type="button" class="btn-perfil btn-perfil-secondary" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white !important;" onclick="if(confirm('Tem certeza? Esta ação não pode ser desfeita!')) alert('Conta excluída!')">
                    <i class="fas fa-trash-alt"></i> Excluir conta
                </button>
            </div>
            <div class="col-md-6">
                <button type="button" class="btn-perfil btn-perfil-secondary" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);" onclick="if(confirm('Exportar todos os dados?')) alert('Exportação iniciada!')">
                    <i class="fas fa-download"></i> Exportar dados
                </button>
            </div>
        </div>
    </div>
</div>
@endsection