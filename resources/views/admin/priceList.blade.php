@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">Tarifs
                  <button class="btn btn-success btn-sm float-end" onclick="displayFlightModal(1);">Simulateur Tarifs</button>
                </div>

                <div class="card-body">
                  <h5>Aéronefs</h5>
                  
                  <div class="mb-3">
                    <button class="btn btn-success" onclick="openAddModal()">
                      <i class="fas fa-plus"></i> Nouvel aéronef
                    </button>
                  </div>
                  
                  <div class="table-responsive">
                    <table class="table table-striped table-sm">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Type</th>
                          <th scope="col">Nom</th>
                          <th scope="col">Immatriculation</th>
                          <th scope="col">Tarif Cellule</th>
                          <th scope="col">Tarif Moteur</th>
                          <th scope="col">Type de Compteur moteur</th>
                          <th scope="col">Tarification Minimum</th>
                          <th scope="col">Actif</th>
                          <th scope="col">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($prices as $price)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $price->type_str }}</td>
                          <td>{{ $price->name ?? '-' }}</td>
                          <td>{{ $price->register }}</td>
                          <td>{{ number_format($price->basePrice, 2).' €/Heure' }}</td>
                          <td>{{ number_format($price->motorPrice, 2).' €/Heure' }}</td>
                          <td>{{ $price->motor_price_type_str }}</td>
                          <td>{{ number_format(($price->minPrice/100), 2).' €' }}</td>
                          <td>
                            <div class="form-check form-switch" style="margin-right: 10px;">
                                <input type="checkbox" class="form-check-input" id="activeAircraft-{{ $price->id }}"
                                @if($price->actif == 1)
                                checked
                                @endif
                                onchange="activeAircraft({{ $price->id }}, this.checked);"
                                >
                                <label class="form-check-label" id="labelActiveAircraft-{{ $price->id }}" for="activeAircraft-{{ $price->id }}">
                                  @if($price->actif == 1)
                                  Actif
                                  @else
                                  Inactif
                                  @endif
                                </label>
                              </div>  
                          </td>
                          <td>
                            <button class="btn btn-primary btn-sm" onclick="openEditModal({{ $price->id }}, '{{ $price->type_str }}', '{{ $price->name ?? '' }}', '{{ $price->register }}', {{ $price->basePrice }}, {{ $price->motorPrice }}, {{ $price->motorPriceType }}, {{ $price->minPrice }}, {{ $price->actif }})">Modifier</button>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  <h5>Moyens de mise en l'air</h5>
                  
                  <div class="mb-3">
                    <button class="btn btn-success" onclick="openAddStartPriceModal()">
                      <i class="fas fa-plus"></i> Nouveau moyen de mise en l'air
                    </button>
                  </div>
                  
                  <div class="table-responsive">
                    <table class="table table-striped table-sm">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Nom</th>
                          <th scope="col">Prix (€)</th>
                          <th scope="col">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($startPrices as $startPrice)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $startPrice->name }}</td>
                          <td>{{ number_format(($startPrice->basePrice/100), 2).' €' }}</td>
                          <td>
                            <button class="btn btn-primary btn-sm" onclick="openEditStartPriceModal({{ $startPrice->id }}, '{{ $startPrice->name }}', {{ $startPrice->basePrice }})">Modifier</button>
                          </td>
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

<!-- Modal de modification des tarifs -->
<div class="modal fade" id="editPriceModal" tabindex="-1" role="dialog" aria-labelledby="editPriceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPriceModalLabel">Modifier les tarifs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editPriceForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <input type="hidden" id="edit_aircraft_id" name="aircraft_id">
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_type">Type d'aéronef</label>
                <input type="text" class="form-control" id="edit_type" name="type" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_name">Nom</label>
                <input type="text" class="form-control" id="edit_name" name="name" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_register">Immatriculation</label>
                <input type="text" class="form-control" id="edit_register" name="register" readonly>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_base_price">Tarif Cellule (€/Heure)</label>
                <input type="number" step="0.01" class="form-control" id="edit_base_price" name="base_price" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_motor_price">Tarif Moteur (€/Heure)</label>
                <input type="number" step="0.01" class="form-control" id="edit_motor_price" name="motor_price" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_motor_price_type">Type de Compteur moteur</label>
                <select class="form-control" id="edit_motor_price_type" name="motor_price_type" required>
                  <option value="centieme">Centième</option>
                  <option value="minutes">Minutes</option>
                  <option value="aucun">Aucun</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_min_price">Tarification Minimum (€)</label>
                <input type="number" step="0.01" class="form-control" id="edit_min_price" name="min_price" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="edit_actif" name="actif">
                  <label class="form-check-label" for="edit_actif">Actif</label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal d'ajout d'un nouvel aéronef -->
<div class="modal fade" id="addAircraftModal" tabindex="-1" role="dialog" aria-labelledby="addAircraftModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addAircraftModalLabel">Nouvel aéronef</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addAircraftForm" method="POST" action="/admin/aircraft/create">
        @csrf
        <div class="modal-body">
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add_type">Type d'aéronef</label>
                <select class="form-control" id="add_type" name="type" required>
                  <option value="">Sélectionner un type</option>
                  <option value="1">Avion/Motoplaneur</option>
                  <option value="2">Planeur</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add_name">Nom</label>
                <input type="text" class="form-control" id="add_name" name="name" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add_register">Immatriculation</label>
                <input type="text" class="form-control" id="add_register" name="register" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add_base_price">Tarif Cellule (€/Heure)</label>
                <input type="number" step="0.01" class="form-control" id="add_base_price" name="base_price" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add_motor_price">Tarif Moteur (€/Heure)</label>
                <input type="number" step="0.01" class="form-control" id="add_motor_price" name="motor_price" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add_motor_price_type">Type de Compteur moteur</label>
                <select class="form-control" id="add_motor_price_type" name="motor_price_type" required>
                  <option value="centieme">Centième</option>
                  <option value="minutes">Minutes</option>
                  <option value="aucun">Aucun</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add_min_price">Tarification Minimum (€)</label>
                <input type="number" step="0.01" class="form-control" id="add_min_price" name="min_price" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="add_actif" name="actif" checked>
                  <label class="form-check-label" for="add_actif">Actif</label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Créer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal de modification des moyens de mise en l'air -->
<div class="modal fade" id="editStartPriceModal" tabindex="-1" role="dialog" aria-labelledby="editStartPriceModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editStartPriceModalLabel">Modifier le moyen de mise en l'air</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editStartPriceForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <input type="hidden" id="edit_start_price_id" name="start_price_id">
          
          <div class="mb-3">
            <label for="edit_start_price_name">Nom</label>
            <input type="text" class="form-control" id="edit_start_price_name" name="name" required>
          </div>
          
          <div class="mb-3">
            <label for="edit_start_price_base_price">Prix (€)</label>
            <input type="number" step="0.01" class="form-control" id="edit_start_price_base_price" name="base_price" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal d'ajout d'un nouveau moyen de mise en l'air -->
<div class="modal fade" id="addStartPriceModal" tabindex="-1" role="dialog" aria-labelledby="addStartPriceModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addStartPriceModalLabel">Nouveau moyen de mise en l'air</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addStartPriceForm" method="POST" action="/admin/start-price/create">
        @csrf
        <div class="modal-body">
          
          <div class="mb-3">
            <label for="add_start_price_name">Nom</label>
            <input type="text" class="form-control" id="add_start_price_name" name="name" required>
          </div>
          
          <div class="mb-3">
            <label for="add_start_price_base_price">Prix (€)</label>
            <input type="number" step="0.01" class="form-control" id="add_start_price_base_price" name="base_price" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Créer</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('flights.flightModal')

<script type="text/javascript">
  function displayFlightModal(simulation)
  {
    $('#adminAddFlightsTakeOff').val('{{ date('d/m/Y H:i') }}')
    $('.notSimulation').fadeOut();
    $('#adminAddFlightSimulation').val(1);
    $('#flightModal').modal('show');
  }
  
  function activeAircraft(id, state)
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

    $.get( "/aircraft/state", { aircraft: id, state: state } )
      .done(function( data ) {
        $('#labelActiveAircraft-'+id).html(stateMessage);
    });
  }
  
  function openEditModal(id, type, name, register, basePrice, motorPrice, motorPriceType, minPrice, actif) {
    // Remplir les champs du modal avec les données de la ligne
    $('#edit_aircraft_id').val(id);
    $('#edit_type').val(type);
    $('#edit_name').val(name); // Add this line to fill the new name field
    $('#edit_register').val(register);
    $('#edit_base_price').val(basePrice);
    $('#edit_motor_price').val(motorPrice);
    
    // Gérer les options du select selon les valeurs du modèle
    let motorPriceTypeValue = '';
    switch(motorPriceType) {
      case 1:
        motorPriceTypeValue = 'centieme';
        break;
      case 2:
        motorPriceTypeValue = 'minutes';
        break;
      default:
        motorPriceTypeValue = 'aucun';
        break;
    }
    $('#edit_motor_price_type').val(motorPriceTypeValue);
    
    $('#edit_min_price').val(minPrice / 100); // Convertir de centimes en euros
    $('#edit_actif').prop('checked', actif == 1);
    
    // Définir l'URL de soumission du formulaire
    $('#editPriceForm').attr('action', '/admin/aircraft/' + id + '/update-price');
    
    // Ouvrir le modal
    $('#editPriceModal').modal('show');
  }
  
  function openAddModal() {
    // Réinitialiser le formulaire
    $('#addAircraftForm')[0].reset();
    $('#add_actif').prop('checked', true);
    
    // Ouvrir le modal
    $('#addAircraftModal').modal('show');
  }
  
  function openEditStartPriceModal(id, name, basePrice) {
    $('#edit_start_price_id').val(id);
    $('#edit_start_price_name').val(name);
    $('#edit_start_price_base_price').val(basePrice / 100);
    
    // Définir l'URL de soumission du formulaire
    $('#editStartPriceForm').attr('action', '/admin/start-price/' + id + '/update');
    
    $('#editStartPriceModal').modal('show');
  }

  function openAddStartPriceModal() {
    $('#addStartPriceForm')[0].reset();
    $('#addStartPriceModal').modal('show');
  }
  
  // Gestion de la soumission du formulaire
  $('#editPriceForm').on('submit', function(e) {
    // Laisser le formulaire se soumettre normalement
    // Le contrôleur redirigera vers /tarifs
  });
</script>

@endsection
