@php
    $cores = ['pendente' => 'warning', 'confirmado' => 'info', 'concluido' => 'success', 'cancelado' => 'secondary', 'faltou' => 'danger', 'bloqueado' => 'dark'];
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="cliente_id">Cliente</label>
        <select class="form-select" id="cliente_id" name="cliente_id">
            <option value="">— Cliente avulso / não cadastrado —</option>
            @foreach ($clientes as $cliente)
                <option value="{{ $cliente->id }}" {{ (int) old('cliente_id', $agendamento->cliente_id) === $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->pessoa->nome ?? 'Cliente' }}{{ $cliente->pessoa->telefone ? ' · '.$cliente->pessoa->telefone : '' }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Não achou o cliente? <a href="{{ route('admin.pessoas.create', ['tipo' => 'cliente']) }}">Cadastre aqui</a>.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="funcionario_id">Profissional</label>
        <select class="form-select" id="funcionario_id" name="funcionario_id">
            <option value="">— Qualquer profissional —</option>
            @foreach ($funcionarios as $funcionario)
                <option value="{{ $funcionario->id }}" {{ (int) old('funcionario_id', $agendamento->funcionario_id) === $funcionario->id ? 'selected' : '' }}>
                    {{ $funcionario->pessoa->nome ?? 'Profissional' }}{{ $funcionario->cargo ? ' · '.$funcionario->cargo->nome : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label" for="data">Data *</label>
        <input type="date" class="form-control @error('data') is-invalid @enderror" id="data" name="data"
               value="{{ old('data', $agendamento->data ? $agendamento->data->format('Y-m-d') : today()->format('Y-m-d')) }}" required>
        @error('data')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="hora">Horário *</label>
        <select class="form-select @error('hora') is-invalid @enderror" id="hora" name="hora" required>
            @php $horaAtual = old('hora', $agendamento->hora ? substr($agendamento->hora, 0, 5) : ''); @endphp
            @if ($horaAtual)
                <option value="{{ $horaAtual }}" selected>{{ $horaAtual }}</option>
            @endif
        </select>
        @error('hora')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text" id="ajudaHora">Selecione a data para carregar os horários livres.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label" for="status">Situação</label>
        <select class="form-select" id="status" name="status">
            @foreach ($statusDisponiveis as $valor => $rotulo)
                <option value="{{ $valor }}" {{ old('status', $agendamento->status ?: 'confirmado') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label class="form-label">Serviços *</label>
        <div class="row g-2">
            @forelse ($servicos as $servico)
                <div class="col-md-4">
                    <div class="form-check border rounded p-2 h-100">
                        <input class="form-check-input servico-check" type="checkbox" name="servico_ids[]" value="{{ $servico->id }}"
                               id="servico-{{ $servico->id }}" data-valor="{{ $servico->valor }}" data-duracao="{{ $servico->duracao }}"
                               {{ in_array($servico->id, old('servico_ids', $servicosSelecionados ?? [])) ? 'checked' : '' }}>
                        <label class="form-check-label w-100" for="servico-{{ $servico->id }}">
                            <span class="d-block small">{{ $servico->nome }}</span>
                            <small class="text-muted">@money($servico->valor) · {{ $servico->duracao }} min</small>
                        </label>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning mb-0">Nenhum serviço cadastrado. <a href="{{ route('admin.servicos.create') }}">Cadastre o primeiro serviço</a>.</div>
                </div>
            @endforelse
        </div>
        @error('servico_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="observacoes">Observações</label>
        <textarea class="form-control" id="observacoes" name="observacoes" rows="2" maxlength="500">{{ old('observacoes', $agendamento->observacoes) }}</textarea>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center mt-4 gap-2">
    <div>Total dos serviços: <strong id="totalServicos">R$ 0,00</strong></div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.agendamentos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Salvar agendamento</button>
    </div>
</div>

@php
    $urlHorarios = route('admin.agendamentos.horarios');
    $horaSelecionada = old('hora', isset($agendamento) && $agendamento->hora ? substr((string) $agendamento->hora, 0, 5) : '');
@endphp
@push('scripts')
<script>
(function () {
    const url = {!! json_encode($urlHorarios) !!};
    const campoData = document.getElementById('data');
    const campoFuncionario = document.getElementById('funcionario_id');
    const campoHora = document.getElementById('hora');
    const ajuda = document.getElementById('ajudaHora');
    const total = document.getElementById('totalServicos');
    const horaSelecionada = {!! json_encode($horaSelecionada) !!};

    function somarServicos() {
        let soma = 0;
        document.querySelectorAll('.servico-check:checked').forEach(function (item) {
            soma += parseFloat(item.dataset.valor || 0);
        });
        total.textContent = 'R$ ' + soma.toFixed(2).replace('.', ',');
    }

    async function carregarHorarios() {
        if (!campoData.value) return;
        campoHora.innerHTML = '<option value="">Carregando...</option>';

        const params = new URLSearchParams({ data: campoData.value });
        if (campoFuncionario.value) params.append('funcionario_id', campoFuncionario.value);

        try {
            const resposta = await fetch(url + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const dados = await resposta.json();
            let opcoes = '';

            if (dados.fechado) {
                ajuda.textContent = 'Não há atendimento neste dia da semana.';
                campoHora.innerHTML = '<option value="">Dia fechado</option>';
                return;
            }

            if (!dados.horarios.length) {
                ajuda.textContent = 'Nenhum horário livre nesta data.';
                campoHora.innerHTML = '<option value="">Sem horários</option>';
                return;
            }

            dados.horarios.forEach(function (h) {
                opcoes += '<option value="' + h + '"' + (h === horaSelecionada ? ' selected' : '') + '>' + h + '</option>';
            });

            if (horaSelecionada && dados.horarios.indexOf(horaSelecionada) === -1) {
                opcoes = '<option value="' + horaSelecionada + '" selected>' + horaSelecionada + ' (atual)</option>' + opcoes;
            }

            campoHora.innerHTML = opcoes;
            ajuda.textContent = dados.horarios.length + ' horário(s) livre(s).';
        } catch (e) {
            ajuda.textContent = 'Falha ao carregar horários.';
        }
    }

    campoData.addEventListener('change', carregarHorarios);
    campoFuncionario.addEventListener('change', carregarHorarios);
    document.querySelectorAll('.servico-check').forEach(function (item) { item.addEventListener('change', somarServicos); });

    somarServicos();
    carregarHorarios();
})();
</script>
@endpush
