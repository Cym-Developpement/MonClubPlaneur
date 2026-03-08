@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <span>Saisie Transaction</span>
                  @if($selectedUser > 0)
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#adminAddFlight">
                      <i class="fas fa-plane me-1"></i>Enregistrer un vol
                    </button>
                    <a href="/accountExport?user={{ $selectedUser }}" target="_blank" class="btn btn-info">
                      <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </a>
                    @can('admin:super')
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalSolderCompte">
                      <i class="fas fa-balance-scale me-1"></i>Solder le compte
                    </button>
                    @endcan
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-invoice me-1"></i>Facture
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/invoiceExport?user={{ $selectedUser }}&year={{ date('Y') }}">Année {{ date('Y') }}</a></li>
                        <li><a class="dropdown-item" href="/invoiceExport?user={{ $selectedUser }}&year={{ date('Y') - 1 }}">Année {{ date('Y') - 1 }}</a></li>
                        <li><a class="dropdown-item" href="/invoiceExport?user={{ $selectedUser }}&year={{ date('Y') - 2 }}">Année {{ date('Y') - 2 }}</a></li>
                        <li><a class="dropdown-item" href="/invoiceExport?user={{ $selectedUser }}&year={{ date('Y') - 3 }}">Année {{ date('Y') - 3 }}</a></li>
                      </ul>
                    </div>
                  </div>
                  @endif
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form method="GET">
                      <div class="input-group">
                        <select  onchange="$('#userBoard').fadeOut();" class="form-select" id="selectUserInTransaction" name="selectUserInTransaction" aria-label="Liste des utilisateurs">
                          @foreach ($users as $user)
                            <option value="{{ $user->id }}"
                              @if($user->id == $selectedUser)
                              selected
                              @php
                                $currentUserName = $user->name;
                              @endphp
                              @endif
                              >{{ $user->name }}</option>
                          @endforeach
                        </select>
                        <button class="btn btn-outline-secondary" type="submit">Afficher</button>
                      </div>
                    </form>


                    <div id="userBoard">
                      
                    

                      <hr>
                      @if (count($transactions) > 0 || count($availableYears) > 0)
                      {{-- OLD: table statique avec toutes les années chargées --}}
                      {{-- <table class="table table-striped">
                        <thead>
                          <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Description</th>
                            <th scope="col">Montant</th>
                            <th scope="col">Solde</th>
                            <th scope="col"></th>
                          </tr>
                        </thead>
                        <tbody>
                          @php
                            $currentYear = 0;
                          @endphp
                          @foreach ($transactions as $transaction)
                              @if($currentYear !== $transaction['year'] && $transaction['year'] !== date('Y'))
                              <tr>
                                <th><button class="btn btn-default btn-sm" onclick="$('.{{ $transaction['year'] }}').toggle();">Afficher/Masquer {{ $transaction['year'] }}</button></th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                              </tr>
                              @endif
                              <tr class="@if($transaction['valid'] == 0) table-warning @endif {{ $transaction['year'] }}" @if($transaction['year'] !== date('Y'))
                                style="display: none;"
                              @endif>
                                <th scope="row">
                                  <div id="currentTrDateBlock-{{ $transaction['id'] }}">
                                  <button class="btn btn-link" style="font-weight: bold; text-decoration: none; color: black;" onclick="displayNewTrDate({{ $transaction['id'] }})">{{ $transaction['time'] }}</button>
                                  </div>
                                  <div id="newTrDateBlock-{{ $transaction['id'] }}" style="display: none;">
                                    <div class="input-group mb-3">
                                      <input type="text" value="{{ $transaction['time'] }}" class="form-control form-control-sm newTrDateBlock-datePicker" id="newTrDateInput-{{ $transaction['id'] }}">
                                        <button class="btn btn-success btn-sm" type="button" onclick="validNewTrDate({{ $transaction['id'] }});">
                                          <i data-feather="check" style="width: 16px;height: 16px;"></i>
                                        </button>
                                    </div>
                                  </div>
                                </th>
                                <td>{{ $transaction['name'] }}
                                @if($transaction['observation'] != '')
                                  <br><small style="font-size: 70%;"><i>{{ $transaction['observation'] }}</i></small>
                                @endif
                                </td>
                                <td>{{ $transaction['value'] }}€</td>
                                <td
                                @if($transaction['solde']<0)
                                  class="table-danger"
                                @endif
                                 >{{ $transaction['solde'] }}€</td>
                                <td>
                                  <form method="POST" action="{{ route('deleteTransactionPost') }}" onsubmit="return confirm('Supprimer la transaction {{ $transaction['id'] }} ?');">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="id" value="{{ $transaction['id'] }}">
                                    <button type="submit" class="btn btn-link text-danger p-0" title="Supprimer">
                                      <i class="fas fa-trash"></i>
                                    </button>
                                  </form>
                                </td>
                              </tr>
                               @php
                                $currentYear = $transaction['year'];
                              @endphp
                          @endforeach
                        </tbody>
                      </table> --}}

                      <x-transaction-table
                          :transactions="$transactions"
                          :availableYears="$availableYears"
                          :userId="$selectedUser"
                          :striped="true"
                      />

                      <div class="row">
                        <div class="col-md-12" style="text-align: center;">
                          <a href="updateSolde?selectUserInTransaction={{ $selectedUser }}" class="btn btn-warning btn-block">Recalculer le solde</a>
                        </div>
                      </div>
                      @endif


                      @if($selectedUser > 0)
                        <hr>
                        <h3>Saisie Rapide</h3>
                        <br>
                        <form method="POST">
                          {{ csrf_field() }}
                          <div class="input-group">
                            <select class="form-select" id="selectTransactionTypeEnc" name="selectTransactionTypeEnc" aria-label="Type de transaction">
                              <option value="0">Encaissement (+)</option>
                              <option value="1">Vente (-)</option>
                            </select>
                            <select onchange="changeTransactionType();" class="form-select" id="selectTransactionType" name="selectTransactionType" aria-label="Type de transaction">
                              @foreach($transactionType as $type)
                              <option value="{{ $type->name }}" data-type="{{ $type->defaultType }}" 
                                @if($type->defaultType == 1)
                                  data-amount="{{ (App\Models\parametre::getValue('Club - '.$type->name, 0.0)*100) }}"
                                @else
                                  data-amount="0"
                                @endif
                                >{{ $type->name_year }}</option>
                              @endforeach
                            </select>
                            <input type="number" aria-label="Valeur" name="valueTransaction" id="valueTransaction" step="0.01" placeholder="Montant" class="form-control">
                            <button class="btn btn-outline-secondary" type="submit">Ajouter</button>
                          </div>
                        </form>
                        <hr>
                        <h3>Saisie Compléte</h3>
                        <form method="POST">
                          {{ csrf_field() }}
                          <div class="input-group">
                            <input type="text" aria-label="Valeur" name="nameFreeTransaction" id="nameFreeTransaction" placeholder="Intitulé" class="form-control">
                            <input type="number" aria-label="Valeur" name="valueFreeTransaction" id="valueTransaction" step="0.01" placeholder="Montant" class="form-control">
                            <button class="btn btn-outline-secondary" type="submit">Ajouter</button>
                          </div>
                        </form>
                      @endif

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


@isset($selectedUser)
  @if($selectedUser > 0)
    @include('flights.addflight')

    @can('admin:super')
    <div class="modal fade" id="modalSolderCompte" tabindex="-1" aria-labelledby="modalSolderCompteLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="modalSolderCompteLabel">
              <i class="fas fa-balance-scale me-2"></i>Solder le compte
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Cette action va créer une écriture de compensation pour ramener le solde de <strong>{{ $currentUserName }}</strong> à <strong>0 €</strong>.</p>
            <p class="text-danger mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Cette opération est irréversible (sauf suppression manuelle de l'écriture).</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <form method="POST" action="{{ route('solderCompte') }}">
              @csrf
              <input type="hidden" name="userId" value="{{ $selectedUser }}">
              <button type="submit" class="btn btn-danger">
                <i class="fas fa-check me-1"></i>Confirmer la remise à zéro
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
    @endcan

  @endif
@endisset


<script type="text/javascript">
  function changeTransactionType()
  {
    var type = $('#selectTransactionType option:selected').attr('data-type');
    var amount = $('#selectTransactionType option:selected').attr('data-amount');
    $("#valueTransaction").val((amount/100));
    $("#selectTransactionTypeEnc").val(type);
  }
</script>
@endsection

