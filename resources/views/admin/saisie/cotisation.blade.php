<form method="post" action="/saisiePeriodique">
  <input type="hidden" name="year" value="{{ $year }}">
  @csrf
  <input type="hidden" name="typeAdd" value="cotisation">
  <table class="table">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Adhérent</th>
        <th scope="col">Cotisation</th>
        <th scope="col">Enregistrer</th>
      </tr>
    </thead>
    <tbody>
      @php $totalInvoiceCotisation = 0; @endphp
      @foreach($users as $user)
      @php $user->yearState = $year; @endphp
      <tr>
        <th scope="row">{{ $user->id }}</th>
        <td>{{ $user->name }}</td>
        <td>
          @if($user->cotisation_forced)
          <span class="badge badge-danger">Cotisation {{ $year }} non enregistrée</span>
          <br><small class="text-danger">Au moins 1 vol a été effectué en {{ $year }}</small>
          @elseif(!$user->cotisation_state)
          <span class="badge badge-light">Cotisation {{ $year }} non enregistrée</span>
          @else
          <span class="badge badge-success">Cotisation {{ $year }} enregistrée</span>
          @endif
        </td>
        <td>
          @if(!$user->cotisation_state)
            @if($user->cotisation_forced)
              @php $totalInvoiceCotisation += App\Models\parametre::getValue('Club - Cotisation', 50); @endphp
            @endif
            <div class="form-check">
              <input type="checkbox" class="form-check-input" name="addCotisation[{{ $user->id }}]" id="addCotisation-{{ $user->id }}"
              @if($user->cotisation_forced)
              checked
              @endif
              >
              <label class="form-check-label" for="addCotisation-{{ $user->id }}">Enregistrer la cotisation {{ $year }}</label>
            </div>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="row justify-content-center">
    <div class="col-md-8 text-center">
      <h4>{{ $totalInvoiceCotisation }} € a facturer - (Cotisation : {{ App\Models\parametre::getValue('Club - Cotisation', 50) }} €)</h4>
    </div>
  </div>
  <div class="row justify-content-center">
    <div class="col-md-8">
      <button type="submit" class="btn btn-primary btn-block">Enregistrer</button>
    </div>
  </div>
</form>