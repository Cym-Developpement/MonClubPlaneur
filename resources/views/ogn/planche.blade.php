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
                                    <col style="width:40px;">     {{-- checkbox --}}
                                    <col style="width:110px;">    {{-- source --}}
                                    <col style="width:130px;">    {{-- aéronef --}}
                                    <col style="width:85px;">     {{-- décollage --}}
                                    <col style="width:85px;">     {{-- atterrissage --}}
                                    <col style="width:75px;">     {{-- durée --}}
                                    <col style="width:100px;">    {{-- ind. moteur départ --}}
                                    <col style="width:100px;">    {{-- ind. moteur arrivée --}}
                                    <col style="min-width:140px;"> {{-- PIC --}}
                                    <col style="min-width:140px;"> {{-- facturable --}}
                                    <col style="min-width:140px;"> {{-- instructeur --}}
                                    <col style="min-width:120px;"> {{-- type lancement --}}
                                    <col style="width:36px;">     {{-- actions --}}
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
                                <tbody>

                                    {{-- ── Vols OGN ───────────────────────── --}}
                                    @isset($flights)
                                    @foreach($flights->flights as $flight)
                                    @php
                                        $i = $loop->index;
                                        $hasAircraft = isset($flight['aircraft']);
                                        $isMotor     = $hasAircraft && $flight['aircraft']->type == 1;
                                        $startTsp    = $flight['flight']['start_tsp'];
                                        $stopTsp     = $flight['flight']['stop_tsp'];
                                        $duration    = $stopTsp > $startTsp ? gmdate('H\hi', $stopTsp - $startTsp) : '—';
                                    @endphp
                                    <tr class="{{ $hasAircraft ? '' : 'table-warning' }}">
                                        <td>
                                            @if($hasAircraft)
                                            <div class="form-check mb-0">
                                                <input type="checkbox" class="form-check-input ogn-check"
                                                       name="flights[{{ $i }}][import]" value="1">
                                            </div>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-info text-dark">OGN</span></td>
                                        <td class="fw-semibold text-truncate">
                                            @if($hasAircraft)
                                                {{ $flight['aircraft']->name }}
                                                <input type="hidden" name="flights[{{ $i }}][aircraftId]" value="{{ $flight['aircraft']->id }}">
                                            @else
                                                <span class="text-muted small">{{ $flight['device']['address'] }}</span>
                                                <span class="badge bg-warning text-dark">?</span>
                                            @endif
                                            <input type="hidden" name="flights[{{ $i }}][start_tsp]" value="{{ $startTsp }}">
                                            <input type="hidden" name="flights[{{ $i }}][stop_tsp]" value="{{ $stopTsp }}">
                                        </td>
                                        <td class="small text-nowrap">{{ $flight['flight']['start'] }}</td>
                                        <td class="small text-nowrap">{{ $flight['flight']['stop'] }}</td>
                                        <td class="small text-nowrap">{{ $duration }}</td>
                                        <td>
                                            @if($isMotor)
                                            <input type="number" step="0.01" class="form-control form-control-sm"
                                                   name="flights[{{ $i }}][motorStartTime]" value="0" placeholder="0.00">
                                            @else
                                            <input type="hidden" name="flights[{{ $i }}][motorStartTime]" value="0">
                                            @endif
                                        </td>
                                        <td>
                                            @if($isMotor)
                                            <input type="number" step="0.01" class="form-control form-control-sm"
                                                   name="flights[{{ $i }}][motorEndTime]" value="0" placeholder="0.00">
                                            @else
                                            <input type="hidden" name="flights[{{ $i }}][motorEndTime]" value="0">
                                            @endif
                                        </td>
                                        <td>
                                            @if($hasAircraft)
                                            <select class="form-select form-select-sm ogn-pic"
                                                    name="flights[{{ $i }}][userId]" data-idx="{{ $i }}">
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
                                                    name="flights[{{ $i }}][userPayId]" id="ognUserPay-{{ $i }}">
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
                                                    name="flights[{{ $i }}][instructorId]">
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

                                    {{-- ── Vols manuels (ajoutés par JS) ─── --}}
                                    <tbody id="manualFlightsBody"></tbody>

                                </tbody>
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
        <td><input type="number" step="0.01" class="form-control form-control-sm" name="manualFlights[${i}][motorStartTime]" value="0" placeholder="0.00"></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm" name="manualFlights[${i}][motorEndTime]" value="0" placeholder="0.00"></td>
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

document.querySelectorAll('.ogn-pic').forEach(function(sel) {
    sel.addEventListener('change', function() {
        var pay = document.getElementById('ognUserPay-' + this.dataset.idx);
        if (pay) pay.value = this.value;
    });
});
</script>
@endpush

@endsection
