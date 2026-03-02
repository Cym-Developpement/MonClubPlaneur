@props([
    'transactions'   => [],
    'availableYears' => [],
    'solde'          => null,
    'userId'         => 0,
    'striped'        => false,
])

@php
    $hasPending = collect($transactions)->contains(fn($t) => $t['valid'] == 0);
@endphp

<div class="table-responsive">
  <table class="table {{ $striped ? 'table-striped' : '' }}">
    <thead>
      <tr>
        <th scope="col">Date</th>
        <th scope="col">Description</th>
        <th scope="col">Montant</th>
        <th scope="col">Solde</th>
        @can('admin:transactions')
        <th scope="col"></th>
        @endcan
      </tr>
    </thead>
    <tbody>

      {{-- Ligne de solde final (home uniquement, quand $solde est fourni) --}}
      @if($solde !== null)
      <tr>
        <td></td>
        <th>Solde au {{ date('d/m/Y') }}
          @if($hasPending)
          <br><span class="badge bg-danger">En attente de validation.</span>
          @endif
        </th>
        <td></td>
        <th @if($solde < 0)
              class="table-danger"
            @elseif($solde > 0 && $hasPending)
              class="table-warning"
            @else
              class="table-success"
            @endif>
          {{ $solde }}€
        </th>
        @can('admin:transactions')
        <td></td>
        @endcan
      </tr>
      @endif

      {{-- Transactions de l'année courante --}}
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
          @if($transaction['valid'] == 0 && $solde !== null)
          <br><span class="badge bg-danger">En attente de validation.</span>
          @endif
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

      {{-- Années passées (chargement différé au clic) --}}
      @foreach ($availableYears as $ay)
      <tr class="table-active" id="year-sep-{{ $ay['year'] }}"
          data-year="{{ $ay['year'] }}" data-user="{{ $userId }}" data-loaded="0">
        <th>
          <button class="btn btn-default btn-sm"
                  onclick="toggleYear({{ $ay['year'] }}, {{ $userId }})">
            Afficher/Masquer {{ $ay['year'] }}
          </button>
        </th>
        <td></td>
        <td></td>
        <td class="text-muted fst-italic">Solde au 31/12 : {{ $ay['solde'] }}€</td>
        @can('admin:transactions')
        <td></td>
        @endcan
      </tr>
      @endforeach

    </tbody>
  </table>
</div>
