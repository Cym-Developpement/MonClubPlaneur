<div class="alert alert-success" role="alert">
  <b>{{ $loop->iteration }}</b> - Le vol du {{ $flightImport[0]->takeOffTime }} au {{ $flightImport[0]->landingTime }}, aeronef :  {{ $flightImport[0]->aircraft->register }}, pilote : {{ $flightImport[0]->user->name }} a été importé ({{ $flightImport[1]->value_eur }} €).
</div>
