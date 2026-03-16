@extends('admin.layouts.admin')

@section('page-title', 'Criar Novo Usuário')

@section('content')
<div class="admin-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-user-plus"></i> Novo Usuário</h5>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nome completo *</label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">E-mail *</label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="telefone" class="form-label">
                        <i class="fab fa-whatsapp text-success"></i> WhatsApp
                    </label>
                    <input type="text" 
                           class="form-control telefone-mask @error('telefone') is-invalid @enderror" 
                           id="telefone" 
                           name="telefone" 
                           value="{{ old('telefone') }}" 
                           placeholder="(18) 99798-7391">
                    <small class="text-muted">Número para notificações</small>
                    @error('telefone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label">Tipo de usuário *</label>
                    <select class="form-control @error('tipo') is-invalid @enderror" 
                            id="tipo" 
                            name="tipo" 
                            required>
                        <option value="usuario" {{ old('tipo') == 'usuario' ? 'selected' : '' }}>Usuário comum</option>
                        <option value="admin" {{ old('tipo') == 'admin' ? 'selected' : '' }}>Administrador</option>
                    </select>
                    @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Senha *</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar senha *</label>
                    <input type="password" 
                           class="form-control" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="btn btn-success" style="border-radius: 50px; padding: 10px 25px;">
                    <i class="fas fa-save"></i> Criar Usuário
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const telefoneInput = document.getElementById('telefone');
    
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else if (value.length > 0) {
                value = value.replace(/^(\d*)/, '($1');
            }
            
            e.target.value = value;
        });
    }
});
</script>
@endsection