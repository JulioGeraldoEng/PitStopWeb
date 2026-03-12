@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="login-container">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div><img src="{{ asset('assets/icon/pitstop_icon.ico') }}" style="width: 64px; margin-bottom: 0px;"></div>
            <h2>Login - PitStop</h2>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
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

            <button type="submit">Entrar</button>

            <p style="text-align: center;">
                <a href="{{ route('register') }}">Cadastrar novo usuário</a>
            </p>
        </form>
    </div>
</div>
@endsection