@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <style>
        /* Forçar remoção de qualquer fundo branco */
        main {
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .container {
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100%;
        }

        /* Remover qualquer fundo do body que não seja o gradiente */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #4776E6 100%);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        /* Garantir que o container do login fique centralizado */
        .login-container {
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }
    </style>
@endpush

@section('content')
<div class="login-container-wrapper" style="width: 100%; display: flex; justify-content: center; align-items: center; min-height: 100vh;">
    <div class="login-container">
        <div class="login-logo">
            <img src="{{ asset('assets/icon/pitstop_icon.ico') }}" alt="PitStop" style="width: 80px; margin-bottom: 10px;">
        </div>
        
        <h1>Bem-vindo</h1>
        <p class="login-subtitle">Faça login para acessar o sistema</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group input-icon">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Seu e-mail" required autofocus>
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group input-icon">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Sua senha" required>
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" id="login-btn">
                <span class="btn-text">Entrar</span>
                <span class="spinner" style="display: none;"></span>
            </button>

            {{--
                <div class="login-links">
                    <a href="{{ route('register') }}">
                        <i class="fas fa-user-plus"></i> Criar nova conta
                    </a>
                </div>
            --}}
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Prevenir múltiplos envios do formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        const btn = document.getElementById('login-btn');
        if (btn.disabled) {
            e.preventDefault();
            return;
        }
        
        btn.disabled = true;
        btn.querySelector('.btn-text').style.display = 'none';
        btn.querySelector('.spinner').style.display = 'inline-block';
    });
</script>
@endpush