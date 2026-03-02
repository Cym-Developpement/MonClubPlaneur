@props([
    'transactions'   => [],
    'availableYears' => [],
    'solde'          => false,
    'userId'         => 0,
    'striped'        => false,
])

@php
    $hasPending = collect($transactions)->contains(fn($t) => $t['valid'] == 0);
@endphp

<div class="table-responsive">
  <table class="table table-hover align-middle {{ $striped ? 'table-striped' : '' }}">
    <thead class="table-light">
      <tr>
        <th scope="col">Date</th>
        <th scope="col">Description</th>
        <th scope="col" class="text-end">Montant</th>
        <th scope="col" class="text-end">Solde</th>
        @can('admin:transactions')
        <th scope="col"></th>
        @endcan
      </tr>
    </thead>
    <tbody>

      {{-- Ligne de solde final (home uniquement, quand $solde est fourni) --}}
      @if($solde !== false)
      <tr class="fw-bold border-top border-2">
        <td class="text-muted">{{ date('d/m/Y') }}</td>
        <td>Solde actuel
          @if($hasPending)
          <br><span class="badge bg-danger">En attente de validation.</span>
          @endif
        </td>
        <td></td>
        <td class="text-end @if($solde < 0) table-danger @elseif($solde > 0 && $hasPending) table-warning @else table-success @endif">
          {{ $solde }}€
        </td>
        @can('admin:transactions')
        <td></td>
        @endcan
      </tr>
      @endif

      {{-- Transactions de l'année courante --}}
      @foreach ($transactions as $transaction)
      <tr class="@if($transaction['valid'] == 0) table-warning @endif year-{{ $transaction['year'] }}">
        <td class="text-muted small">
          @can('admin:transactions')
          <div id="currentTrDateBlock-{{ $transaction['id'] }}">
            <button class="btn btn-link p-0 text-muted small"
                    style="text-decoration: none;"
                    onclick="displayNewTrDate({{ $transaction['id'] }})">
              {{ $transaction['time'] }}
            </button>
          </div>
          <div id="newTrDateBlock-{{ $transaction['id'] }}" style="display: none;">
            <div class="input-group input-group-sm">
              <input type="text" value="{{ $transaction['time'] }}"
                     class="form-control form-control-sm newTrDateBlock-datePicker"
                     id="newTrDateInput-{{ $transaction['id'] }}">
              <button class="btn btn-success btn-sm" type="button"
                      onclick="validNewTrDate({{ $transaction['id'] }});">
                <i data-feather="check" style="width: 14px; height: 14px;"></i>
              </button>
            </div>
          </div>
          @else
          {{ $transaction['time'] }}
          @endcan
        </td>
        <td class="fw-semibold">{{ $transaction['name'] }}
          @if($transaction['valid'] == 0 && $solde !== false)
          <br><span class="badge bg-danger">En attente de validation.</span>
          @endif
          @if($transaction['observation'] != '')
          <br><small class="text-muted fw-normal fst-italic">{{ $transaction['observation'] }}</small>
          @endif
        </td>
        <td class="text-end">{{ $transaction['value'] }}€</td>
        <td class="text-end @if($transaction['solde'] < 0) table-danger @endif">{{ $transaction['solde'] }}€</td>
        @can('admin:transactions')
        <td class="text-center">
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
      <tr class="table-secondary" id="year-sep-{{ $ay['year'] }}"
          style="cursor: pointer;"
          data-year="{{ $ay['year'] }}" data-user="{{ $userId }}" data-loaded="0"
          onclick="toggleYear({{ $ay['year'] }}, {{ $userId }})">
        <td colspan="2" class="fw-semibold">
          <i class="fas fa-chevron-right me-2 small year-chevron"></i>{{ $ay['year'] }}
        </td>
        <td></td>
        <td class="text-end text-muted fst-italic small">Solde au 31/12 : {{ $ay['solde'] }}€</td>
        @can('admin:transactions')
        <td></td>
        @endcan
      </tr>
      @endforeach

    </tbody>
  </table>
</div>
