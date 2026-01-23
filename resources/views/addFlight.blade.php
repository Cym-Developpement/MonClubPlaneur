@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">Ajouter un vol</div>

                <div class="card-body">
                  <div style="min-height: 320px;">
                    <div class="stepAddFlight" id="step1" style="min-height: 300px;">
                      <div class="container">
                        @include('flights.add.aircraft')
                      </div>
                    </div>

                    <div class="stepAddFlight" id="step2" style="min-height: 300px;display: none;">
                      <div class="container">
                        @include('flights.add.time')
                      </div>
                    </div>

                    <div class="stepAddFlight" id="step3" style="min-height: 300px;display: none;">
                      <div class="container">
                        @include('flights.add.start')
                      </div>
                    </div>

                    <div class="stepAddFlight" id="step4" style="min-height: 300px;display: none;">
                      <div class="container">
                        <div class="row justify-content-center">
                          <div class="col-md-6" style="text-align: center;"><h3>Récapitulatif</h3>
                          </div>
                        </div>
                        <hr>

                        
                      </div>
                    </div>
                  </div>
                  <div class="row justify-content-center" style="min-height: 50px;">
                    <div class="col-md-4">
                      <button type="button" style="display: none;" onclick="nextStep();" class="btn btn-block btn-success" id="validStepButton" onclick="">
                        Continuer&nbsp;&nbsp; 
                        <i class="float-end" data-feather="arrow-right-circle"></i>
                      </button>
                    </div>
                  </div>

                  <nav aria-label="Page navigation example">
                    <ul class="pagination pagination-lg justify-content-center">
                      <li class="page-item active" id="pageLink1"><a class="page-link" onclick="gotoStep(1);" href="#">Appareil</a></li>
                      <li class="page-item disabled" id="pageLink2"><a class="page-link" onclick="gotoStep(2);" href="#">Heure & temps de vol</a></li>
                      <li class="page-item disabled" id="pageLink3"><a class="page-link"  onclick="gotoStep(3);" href="#">Moyen de lancement / temps Moteur</a></li>
                      <li class="page-item disabled" id="pageLink4"><a class="page-link" onclick="gotoStep(4);" href="#">Récapitulatif</a></li>
                    </ul>
                  </nav>
                  
                  
                </div>
            </div>
        </div>
    </div>
</div>
@include('flights.add.script')
@endsection
