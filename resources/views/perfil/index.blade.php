@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/perfil.css') }}">
@endpush

@section('content')
<div class="container perfil-container">
    <h1 class="mb-4"><i class="fas fa-user-circle"></i> Meu Perfil</h1>

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

    <!-- CARD DE INFORMAÇÕES PESSOAIS -->
    <div class="perfil-card">
        <h5><i class="fas fa-id-card"></i> Informações Pessoais</h5>
        
        <div class="avatar-section">
            <div class="avatar-large">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="avatar-info">
                <h3>{{ Auth::user()->name }}</h3>
                <p>{{ Auth::user()->email }}</p>
                <p><small>Membro desde {{ Auth::user()->created_at->format('d/m/Y') }}</small></p>
            </div>
        </div>

        <form action="{{ route('perfil.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nome completo</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}" required>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn-perfil btn-perfil-primary">
                    <i class="fas fa-save"></i> Salvar alterações
                </button>
            </div>
        </form>
    </div>

    <!-- CARD DE ALTERAR SENHA -->
    <div class="perfil-card">
        <h5><i class="fas fa-lock"></i> Alterar Senha</h5>

        <form action="{{ route('perfil.password') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="current_password">Senha atual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="new_password">Nova senha</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="new_password_confirmation">Confirmar nova senha</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn-perfil btn-perfil-secondary">
                    <i class="fas fa-key"></i> Alterar senha
                </button>
            </div>
        </form>
    </div>
</div>
@endsection