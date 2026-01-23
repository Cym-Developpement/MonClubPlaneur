@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">Liste des vols</div>

                <div class="card-body">
                  <div class="row">
                    <div class="col-md-8">
                      <select class="form-select" id="filterFlightBoard" onchange="selectFilterFlightBoard();">
                        <option value="C"
                        @if($currentFilter == "C")
                          selected
                          @endif
                        >Choisissez un Filtre</option>
                        <option value="0"
                        @if($currentFilter == 0)
                          selected
                          @endif>Tous</option>
                        @foreach($filters as $filter)
                        <option value="{{ $filter[0] }}"
                          @if($currentFilter == $filter[0])
                          selected
                          @endif
                        >{{ $filter[1] }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-2">
                      <select class="form-select" id="filterFlightBoardYear" onchange="selectFilterFlightBoard();">
                        @for ($i = 2018; $i <= intval(date('Y')); $i++)
                            <option value="{{ $i }}" @if($year == $i) selected @endif>{{ $i }}</option>
                        @endfor
                      </select>
                    </div>
                    <div class="col-md-2">
                      <a href="{!!$export['gesasso']!!}" class="btn btn-success">Export GESASSO</a>
                    </div>
                  </div>
                  <hr>

                  @if($currentFilter <> 'C')
                  @php $lastIndexMotor = []; $firstIndexMotor = []; @endphp
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
                          <th scope="col">Centièmes Moteur</th>
                          <th scope="col">Prix</th>
                          <th scope="col" style="width:1%"><input type="checkbox" id="selectAllFlights"></th>
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody>
                        @php $transactionErrors = 0; @endphp
                        @foreach ($flights as $flight)
                        <tr>
                          <td>@isset($flight['id']) {{ $flight['id'] }} @endisset</td>
                          <td>{{ $flight['aircraft'] }}</td>
                          <td>{{ $flight['pilot'] }}</td>
                          <td>{{ $flight['startDate'] }}</td>
                          <td>{{ $flight['endDate'] }}</td>
                          <td>{{ $flight['nbLanding'] }}</td>
                          <td>{{ $flight['flighTime'] }}</td>
                          <td>{{ $flight['startType'] }}</td>
                          <td>
                            {{ $flight['motorTime'] }}
                            @isset($flight['id'])
                              @if($flight['data']->motorStartTime>0)

                              @if(!isset($firstIndexMotor[$flight['aircraft']]))
                              @php $firstIndexMotor[$flight['aircraft']] = $flight['data']->motorStartTime; @endphp
                              @endif

                              <small class="form-text
                              @if(isset($lastIndexMotor[$flight['aircraft']]) && $lastIndexMotor[$flight['aircraft']] !== $flight['data']->motorStartTime)
                              text-danger
                              @else
                              text-muted
                              @endif
                              ">{{ number_format($flight['data']->motorStartTime, 2, ',', '') }} - {{ number_format($flight['data']->motorEndTime, 2, ',', '') }}</small>
                              @php $lastIndexMotor[$flight['aircraft']] = $flight['data']->motorEndTime @endphp
                              @endif
                            @endisset
                          </td>
                          <td style="text-align: right;">
                            {{ $flight['price'] }}
                            @isset($flight['id'])
                            <br>
                            @isset($flight['data']->user_paid)<small class="form-text text-muted">{{ $flight['data']->user_paid->name }}</small>@endisset
                            @if(is_null($flight['data']->user_paid))<small class="form-text text-danger">Erreur de transaction</small>@php $transactionErrors ++; @endphp@endif
                            @endisset
                          </td>
                          <td>
                            @isset($flight['id'])
                            <input type="checkbox" class="js-flight-delete-checkbox" value="{{ $flight['id'] }}" data-delete-url="/{{ Request::path() }}?filterID={{ $currentFilter }}&year={{ $year }}&deleteFlight={{ $flight['id'] }}">
                            @endisset
                          </td>
                          <td>
                            @isset($flight['id'])
                            <a href="/{{ Request::path() }}?filterID={{ $currentFilter }}&year={{ $year }}&deleteFlight={{ $flight['id'] }}" onclick="return confirm('Etes vous sur de vouloir supprimer le vol {{ $flight['id'] }} ?');" class="text-danger"><i class="fas fa-trash"></i></a>
                            @endisset
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                    <div class="mt-2">
                      <button type="button" id="deleteSelectedFlightsBtn" class="btn btn-danger btn-sm" disabled>Supprimer la sélection</button>
                    </div>
                    <!-- Modal de progression de suppression -->
                    <div class="modal fade" id="deleteProgressModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <div class="modal-header py-2">
                            <h5 class="modal-title">Suppression en cours…</h5>
                          </div>
                          <div class="modal-body">
                            <div class="progress">
                              <div id="deleteProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                            </div>
                            <div class="mt-2"><small id="deleteProgressText">0 / 0</small></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <script>
                   $(document).ready(function() {
    const selectAll = document.getElementById('selectAllFlights');
    const deleteBtn = document.getElementById('deleteSelectedFlightsBtn');

    function getCheckboxes() {
      return Array.from(document.querySelectorAll('.js-flight-delete-checkbox'));
    }

    function updateButtonState() {
      const anyChecked = getCheckboxes().some(cb => cb.checked);
      if (deleteBtn) deleteBtn.disabled = !anyChecked;
    }

    if (selectAll) {
      selectAll.addEventListener('change', function() {
        const checked = selectAll.checked;
        getCheckboxes().forEach(cb => { cb.checked = checked; });
        updateButtonState();
      });
    }

    // Délégation jQuery: écoute les changements sur toutes les checkboxes ciblées, y compris futures
    $(document).on('change', '.js-flight-delete-checkbox', function() {
      if (!this.checked && selectAll && selectAll.checked) {
        selectAll.checked = false;
      }
      updateButtonState();
    });

    if (deleteBtn) {
      deleteBtn.addEventListener('click', async function() {
        const selected = getCheckboxes().filter(cb => cb.checked);
        if (selected.length === 0) return;
        if (!confirm('Etes vous sur de vouloir supprimer ' + selected.length + ' vol(s) ?')) return;

        const total = selected.length;
        let done = 0;
        const useModal = total > 3;

        function updateProgress() {
          const percent = Math.floor((done / total) * 100);
          const bar = document.getElementById('deleteProgressBar');
          const txt = document.getElementById('deleteProgressText');
          if (bar) bar.style.width = percent + '%', bar.textContent = percent + '%';
          if (txt) txt.textContent = done + ' / ' + total;
        }

        deleteBtn.disabled = true;
        if (selectAll) selectAll.disabled = true;
        getCheckboxes().forEach(cb => cb.disabled = true);

        if (useModal && typeof $ !== 'undefined' && $('#deleteProgressModal').length) {
          updateProgress();
          $('#deleteProgressModal').modal({ backdrop: 'static', keyboard: false });
          $('#deleteProgressModal').modal('show');
        }

        for (const cb of selected) {
          const url = cb.getAttribute('data-delete-url');
          if (!url) { done++; updateProgress(); continue; }
          try {
            await fetch(url, { credentials: 'same-origin' });
          } catch (e) {
            // On ignore et continue
          } finally {
            done++;
            if (useModal) updateProgress();
          }
        }

        window.location.reload();
      });
    }

    updateButtonState();
   });
                  </script>
                    <hr>
                    <h4>Statistique : </h4>
                    <table class="table table-striped table-sm">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Heure en instruction Homme</td>
                          <td>{{ $stat['instruction'][0] }}</td>
                        </tr>
                        <tr>
                          <td>Heure en instruction Femme</td>
                          <td>{{ $stat['instruction'][1] }}</td>
                        </tr>
                        <tr>
                          <td>Heure en instruction Total</td>
                          <td>{{ $stat['instruction'][2] }}</td>
                        </tr>
                        <tr>
                          <td>Décollage en instruction</td>
                          <td>{{ $stat['instruction'][3] }}</td>
                        </tr>
                        @foreach($stat['instruction'][4] as $typeStart => $nbStart)
                        <tr>
                          <td>type de lancement : {{ $typeStart }} en instruction</td>
                          <td>{{ $nbStart }}</td>
                        </tr>
                        @endforeach
                        <tr><td></td><td></td></tr>
                        <tr>
                          <td>Heure normal Homme</td>
                          <td>{{ $stat['normal'][0] }}</td>
                        </tr>
                        <tr>
                          <td>Heure normal Femme</td>
                          <td>{{ $stat['normal'][1] }}</td>
                        </tr>
                        <tr>
                          <td>Heure normal Total</td>
                          <td>{{ $stat['normal'][2] }}</td>
                        </tr>
                        <tr>
                          <td>Décollage normal</td>
                          <td>{{ $stat['normal'][3] }}</td>
                        </tr>
                        @foreach($stat['normal'][4] as $typeStart => $nbStart)
                        <tr>
                          <td>type de lancement : {{ $typeStart }} hors instruction</td>
                          <td>{{ $nbStart }}</td>
                        </tr>
                        @endforeach
                        <tr>
                          <td>Temps moteur</td>
                          <td>à partir de l'index</td>
                        </tr>
                        @foreach($firstIndexMotor as $aircraft => $indexStart)
                        <tr>
                          <td>{{ $aircraft }}</td>
                          <td>{{ round($lastIndexMotor[$aircraft] - $indexStart) }} Heures</td>
                        </tr>
                        @endforeach
                        @if($transactionErrors > 0)
                        <tr>
                          <td>Erreur de transaction</td>
                          <td>{{ $transactionErrors }}</td>
                        </tr>
                        @endif
                      </tbody>
                    </table>
                  </div>
                  @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
