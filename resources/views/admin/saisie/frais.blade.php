<form method="post" action="/saisiePeriodique">
  <input type="hidden" name="year" value="{{ $year }}">
  <input type="hidden" name="typeAdd" value="frais">
  @csrf
  <table class="table">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Adhérent</th>
        <th scope="col">Frais</th>
        <th scope="col">Facturer</th>
      </tr>
    </thead>
    <tbody>
      @php $totalInvoiceDayFlight = 0; @endphp
      @foreach($users as $user)
      @php $user->yearState = $year; @endphp
      <tr>
        <th scope="row">{{ $user->id }}</th>
        <td>{{ $user->name }}</td>
        <td>
          @if($user->current_day_flight_paid > 0)
            @if($user->current_day_flight_state == $user->current_day_flight_invoice)
            <span class="badge badge-success">{{ $user->current_day_flight_state }} Jours facturé(s)</span>
            @else
            <span class="badge badge-danger">{{ $user->current_day_flight_state }} Jours facturé(s) & {{ ($user->current_day_flight_invoice-$user->current_day_flight_state) }} a facturer</span>
            @endif
          @endif
        </td>
        <td>
          @if($user->current_day_flight_state < $user->current_day_flight_invoice)
            @php $totalInvoiceDayFlight += ($user->current_day_flight_invoice-$user->current_day_flight_state); @endphp
            <input name="addDayFlightInvoice[{{ $user->id }}]" class="form-control form-control-sm" type="number" step="1" min="0" max="10" value="{{ ($user->current_day_flight_invoice-$user->current_day_flight_state) }}" placeholder=".form-control-sm">
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="row justify-content-center">
    <div class="col-md-8 text-center">
      <h4>{{ $totalInvoiceDayFlight }} Jour(s) a facturer ({{ ($totalInvoiceDayFlight*15) }} €)</h4>
    </div>
  </div>
  <div class="row justify-content-center">
    <div class="col-md-8">
      <button type="submit" class="btn btn-primary btn-block">Enregistrer</button>
    </div>
  </div>
</form>