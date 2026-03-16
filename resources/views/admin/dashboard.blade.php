@extends('admin.layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-users"></i> Total de Usuários</h5>
            </div>
            <div class="text-center">
                <h2 style="font-size: 3rem; color: #667eea;">{{ \App\Models\User::count() }}</h2>
                <p class="text-muted">Usuários cadastrados</p>
                <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-rounded">Gerenciar</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-user-tie"></i> Administradores</h5>
            </div>
            <div class="text-center">
                <h2 style="font-size: 3rem; color: #dc3545;">{{ \App\Models\User::where('tipo', 'admin')->count() }}</h2>
                <p class="text-muted">Com acesso total</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-user"></i> Usuários Comuns</h5>
            </div>
            <div class="text-center">
                <h2 style="font-size: 3rem; color: #28a745;">{{ \App\Models\User::where('tipo', 'usuario')->count() }}</h2>
                <p class="text-muted">Acesso restrito</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-clock"></i> Últimos Usuários Cadastrados</h5>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\User::orderBy('created_at', 'desc')->take(5)->get() as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->tipo === 'admin')
                                <span class="badge bg-danger">Admin</span>
                            @else
                                <span class="badge bg-secondary">Usuário</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Informações do Sistema</h5>
            </div>
            <table class="table">
                <tr>
                    <th style="width: 200px;">Laravel:</th>
                    <td>{{ app()->version() }}</td>
                </tr>
                <tr>
                    <th>Ambiente:</th>
                    <td>{{ app()->environment() }}</td>
                </tr>
                <tr>
                    <th>Data/Hora:</th>
                    <td>{{ now()->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr>
                    <th>Usuário logado:</th>
                    <td>{{ Auth::user()->name }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection