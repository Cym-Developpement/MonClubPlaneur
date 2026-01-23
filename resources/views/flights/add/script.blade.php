<script type="text/javascript">
  var currentStep = 1;
  var currentAircraft = 0;
  function selectAircraft(id)
  {
    currentAircraft = id;
    $('.aircraftSelect').removeClass('btn-success');
    $('#aircraftSelect-'+id).addClass('btn-success');
    $('#validStepButton').fadeIn(800);
    let type = $('#aircraftSelect-'+id).attr('data-start');
    $('.takeOffType').fadeOut(0);
    $('#takeOffType-'+type).fadeIn(0);
  }

  function setDatePickerFromInput()
  {
    let jsDate = strToJsDate($('#addFlightsTakeOff').val());
    if (isNaN(jsDate.getDate()) || isNaN(jsDate.getMonth()) || isNaN(jsDate.getFullYear())) {
      $('#addFlightsTakeOff').val('');
      return;
    }
    datePickerTakeOff.selectDate(jsDate);
  }

  function controleDateFlight()
  {
    let minutes = $('#addFlightsTime').val();
    let jsDate = strToJsDate($('#addFlightsTakeOff').val());
    
    if (isNaN(jsDate.getDate()) || isNaN(jsDate.getMonth()) 
        || isNaN(jsDate.getFullYear()) || minutes == 0) {
      if (isNaN(jsDate.getDate()) || isNaN(jsDate.getMonth()) 
        || isNaN(jsDate.getFullYear())) {
        $('#addFlightsTakeOff').val('');
      }
      $('#pageLink3').addClass('disabled');
      $('#pageLink4').addClass('disabled');

      $('#validStepButton').fadeOut(400);
      return;
    }
    
    jsDate = new Date(jsDate.getTime() + minutes*60000);
    let day = jsDate.getDate().toString().padStart(2, '0');
    let month = (jsDate.getMonth()+1);
    month = month.toString().padStart(2, '0');
    
    hour = jsDate.getHours().toString().padStart(2, '0');
    minutes = jsDate.getMinutes().toString().padStart(2, '0');
    strDate = day+'/'+month+'/'+jsDate.getFullYear()+' '+hour+':'+minutes;
    $('#landing').val(strDate);
    minutesFlight = $('#addFlightsTime').val();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
      $.post( "getAddFlightInfoTime", { aircraft: currentAircraft, startDate: $('#addFlightsTakeOff').val(), flightTime: minutesFlight})
        .done(function( data ) {
          data = JSON.parse(data);
          if (data[0] == 'ok') {
            $('#errorTimeAddFlight').fadeOut(0);
            $('#validStepButton').fadeIn(800);
            $('#addFlightsTakeOffMotorTime').attr('min', data[1]);
            $('#addFlightsLandingMotorTime').attr('min', data[1]);
            $('#addFlightsTakeOffMotorTime').val(data[1]);
            if (data[2] > 0) {
              $('#addFlightsLandingMotorTime').attr('max', data[2]);
            } else {
              $('#addFlightsLandingMotorTime').attr('max', 100000);
            }

          } else {
            $('#validStepButton').fadeOut(800);
            $('#errorTimeAddFlight').html(data[1]);
            $('#errorTimeAddFlight').fadeIn(0);
          }
          console.log(data);
      });
    
  }

  function controlIndexMotor()
  {
    let startIndex = $('#addFlightsTakeOffMotorTime').val();
    let endIndex = $('#addFlightsLandingMotorTime').val();
    let minStart = parseFloat($('#addFlightsTakeOffMotorTime').attr('min'));
    let maxEnd = parseFloat($('#addFlightsLandingMotorTime').attr('max'));

    if (endIndex <= startIndex) {
      $('#startMotorIndexError').html('L\'index de fin doit être plus grand que l\'index de départ.');
      $('#startMotorIndexError').fadeIn(0);
      $('#validStepButton').fadeOut(0);
      return;
    }

    if (startIndex < minStart) {
      $('#startMotorIndexError').html('L\'index de départ ne peut pas êtres inférieur au vol précédent ('+minStart+').');
      $('#startMotorIndexError').fadeIn(0);
      $('#validStepButton').fadeOut(0);
      return;
    }

    if (endIndex > maxEnd) {
      $('#startMotorIndexError').html('L\'index de fin ne peut pas êtres supérieur au vol suivant ('+maxEnd+').');
      $('#startMotorIndexError').fadeIn(0);
      $('#validStepButton').fadeOut(0);
      return;
    }
    $('#startMotorIndexError').fadeOut(0);
    $('#validStepButton').fadeIn(800);
  }

  function selectStart(id)
  {
    $('.startSelect').removeClass('btn-success');
    $('#startSelect-'+id).addClass('btn-success');
    $('#validStepButton').fadeIn(800);
    $('#currentStartPrice').val(id); 
  }

  function nextStep()
  {
    currentStep ++;
    validNewStep();
  }
  
  function gotoStep(step)
  {
    currentStep = step;
    validNewStep();
  }

  function validNewStep()
  {
    $('.page-item').removeClass('active');
    $('#validStepButton').fadeOut(400);
    $('.stepAddFlight').fadeOut(800);
        //$('#validStep').fadeOut();
        //$('#step'+currentStep).fadeIn(800);

    setTimeout(function(){ $('#step'+currentStep).fadeIn(800); }, 1000);
    $('#pageLink'+currentStep).addClass('active');
    $('#pageLink'+currentStep).removeClass('disabled');
  }

  $(document).ready(function(){
    datePickerTakeOff = $('#addFlightsTakeOff').datepicker({
      language: 'fr',
        autoClose: true,
        showEvent: 'focus',
        timepicker: true,
        position: "bottom left",
        onSelect: function(formattedDate, date, inst){
            controleDateFlight();
        },
    }).data('datepicker');
  });
  


</script>