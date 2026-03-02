{{-- Partial retourné par AJAX pour le chargement différé d'une année --}}
@foreach ($transactions as $transaction)
<tr class="@if($transaction['valid'] == 0) table-warning @endif year-{{ $transaction['year'] }}">
  <th scope="row">
    @can('admin:transactions')
    <div id="currentTrDateBlock-{{ $transaction['id'] }}">
      <button class="btn btn-link"
              style="font-weight: bold; text-decoration: none; color: black;"
              onclick="displayNewTrDate({{ $transaction['id'] }})">
        {{ $transaction['time'] }}
      </button>
    </div>
    <div id="newTrDateBlock-{{ $transaction['id'] }}" style="display: none;">
      <div class="input-group mb-3">
        <input type="text" value="{{ $transaction['time'] }}"
               class="form-control form-control-sm newTrDateBlock-datePicker"
               id="newTrDateInput-{{ $transaction['id'] }}">
        <button class="btn btn-success btn-sm" type="button"
                onclick="validNewTrDate({{ $transaction['id'] }});">
          <i data-feather="check" style="width: 16px; height: 16px;"></i>
        </button>
      </div>
    </div>
    @else
    {{ $transaction['time'] }}
    @endcan
  </th>
  <td style="font-weight: bold;">{{ $transaction['name'] }}
    @if($transaction['observation'] != '')
    <br><small style="font-size: 70%; font-weight: normal;"><i>{{ $transaction['observation'] }}</i></small>
    @endif
  </td>
  <td>{{ $transaction['value'] }}€</td>
  <td @if($transaction['solde'] < 0) class="table-danger" @endif>{{ $transaction['solde'] }}€</td>
  @can('admin:transactions')
  <td>
    <form method="POST" action="{{ route('deleteTransactionPost') }}"
          onsubmit="return confirm('Supprimer la transaction {{ $transaction['id'] }} ?');">
      {{ csrf_field() }}
      <input type="hidden" name="id" value="{{ $transaction['id'] }}">
      <button type="submit" class="btn btn-link text-danger p-0" title="Supprimer">
        <i class="fas fa-trash"></i>
      </button>
    </form>
  </td>
  @endcan
</tr>
@endforeach
