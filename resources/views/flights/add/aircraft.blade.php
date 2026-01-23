<div class="row justify-content-center">
  <div class="col-md-6" style="text-align: center;"><h3>Choisissez l'aeronef</h3>
  </div>
</div>
<hr>
<div class="row justify-content-center">
  @foreach(App\Models\aircraft::where('actif', 1)->where('public', 1)->get() as $aircraft)
  <div class="col-md-3" style="margin-bottom: 10px;">
    <button type="button" 
      class="aircraftSelect btn btn-block btn-primary" 
      data-start="{{ $aircraft->type }}"
      data-motormin=""
      data-motormax=""
      data-motorindextype="{{ $aircraft->motorPriceType }}"
      id="aircraftSelect-{{ $aircraft->id }}" 
      onclick="selectAircraft({{ $aircraft->id }});">
      {{ $aircraft->name }} ({{ $aircraft->register }})
    </button>
  </div>
  @endforeach
</div>