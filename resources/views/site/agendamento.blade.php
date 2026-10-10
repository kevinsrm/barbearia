@extends('layouts.site')

@section('title', 'Agendar horário')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <h1 class="h3 mb-1">Agendar horário</h1>
                <p class="text-muted">Escolha o serviço, o profissional e o melhor horário. Leva menos de um minuto.</p>

                <div class="card mt-3">
                    <div class="card-body">
                        <form action="{{ route('site.agendar.store') }}" method="POST" id="formAgendamento">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="nome">Seu nome *</label>
                                    <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome"
                                           value="{{ old('nome', auth()->user()->nome ?? '') }}" required>
                                    @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="telefone">WhatsApp / telefone *</label>
                                    <input type="tel" class="form-control @error('telefone') is-invalid @enderror" id="telefone" name="telefone"
                                           value="{{ old('telefone', auth()->user()->pessoa->telefone ?? '') }}" placeholder="(98) 99999-0000" required>
                                    @error('telefone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Serviços *</label>
                                    <div class="row g-2">
                                        @forelse ($servicos as $servico)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-check border rounded p-2 h-100">
                                                    <input class="form-check-input servico-check" type="checkbox" name="servico_ids[]"
                                                           value="{{ $servico->id }}" id="servico{{ $servico->id }}"
                                                           data-valor="{{ $servico->valor }}" data-duracao="{{ $servico->duracao }}"
                                                           {{ in_array($servico->id, (array) old('servico_ids', request('servico') ? [request('servico')] : [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="servico{{ $servico->id }}">
                                                        <span class="d-block">{{ $servico->nome }}</span>
                                                        <small class="text-muted">@money($servico->valor) · {{ $servico->duracao }} min</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <div class="alert alert-light border mb-0">Nenhum serviço disponível para agendamento online no momento.</div>
                                            </div>
                                        @endforelse
                                    </div>
                                    @error('servico_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="funcionario_id">Profissional</label>
                                    <select class="form-select" id="funcionario_id" name="funcionario_id">
                                        <option value="">Sem preferência</option>
                                        @foreach ($funcionarios as $funcionario)
                                            <option value="{{ $funcionario->id }}" {{ (string) old('funcionario_id') === (string) $funcionario->id ? 'selected' : '' }}>
                                                {{ $funcionario->pessoa->nome ?? 'Profissional' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="data">Data *</label>
                                    <input type="date" class="form-control @error('data') is-invalid @enderror" id="data" name="data"
                                           value="{{ old('data', now()->toDateString()) }}"
                                           min="{{ now()->toDateString() }}"
                                           max="{{ now()->addDays(config('barbearia.agenda.dias_visiveis', 30))->toDateString() }}" required>
                                    @error('data')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="hora">Horário *</label>
                                    <select class="form-select @error('hora') is-invalid @enderror" id="hora" name="hora" required>
                                        <option value="">Selecione a data...</option>
                                        @foreach ((array) old('hora') as $horaAntiga)
                                            <option value="{{ $horaAntiga }}" selected>{{ $horaAntiga }}</option>
                                        @endforeach
                                    </select>
                                    @error('hora')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text" id="ajudaHora">Escolha a data para ver os horários livres.</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="observacoes">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes" rows="2" maxlength="500"
                                              placeholder="Ex.: prefiro corte na tesoura">{{ old('observacoes') }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 gap-2">
                                <div class="fs-5">
                                    Total estimado: <strong class="text-marca" id="totalEstimado">R$ 0,00</strong>
                                </div>
                                <button type="submit" class="btn btn-marca btn-lg px-4" id="btnEnviar">
                                    <i class="bi bi-calendar-check"></i> Confirmar agendamento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const urlHorarios = @json(route('site.horarios'));
    const campoData = document.getElementById('data');
    const campoFuncionario = document.getElementById('funcionario_id');
    const campoHora = document.getElementById('hora');
    const ajuda = document.getElementById('ajudaHora');
    const total = document.getElementById('totalEstimado');
    const botao = document.getElementById('btnEnviar');
    const horaAntiga = @json(old('hora'));

    function formatarDinheiro(valor) {
        return 'R$ ' + Number(valor || 0).toFixed(2).replace('.', ',');
    }

    function atualizarTotal() {
        let soma = 0;
        document.querySelectorAll('.servico-check:checked').forEach(function (item) {
            soma += parseFloat(item.dataset.valor || 0);
        });
        total.textContent = formatarDinheiro(soma);
    }

    async function carregarHorarios() {
        if (!campoData.value) return;

        campoHora.innerHTML = '<option value="">Carregando...</option>';
        ajuda.textContent = 'Buscando horários livres...';

        const params = new URLSearchParams({ data: campoData.value });
        if (campoFuncionario.value) params.append('funcionario_id', campoFuncionario.value);

        try {
            const resposta = await fetch(urlHorarios + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const dados = await resposta.json();

            campoHora.innerHTML = '';

            if (dados.fechado) {
                campoHora.innerHTML = '<option value="">Não atendemos neste dia</option>';
                ajuda.textContent = 'Escolha outra data.';
                return;
            }

            if (!dados.horarios.length) {
                campoHora.innerHTML = '<option value="">Sem horários livres</option>';
                ajuda.textContent = 'Todos os horários deste dia já foram reservados.';
                return;
            }

            campoHora.innerHTML = '<option value="">Selecione...</option>' + dados.horarios.map(function (h) {
                const selecionado = horaAntiga === h ? ' selected' : '';
                return '<option value="' + h + '"' + selecionado + '>' + h + '</option>';
            }).join('');
            ajuda.textContent = dados.horarios.length + ' horário(s) livre(s).';
        } catch (erro) {
            campoHora.innerHTML = '<option value="">Erro ao carregar</option>';
            ajuda.textContent = 'Não foi possível carregar os horários. Tente novamente.';
        }
    }

    campoData.addEventListener('change', carregarHorarios);
    campoFuncionario.addEventListener('change', carregarHorarios);
    document.querySelectorAll('.servico-check').forEach(function (item) {
        item.addEventListener('change', atualizarTotal);
    });
    document.getElementById('formAgendamento').addEventListener('submit', function () {
        botao.disabled = true;
        botao.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';
    });

    atualizarTotal();
    if (campoData.value) carregarHorarios();
})();
</script>
@endpush
