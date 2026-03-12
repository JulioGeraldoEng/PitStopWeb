@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/usuario.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="login-container">
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div><img src="{{ asset('assets/icon/pitstop_icon.ico') }}" style="width: 64px; margin-bottom: 0px;"></div>
            <h2>Cadastrar Novo Usuário</h2>

            <div class="form-group">
                <label for="name">Nome:</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar Senha:</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <button type="submit">Cadastrar</button>

            <p style="text-align: center;">
                <a href="{{ route('login') }}">Voltar para o login</a>
            </p>
        </form>
    </div>
</div>
@endsection