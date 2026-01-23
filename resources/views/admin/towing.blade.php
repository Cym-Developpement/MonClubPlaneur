@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">Remorquage</div>

                <div class="card-body">
                  
                  <div class="table-responsive">
                    <table class="table table-striped table-sm">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Planeur</th>
                          <th scope="col">Heure départ</th>
                          <th scope="col">Heure arrivée</th>
                          <th scope="col">Remorqueur</th>
                          <th scope="col">Compteur départ remorqueur</th>
                          <th scope="col">Compteur arrivée remorqueur</th>
                          <th scope="col">Pilote Remorqueur</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($flights as $key => $flight)
                        <tr>
                          <th>{{ $flight->id }}

                            <input type="hidden" name="flightId[]" value="{{ $flight->id }}">
                          </th>
                          <td>{{ $flight->aircraft->register }}</td>
                          <td>{{ $flight->takeOffTime }}</td>
                          <td>{{ $flight->landingTime }}</td>
                          <td>
                            <select class="form-select">
                              @foreach(App\Models\Aircraft::getTowerList() as $remorqueur)
                                <option value="{{ $remorqueur->id }}" data-last="{{ $remorqueur->getLastIndex(date('Y-m-d', $flight->flightTimestamp)) }}" data-next="{{ $remorqueur->getNextIndex(date('Y-m-d', $flight->flightTimestamp)) }}">{{ $remorqueur->register }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td><input onchange="updateControleIndex()" class="form-control startMotor
                            @if($loop->first())
                            firstFlight
                            @endif
                            " type="number" step="0.01" name="motorStartIndex[]"></td>
                          <td><input onchange="updateControleIndex()" class="form-control endMotor
                            @if($loop->last())
                            lastFlight
                            @endif
                            " type="number" step="0.01" name="motorEndIndex[]"></td>
                          <td>
                            <select class="form-select">
                              @foreach(App\Models\User::getUserListByAttr('Remorqueur') as $remorqueur)
                                <option value="{{ $remorqueur->id }}">{{ $remorqueur->name }}</option>
                              @endforeach
                            </select>
                          </td>
                        </tr>
                        @endforeach
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
  function updateControleIndex()
  {
    
  }

  
</script>


@endsection
