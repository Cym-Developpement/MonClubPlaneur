<div class="row justify-content-center takeOffType" style="display: none;" id="takeOffType-2">
  <div class="col-md-6" style="text-align: center;"><h3>Moyen de lancement</h3>
  </div>
  <div class="col-md-12">
    <div class="row justify-content-center">
      @foreach(App\Models\sailplaneStartPrice::all() as $start)
        <div class="col-md-4" style="margin-bottom: 10px;"><button type="button" class="startSelect btn w-100 btn-primary" id="startSelect-{{ $start->id }}" data-name="{{ $start->name }}" data-price="{{ ($start->basePrice/100) }}" onclick="selectStart({{ $start->id }});">{{ $start->name }} ({{ number_format(($start->basePrice/100), 2).'€' }})</button></div>
      @endforeach
      <input type="hidden" id="currentStartPrice">
    </div>
  </div>
</div>
<div class="row justify-content-center takeOffType" style="display: none;" id="takeOffType-1">
  <div class="col-md-6" style="text-align: center;"><h3>Temps moteur</h3>
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="mb-3">
          <label for="addFlightsTakeOffMotorTime">Index moteur au Départ</label>
            <input type="number" step="0.01" onchange="controlIndexMotor();" onkeyup="controlIndexMotor();" class="form-control addFlightsTakeOffMotorTime" id="addFlightsTakeOffMotorTime">
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="mb-3">
          <label for="addFlightsLandingMotorTime">Index moteur a l'arrivée</label>
            <input type="number" step="0.01" onchange="controlIndexMotor();" onkeyup="controlIndexMotor();" class="form-control addFlightsLandingMotorTime" id="addFlightsLandingMotorTime">
        </div>
      </div>
    </div>
    <div class="alert alert-danger" id="startMotorIndexError" style="display: none;"></div>
  </div>
</div>
<hr>
