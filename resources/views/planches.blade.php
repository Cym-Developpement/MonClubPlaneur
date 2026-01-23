@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">Planche de vol</div>

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
                          <th scope="col">Pilote</th>
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
                          <td>{{ $flight['pilot'] }}</td>
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
        </div>
    </div>
</div>
@endsection
