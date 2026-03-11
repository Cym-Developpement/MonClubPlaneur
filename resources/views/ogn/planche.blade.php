@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">

            @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {{-- ── Sélection de date ──────────────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form method="GET" action="/planchesOgn" class="d-flex align-items-center gap-2">
                        <label class="fw-semibold mb-0 me-2">
                            <i class="fas fa-satellite-dish me-1"></i>Import OGN
                        </label>
                        <input type="date" name="DATE" value="{{ $date }}" class="form-control form-control-sm" style="width:160px;">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-sync-alt me-1"></i>Charger
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">
                    Vols du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                </div>
                <div class="card-body">

                    @if($flights === null)
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>Pas de données OGN pour cette journée.
                    </div>
                    @endif

                    <form method="POST" action="/planchesOgn/{{ $date }}">
                        @csrf

                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-hover align-middle" style="table-layout:fixed;">
                                <colgroup>
                                    <col style="width:40px;">
                                    <col style="width:120px;">
                                    <col style="width:130px;">
                                    <col style="width:85px;">
                                    <col style="width:85px;">
                                    <col style="width:75px;">
                                    <col style="width:100px;">
                                    <col style="width:100px;">
                                    <col style="min-width:140px;">
                                    <col style="min-width:140px;">
                                    <col style="min-width:140px;">
                                    <col style="min-width:120px;">
                                    <col style="width:36px;">
                                </colgroup>
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        <th class="small text-muted">Source</th>
                                        <th>Aéronef</th>
                                        <th>Décollage</th>
                                        <th>Atterri.</th>
                                        <th>Durée</th>
                                        <th class="small">Ind. moteur ↑</th>
                                        <th class="small">Ind. moteur ↓</th>
                                        <th>PIC</th>
                                        <th>Facturable</th>
                                        <th>Instructeur</th>
                                        <th>Type lancement</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                {{-- ── Vols OGN ────────────────────────────── --}}
                                <tbody>
                                @isset($flights)
                                @foreach($flights->flights as $flight)
                                @php
                                    $i        = $loop->index;
                                    $hasAc    = isset($flight['aircraft']);
                                    $isMotor  = $hasAc && $flight['aircraft']->type == 1;
                                    $isTowing = !empty($flight['flight']['towing']);
                                    $towIdx   = isset($flight['flight']['tow']) && $flight['flight']['tow'] !== null
                                                ? (int) $flight['flight']['tow']
                                                : null;
                                    $startTsp = $flight['flight']['start_tsp'];
                                    $stopTsp  = $flight['flight']['stop_tsp'];
                                    $duration = $stopTsp > $startTsp ? gmdate('H\hi', $stopTsp - $startTsp) : '—';
                                @endphp
                                <tr class="{{ $hasAc ? '' : 'table-warning' }} {{ $isTowing ? 'tow-pair border-start border-3 border-warning' : ($towIdx !== null ? 'tow-pair border-start border-3 border-primary' : '') }}"
                                    data-ogn-idx="{{ $i }}"
                                    @if($towIdx !== null) data-tow="{{ $towIdx }}" @endif>
                                    <td>
                                        @if($hasAc)
                                        <div class="form-check mb-0">
                                            <input type="checkbox" class="form-check-input ogn-check"
                                                   name="flights[{{ $i }}][import]" value="1"
                                                   id="ognCheck-{{ $i }}">
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isTowing)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-link me-1"></i>Remorqueur
                                            </span>
                                        @elseif($towIdx !== null)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-link me-1"></i>Remorqué
                                            </span>
                                        @else
                                            <span class="badge bg-info text-dark">OGN</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold text-truncate">
                                        @if($hasAc)
                                            {{ $flight['aircraft']->name }}
                                            <input type="hidden" name="flights[{{ $i }}][aircraftId]" value="{{ $flight['aircraft']->id }}">
                                        @else
                                            <span class="text-muted small">{{ $flight['device']['address'] }}</span>
                                            <span class="badge bg-warning text-dark">?</span>
                                        @endif
                                        <input type="hidden" name="flights[{{ $i }}][start_tsp]" value="{{ $startTsp }}">
                                        <input type="hidden" name="flights[{{ $i }}][stop_tsp]"  value="{{ $stopTsp }}">
                                    </td>
                                    <td class="small text-nowrap">{{ $flight['flight']['start'] }}</td>
                                    <td class="small text-nowrap">{{ $flight['flight']['stop'] }}</td>
                                    <td class="small text-nowrap">{{ $duration }}</td>
                                    <td>
                                        @if($isMotor)
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                               name="flights[{{ $i }}][motorStartTime]" value="0" placeholder="0.00"
                                               title="Laisser à 0 = temps de vol utilisé automatiquement">
                                        @else
                                        <input type="hidden" name="flights[{{ $i }}][motorStartTime]" value="0">
                                        @endif
                                    </td>
                                    <td>
                                        @if($isMotor)
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                               name="flights[{{ $i }}][motorEndTime]" value="0" placeholder="0.00"
                                               title="Laisser à 0 = temps de vol utilisé automatiquement">
                                        @else
                                        <input type="hidden" name="flights[{{ $i }}][motorEndTime]" value="0">
                                        @endif
                                    </td>
                                    <td>
                                        @if($hasAc)
                                        @if($isTowing)
                                        <div class="small text-muted mb-1"><i class="fas fa-user-tie me-1"></i>Pilote remor.</div>
                                        @endif
                                        <select class="form-select form-select-sm ogn-pic"
                                                name="flights[{{ $i }}][userId]" data-idx="{{ $i }}">
                                            <option value="0">— Pilote —</option>
                                            @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hasAc)
                                        <select class="form-select form-select-sm"
                                                name="flights[{{ $i }}][userPayId]" id="ognUserPay-{{ $i }}">
                                            <option value="0">— Facturable —</option>
                                            @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hasAc)
                                        <select class="form-select form-select-sm"
                                                name="flights[{{ $i }}][instructorId]">
                                            <option value="0">— Aucun —</option>
                                            @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hasAc)
                                        <select class="form-select form-select-sm"
                                                name="flights[{{ $i }}][startTypeId]">
                                            @foreach($startTypes as $st)
                                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </td>
                                    <td></td>
                                </tr>
                                @endforeach
                                @endisset
                                </tbody>

                                {{-- ── Vols manuels (ajoutés par JS) ──────── --}}
                                <tbody id="manualFlightsBody"></tbody>

                            </table>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-secondary mb-4" onclick="addManualFlight()">
                            <i class="fas fa-plus me-1"></i>Ajouter un vol manuel
                        </button>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-save me-2"></i>Enregistrer les vols sélectionnés
                            </button>
                        </div>
                    </form>

                    @can('admin:super')
                    @isset($flights)
                    <div class="accordion mt-3" id="ognRawAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2 small text-muted" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#ognRawBody">
                                    <i class="fas fa-code me-2"></i>Réponse brute OGN
                                </button>
                            </h2>
                            <div id="ognRawBody" class="accordion-collapse collapse">
                                <div class="accordion-body p-0">
                                    <pre class="mb-0 p-3 small bg-light" style="max-height:400px;overflow:auto;">{{ json_encode($flights->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endisset
                    @endcan

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
var aircraftOptions  = `@foreach($aircrafts as $ac)<option value="{{ $ac->id }}">{{ addslashes($ac->name) }}</option>@endforeach`;
var userOptions      = `<option value="0">— Aucun —</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ addslashes($u->name) }}</option>@endforeach`;
var picOptions       = `<option value="0">— PIC —</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ addslashes($u->name) }}</option>@endforeach`;
var startTypeOptions = `@foreach($startTypes as $st)<option value="{{ $st->id }}">{{ addslashes($st->name) }}</option>@endforeach`;

// ── Auto-sync des paires remorqueur/remorqué ─────────────────────────────
document.querySelectorAll('[data-tow]').forEach(function(gliderRow) {
    var gliderCb = gliderRow.querySelector('.ogn-check');
    if (!gliderCb) return;

    var towIdx = gliderRow.dataset.tow;
    var towRow = document.querySelector('[data-ogn-idx="' + towIdx + '"]');
    if (!towRow) return;
    var towCb = towRow.querySelector('.ogn-check');
    if (!towCb) return;

    // Coche le planeur → coche le remorqueur
    gliderCb.addEventListener('change', function() {
        towCb.checked = this.checked;
    });
    // Décoche le remorqueur → décoche le planeur
    towCb.addEventListener('change', function() {
        if (!this.checked) gliderCb.checked = false;
    });
});

// ── Auto-fill facturable depuis PIC (vols OGN) ───────────────────────────
document.querySelectorAll('.ogn-pic').forEach(function(sel) {
    sel.addEventListener('change', function() {
        var pay = document.getElementById('ognUserPay-' + this.dataset.idx);
        if (pay) pay.value = this.value;
    });
});

// ── Vols manuels ─────────────────────────────────────────────────────────
var manualIdx = 0;

function addManualFlight() {
    var i = manualIdx++;
    var row = `
    <tr id="manualRow-${i}">
        <td><div class="form-check mb-0"><input type="checkbox" class="form-check-input" name="manualFlights[${i}][import]" value="1" checked></div></td>
        <td><span class="badge bg-secondary">Manuel</span></td>
        <td><select class="form-select form-select-sm" name="manualFlights[${i}][aircraftId]">${aircraftOptions}</select></td>
        <td><input type="time" class="form-control form-control-sm" name="manualFlights[${i}][takeOffTime]" required></td>
        <td><input type="time" class="form-control form-control-sm" name="manualFlights[${i}][landingTime]" required></td>
        <td class="text-muted small">—</td>
        <td><input type="number" step="0.01" class="form-control form-control-sm" name="manualFlights[${i}][motorStartTime]" value="0" placeholder="0.00" title="Laisser à 0 = temps de vol utilisé automatiquement"></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm" name="manualFlights[${i}][motorEndTime]" value="0" placeholder="0.00" title="Laisser à 0 = temps de vol utilisé automatiquement"></td>
        <td><select class="form-select form-select-sm manual-pic" name="manualFlights[${i}][userId]" data-idx="${i}">${picOptions}</select></td>
        <td><select class="form-select form-select-sm" name="manualFlights[${i}][userPayId]" id="manualUserPay-${i}">${userOptions}</select></td>
        <td><select class="form-select form-select-sm" name="manualFlights[${i}][instructorId]">${userOptions}</select></td>
        <td><select class="form-select form-select-sm" name="manualFlights[${i}][startTypeId]">${startTypeOptions}</select></td>
        <td><button type="button" class="btn btn-link text-danger p-0" onclick="removeManualFlight(${i})"><i class="fas fa-trash"></i></button></td>
    </tr>`;
    document.getElementById('manualFlightsBody').insertAdjacentHTML('beforeend', row);
    document.querySelector(`[name="manualFlights[${i}][userId]"]`).addEventListener('change', function() {
        var pay = document.getElementById('manualUserPay-' + i);
        if (pay) pay.value = this.value;
    });
}

function removeManualFlight(i) {
    var row = document.getElementById('manualRow-' + i);
    if (row) row.remove();
}
</script>
@endpush

@endsection
