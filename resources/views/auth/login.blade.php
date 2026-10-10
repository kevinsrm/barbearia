@extends('layouts.site')

@section('title', 'Entrar')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 text-center mb-1">Acessar o sistema</h1>
                        <p class="text-muted text-center small mb-4">Área do cliente e da equipe {{ config('barbearia.nome') }}</p>

                        @if (request('instalado'))
                            <div class="alert alert-success small">
                                <i class="bi bi-check-circle"></i> Instalação concluída! Entre com o usuário criado no instalador.
                            </div>
                        @endif

                        <form action="{{ route('login.post') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="email">E-mail</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                                       value="{{ old('email') }}" required autofocus>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Senha</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                                <label class="form-check-label" for="remember">Continuar conectado</label>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">Entrar</button>
                        </form>

                        <hr>
                        <p class="text-center small mb-0">
                            Ainda não tem conta? <a href="{{ route('register') }}">Cadastre-se</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
