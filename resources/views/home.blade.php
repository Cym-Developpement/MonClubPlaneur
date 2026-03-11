@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
          {!! $alertsList !!}
            <div class="card">
                <div class="card-header">Mon Compte Pilote
                  
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    {{-- OLD: table statique avec toutes les années chargées --}}
                    {{-- <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Description</th>
                            <th scope="col">Montant</th>
                            <th scope="col">Solde</th>
                          </tr>
                        </thead>
                        <tbody>
                          @php
                            $temporySolde = 0;
                            $currentYear = 0;
                          @endphp
                          @foreach ($transactions as $transaction)
                              @if($currentYear !== $transaction['year'] && $transaction['year'] !== date('Y'))
                              <tr class="table-active" >
                                <th><button class="btn btn-default btn-sm" onclick="$('.{{ $transaction['year'] }}').toggle();">Afficher/Masquer {{ $transaction['year'] }}</button></th>
                                <td></td>
                                <td></td>
                                <td></td>
                              </tr>
                              @endif
                              <tr class="@if($transaction['valid'] == 0) table-warning @endif {{ $transaction['year'] }}"
                              @if($transaction['year'] !== date('Y'))
                                style="display: none;"
                              @endif
                              >
                                <th scope="row">{{ $transaction['time'] }}</th>
                                <td style="font-weight: bold;">{{ $transaction['name'] }}
                                  @if($transaction['valid'] == 0)
                                  <br><span class="badge bg-danger">En attente de validation.</span>
                                    @php
                                      $temporySolde = 1;
                                    @endphp
                                  @endif
                                  @if($transaction['observation'] != '')
                                  <br><small style="font-size: 70%;font-weight: normal;"><i>{{ $transaction['observation'] }}</i></small>
                                  @endif
                                </td>
                                <td>{{ $transaction['value'] }}€</td>
                                <td>{{ $transaction['solde'] }}€</td>
                              </tr>
                              @php
                                $currentYear = $transaction['year'];
                              @endphp
                          @endforeach

                          <tr>
                              <td></td>
                              <th>Solde au
                              @php
                                  echo date('d/m/Y');
                              @endphp
                              @if($temporySolde == 1)
                                <br><span class="badge bg-danger">En attente de validation.</span>
                              @endif
                              </th>
                              <td></td>
                              <th
                              @if($solde<0)
                               class="table-danger"
                              @elseif($solde>0 && $temporySolde == 1)
                                class="table-warning"
                              @else
                               class="table-success"
                              @endif>
                              {{ $solde }}€
                              </th>
                          </tr>
                        </tbody>
                      </table>
                    </div> --}}

                    <div class="d-flex justify-content-center mb-3">
                      <div class="btn-group">
                        @can('debug')
                        <a class="btn btn-outline-success btn-sm" href="addFlight">
                          <i class="fas fa-plane me-1"></i>Enregistrer un vol
                        </a>
                        @endcan
                        <a class="btn btn-sm btn-outline-info" href="{{ route('transfer') }}">
                          <i class="fas fa-exchange-alt me-1"></i>Transfert pilote
                        </a>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#remboursementModal">
                          <i class="fas fa-shopping-cart me-1"></i>Achat club
                        </button>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#payModal">
                          <i class="fas fa-wallet me-1"></i>Approvisionner mon compte
                        </button>
                      </div>
                    </div>

                    <x-transaction-table
                        :transactions="$transactions"
                        :availableYears="$availableYears"
                        :solde="$solde"
                        :userId="0"
                    />

                    @if($solde < 0)
                      <div class="alert alert-warning mt-2" role="alert">
                        Le solde de votre compte est négatif. merci d'approvisionner votre compte.
                      </div>
                    @endif

                    @if($invoices->isNotEmpty())
                    <hr>
                    <h6 class="mb-2"><i class="fas fa-file-invoice me-2"></i>Mes factures</h6>
                    <div class="table-responsive">
                      <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                          <tr>
                            <th>Numéro</th>
                            <th>Période</th>
                            <th>Émis le</th>
                            <th class="text-end">Montant</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($invoices as $inv)
                          <tr>
                            <td class="fw-semibold {{ $inv->type === 'avoir' ? 'text-success' : '' }}">
                              {{ $inv->invoiceNumber }}
                            </td>
                            <td class="small text-nowrap">
                              {{ $inv->periodStart > 0 ? date('d/m/Y', $inv->periodStart) : 'Origine' }}
                              → {{ date('d/m/Y', $inv->periodEnd) }}
                            </td>
                            <td class="small text-nowrap">{{ date('d/m/Y', $inv->emittedAt) }}</td>
                            <td class="text-end text-nowrap fw-semibold {{ $inv->totalAmount < 0 ? 'text-danger' : 'text-success' }}">
                              {{ number_format(abs($inv->totalAmount / 100), 2, ',', ' ') }} €
                            </td>
                            <td class="text-end">
                              <a href="{{ route('invoicePdf', $inv->id) }}" target="_blank"
                                 class="btn btn-sm btn-outline-secondary py-0 px-2" title="Télécharger">
                                <i class="fas fa-file-pdf"></i>
                              </a>
                            </td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                    @endif

                </div>
            </div>



            <div class="card" style="margin-top: 30px;">
                <div class="card-header">Journée de Vol</div>

                <div class="card-body">
                  <h3>S'inscrire a une journée de vol:</h3>
                  <div class="row">
                    <div class="mb-3 col-md-4">
                      <label>Date</label>
                      <input type="text" id="datepicker-flightDay" class="form-control">
                    </div>
                    <div class="mb-3 col-md-4">
                      <label>Statut</label>
                      <select class="form-control" id="addFlightDayAttributes">
                        @foreach ($userAttributes as $userAttribute)
                            <option value="{{ $userAttribute->attributeName }}">{{ $userAttribute->attributeName }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="mb-3 col-md-4">
                      <label>Inscription minimum 24H avant</label>
                      <button class="btn btn-success btn-block" onclick="saveFlightDay();">s'enregistrer</button>
                    </div>
                  </div>
                  <input style="margin-bottom: 20px;" type="text"  class="form-control" id="addFlightDayObservation" placeholder="observation">
                  <div class="alert alert-success" role="alert" id="flightDayRegisterOK" style="display: none;"></div>
                  <div class="alert alert-danger" role="alert" id="flightDayRegisterERROR" style="display: none;"></div>
                  <h3 style="margin-top: 30px;">Les journées de vol à venir:</h3>
                  <div id="flightDayBoardContent"></div>
                </div>
            </div>


        </div>
    </div>
</div>

@include('modal.paiement')
@include('modal.remboursement')

@endsection
