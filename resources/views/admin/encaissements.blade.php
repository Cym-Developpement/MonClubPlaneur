@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-coins me-2"></i><strong>Encaissements</strong></span>
                    <span class="badge bg-success fs-6">Total : {{ number_format($totalCents / 100, 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="card-body">

                    <form method="GET" action="/admin/encaissements">
                        <div class="row g-3 mb-4">
                            <div class="col-auto">
                                <label class="form-label fw-bold">Type</label>
                                <select name="type" class="form-select">
                                    <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Tous</option>
                                    <option value="cb" {{ $type === 'cb' ? 'selected' : '' }}>CB / HelloAsso</option>
                                    <option value="cheque" {{ $type === 'cheque' ? 'selected' : '' }}>Chèque</option>
                                    <option value="virement" {{ $type === 'virement' ? 'selected' : '' }}>Virement</option>
                                    <option value="especes" {{ $type === 'especes' ? 'selected' : '' }}>Espèces</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label fw-bold">Du</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                            </div>
                            <div class="col-auto">
                                <label class="form-label fw-bold">Au</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                            </div>
                            <div class="col-auto">
                                <label class="form-label fw-bold">Statut</label>
                                <select name="statut" class="form-select">
                                    <option value="all" {{ $statut === 'all' ? 'selected' : '' }}>Tous</option>
                                    <option value="valide" {{ $statut === 'valide' ? 'selected' : '' }}>Validé</option>
                                    <option value="attente" {{ $statut === 'attente' ? 'selected' : '' }}>En attente</option>
                                </select>
                            </div>
                            <div class="col-auto d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>Filtrer
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Adhérent</th>
                                    <th>Libellé</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-center">Statut</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $tr)
                                <tr>
                                    <td>{{ date('d/m/Y', $tr->time) }}</td>
                                    <td>{{ $users[$tr->idUser] ?? 'Utilisateur #'.$tr->idUser }}</td>
                                    <td>{{ $tr->name }}</td>
                                    <td class="text-end">{{ number_format($tr->value / 100, 2, ',', ' ') }} &euro;</td>
                                    <td class="text-center">
                                        @if($tr->valid)
                                            <span class="badge bg-success">Validé</span>
                                        @else
                                            <span class="badge bg-warning text-dark">En attente</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$tr->valid)
                                            <form method="POST" action="{{ route('validTransactionPost') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $tr->id }}">
                                                <button type="submit" class="btn btn-sm btn-success" title="Valider">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('deleteTransactionPost') }}" class="d-inline"
                                                  onsubmit="return confirm('Supprimer cette transaction ?');">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $tr->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $transactions->appends(request()->query())->links('pagination::simple-centered') }}
                    </div>
                    @else
                    <p class="text-muted">Aucun encaissement trouvé pour ces critères.</p>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
