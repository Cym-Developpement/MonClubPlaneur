feather.replace();
var landingDatePicker;
var takeOffDatePicker;
document.addEventListener("DOMContentLoaded", function() {
  ;(function ($) { $.fn.datepicker.language['fr'] = {
	    days: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
	    daysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
	    daysMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
	    months: ['Janvier','Février','Mars','Avril','Mai','Juin', 'Juillet','Août','Septembre','Octobre','Novembre','Decembre'],
	    monthsShort: ['Jan', 'Fév', 'Mars', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Dec'],
	    today: "Aujourd'hui",
	    clear: 'Effacer',
	    dateFormat: 'dd/mm/yyyy',
	    timeFormat: 'hh:ii',
	    firstDay: 1
	}; })(jQuery);
	var nextDay = new Date();
	// add a day
	nextDay.setDate(nextDay.getDate() + 1);
	maxFlightDay = new Date();
	maxFlightDay.setDate(maxFlightDay.getDate() + 30);

	$('#datepicker-flightDay').datepicker({
		language: 'fr',
		startDate: nextDay,
    	minDate: nextDay,
    	maxDate: maxFlightDay,
    	autoClose: true,
    	showEvent: 'focus',
    	position: "bottom left"
	});
	var start = new Date();
    start.setHours(0);
    start.setMinutes(0);

    $('.planches-datepicker').datepicker({
		language: 'fr',
    	autoClose: true,
    	position: "bottom left"
	});

 
	$('.newTrDateBlock-datePicker').datepicker({
		language: 'fr',
		startDate: start,
    	autoClose: true,
    	showEvent: 'focus',
    	timepicker: true,
    	position: "bottom left"
	});

	takeOffDatePicker = $('#adminAddFlightsTakeOff').datepicker({
		language: 'fr',
    	autoClose: true,
    	showEvent: 'focus',
    	timepicker: true,
    	position: "bottom left",
    	onSelect: function(formattedDate, date, inst){
	        adminAddFlightTimeCalc();
	        selectAtterissage();

    	},
	}).data('datepicker');

	landingDatePicker = $('#adminAddFlightsLanding').datepicker({
		language: 'fr',
    	autoClose: true,
    	showEvent: 'focus',
    	timepicker: true,
    	position: "bottom left",
    	onSelect: function(formattedDate, date, inst){
	        adminAddFlightTimeCalc();
    	},
	}).data('datepicker');

	


	getFlightDayBoard();

	$('#adminAddFlight').on('hide.bs.modal', function (e) {
	  resetAdminAddFlightForm();
	});
});

function saveNewUser()
{
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});

	var mail = $('#addUserMailInput').val();
	var name = $('#addUserNameInput').val();
	var licence = $('#addUserLicNumberInput').val();

	if (mail == '' || name == '') {
		$('#addUserHelpName').fadeIn();
		return;
	}

	var userState = [];

	if( $('#addUserStateaccomp').is(':checked') ){
	    userState.push('Licence Administrative')
	}
	if( $('#addUserStateeleve').is(':checked') ){
	    userState.push('Elève')
	}
	if( $('#addUserStatepilote').is(':checked') ){
	    userState.push('Pilote')
	}
	if( $('#addUserStateinstructeurplaneur').is(':checked') ){
	    userState.push('Instructeur Planeur')
	}
	if( $('#addUserStateinstructeurULM').is(':checked') ){
	    userState.push('Instructeur ULM')
	}
	if( $('#addUserStateremorqueur').is(':checked') ){
	    userState.push('Remorqueur')
	}

	if (userState.length == 0) {
		$('#addUserHelpState').fadeIn();
		return;
	}

	$.post( "admin/addUser", { name: name, mail: mail, state: userState, licence: licence})
	  .done(function( data ) {
	  	result = data.split('|');
	  	if (result[0] == 'OK') {
	  		$('#addUserModal').modal('hide');
	  		$('#addUserMailInput').val('');
	  		$('#addUserNameInput').val('');
	  		$('#addUserLicNumberInput').val('');
	  		$('#addUserStateaccomp').prop('checked', false);
	  		$('#addUserStateeleve').prop('checked', false);
	  		$('#addUserStatepilote').prop('checked', false);
	  		$('#addUserStateinstructeurplaneur').prop('checked', false);
	  		$('#addUserStateinstructeurULM').prop('checked', false);
	  		$('#addUserStateremorqueur').prop('checked', false);
	  		return;
	  	} else {
	  		$('#addUserHelpServerError').html(result[1]);
	  		$('#addUserHelpServerError').fadeIn();
	  	}
	});
	  
	$('#addUserHelpServerError').fadeOut();
	$('#addUserHelpName').fadeOut();
	$('#addUserHelpState').fadeOut();
}

function saveFlightDay()
{
	$('#flightDayRegisterOK').fadeOut();
	$('#flightDayRegisterERROR').fadeOut();

	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});
	var date = $('#datepicker-flightDay').val();

	if (date == '') {
		return;
	}

	var attribute = $('#addFlightDayAttributes').val();
	var observation = $('#addFlightDayObservation').val();
	$.post( "flightDay", { date: date, attribute: attribute, observation: observation})
	  .done(function( data ) {
	  	result = data.split('|');
	  	if (result[0] == 'OK') {
	  		$('#flightDayRegisterOK').html(result[1]);
	  		$('#flightDayRegisterOK').fadeIn();
	  		getFlightDayBoard();
	  	} else {
	  		$('#flightDayRegisterERROR').html(result[1]);
	  		$('#flightDayRegisterERROR').fadeIn();
	  	}
	});
}

function getFlightDayBoard()
{
	$.get( "flightDayBoard")
	  .done(function( data ) {
	  	$('#flightDayBoardContent').html(data);
	  	feather.replace();
	});
}

function deleteFlightDayRegister(id)
{
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});
	$.post( "flightDay/delete", { id: id})
	  .done(function( data ) {
	  	getFlightDayBoard();
	});
}

function pay()
{
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});

	$('#payModalErrorAmount').fadeOut();
	var amount = $('#payModalAmount').val();
	var type = $('#payModalType').val();
	var observation = $('#payModalText').val();
	
	var mail = 0;

	if( $('#payModalSendMail').is(':checked') ){
	    mail = 1;
	}

	if(isNaN(amount) || amount < 10) {
		$('#payModalErrorAmount').fadeIn();
		return;
	}

	$.post( "pay/add", { amount: amount, type: type, mail: mail, observation: observation})
	  .done(function( data ) {
	  	if(type != 'CB')
	  	{
	  		$('#payModal').modal('hide');
	  		document.location.reload(false);
	  	}
	});
}

function validTransactions(id)
{
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});

	$.post( "validTransactionPost", { id: id})
	  .done(function( data ) {
	  	document.location.reload(false);
	});
}

function deleteTransactions(id)
{
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});

	$.post( "deleteTransactionPost", { id: id})
	  .done(function( data ) {
	  	document.location.reload(false);
	});
}

function displayNewTrDate(id)
{
	$('#currentTrDateBlock-'+id).fadeOut( "slow", function() {
    	$('#newTrDateBlock-'+id).fadeIn("slow");
  	});

}

function validNewTrDate(id)
{
	var newDate = $('#newTrDateInput-'+id).val();
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});
	$.post( "validNewTrDate", { id: id, date: newDate})
	  .done(function( data ) {
	  	window.location = window.location.href.split("#")[0];
	});
}

function adminAddFlightSelectType()
{
	var type = $('#adminAddFlightAircraft').find(':selected').attr('data-aircrafttype');
	$('.aircraftTypeBlock').fadeOut('slow', function(){
		$('#flightSelectType'+type).fadeIn();
	});
	priceAdminFlight();
}


function dateToIso(strDate)
{
	var arrayDateTime = strDate.split(' ');
	var arrayDate = arrayDateTime[0].split('/');
	var Time = arrayDateTime[1]+':00';
	return arrayDate[2]+'-'+arrayDate[1]+'-'+arrayDate[0]+' '+Time;
}

function adminAddFlightTimeCalc()
{
	var date1 = new Date(dateToIso($('#adminAddFlightsTakeOff').val()));
	var date2 = new Date(dateToIso($('#adminAddFlightsLanding').val()));
	var timeMin = ((date2 - date1)/60000);

	if (timeMin > 0 && timeMin < 900) {
		$('#adminAddFlightsTime2').val(timeMin);
		priceAdminFlight();
	}
}

function minToHM(minutes)
{
	if (minutes < 60) {
            return "00h"+minutes.padStart(2, '0');
        } else {
            var hourR = parseInt((minutes/60), 10);
            var minutesR = minutes-(hourR*60);
            if (minutesR == 0) {
                return hourR.toString().padStart(2, '0')+"h00";
            } else {
            	return hourR.toString().padStart(2, '0')+"h"+minutesR.toString().padStart(2, '0');
            }
        }
}

function priceAdminFlight()
{
	if ($('#adminAddFlightSimulation').val() == 0) {
		if ($('#adminAddFlightAircraft').val() == 0) {
			console.log('CHOISIR AIRCRAFT');
			$( ".validNewAdminFlight" ).prop( "disabled", true );
			$('#adminAddFlightTotalPrice').html("");
			return;
		}

		$('#validLineFlightBoardPlane').html($('#adminAddFlightAircraft').find(':selected').attr('data-name'));

		if ($('#adminAddFlightsTakeOff').val() == '') {
			console.log('CHOISIR HEURE DECOLLAGE OU temps de vol');
			$( ".validNewAdminFlight" ).prop( "disabled", true );
			$('#adminAddFlightTotalPrice').html("");
			return;
		}	
	}
	

	$('#validLineFlightBoardDate').html($('#adminAddFlightsTakeOff').val().substring(0,5));
	$('#validLineFlightBoardtakeoff').html($('#adminAddFlightsTakeOff').val().substring(11));

	$('#validLineFlightBoardSupervisor').html($('#userSupervisor').find(':selected').attr('data-name'));
	$('#validLineFlightBoardtime').html(minToHM($('#adminAddFlightsTime2').val()));



	let aircraft = $('#adminAddFlightAircraft').val();
	let takeOffDate = $('#adminAddFlightsTakeOff').val();
	let landingDate = $('#adminAddFlightsLanding').val();
	let flightTime = $('#adminAddFlightsTime2').val();
	let nbTakeOff = $('#adminAddFlightsTakeOff2').val();
	let motorStart = ($('#adminAddFlightsMotorStart').val());
	let motorEnd = ($('#adminAddFlightsMotorEnd').val());
	let startType = $('#adminAddFlightsTakeOffType2').val();

	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});

	$.get( "getPrice", { aircraft: aircraft, 
						takeOffDate: takeOffDate, 
						landingDate: landingDate, 
						flightTime: flightTime, 
						nbTakeOff: nbTakeOff, 
						motorStart: motorStart, 
						motorEnd: motorEnd,
						startType: startType,
						simulation: $('#adminAddFlightSimulation').val() } )
	  .done(function( data ) {
	  	const obj = JSON.parse(data);
	  	//$('#adminAddFlightTotalPrice').html((obj[3]/100)+"€");
	  	$('#adminAddFlightTotalPrice').html(new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((obj[3]/100)));
	    console.log(obj);
	});

	if ($('#adminAddFlightSimulation').val() == 1) {
		return;
	}
	$('.validNewAdminFlight').removeAttr('disabled');
	$('#validLineFlightBoardlanding').html($('#adminAddFlightsLanding').val().substring(11));


	/*if (type == 1) {
		if ($('#adminAddFlightsMotorStart').val() == 0 || $('#adminAddFlightsMotorEnd').val() == 0 || $('#adminAddFlightsMotorStart').val()>$('#adminAddFlightsMotorEnd').val()) {
			$('#adminAddFlightTotalPrice').html("");
			return;
		}

		//var totalTime = $('#adminAddFlightsTime2').val();
		//var nbTakeOff = $('#adminAddFlightsTakeOff2').val();
		//var basePrice = (($('#adminAddFlightAircraft').find(':selected').attr('data-price')/60)*100);
		//var motorPrice = $('#adminAddFlightAircraft').find(':selected').attr('data-motorprice');
		

		totalPrice = (basePrice*totalTime)+(motorTime*motorPrice);
		totalPrice = Math.round(totalPrice);
		$('.validNewAdminFlight').removeAttr('disabled');
		$('#adminAddFlightTotalPrice').html((totalPrice/100)+"€");
	}

	if (type == 2) {
		if ($('#adminAddFlightsTime2').val() == 0) {
			console.log('INDIQUER TEMPS DE VOL');
			$( ".validNewAdminFlight" ).prop( "disabled", true );
			$('#adminAddFlightTotalPrice').html("");
			return;
		}
		//var totalTime = $('#adminAddFlightsTime2').val();
		//var nbTakeOff = $('#adminAddFlightsTakeOff2').val();
		//var basePrice = (($('#adminAddFlightAircraft').find(':selected').attr('data-price')/60)*100);
		//var startPrice = $('#adminAddFlightsTakeOffType2').find(':selected').attr('data-price');
		totalPrice = (basePrice*totalTime)+(nbTakeOff*startPrice);
		totalPrice = Math.round(totalPrice);
		$('.validNewAdminFlight').removeAttr('disabled');
		$('#adminAddFlightTotalPrice').html((totalPrice/100)+"€");
	}*/

	
}



function resetAdminAddFlightForm()
{
	$( ".validNewAdminFlight" ).prop( "disabled", true );
	$('#adminAddFlightTotalPrice').html("");
	$('#adminAddFlightsTime2').val(0);
	$('#adminAddFlightsTakeOff2').val(1);
	$('#adminAddFlightsTakeOff').val('');
	$('#adminAddFlightsLanding').val('');
	$('#adminAddFlightsTakeOffType2 option[value="1"]').prop('selected', true);
	$('#adminAddFlightAircraft option[value="0"]').prop('selected', true);
	$('#flightSelectType1').fadeOut();
	$('#flightSelectType2').fadeOut();
	$('#adminAddFlightsMotorEnd').val('');
	$('#adminAddFlightsMotorStart').val('');
}

function validNewAdminFlight(close)
{
	var user = $('#userAdminAddFlight').val();
	var aircraft = $('#adminAddFlightAircraft').val();
	var aircraftType = $('#adminAddFlightAircraft').find(':selected').attr('data-aircrafttype');
	var takeOffDate = $('#adminAddFlightsTakeOff').val();
	var landingDate = $('#adminAddFlightsLanding').val();
	var flightTime = $('#adminAddFlightsTime2').val();
	var nbTakeOff = $('#adminAddFlightsTakeOff2').val();
	var userPay = $('#userPayAdminAddFlight').val();
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});
	var startType = 0;
	if (aircraftType == 2) {
		startType = $('#adminAddFlightsTakeOffType2').val();

	}

	var startMotor = 0.0;
	var endMotor = 0.0;
	
	if (aircraftType == 1) {
		startMotor = $('#adminAddFlightsMotorStart').val();
		endMotor = $('#adminAddFlightsMotorEnd').val();
	}

	var supervisor = $('#userSupervisor').val();

	$.post( "validNewAdminFlight", {user: user, userPay: userPay, aircraft: aircraft, aircraftType: aircraftType, takeOffDate: takeOffDate, landingDate: landingDate,
									flightTime: flightTime, nbTakeOff: nbTakeOff, startType: startType, startMotor: startMotor, endMotor: endMotor, supervisor: supervisor})
	  .done(function( data ) {
	  	console.log(data);
	  	if (close) {
	  		window.location = window.location.href.split("#")[0];
	  	} else {
	  		resetAdminAddFlightForm();
	  	}
	});
}

function selectFilterFlightBoard()
{
	var filter = $('#filterFlightBoard').val();
	var newuri = window.location.href.split("#")[0].split("?")[0];
	newuri = newuri + "?filterID="+filter+"&year="+$('#filterFlightBoardYear').val();
	window.location = newuri;
}

function controlBDDData()
{
	$.get( "controlData")
	  .done(function( data ) {
	  	console.log(data);
	  	$('#controlDataResult').html(data);
	  	$('#closeControlDataModal').removeAttr('disabled');
	});
}

function markReadAlert(id)
{
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});

	$.post( "alertRead", { id: id})
	  .done(function( data ) {
	  	console.log(data);
	});
}

function strToJsDate(strDate)
{
	let data = strDate.split(' ');
	let year = data[0].split('/');
	return new Date(year[2]+"/"+year[1]+"/"+year[0]+" "+data[1]);
}



