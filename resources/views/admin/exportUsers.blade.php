@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">

            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="fas fa-file-csv"></i>
                    <strong>Export CSV — Utilisateurs</strong>
                </div>
                <div class="card-body">

                    <form method="POST" action="/admin/export/users">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">Filtre</label>
                            <select name="filter" class="form-select" style="max-width: 300px;">
                                <option value="active" {{ $filter === 'active' ? 'selected' : '' }}>Actifs uniquement</option>
                                <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>Tous (actifs + inactifs)</option>
                                @foreach(['2022','2023','2024','2025','2026'] as $y)
                                <option value="year:{{ $y }}" {{ $filter === 'year:'.$y ? 'selected' : '' }}>Adhérents {{ $y }}</option>
                                @endforeach
                            </select>
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

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-secondary fs-6">{{ count($rows) }} utilisateur(s)</span>

                        @php
                            $csvParams = http_build_query(['filter' => $filter] + ['cols' => $selectedCols]);
                        @endphp
                        <a href="/admin/export/users/csv?{{ $csvParams }}"
                           class="btn btn-success">
                            <i class="fas fa-download me-1"></i>Télécharger CSV
                        </a>
                    </div>

                    @if(count($rows) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    @foreach($headers as $h)
                                    <th>{{ $h }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                <tr>
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

                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
