        <!-- Modal adminAddFlight -->
        <div class="modal fade" id="adminAddFlight" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ $currentUserName }} : Ajouter un vol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" id="userAdminAddFlight" value="{{ $selectedUser }}">
                
                <div class="mb-3">
                  <label for="userSupervisor">Instructeur</label>
                  <select class="form-select" id="userSupervisor" onchange="$('#validLineFlightBoardSupervisor').html($('#userSupervisor').find(':selected').attr('data-name'));">
                    <option data-name="" value="" 
                      >-----</option>  
                      @foreach(App\Models\User::supervisor() as $supervisor)
                    <option value="{{ $supervisor->id }}" data-name="{{ $supervisor->name }}" 
                      >{{ $supervisor->name }}</option>
                      @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="userPayAdminAddFlight">Utilisateur a facturer</label>
                  <select class="form-select" id="userPayAdminAddFlight">
                      @foreach($users as $user)
                    <option value="{{ $user->id }}" 
                        @if($selectedUser == $user->id)
                        selected
                        @endif
                      >{{ $user->name }}</option>
                      @endforeach
                  </select>
                </div>
                <input type="hidden" id="adminAddFlightSimulation" value="0">
                <select class="form-select" id="adminAddFlightAircraft" onchange="adminAddFlightSelectType();">
                  <option selected value="0">Séléctionnez l'appareil</option>
                  @foreach($aircrafts as $aircraft)
                  <option value="{{ $aircraft->id }}" data-aircrafttype="{{ $aircraft->type }}" data-motorprice="{{ $aircraft->motorPrice }}" data-price="{{ $aircraft->basePrice }}" data-name="{{ $aircraft->name }}">{{ $aircraft->name }} ({{ $aircraft->register }})</option>
                  @endforeach
                </select>
                <br><br>
                <div class="mb-3">
                  <label for="adminAddFlightsTakeOff">Heure de Décollage</label>
                  <input type="text" onchange="takeOffDatePicker.selectDate(strToJsDate($('#adminAddFlightsTakeOff').val()));" class="form-control addFlightDatePicker" id="adminAddFlightsTakeOff" placeholder="dd/mm/yyyy 12:00" data-inputmask="'alias': 'datetime', 'inputFormat': 'dd/mm/yyyy H2:MM', 'placeholder': 'dd/mm/yyyy 12:00'">
                </div>
                <div class="mb-3">
                  <label for="adminAddFlightsLanding">Heure d'atterrissage</label>
                  <input type="text" onchange="adminAddFlightTimeCalc();" class="form-control addFlightDatePicker" id="adminAddFlightsLanding" placeholder="dd/mm/yyyy 12:00" data-inputmask="'alias': 'datetime', 'inputFormat': 'dd/mm/yyyy H2:MM', 'placeholder': 'dd/mm/yyyy 12:00'">
                </div>
                <div class="mb-3">
                  <label for="adminAddFlightsTime">Temps de vol.</label>
                  <input type="number" onkeyup="priceAdminFlight();" onchange="priceAdminFlight();" min="3" step="1" value="0" class="form-control" id="adminAddFlightsTime2" aria-describedby="adminAddFlightsTimeHelp">
                  <small id="adminAddFlightsTimeHelp" class="form-text text-muted">Temps de vol en minutes</small>
                </div>
                <div class="mb-3">
                  <label for="adminAddFlightsTakeOff2">Nombre de décollage</label>
                  <input type="number" onkeyup="priceAdminFlight();" onchange="priceAdminFlight();" step="1" min="1" value="1" class="form-control" id="adminAddFlightsTakeOff2" placeholder="">
                </div>
                <hr>
                <div id="flightSelectType2" class="aircraftTypeBlock" style="display: none;">
                  <h3>Planeur : </h3>
                  <div class="mb-3">
                    <select class="form-select" onchange="priceAdminFlight();" id="adminAddFlightsTakeOffType2">
                      @foreach($sailplaneStartPrices as $sailplaneStartPrice)
                      <option value="{{ $sailplaneStartPrice->id }}" data-price="{{ $sailplaneStartPrice->basePrice }}">{{ $sailplaneStartPrice->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div id="flightSelectType1" class="aircraftTypeBlock" style="display: none;">
                  <h3>Avion / TMG / ULM : </h3>
                  <div class="mb-3">
                    <label for="adminAddFlightsMotorStart">Compteur moteur au départ</label>
                    <input type="number" onkeyup="priceAdminFlight();" onchange="priceAdminFlight();" step="0.01" min="1" class="form-control" id="adminAddFlightsMotorStart" placeholder="">
                  </div>
                  <div class="mb-3">
                    <label for="adminAddFlightsMotorEnd">Compteur moteur a l'arrivé</label>
                    <input type="number" onkeyup="priceAdminFlight();" onchange="priceAdminFlight();" step="0.01" min="1" class="form-control" id="adminAddFlightsMotorEnd" placeholder="">
                  </div>

                </div>
                <div id="adminAddFlightTotalPrice" style="text-align: center;font-weight: bold;font-size: 2em;font-style: italic;"></div>
                <hr>
                <h5>Résumé planche de vol : </h5>
                <table class="table">
                  <thead>
                    <tr>
                      <th scope="col">Date</th>
                      <th scope="col">Planeur</th>
                      <th scope="col">Pilote</th>
                      <th scope="col">Instructeur/passager</th>
                      <th scope="col">Décollé à</th>
                      <th scope="col">Posé à</th>
                      <th scope="col">Durée</th>
                      <th scope="col">Type</th>
                      <th scope="col">Observations</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <th scope="row" id="validLineFlightBoardDate"></th>
                      <th id="validLineFlightBoardPlane"></th>
                      <th id="validLineFlightBoardPilot">{{ $currentUserName }}</th>
                      <th id="validLineFlightBoardSupervisor"></th>
                      <th id="validLineFlightBoardtakeoff"></th>
                      <th id="validLineFlightBoardlanding"></th>
                      <th id="validLineFlightBoardtime"></th>
                      <th id="validLineFlightBoardtype"></th>
                      <th> --- </th>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetAdminAddFlightForm();">Annuler</button>
                <button type="button" class="btn btn-success" onclick="priceAdminFlight();">Calculer</button>
                <button type="button" class="btn btn-primary validNewAdminFlight" disabled onclick="validNewAdminFlight(false);">Enregistrer</button>
                <button type="button" class="btn btn-primary validNewAdminFlight" disabled onclick="validNewAdminFlight(true);">Enregistrer & fermer</button>
              </div>
            </div>
          </div>
        </div>
        <script type="text/javascript">
          function selectAtterissage()
          {
            

            let atterrissage = $('#adminAddFlightsLanding').val();
            if (atterrissage == '') {
              //$('#adminAddFlightsLanding').val($('#adminAddFlightsTakeOff').val());
              
              landingDatePicker.date = takeOffDatePicker.selectedDates[0];
              landingDatePicker.selectDate(takeOffDatePicker.selectedDates[0]);
            }
          }
        </script>