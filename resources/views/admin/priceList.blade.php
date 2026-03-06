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
                          <th scope="col">Page publique</th>
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
                            @if($price->public)
                              <span class="badge text-bg-success"><i class="fas fa-eye"></i> Oui</span>
                            @else
                              <span class="badge text-bg-secondary"><i class="fas fa-eye-slash"></i> Non</span>
                            @endif
                          </td>
                          <td>
                            <button class="btn btn-primary btn-sm" onclick="openEditModal({{ $price->id }}, '{{ $price->type_str }}', '{{ $price->name ?? '' }}', '{{ $price->register }}', {{ $price->basePrice }}, {{ $price->motorPrice }}, {{ $price->motorPriceType }}, {{ $price->minPrice }}, {{ $price->actif }}, {{ $price->public }})">Modifier</button>
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
                          <th scope="col">Page publique</th>
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
                            @if($startPrice->public)
                              <span class="badge text-bg-success"><i class="fas fa-eye"></i> Oui</span>
                            @else
                              <span class="badge text-bg-secondary"><i class="fas fa-eye-slash"></i> Non</span>
                            @endif
                          </td>
                          <td>
                            <button class="btn btn-primary btn-sm" onclick="openEditStartPriceModal({{ $startPrice->id }}, '{{ $startPrice->name }}', {{ $startPrice->basePrice }}, {{ $startPrice->public ?? 1 }})">Modifier</button>
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
            <div class="col-md-6">
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="edit_public" name="public">
                  <label class="form-check-label" for="edit_public">Visible sur la page publique des tarifs</label>
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

          <div class="mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="edit_start_price_public" name="public">
              <label class="form-check-label" for="edit_start_price_public">Visible sur la page publique des tarifs</label>
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

          <div class="mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="add_start_price_public" name="public" checked>
              <label class="form-check-label" for="add_start_price_public">Visible sur la page publique des tarifs</label>
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

@can('admin:vi')
                  <hr>
                  <h5>Vols d'initiation</h5>

                  <div class="mb-3">
                    <button class="btn btn-success" onclick="openAddViTypeModal()">
                      <i class="fas fa-plus"></i> Nouveau type VI
                    </button>
                  </div>

                  <div class="table-responsive">
                    <table class="table table-striped table-sm">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Nom</th>
                          <th>Prix</th>
                          <th>Page publique</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($viTypes as $vt)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ trim(explode('-', $vt->nom, 2)[1] ?? $vt->nom) }}</td>
                          <td>{{ number_format($vt->value / 100, 2) }} €</td>
                          <td>
                            @if($vt->public)
                              <span class="badge text-bg-success"><i class="fas fa-eye"></i> Oui</span>
                            @else
                              <span class="badge text-bg-secondary"><i class="fas fa-eye-slash"></i> Non</span>
                            @endif
                          </td>
                          <td>
                            <button class="btn btn-primary btn-sm"
                                    onclick="openEditViTypeModal({{ $vt->id }}, '{{ addslashes(trim(explode('-', $vt->nom, 2)[1] ?? $vt->nom)) }}', {{ $vt->value }}, {{ $vt->public ?? 0 }})">
                              Modifier
                            </button>
                            <form action="{{ route('admin.vi.type.destroy', $vt->id) }}" method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer ce type VI ?')">
                              @csrf @method('DELETE')
                              <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                              </button>
                            </form>
                          </td>
                        </tr>
                        @empty
                        <tr>
                          <td colspan="5" class="text-muted text-center">Aucun type configuré.</td>
                        </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
@endcan

<!-- Modal ajout type VI -->
<div class="modal fade" id="addViTypeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nouveau type de vol d'initiation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('admin.vi.type.store') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nom du type</label>
            <input type="text" name="label" class="form-control" placeholder="Ex : Baptême de l'air" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Prix (€)</label>
            <input type="number" name="prix" class="form-control" step="0.01" min="0" placeholder="50.00" required>
          </div>
          <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="add_vi_public" name="public" value="1" checked>
            <label class="form-check-label" for="add_vi_public">Visible sur la page publique des tarifs</label>
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

<!-- Modal modification type VI -->
<div class="modal fade" id="editViTypeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier le type VI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editViTypeForm" method="POST" action="">
        @csrf @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nom du type</label>
            <input type="text" name="label" id="edit_vi_label" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Prix (€)</label>
            <input type="number" name="prix" id="edit_vi_prix" class="form-control" step="0.01" min="0" required>
          </div>
          <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="edit_vi_public" name="public" value="1">
            <label class="form-check-label" for="edit_vi_public">Visible sur la page publique des tarifs</label>
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
  
  function openEditModal(id, type, name, register, basePrice, motorPrice, motorPriceType, minPrice, actif, publicPage) {
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
    $('#edit_public').prop('checked', publicPage == 1);

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
  
  function openEditStartPriceModal(id, name, basePrice, publicPage) {
    $('#edit_start_price_id').val(id);
    $('#edit_start_price_name').val(name);
    $('#edit_start_price_base_price').val(basePrice / 100);
    $('#edit_start_price_public').prop('checked', publicPage == 1);

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

  function openAddViTypeModal() {
    $('#addViTypeModal').modal('show');
  }

  function openEditViTypeModal(id, label, prixCts, publicPage) {
    $('#edit_vi_label').val(label);
    $('#edit_vi_prix').val((prixCts / 100).toFixed(2));
    $('#edit_vi_public').prop('checked', publicPage == 1);
    $('#editViTypeForm').attr('action', '/admin/vi-types/' + id);
    $('#editViTypeModal').modal('show');
  }
</script>

@endsection
