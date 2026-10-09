@extends('layouts.app')

@section('title', 'Agendamento Online - Barbearia')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Agende seu Horário</h2>
                        <form id="agendamento-form" action="{{ route('site.agendamento.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="servico" class="form-label">Serviço</label>
                                <select name="servico_id" id="servico" class="form-select" required>
                                    <option value="">Carregando serviços...</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="funcionario" class="form-label">Barbeiro</label>
                                <select name="funcionario_id" id="funcionario" class="form-select" required>
                                    <option value="">Carregando barbeiros...</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="data" class="form-label">Data</label>
                                <input type="date" name="data" id="data" class="form-control" min="{{ today()->toDateString() }}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="hora" class="form-label">Horário</label>
                                <select name="hora" id="hora" class="form-select" required>
                                    <option value="">Selecione um horário...</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="cliente_nome" class="form-label">Nome Completo</label>
                                <input type="text" name="cliente_nome" id="cliente_nome" class="form-control" placeholder="Seu nome completo" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="cliente_email" class="form-label">E-mail</label>
                                <input type="email" name="cliente_email" id="cliente_email" class="form-control" placeholder="Seu e-mail" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="cliente_telefone" class="form-label">Telefone</label>
                                <input type="text" name="cliente_telefone" id="cliente_telefone" class="form-control" placeholder="(00) 00000-0000" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Confirmar Agendamento</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/agendamento.js') }}"></script>
    @endpush
@endsection