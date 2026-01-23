@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">Liste des vols sans instructeur</div>

                <div class="card-body">

                  <div class="table-responsive">
                    <table class="table table-striped table-sm">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Appareil</th>
                          <th scope="col">Pilote</th>
                          <th scope="col">Décollage</th>
                          <th scope="col">Atterissage</th>
                          <th scope="col">Nombre d'atterissage</th>
                          <th scope="col">Durée</th>
                          <th scope="col">Instruteur</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($flights as $flight)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $flight['aircraft'] }}</td>
                          <td>{{ $flight['pilot'] }}</td>
                          <td>{{ $flight['startDate'] }}</td>
                          <td>{{ $flight['endDate'] }}</td>
                          <td>{{ $flight['nbLanding'] }}</td>
                          <td>{{ $flight['flighTime'] }}</td>
                          <td>
                            
                            <a href="/addInstructeur-{{ $flight['id'] }}">Ajouter</a>
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
@endsection
