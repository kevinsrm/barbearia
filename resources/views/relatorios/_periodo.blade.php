<div class="card mb-3">
    <div class="card-body py-2">
        <form action="{{ $acao }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1" for="de">De</label>
                <input type="date" class="form-control form-control-sm" id="de" name="de" value="{{ request('de', $de->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1" for="ate">Até</label>
                <input type="date" class="form-control form-control-sm" id="ate" name="ate" value="{{ request('ate', $ate->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <div class="btn-group btn-group-sm w-100" role="group">
                    <a class="btn btn-outline-secondary" href="{{ $acao }}?de={{ now()->startOfMonth()->format('Y-m-d') }}&ate={{ now()->endOfMonth()->format('Y-m-d') }}">Mês atual</a>
                    <a class="btn btn-outline-secondary" href="{{ $acao }}?de={{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}&ate={{ now()->subMonth()->endOfMonth()->format('Y-m-d') }}">Mês passado</a>
                </div>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-sm btn-dark flex-grow-1" type="submit"><i class="bi bi-funnel"></i> Aplicar</button>
                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="window.print()"><i class="bi bi-printer"></i></button>
            </div>
        </form>
    </div>
</div>
