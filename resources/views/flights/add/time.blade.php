<div class="row justify-content-center">
  <div class="col-md-6" style="text-align: center;"><h3>Heure & temps de vol</h3>
  </div>
</div>
<hr>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="mb-3">
      <label for="addFlightsTakeOff">Heure de Décollage</label>
        <input type="text" onchange="setDatePickerFromInput();controleDateFlight();" class="form-control addFlightsTakeOff" id="addFlightsTakeOff" placeholder="dd/mm/yyyy 12:00" data-inputmask="'alias': 'datetime', 'inputFormat': 'dd/mm/yyyy H2:MM', 'placeholder': 'dd/mm/yyyy 12:00'">
    </div>
    <div class="mb-3">
      <label for="addFlightsTime">Temps de vol.</label>
      <input onkeyup="controleDateFlight();" onchange="controleDateFlight();" type="number" min="3" step="1" max="720" value="0" class="form-control" id="addFlightsTime" aria-describedby="addFlightsTimeHelp">
      <small id="addFlightsTimeHelp" class="form-text text-body-secondary">Temps de vol en minutes</small>
    </div>
    <div class="mb-3">
      <label for="landing">Heure d'atterissage</label>
        <input type="text" class="form-control" id="landing" placeholder="dd/mm/yyyy 12:00" readonly>
    </div>
    <div class="alert alert-danger" role="alert" id="errorTimeAddFlight" style="display: none;">
      A simple danger alert—check it out!
    </div>
  </div>
</div>
