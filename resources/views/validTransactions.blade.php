@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Liste des Transactions a valider</div>
                <div class="card-body">
                  <div class="table-responsive">
                    <h4>Remboursement achat</h4>
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Nom</th>
                          <th scope="col">Montant</th>
                          <th scope="col">Intitulé</th>
                          <th scope="col">Date</th>
                          <th scope="col">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($transactions as $transaction)
                        <tr>
                          <td>{{ $transaction->id }}</td>
                          <td>{{ $transaction->CompleteName }}

                          </td>
                          <td style="text-align: right;">{{ $transaction->value/100 }}€</td>
                          <td>{{ $transaction->name }}
                            @if(!is_null($transaction->observation))
                            <br>{{ $transaction->observation }}
                            @endif
                            @if($transaction->refundId > 0)
                              <br><small class="form-text text-muted">{{ $transaction->refund->intitule }}. <a href="/facture/{{ $transaction->refund->id }}">Télécharger la facture</a></small>
                            @endif
                          </td>
                          <td>{{ $transaction->created_at }}</td>
                          <td>
                             <button class="btn btn-danger btn-sm float-end" style="margin-left: 5px;" onclick="deleteTransactions({{ $transaction->id }});"><i data-feather="trash" style="width: 16px;
  height: 16px;"></i></button>
                            <button class="btn btn-success btn-sm float-end" onclick="validTransactions({{ $transaction->id }});"><i data-feather="check" style="width: 16px;
  height: 16px;"></i></button>

                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                    <h4>Vol saisie pilote</h4>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
