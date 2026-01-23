@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">Mon Carnet de vol au CVVT
                  @can('debug')
                  <button type="button" class="btn btn-success float-end" data-bs-toggle="modal" data-bs-target="#addExternalFlight">Enregistrer un vol hors planche</button>
                  @endcan
                </div>

                <div class="card-body">
                  <form method="post">
                    @csrf
                    <div class="input-group">
                      <span class="input-group-text">Dates : </span>
                      <input type="text" value="{{ $dates[0] }}" placeholder="date début" class="form-control planches-datepicker" name="start">
                      <input type="text" value="{{ $dates[1] }}" placeholder="date fin" class="form-control planches-datepicker" name="end">
                      <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Afficher</button>
                    </div>
                  </form>
                  <br>

                  <div class="table-responsive">
                    <table class="table table-striped table-sm">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Appareil</th>
                          <th scope="col">Décollage</th>
                          <th scope="col">Atterissage</th>
                          <th scope="col">Nombre d'atterissage</th>
                          <th scope="col">Durée</th>
                          <th scope="col">Lancement</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($flights as $flight)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $flight['aircraft'] }}</td>
                          <td>{{ $flight['startDate'] }}</td>
                          <td>{{ $flight['endDate'] }}</td>
                          <td>{{ $flight['nbLanding'] }}</td>
                          <td>{{ $flight['flighTime'] }}</td>
                          <td>{{ $flight['startType'] }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
            </div>


            <div class="card" style="margin-top: 30px;">
                <div class="card-header">Mon Carnet de vol Externe</div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-striped table-sm">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Date</th>
                          <th scope="col">Durée</th>
                          <th scope="col">Nombre d'atterissage</th>
                          <th scope="col">Rôle</th>
                          <th scope="col">Type d'aéronef</th>
                          <th scope="col">Moyen de mise en l'air</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($externalFlights as $flight)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $flight['startDate'] }}</td>
                          <td>{{ $flight['flighTime'] }}</td>
                          <td>{{ $flight['nbLanding'] }}</td>
                          <td>{{ $flight['aircraftType'] }}</td>
                          <td>{{ $flight['startType'] }}</td>
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



<!-- Modal -->
<div class="modal fade" id="addExternalFlight" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Enregistrer un vol hors planche ou antérieur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <b>Ce formulaire permet d'enregistrer facilement des vols hors planche<br>ou une expérience antérieur pour être enregistré sur GESSASSO.</b>
        <hr>
        <h2>1. choisir l'année : 
          <select class="form-select form-select-lg mb-3">
            <option selected value="@php
                echo date('Y');
              @endphp">
              @php
                echo date('Y');
              @endphp
            </option>
            <option value="@php
                echo (date('Y')-1);
              @endphp">@php
                echo (date('Y')-1);
              @endphp</option>
            <option value="@php
                echo (date('Y')-2);
              @endphp">@php
                echo (date('Y')-2);
              @endphp</option>
            <option value="@php
                echo (date('Y')-3);
              @endphp">@php
                echo (date('Y')-3);
              @endphp</option>
          </select>
        </h2>
        <hr>
        <h2>2. Remplir le formulaire</h2>
        <i>Vous pouvez saisir directement sans cliquer ni appuyer sur entrée<br>le curseur passe a la case suivante automatiquement.</i>
        <br>
        <div class="input-group">
          <span class="input-group-text">Date</span>
          <span class="input-group-text">Jour : </span>
          <input id="addExternalDay" type="text" aria-label="Jour" class="form-control">
          <span class="input-group-text">Mois : </span>
          <input id="addExternalMonth" type="text" aria-label="Mois" class="form-control">
        </div>
        <br>
        <div class="input-group">
          <span class="input-group-text">Temps de vol</span>
          <span class="input-group-text">Heures : </span>
          <input id="addExternalFlightHour" type="text" aria-label="Jour" class="form-control">
          <span class="input-group-text">Minutes : </span>
          <input id="addExternalFlightMinutes" type="text" aria-label="Mois" class="form-control">
        </div>
        <br>

        <div class="input-group">
          <span class="input-group-text">Atterrissage : </span>
          <input id="addExternalFlightLanding" type="text" aria-label="" value="1" class="form-control">
          <span class="input-group-text">Rôle : </span>
          
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    if (window.location.search.substr(1).indexOf("displayExt") > -1) {
      $('#addExternalFlight').modal('show');
    }


    $('#addExternalFlight').on('shown.bs.modal', function (e) {
      $( "#addExternalDay" ).focus();
    });

    $( "#addExternalDay" ).keyup(function() {
      var day = $( "#addExternalDay" ).val();
      if (day.length == 0) {
        return;
      }
      if (day.length == 2 && day > 0 && day < 32) {
        $( "#addExternalMonth" ).focus();
        $( "#addExternalDay" ).removeClass('is-invalid');
        $( "#addExternalDay" ).addClass('is-valid');
      }

      if (day.length > 2 || day <= 0 || day >= 32) {
        $( "#addExternalDay" ).removeClass('is-valid');
        $( "#addExternalDay" ).addClass('is-invalid');
      }
    });

    $( "#addExternalMonth" ).keyup(function() {
      var month = $( "#addExternalMonth" ).val();
      if (month.length == 0) {
        return;
      }
      if (month.length == 2 && month > 0 && month < 13) {
        $( "#addExternalFlightHour" ).focus();
        $( "#addExternalMonth" ).removeClass('is-invalid');
        $( "#addExternalMonth" ).addClass('is-valid');
      }

      if (month.length > 2 || month <= 0 || month >= 13) {
        $( "#addExternalMonth" ).removeClass('is-valid');
        $( "#addExternalMonth" ).addClass('is-invalid');
      }
    });

    $( "#addExternalFlightHour" ).keyup(function() {
      var hour = $( "#addExternalFlightHour" ).val();
      if (hour.length == 0) {
        return;
      }
      if (hour.length == 2 && hour > 0 && hour < 12) {
        $( "#addExternalFlightMinutes" ).focus();
        $( "#addExternalFlightHour" ).removeClass('is-invalid');
        $( "#addExternalFlightHour" ).addClass('is-valid');
      }

      if (hour.length > 2 || hour <= 0 || hour >= 12) {
        $( "#addExternalFlightHour" ).removeClass('is-valid');
        $( "#addExternalFlightHour" ).addClass('is-invalid');
      }
    });




  });
  
  
</script>

@endsection
