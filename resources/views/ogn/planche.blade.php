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

            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <a class="btn btn-sm btn-outline-secondary me-2" href="/planchesOgn/{{ $previous }}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <span class="fw-semibold">Import OGN — {{ $date }}</span>
                    <a class="btn btn-sm btn-outline-secondary ms-2" href="/planchesOgn/{{ $next }}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    @isset($flights)
                    <a href="/planchesOgn/ignore/{{ $flights->id }}"
                       class="btn btn-sm btn-outline-danger ms-auto"
                       onclick="return confirm('Ignorer cette planche de vol ?');">
                        <i class="fas fa-ban me-1"></i>Ignorer
                    </a>
                    @endisset
                </div>

                <div class="card-body">
                    @isset($flights)
                    <form method="POST" action="/planchesOgn/{{ $date }}">
                        @csrf

                        {{-- ── Vols OGN ─────────────────────────────────────────── --}}
                        <h6 class="fw-semibold mb-2"><i class="fas fa-satellite-dish me-2"></i>Vols OGN</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        <th>Aéronef</th>
                                        <th>Décollage</th>
                                        <th>Atterrissage</th>
                                        <th>Durée</th>
                                        <th>Hauteur max</th>
                                        <th>PIC</th>
                                        <th>Facturable</th>
                                        <th>Instructeur</th>
                                        <th>Type lancement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($flights->flights as $flight)
                                    @php
                                        $i = $loop->index;
                                        $hasAircraft = isset($flight['aircraft']);
                                        $startTsp = $flight['flight']['start_tsp'];
                                        $stopTsp  = $flight['flight']['stop_tsp'];
                                        $duration = $stopTsp > $startTsp
                                            ? gmdate('H\hi', $stopTsp - $startTsp)
                                            : '—';
                                    @endphp
                                    <tr class="{{ $hasAircraft ? '' : 'table-warning' }}">
                                        <td>
                                            @if($hasAircraft)
                                            <div class="form-check">
                                                <input type="checkbox"
                                                       class="form-check-input ogn-check"
                                                       name="flights[{{ $i }}][import]"
                                                       id="ognImport-{{ $i }}"
                                                       value="1">
                                            </div>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">
                                            @if($hasAircraft)
                                                {{ $flight['aircraft']->name }}
                                                <input type="hidden" name="flights[{{ $i }}][aircraftId]" value="{{ $flight['aircraft']->id }}">
                                            @else
                                                <span class="text-muted small">{{ $flight['device']['address'] }}</span>
                                                <span class="badge bg-warning text-dark ms-1">Non reconnu</span>
                                            @endif
                                            <input type="hidden" name="flights[{{ $i }}][start_tsp]" value="{{ $startTsp }}">
                                            <input type="hidden" name="flights[{{ $i }}][stop_tsp]" value="{{ $stopTsp }}">
                                        </td>
                                        <td class="text-nowrap small">{{ $flight['flight']['start'] }}</td>
                                        <td class="text-nowrap small">{{ $flight['flight']['stop'] }}</td>
                                        <td class="text-nowrap small">{{ $duration }}</td>
                                        <td class="small text-muted">{{ $flight['flight']['max_height'] }} m ({{ $flight['flight']['max_alt'] }} m)</td>
                                        <td>
                                            @if($hasAircraft)
                                            <select class="form-select form-select-sm ogn-pic"
                                                    name="flights[{{ $i }}][userId]"
                                                    data-idx="{{ $i }}"
                                                    style="min-width: 140px;">
                                                <option value="0">— PIC —</option>
                                                @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hasAircraft)
                                            <select class="form-select form-select-sm"
                                                    name="flights[{{ $i }}][userPayId]"
                                                    id="ognUserPay-{{ $i }}"
                                                    style="min-width: 140px;">
                                                <option value="0">— Facturable —</option>
                                                @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hasAircraft)
                                            <select class="form-select form-select-sm"
                                                    name="flights[{{ $i }}][instructorId]"
                                                    style="min-width: 140px;">
                                                <option value="0">— Aucun —</option>
                                                @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hasAircraft)
                                            <select class="form-select form-select-sm"
                                                    name="flights[{{ $i }}][startTypeId]"
                                                    style="min-width: 120px;">
                                                @foreach($startTypes as $st)
                                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                                                @endforeach
                                            </select>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- ── Vols manuels ─────────────────────────────────────── --}}
                        <h6 class="fw-semibold mb-2"><i class="fas fa-plus-circle me-2"></i>Vols manuels</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle" id="manualFlightsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        <th>Aéronef</th>
                                        <th>Décollage</th>
                                        <th>Atterrissage</th>
                                        <th>PIC</th>
                                        <th>Facturable</th>
                                        <th>Instructeur</th>
                                        <th>Type lancement</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="manualFlightsBody">
                                    {{-- rows added by JS --}}
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-4" onclick="addManualFlight()">
                            <i class="fas fa-plus me-1"></i>Ajouter un vol
                        </button>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-save me-2"></i>Enregistrer les vols sélectionnés
                            </button>
                        </div>
                    </form>

                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Pas de données OGN pour cette journée.
                    </div>
                    <form method="POST" action="/planchesOgn/{{ $date }}">
                        @csrf
                        {{-- ── Vols manuels seulement ───────────────────────────── --}}
                        <h6 class="fw-semibold mb-2"><i class="fas fa-plus-circle me-2"></i>Vols manuels</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle" id="manualFlightsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        <th>Aéronef</th>
                                        <th>Décollage</th>
                                        <th>Atterrissage</th>
                                        <th>PIC</th>
                                        <th>Facturable</th>
                                        <th>Instructeur</th>
                                        <th>Type lancement</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="manualFlightsBody"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-4" onclick="addManualFlight()">
                            <i class="fas fa-plus me-1"></i>Ajouter un vol
                        </button>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-save me-2"></i>Enregistrer les vols
                            </button>
                        </div>
                    </form>
                    @endisset

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Templates pour les selects ────────────────────────────────────────────
var aircraftOptions = `
    @foreach($aircrafts as $ac)
    <option value="{{ $ac->id }}">{{ addslashes($ac->name) }}</option>
    @endforeach
`;
var userOptions = `
    <option value="0">— Aucun —</option>
    @foreach($users as $u)
    <option value="{{ $u->id }}">{{ addslashes($u->name) }}</option>
    @endforeach
`;
var picOptions = `
    <option value="0">— PIC —</option>
    @foreach($users as $u)
    <option value="{{ $u->id }}">{{ addslashes($u->name) }}</option>
    @endforeach
`;
var startTypeOptions = `
    @foreach($startTypes as $st)
    <option value="{{ $st->id }}">{{ addslashes($st->name) }}</option>
    @endforeach
`;

var manualIdx = 0;

function addManualFlight() {
    var i = manualIdx++;
    var row = `
    <tr id="manualRow-${i}">
        <td>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="manualFlights[${i}][import]" value="1" checked>
            </div>
        </td>
        <td>
            <select class="form-select form-select-sm" name="manualFlights[${i}][aircraftId]" style="min-width:120px;">
                ${aircraftOptions}
            </select>
        </td>
        <td>
            <input type="time" class="form-control form-control-sm" name="manualFlights[${i}][takeOffTime]"
                   style="min-width:90px;" required>
        </td>
        <td>
            <input type="time" class="form-control form-control-sm" name="manualFlights[${i}][landingTime]"
                   style="min-width:90px;" required>
        </td>
        <td>
            <select class="form-select form-select-sm manual-pic" name="manualFlights[${i}][userId]"
                    data-idx="${i}" style="min-width:140px;">
                ${picOptions}
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm" name="manualFlights[${i}][userPayId]"
                    id="manualUserPay-${i}" style="min-width:140px;">
                ${userOptions}
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm" name="manualFlights[${i}][instructorId]"
                    style="min-width:140px;">
                ${userOptions}
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm" name="manualFlights[${i}][startTypeId]"
                    style="min-width:120px;">
                ${startTypeOptions}
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-link text-danger p-0" onclick="removeManualFlight(${i})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;
    document.getElementById('manualFlightsBody').insertAdjacentHTML('beforeend', row);

    // Auto-fill facturable when PIC changes
    document.querySelector(`[name="manualFlights[${i}][userId]"]`).addEventListener('change', function() {
        var paySelect = document.getElementById(`manualUserPay-${i}`);
        paySelect.value = this.value;
    });
}

function removeManualFlight(i) {
    var row = document.getElementById('manualRow-' + i);
    if (row) row.remove();
}

// ── Auto-fill facturable depuis PIC (vols OGN) ────────────────────────────
document.querySelectorAll('.ogn-pic').forEach(function(sel) {
    sel.addEventListener('change', function() {
        var idx = this.dataset.idx;
        var paySelect = document.getElementById('ognUserPay-' + idx);
        if (paySelect) paySelect.value = this.value;
    });
});
</script>
@endpush

@endsection
