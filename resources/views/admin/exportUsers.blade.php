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
                            <button class="nav-link active" id="tab-users-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-users"
                                    type="button" role="tab">
                                <i class="fas fa-users me-1"></i>Utilisateurs
                            </button>
                        </li>
                        {{-- Futurs onglets ici --}}
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-users" role="tabpanel">

                            {{-- Formulaire filtre + colonnes → prévisualisation --}}
                            <form method="POST" action="/admin/export/users">
                                @csrf

                                <div class="row g-3 mb-4">
                                    <div class="col-auto">
                                        <label class="form-label fw-bold">Filtre</label>
                                        <select name="filter" class="form-select">
                                            <option value="active" {{ $filter === 'active' ? 'selected' : '' }}>Actifs uniquement</option>
                                            <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>Tous (actifs + inactifs)</option>
                                            @foreach(['2022','2023','2024','2025','2026'] as $y)
                                            <option value="year:{{ $y }}" {{ $filter === 'year:'.$y ? 'selected' : '' }}>Adhérents {{ $y }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <label class="form-label fw-bold">Date du solde</label>
                                        <input type="date" name="solde_date" class="form-control"
                                               value="{{ $soldeDate }}">
                                    </div>
                                </div>

                                <div class="mb-3 d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="exclude_technique" id="exclude_technique" value="1"
                                               {{ $excludeTechnique ? 'checked' : '' }}>
                                        <label class="form-check-label" for="exclude_technique">
                                            Exclure les comptes techniques
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="exclude_zero_solde" id="exclude_zero_solde" value="1"
                                               {{ $excludeZeroSolde ? 'checked' : '' }}>
                                        <label class="form-check-label" for="exclude_zero_solde">
                                            Exclure les soldes à 0 €
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Colonnes à exporter</label>
                                    <div class="row row-cols-2 row-cols-md-3 g-2">
                                        @foreach($availableCols as $key => $label)
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="cols[]" value="{{ $key }}"
                                                       id="col_{{ $key }}"
                                                       {{ in_array($key, $selectedCols) ? 'checked' : '' }}>
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

                            @if($rows !== null)
                            <hr>

                            {{-- Formulaire de téléchargement avec sélection par ligne --}}
                            <form method="POST" action="/admin/export/users/csv" id="downloadForm">
                                @csrf
                                <input type="hidden" name="filter" value="{{ $filter }}">
                                <input type="hidden" name="solde_date" value="{{ $soldeDate }}">
                                @if($excludeTechnique)
                                <input type="hidden" name="exclude_technique" value="1">
                                @endif
                                @if($excludeZeroSolde)
                                <input type="hidden" name="exclude_zero_solde" value="1">
                                @endif
                                @foreach($selectedCols as $col)
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
                                                    <input type="checkbox" id="selectAll" title="Tout sélectionner / désélectionner" checked>
                                                </th>
                                                @foreach($headers as $h)
                                                <th>{{ $h }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rows as $i => $row)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="ids[]" value="{{ $userIds[$i] }}" checked>
                                                </td>
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
                    </div>{{-- /tab-content --}}

                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const selectAll = document.getElementById('selectAll');
    if (!selectAll) return;

    const rows = () => document.querySelectorAll('input[name="ids[]"]');

    selectAll.addEventListener('change', function () {
        rows().forEach(cb => cb.checked = this.checked);
    });

    document.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'ids[]') {
            const all = rows();
            selectAll.checked = Array.from(all).every(cb => cb.checked);
            selectAll.indeterminate = !selectAll.checked && Array.from(all).some(cb => cb.checked);
        }
    });
})();
</script>
@endpush

@endsection
