@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">Liste des Pilotes
                  <a  style="margin-left: 10px;" href="/usersSendAccountNotification" class="btn btn-sm btn-warning float-end">Envoyer l'email de compte débiteur</a>
                  <a href="{{ route('usersExportCsv', request()->only('filter')) }}" class="btn btn-sm btn-success float-end me-1 text-white">
                    <i class="fas fa-file-csv"></i> Export CSV
                  </a>
                  <div class="dropdown float-end me-1">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                      Filtres
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                      <a class="dropdown-item" href="/usersList">Actif uniquement</a>
                      <a class="dropdown-item" href="/usersList?filter=all">Actifs et Inactifs</a>
                      <!--<a class="dropdown-item" href="#">Something else here</a>-->
                    </div>
                  </div>
                </div>

                <div class="card-body">
                  
                  <div class="table-responsive">
                    <table id="usersTable" class="table table-striped">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Nom</th>
                          <th scope="col">FFVP</th>
                          <th scope="col">E-mail</th>
                          <th scope="col">Solde</th>
                          <th scope="col">Attributs</th>
                          <th scope="col">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php $totalAll = 0; @endphp
                        @foreach ($users as $user)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $user->name }}</td>
                          <td>{{ $user->licenceNumber }}</td>
                          <td>{{ $user->email }}</td>
                          <td style="text-align: right;">
                          @if($user->solde < 0)
                            <a href="saisie?selectUserInTransaction={{ $user->id }}" class="badge bg-danger">{{ $user->solde }}€</a>
                          @else
                          <a href="saisie?selectUserInTransaction={{ $user->id }}" class="badge bg-success">{{ $user->solde }}€</a>
                          @endif
                          @php $totalAll += floatval($user->solde); @endphp
                          </td>
                          <td>
                            @foreach($user->userAttributes as $attribute)
                              <span class="badge bg-secondary">{{ $attribute }}</span>
                            @endforeach
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="form-check form-switch" style="margin-right: 10px;">
                                <input type="checkbox" class="form-check-input" id="activeUser-{{ $user->id }}"
                                @if($user->state == 1)
                                checked
                                @endif
                                onchange="activeUser({{ $user->id }}, this.checked);"
                                >
                                <label class="form-check-label" id="labelActiveUser-{{ $user->id }}" for="activeUser-{{ $user->id }}">
                                  @if($user->state == 1)
                                  Actif
                                  @else
                                  Inactif
                                  @endif
                                </label>
                              </div>  
                              <div class="dropdown">
                                <button class="btn btn-sm btn-info dropdown-toggle" style="color: white;" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                  <i class="fas fa-info-circle"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                  <a class="dropdown-item" target="_blank" href="/saisie?selectUserInTransaction={{ $user->id }}">Compte pilote</a>
                                  <a class="dropdown-item" target="_blank" href="/vol?filterID={{ $user->id }}&year={{ date('Y') }}">Carnet de vol {{ date('Y') }}</a>
                                  <a class="dropdown-item" target="_blank" href="/vol?filterID={{ $user->id }}&year={{ (date('Y')-1) }}">Carnet de vol {{ (date('Y')-1) }}</a>
                                  <a class="dropdown-item" target="_blank" href="/vol?filterID={{ $user->id }}&year={{ (date('Y')-2) }}">Carnet de vol {{ (date('Y')-2) }}</a>
                                  <button class="dropdown-item" onclick="getAdminAccess({{ $user->id }})">Accès administrateur temporaire</button>
                                  <a class="dropdown-item" href="/userMod/{{ $user->id }}">Modifier l'utilisateur</a>
                                  <div class="dropdown-divider"></div>
                                  <h6 class="dropdown-header">Info : {{ date('Y') }}</h6>
                                  <h6 class="dropdown-header">HDV :  {{ $user->current_hour_flight }}</h6>
                                  <h6 class="dropdown-header">HDV Facturable :  {{ $user->current_hour_flight_paid }}</h6>
                                  <h6 class="dropdown-header">Jour(s) de vol : {{ $user->current_day_flight }}</h6>
                                  <h6 class="dropdown-header">Jour(s) de vol Facturable : {{ $user->current_day_flight_paid }}</h6>
                                </div>
                              </div>   
                            </div>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td style="text-align: right;">
                            @if($totalAll < 0)
                            <a href="#" class="badge bg-danger">{{ $totalAll }}€</a>
                            @else
                            <a href="#" class="badge bg-success">{{ $totalAll }}€</a>
                            @endif
                          </td>
                          <td></td>
                          <td></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  <hr>
                  <h3>Totaux : </h3>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th scope="col">Type</th>
                          <th scope="col" style="text-align: center;">Valeur</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($totaux as $key => $total)
                        <tr>
                          <td>{{ $key }}</td>
                          <td style="text-align: center;"><span class="badge bg-info text-dark" style="width: 30%;color: white;">{{ $total }}</span></td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
  function activeUser(id, state)
  {
    if (state) {
      var stateMessage = 'Actif';
    } else {
      var stateMessage = 'Inactif';
    }
    //$('#labelActiveUser-'+id).html(stateMessage);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.get( "/user/state", { user: id, state: state } )
      .done(function( data ) {
        $('#labelActiveUser-'+id).html(stateMessage);
    });
  }

  function getAdminAccess(id)
  {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.get( "/adminAccess/"+id )
      .done(function( data ) {
        alert(data);
    });
  }

</script>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>
<script>
  $('#usersTable').DataTable({
    pageLength: 10,
    language: { url: 'https://cdn.datatables.net/plug-ins/2.2.2/i18n/fr-FR.json' },
    columnDefs: [{ orderable: false, targets: [5, 6] }]
  });
</script>
@endpush
