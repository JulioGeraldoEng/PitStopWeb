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
                <p><i class="fas fa-envelope"></i> {{ Auth::user()->email }}</p>
                @if(Auth::user()->telefone)
                    <p><i class="fab fa-whatsapp text-success"></i> {{ Auth::user()->telefone }}</p>
                @else
                    <p class="text-muted"><i class="fab fa-whatsapp"></i> Nenhum telefone cadastrado</p>
                @endif
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

            <!-- NOVO CAMPO: TELEFONE PARA WHATSAPP -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefone">
                            <i class="fab fa-whatsapp text-success"></i> WhatsApp para notificações
                        </label>
                        <input type="text" 
                               class="form-control telefone-mask" 
                               id="telefone" 
                               name="telefone" 
                               value="{{ Auth::user()->telefone }}" 
                               placeholder="(18) 99798-7391">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Número onde você receberá as notificações de contas atrasadas e estoque baixo
                        </small>
                    </div>
                </div>
            </div>

            <div class="text-end mt-3">
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

@push('scripts')
<script>
    // Máscara para telefone
    document.addEventListener('DOMContentLoaded', function() {
        const telefoneInput = document.getElementById('telefone');
        
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é número
                
                // Limita a 11 dígitos (2 DDD + 9 números)
                if (value.length > 11) value = value.slice(0, 11);
                
                // Aplica a máscara
                if (value.length > 10) {
                    // (XX) XXXXX-XXXX
                    value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
                } else if (value.length > 6) {
                    // (XX) XXXX-XXXX
                    value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                } else if (value.length > 2) {
                    // (XX) XXXX
                    value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
                } else if (value.length > 0) {
                    // (XX
                    value = value.replace(/^(\d*)/, '($1');
                }
                
                e.target.value = value;
            });

            // Formata valor inicial se existir
            if (telefoneInput.value) {
                const event = new Event('input', { bubbles: true });
                telefoneInput.dispatchEvent(event);
            }
        }
    });
</script>
@endpush