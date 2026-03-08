@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-export me-2"></i><strong>Export</strong>
                </div>
                <div class="card-body">

                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $activeTab === 'users' ? 'active' : '' }}"
                               href="/admin/export/users">
                                <i class="fas fa-users me-1"></i>Utilisateurs
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $activeTab === 'transactions' ? 'active' : '' }}"
                               href="/admin/export/transactions">
                                <i class="fas fa-exchange-alt me-1"></i>Transactions
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">

                        {{-- =================== TAB UTILISATEURS =================== --}}
                        <div class="tab-pane fade {{ $activeTab === 'users' ? 'show active' : '' }}" id="tab-users" role="tabpanel">

                            <form method="POST" action="/admin/export/users">
                                @csrf

                                <div class="row g-3 mb-4">
                                    <div class="col-auto">
                                        <label class="form-label fw-bold">Filtre</label>
                                        <select name="filter" class="form-select">
                                            <option value="active" {{ ($filter ?? 'active') === 'active' ? 'selected' : '' }}>Actifs uniquement</option>
                                            <option value="all" {{ ($filter ?? '') === 'all' ? 'selected' : '' }}>Tous (actifs + inactifs)</option>
                                            @foreach(['2022','2023','2024','2025','2026'] as $y)
                                            <option value="year:{{ $y }}" {{ ($filter ?? '') === 'year:'.$y ? 'selected' : '' }}>Adhérents {{ $y }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <label class="form-label fw-bold">Date du solde</label>
                                        <input type="date" name="solde_date" class="form-control"
                                               value="{{ $soldeDate ?? date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="mb-3 d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="exclude_technique" id="exclude_technique" value="1"
                                               {{ ($excludeTechnique ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="exclude_technique">
                                            Exclure les comptes techniques
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="exclude_zero_solde" id="exclude_zero_solde" value="1"
                                               {{ ($excludeZeroSolde ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="exclude_zero_solde">
                                            Exclure les soldes à 0 €
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Colonnes à exporter</label>
                                    <div class="row row-cols-2 row-cols-md-3 g-2">
                                        @foreach(($availableCols ?? []) as $key => $label)
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="cols[]" value="{{ $key }}"
                                                       id="col_{{ $key }}"
                                                       {{ in_array($key, $selectedCols ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="col_{{ $key }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>Prévisualiser
                                </button>
                            </form>

                            @if(isset($rows) && $rows !== null)
                            <hr>
                            <form method="POST" action="/admin/export/users/csv" id="downloadFormUsers">
                                @csrf
                                <input type="hidden" name="filter" value="{{ $filter ?? 'active' }}">
                                <input type="hidden" name="solde_date" value="{{ $soldeDate ?? date('Y-m-d') }}">
                                @if($excludeTechnique ?? true)
                                <input type="hidden" name="exclude_technique" value="1">
                                @endif
                                @if($excludeZeroSolde ?? false)
                                <input type="hidden" name="exclude_zero_solde" value="1">
                                @endif
                                @foreach(($selectedCols ?? []) as $col)
                                <input type="hidden" name="cols[]" value="{{ $col }}">
                                @endforeach

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="badge bg-secondary fs-6">{{ count($rows) }} utilisateur(s)</span>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-download me-1"></i>Télécharger CSV
                                    </button>
                                </div>

                                @if(count($rows) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width:40px;">
                                                    <input type="checkbox" id="selectAllUsers" title="Tout sélectionner / désélectionner" checked>
                                                </th>
                                                @foreach(($headers ?? []) as $h)
                                                <th>{{ $h }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rows as $i => $row)
                                            <tr>
                                                <td><input type="checkbox" name="ids[]" value="{{ ($userIds ?? [])[$i] ?? '' }}" checked></td>
                                                @foreach($row as $cell)
                                                <td>{{ $cell }}</td>
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <p class="text-muted">Aucun utilisateur trouvé pour ce filtre.</p>
                                @endif
                            </form>
                            @endif

                        </div>{{-- /tab-users --}}

                        {{-- =================== TAB TRANSACTIONS =================== --}}
                        <div class="tab-pane fade {{ $activeTab === 'transactions' ? 'show active' : '' }}" id="tab-transactions" role="tabpanel">

                            <form method="POST" action="/admin/export/transactions">
                                @csrf

                                <div class="row g-3 mb-4">
                                    <div class="col-auto">
                                        <label class="form-label fw-bold">Type</label>
                                        <select name="type" class="form-select">
                                            <option value="all" {{ ($trType ?? 'all') === 'all' ? 'selected' : '' }}>Tous les types</option>
                                            <option value="helloasso" {{ ($trType ?? '') === 'helloasso' ? 'selected' : '' }}>HelloAsso uniquement</option>
                                            <option value="virement" {{ ($trType ?? '') === 'virement' ? 'selected' : '' }}>Virements uniquement</option>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <label class="form-label fw-bold">Du</label>
                                        <input type="date" name="date_from" class="form-control"
                                               value="{{ $dateFrom ?? date('Y-m-01') }}">
                                    </div>
                                    <div class="col-auto">
                                        <label class="form-label fw-bold">Au</label>
                                        <input type="date" name="date_to" class="form-control"
                                               value="{{ $dateTo ?? date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Colonnes à exporter</label>
                                    <div class="row row-cols-2 row-cols-md-3 g-2">
                                        @foreach(($trAvailableCols ?? []) as $key => $label)
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="cols[]" value="{{ $key }}"
                                                       id="trcol_{{ $key }}"
                                                       {{ in_array($key, $trSelectedCols ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="trcol_{{ $key }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>Prévisualiser
                                </button>
                            </form>

                            @if(isset($trRows) && $trRows !== null)
                            <hr>
                            <form method="POST" action="/admin/export/transactions/csv" id="downloadFormTr">
                                @csrf
                                <input type="hidden" name="type" value="{{ $trType ?? 'all' }}">
                                <input type="hidden" name="date_from" value="{{ $dateFrom ?? '' }}">
                                <input type="hidden" name="date_to" value="{{ $dateTo ?? '' }}">
                                @foreach(($trSelectedCols ?? []) as $col)
                                <input type="hidden" name="cols[]" value="{{ $col }}">
                                @endforeach

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="badge bg-secondary fs-6">{{ count($trRows) }} transaction(s)</span>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-download me-1"></i>Télécharger CSV
                                    </button>
                                </div>

                                @if(count($trRows) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width:40px;">
                                                    <input type="checkbox" id="selectAllTr" title="Tout sélectionner / désélectionner" checked>
                                                </th>
                                                @foreach(($trHeaders ?? []) as $h)
                                                <th>{{ $h }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($trRows as $i => $row)
                                            <tr>
                                                <td><input type="checkbox" name="ids[]" value="{{ ($trIds ?? [])[$i] ?? '' }}" checked></td>
                                                @foreach($row as $cell)
                                                <td>{{ $cell }}</td>
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <p class="text-muted">Aucune transaction trouvée pour ces critères.</p>
                                @endif
                            </form>
                            @endif

                        </div>{{-- /tab-transactions --}}

                    </div>{{-- /tab-content --}}

                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function setupSelectAll(selectAllId, checkboxName) {
        const selectAll = document.getElementById(selectAllId);
        if (!selectAll) return;
        const rows = () => document.querySelectorAll('input[name="' + checkboxName + '"]');
        selectAll.addEventListener('change', function () {
            rows().forEach(cb => cb.checked = this.checked);
        });
        document.addEventListener('change', function (e) {
            if (e.target && e.target.name === checkboxName) {
                const all = rows();
                selectAll.checked = Array.from(all).every(cb => cb.checked);
                selectAll.indeterminate = !selectAll.checked && Array.from(all).some(cb => cb.checked);
            }
        });
    }
    setupSelectAll('selectAllUsers', 'ids[]');
    setupSelectAll('selectAllTr', 'ids[]');
})();
</script>
@endpush

@endsection
