@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">Import Gesasso
                </div>

                <div class="card-body">
                  @empty($data)
                  <div class="row justify-content-center">
                    <div class="col-md-10">
                      <form enctype="multipart/form-data" action="/importGesasso" method="post">
                        @csrf
                        <div class="form-group">
                          <label for="plancheGesasso">Fichier Gesasso</label>
                          <div class="custom-file">
                            <input type="file" class="custom-file-input" accept=".csv" name="planche" id="plancheGesasso" required>
                            <label class="custom-file-label" id="plancheGesassoLabel" for="plancheGesasso" data-browse="Parcourir">Choisir un fichier</label>
                          </div>
                          <small  class="form-text text-muted">Fichier CSV d'export Gesasso.</small>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">Importer</button>
                      </form>
                    </div>
                  </div>
                  @endempty
                  @isset($data)
                  <form action="/saveDataGesasso" method="post">
                    @csrf

                    <table class="table table-sm">
                      <thead>
                        <tr>
                          <th scope="col">
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox"  id="defaultCheck-all" onchange="$('.importGesassoFlight').prop('checked', true);">
                              <label class="form-check-label" for="defaultCheck-all">Importer</label>
                            </div>
                          </th>
                          <th scope="col">Date</th>
                          <th scope="col">Début</th>
                          <th scope="col">Fin</th>
                          <th scope="col">Appareil</th>
                          <th scope="col">Pilote 1</th>
                          <th scope="col">Pilote 2</th>
                          <th scope="col">Remorqueur</th>
                          <th scope="col">Ecole</th>
                          <th scope="col">Centièmes Moteur</th>
                          <th scope="col">Lancement</th>
                          <th scope="col">A facturer</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php $totalMin = 0; $i = 0; @endphp
                        @foreach($data as $elem)
                        @php $styleClass = ''; @endphp
                        @if(!$loop->first && count($elem) >= 28)
                          <tr
                          @if(in_array($loop->index, $existList))
                          style="display: none;"
                          @endif
                          >
                            <th scope="row">
                              @if(!in_array($loop->index, $existList) && $elem[30] !== -1 && $elem[29] !== -1)
                              @php array_push($elem, $i); $i++; @endphp

                              <div class="form-check">
                                <input class="form-check-input importGesassoFlight" type="checkbox" id="defaultCheck-{{ $loop->iteration }}" name="import[]" value="{{ json_encode($elem) }}">
                                <label class="form-check-label" for="defaultCheck-{{ $loop->iteration }}"></label>
                              </div>
                              @else
                                @php $styleClass = 'text-danger'; @endphp

                                @if(in_array($loop->index, $existList))
                                  @php $styleClass .= ' existFlight'; @endphp
                                @endif

                                @if($elem[30] == -1)
                                  @php $styleClass .= ' unknowAircraft'; @endphp
                                  <span class="badge rounded-pill bg-danger">Aéronef Inconnu</span>
                                @endif

                                @if($elem[29] == -1)
                                  @php $styleClass .= ' unknowUser'; @endphp
                                  <span class="badge rounded-pill bg-danger">Pilote Inconnu</span>
                                @endif

                              @endif
                            </th>
                            <td class="{{ $styleClass }}">{{ $elem[0] }}</td>
                            <td class="{{ $styleClass }}">{{ $elem[8] }}</td>
                            <td class="{{ $styleClass }}">{{ $elem[9] }}</td>
                            <td class="{{ $styleClass }}">{{ $elem[1] }}</td>
                            <td class="{{ $styleClass }}">{{ $elem[3] }}</td>
                            <td class="{{ $styleClass }}">{{ $elem[5] }}</td>
                            <td class="{{ $styleClass }}">{{ $elem[17] }}</td>
                            <td class="{{ $styleClass }}">{{ $elem[7] }}</td>
                            <td class="{{ $styleClass }}">{{ $elem[15] }}</td>
                            <td>
                              @if(!in_array($loop->index, $existList) && $elem[30] !== -1 && $elem[29] !== -1)
                              {{ $elem[13] }} X 
                              <select class="form-select form-select-sm" name="startType[]" required>
                                @foreach(App\Models\sailplaneStartPrice::all() as $start)
                                  <option value="{{ $start->id }}"
                                    @if(intval($elem[15]) > 15 && $start->byMinutes == 1)
                                    selected
                                    @endif
                                  >{{ $start->name }} ({{ ($start->byMinutes == 1 ? ($start->basePrice * \App\H::centiToMinutes(intval($elem[15])) / 100) : ($start->basePrice/100)) }} €)
                                  </option>
                                @endforeach
                              </select>
                              @endif
                            </td>
                            <td>
                              @if(!in_array($loop->index, $existList) && $elem[30] !== -1 && $elem[29] !== -1)
                              
                              <select class="form-select form-select-sm" name="userPayId[]" required>
                                <option>Choisir un utilisateur</option>
                                @foreach(App\Models\User::where('state', 1)->get() as $user)
                                  <option value="{{ $user->id }}"
                                    @if($elem[29] == $user->id)
                                    selected
                                    @endif
                                  >{{ $user->name }}
                                  </option>
                                @endforeach
                              </select>
                              @endif
                            </td>
                          </tr>
                          @if($elem[28] !== '')
                          <tr
                          @if(in_array($loop->index, $existList))
                          style="display: none;"
                          @endif
                          >
                            <td colspan="10" style="text-align: center;"><i><b class="text-danger"><i class="fas fa-arrow-up"></i><i class="fas fa-arrow-up"></i>&nbsp;&nbsp;{{ $elem[28] }}&nbsp;&nbsp;<i class="fas fa-arrow-up"></i><i class="fas fa-arrow-up"></i></b></i></td>
                          </tr>
                          @endif
                        @endif
                        @endforeach
                        <tr>
                            <th scope="row">
                              TOTAL
                            </th>
                            <td></td>
                            <td scope="row" colspan="2" style="text-align: center;">{{ $totalStr }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                          </tr>
                      </tbody>
                    </table>
                    <div class="row justify-content-center">
                      <div class="col-6">
                        <button class="btn btn-primary btn-sm btn-block">Enregistrer</button>
                      </div>
                    </div>
                  </form>
                  @endisset
                  @isset($resultImport)
                    <hr class="mt-3">
                    @foreach($resultImport as $flightImport)
                      @include('admin.gesasso.imported')
                    @endforeach
                  @endisset
                </div>
            </div>
        </div>
    </div>
</div>



@endsection
