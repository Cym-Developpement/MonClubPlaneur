@if(is_null($flightImport[0]))
<div class="alert alert-warning" role="alert">
  <b>{{ $loop->iteration }}</b> - Aéronef inconnu : seul le remorquage a été facturé à {{ App\Models\User::find($flightImport[1]->idUser)->name }} le {{ date('d/m/Y H:i', $flightImport[1]->time) }} ({{ $flightImport[1]->value_eur }} €).
</div>
@else
<div class="alert alert-success" role="alert">
  <b>{{ $loop->iteration }}</b> - Le vol du {{ $flightImport[0]->takeOffTime }} au {{ $flightImport[0]->landingTime }}, aeronef :  {{ $flightImport[0]->aircraft->register }}, pilote : {{ $flightImport[0]->user->name }} a été importé ({{ $flightImport[1]->value_eur }} €).
</div>
@endif
