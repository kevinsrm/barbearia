@extends('layouts.admin')

@section('title', 'Configurações')

@section('content')
    @php
        $diasSemana = [0 => 'Domingo', 1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado'];
        $fechados = array_map('intval', array_filter(explode(',', (string) ($configuracoes['AGENDA_DIAS_FECHADOS'] ?? '0')), fn ($v) => trim((string) $v) !== ''));
        $intervalos = [10 => '10 minutos', 15 => '15 minutos', 20 => '20 minutos', 30 => '30 minutos', 45 => '45 minutos', 60 => '1 hora'];
    @endphp

    <h1 class="h4 mb-1"><i class="bi bi-gear"></i> Configurações do sistema</h1>
    <p class="text-muted small">
        Estes dados aparecem no site público, nos comprovantes de venda e na agenda online.
        Ao salvar, o sistema grava no arquivo <code>.env</code> — ou seja, nada é perdido quando você atualizar o código.
    </p>

    @unless ($envGravavel)
        <div class="alert alert-warning">
            <strong><i class="bi bi-exclamation-triangle"></i> Sem permissão de escrita no arquivo <code>.env</code>.</strong><br>
            No seu servidor (cPanel/FTP), ajuste a permissão do arquivo <code>.env</code> para <code>644</code> ou <code>664</code>
            e da pasta do sistema para <code>755</code>. Depois recarregue esta página.
        </div>
    @endunless

    <form action="{{ route('admin.configuracoes.update') }}" method="POST" id="formConfig">
        @csrf

        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Dados da barbearia</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="BARBEARIA_NOME">Nome da barbearia <span class="text-danger">*</span></label>
                        <input class="form-control @error('BARBEARIA_NOME') is-invalid @enderror" id="BARBEARIA_NOME" name="BARBEARIA_NOME"
                               value="{{ old('BARBEARIA_NOME', $configuracoes['BARBEARIA_NOME']) }}" required maxlength="120">
                        @error('BARBEARIA_NOME')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="APP_URL">Endereço do site (URL)</label>
                        <input class="form-control" id="APP_URL" name="APP_URL" value="{{ old('APP_URL', config('app.url')) }}" placeholder="https://suabarbearia.com.br">
                        <div class="form-text">Usado nos links enviados por e-mail/WhatsApp.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="BARBEARIA_TELEFONE">Telefone</label>
                        <input class="form-control @error('BARBEARIA_TELEFONE') is-invalid @enderror" id="BARBEARIA_TELEFONE" name="BARBEARIA_TELEFONE"
                               value="{{ old('BARBEARIA_TELEFONE', $configuracoes['BARBEARIA_TELEFONE']) }}">
                        @error('BARBEARIA_TELEFONE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="BARBEARIA_WHATSAPP">WhatsApp (com DDD)</label>
                        <input class="form-control" id="BARBEARIA_WHATSAPP" name="BARBEARIA_WHATSAPP"
                               value="{{ old('BARBEARIA_WHATSAPP', $configuracoes['BARBEARIA_WHATSAPP']) }}" placeholder="98999990000">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="BARBEARIA_EMAIL">E-mail</label>
                        <input type="email" class="form-control @error('BARBEARIA_EMAIL') is-invalid @enderror" id="BARBEARIA_EMAIL" name="BARBEARIA_EMAIL"
                               value="{{ old('BARBEARIA_EMAIL', $configuracoes['BARBEARIA_EMAIL']) }}">
                        @error('BARBEARIA_EMAIL')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="BARBEARIA_ENDERECO">Endereço</label>
                        <input class="form-control" id="BARBEARIA_ENDERECO" name="BARBEARIA_ENDERECO"
                               value="{{ old('BARBEARIA_ENDERECO', $configuracoes['BARBEARIA_ENDERECO']) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="BARBEARIA_CIDADE">Cidade / UF</label>
                        <input class="form-control" id="BARBEARIA_CIDADE" name="BARBEARIA_CIDADE"
                               value="{{ old('BARBEARIA_CIDADE', $configuracoes['BARBEARIA_CIDADE']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="BARBEARIA_INSTAGRAM">Instagram</label>
                        <input class="form-control" id="BARBEARIA_INSTAGRAM" name="BARBEARIA_INSTAGRAM"
                               value="{{ old('BARBEARIA_INSTAGRAM', $configuracoes['BARBEARIA_INSTAGRAM']) }}" placeholder="@suabarbearia">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Agenda e agendamento online</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="AGENDA_ABERTURA">Abre às <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('AGENDA_ABERTURA') is-invalid @enderror" id="AGENDA_ABERTURA" name="AGENDA_ABERTURA"
                               value="{{ old('AGENDA_ABERTURA', $configuracoes['AGENDA_ABERTURA']) }}" required>
                        @error('AGENDA_ABERTURA')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="AGENDA_FECHAMENTO">Fecha às <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('AGENDA_FECHAMENTO') is-invalid @enderror" id="AGENDA_FECHAMENTO" name="AGENDA_FECHAMENTO"
                               value="{{ old('AGENDA_FECHAMENTO', $configuracoes['AGENDA_FECHAMENTO']) }}" required>
                        @error('AGENDA_FECHAMENTO')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="AGENDA_INTERVALO">Intervalo entre horários</label>
                        <select class="form-select" id="AGENDA_INTERVALO" name="AGENDA_INTERVALO">
                            @foreach ($intervalos as $minutos => $rotulo)
                                <option value="{{ $minutos }}" {{ (int) old('AGENDA_INTERVALO', $configuracoes['AGENDA_INTERVALO']) === $minutos ? 'selected' : '' }}>{{ $rotulo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="AGENDA_ANTECEDENCIA_MINIMA">Antecedência mínima (min)</label>
                        <input type="number" min="0" max="1440" class="form-control" id="AGENDA_ANTECEDENCIA_MINIMA" name="AGENDA_ANTECEDENCIA_MINIMA"
                               value="{{ old('AGENDA_ANTECEDENCIA_MINIMA', $configuracoes['AGENDA_ANTECEDENCIA_MINIMA']) }}">
                        <div class="form-text">Tempo mínimo antes do horário para permitir agendamento.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label d-block">Dias em que a barbearia não atende</label>
                        <input type="hidden" name="AGENDA_DIAS_FECHADOS" id="AGENDA_DIAS_FECHADOS" value="{{ implode(',', $fechados) }}">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($diasSemana as $numero => $nome)
                                <div class="form-check border rounded px-3 py-2 mb-0">
                                    <input class="form-check-input dia-fechado" type="checkbox" value="{{ $numero }}" id="dia-{{ $numero }}"
                                           {{ in_array($numero, $fechados, true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dia-{{ $numero }}">{{ $nome }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Deixe tudo desmarcado se a barbearia abre todos os dias.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Comissões e vendas</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="COMISSAO_PADRAO">Comissão padrão (%)</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="COMISSAO_PADRAO" name="COMISSAO_PADRAO"
                               value="{{ old('COMISSAO_PADRAO', $configuracoes['COMISSAO_PADRAO']) }}">
                        <div class="form-text">Usada quando o profissional não tem percentual próprio.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="PRAZO_FIADO_DIAS">Prazo do fiado (dias)</label>
                        <input type="number" min="0" max="365" class="form-control" id="PRAZO_FIADO_DIAS" name="PRAZO_FIADO_DIAS"
                               value="{{ old('PRAZO_FIADO_DIAS', $configuracoes['PRAZO_FIADO_DIAS']) }}">
                        <div class="form-text">Vencimento das contas a receber geradas por vendas no fiado.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="PAGINACAO_POR_PAGINA">Itens por página</label>
                        <input type="number" min="5" max="100" class="form-control" id="PAGINACAO_POR_PAGINA" name="PAGINACAO_POR_PAGINA"
                               value="{{ old('PAGINACAO_POR_PAGINA', $configuracoes['PAGINACAO_POR_PAGINA']) }}">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="COMISSAO_PRODUTOS" name="COMISSAO_PRODUTOS" value="1"
                                   {{ old('COMISSAO_PRODUTOS', $configuracoes['COMISSAO_PRODUTOS']) === 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="COMISSAO_PRODUTOS">
                                Pagar comissão também sobre <strong>produtos</strong> vendidos (e não só sobre serviços)
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="BLOQUEAR_VENDA_SEM_ESTOQUE" name="BLOQUEAR_VENDA_SEM_ESTOQUE" value="1"
                                   {{ old('BLOQUEAR_VENDA_SEM_ESTOQUE', $configuracoes['BLOQUEAR_VENDA_SEM_ESTOQUE']) === 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="BLOQUEAR_VENDA_SEM_ESTOQUE">
                                <strong>Bloquear venda</strong> quando não houver saldo no estoque do produto
                                <span class="d-block small text-muted">
                                    Recomendado. Desligue apenas se você não controla o estoque dos produtos.
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-dark" type="submit" {{ $envGravavel ? '' : 'disabled' }}>
                <i class="bi bi-save"></i> Salvar configurações
            </button>
            <a href="{{ route('site.index') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                <i class="bi bi-box-arrow-up-right"></i> Ver o site público
            </a>
        </div>
    </form>

    <div class="card mt-4">
        <div class="card-header bg-white"><strong>Onde mais posso mexer?</strong></div>
        <div class="card-body small text-muted">
            <ul class="mb-0 ps-3">
                <li><strong>Usuários e permissões:</strong> menu <a href="{{ route('admin.usuarios.index') }}">Usuários</a>.</li>
                <li><strong>Serviços, preços e duração:</strong> menu <a href="{{ route('admin.servicos.index') }}">Serviços</a>.</li>
                <li><strong>Produtos e estoque:</strong> menu <a href="{{ route('admin.produtos.index') }}">Produtos</a>.</li>
                <li><strong>Banco de dados, e-mail e chaves:</strong> edite o arquivo <code>.env</code> na raiz do sistema (ou use o instalador em <code>/install</code>).</li>
            </ul>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var form = document.getElementById('formConfig');
        if (!form) return;

        function sincronizarDias() {
            var marcados = Array.prototype.slice.call(form.querySelectorAll('.dia-fechado:checked'))
                .map(function (campo) { return campo.value; });
            document.getElementById('AGENDA_DIAS_FECHADOS').value = marcados.join(',');
        }

        form.querySelectorAll('.dia-fechado').forEach(function (campo) {
            campo.addEventListener('change', sincronizarDias);
        });

        form.addEventListener('submit', sincronizarDias);
    })();
</script>
@endpush
