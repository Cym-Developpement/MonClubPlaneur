@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header"><a class="btn btn-sm btn-link float-start" href="/planchesOgn/{{ $previous }}"><</a>&nbsp;Import OGN  {{ $date }}<a class="btn btn-sm btn-link float-end" href="/planchesOgn/{{ $next }}">></a> @isset($flights) <a href="/planchesOgn/ignore/{{ $flights->id }}" class="btn btn-sm btn-danger float-end" onclick="return confirm('Etes vous sur de vouloir ignorer cette planche de vol ?');">Ignorer cette planche</a> @endisset 
                </div>

                <div class="card-body">
                  {{-- @dd($flights->data['flights']) --}}
                  @isset($flights)
                  <form method="post">
                    @csrf
                    <table class="table table-sm">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col" style="text-align: center;">Import</th>
                          <th scope="col">Aeronef</th>
                          <th scope="col">Départ</th>
                          <th scope="col">Arrivé</th>
                          <th scope="col">Hauteur (altitude)</th>
                          <th scope="col">Type</th>
                          <th scope="col">PIC</th>
                          <th scope="col">Instructeur</th>
                          <th scope="col">Facturable</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($flights->flights as $flight)
                        <tr>
                          <th>{{ $loop->iteration }}
                            <input type="hidden" name="flightId[]" value="{{ $loop->iteration }}" >
                            
                          </th>
                          <td style="text-align: center;">
                            @isset($flight['aircraft'])
                            <div class="form-check">
                              <input type="checkbox" class="form-check-input" name="flights[{{ $loop->iteration }}][import]" id="importFlightOgn-{{ $loop->iteration }}">
                              <label class="form-check-label" for="importFlightOgn-{{ $loop->iteration }}"> </label>
                            </div>
                            @endisset
                          </td>
                          <td>
                            @isset($flight['aircraft'])
                            {{ $flight['aircraft']->name }}
                            @endisset
                            @empty($flight['aircraft'])
                            {{ $flight['device']['address'] }}
                            @endempty
                          </td>
                          <td>
                            {{ $flight['flight']['start'] }}
                            <input type="hidden" name="flights[{{ $loop->iteration }}][start]" value="{{ $flight['flight']['start_tsp'] }}">
                          </td>
                          <td>
                            {{ $flight['flight']['stop'] }}
                            <input type="hidden" name="flights[{{ $loop->iteration }}][stop]" value="{{ $flight['flight']['stop_tsp'] }}">
                          </td>
                          <td>{{ $flight['flight']['max_height'] }} ({{ $flight['flight']['max_alt'] }})</td>
                          <td>
                            @isset($flight['aircraft'])
                            <select class="form-select form-select-sm" name="flights[{{ $loop->iteration }}][flightType]">
                              <option value="0">Privé</option>
                              <option value="1">Instruction</option>
                              <option value="2">Remorqué</option>
                            </select>
                            <select style="display: none;" class="form-select form-select-sm" name="flights[{{ $loop->iteration }}][flightType]">
                              <option value="0">Privé</option>
                              <option value="1">Instruction</option>
                              <option value="2">Remorqué</option>
                            </select>
                            @endisset
                          </td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </form>
                  @endisset
                  @empty($flights)
                  <div class="alert alert-danger">Pas de vol pour cette journée.</div>
                  @endempty
                </div>
            </div>


        </div>
    </div>
</div>





<script type="text/javascript">

  
</script>

@endsection
