@extends('layouts.app')

@section('title', 'Bem-vindo - Barbearia')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Sobre Nós</h2>
                    <p class="card-text">A melhor barbearia da cidade! Cortes modernos, profissionais experientes e ambiente aconchegante.</p>
                    <p class="card-text"><strong>Horário:</strong> Seg-Sex: 09:00 - 20:00 | Sáb: 09:00 - 18:00</p>
                    <p class="card-text"><strong>Endereço:</strong> Rua das Barbas, 123 - Centro</p>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h3 class="card-title">Nossos Serviços</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-scissors"></i> Corte de Cabelo</h5>
                                    <p class="card-text">R$ 40,00 - 45 min</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-pencil"></i> Barba</h5>
                                    <p class="card-text">R$ 35,00 - 30 min</p>
                                </div>
            </div>
        </div>
    </div>
@endsection