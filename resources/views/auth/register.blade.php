@extends('layouts.site')

@section('title', 'Criar conta')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 text-center mb-1">Criar minha conta</h1>
                        <p class="text-muted text-center small mb-4">Com a conta você agenda mais rápido e vê seu histórico.</p>

                        <form action="{{ route('register.post') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="nome">Nome completo *</label>
                                <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome') }}" required>
                                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="email">E-mail *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="telefone">Telefone / WhatsApp *</label>
                                    <input type="tel" class="form-control @error('telefone') is-invalid @enderror" id="telefone" name="telefone" value="{{ old('telefone') }}" placeholder="(98) 99999-0000" required>
                                    @error('telefone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="password">Senha *</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="password_confirmation">Confirmar senha *</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="cpf">CPF <span class="text-muted small">(opcional)</span></label>
                                    <input type="text" class="form-control @error('cpf') is-invalid @enderror" id="cpf" name="cpf" value="{{ old('cpf') }}">
                                    @error('cpf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 mt-4">Criar conta</button>
                        </form>

                        <hr>
                        <p class="text-center small mb-0">Já tem conta? <a href="{{ route('login') }}">Entrar</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
