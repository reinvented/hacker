var currentPosition = false;
var currentPositionLatitude = false;
var currentPositionLongitude = false;
var positionInterval = false;
var positionError = false;
var reportssent = 0;
var updatefrequency = 30000;
var updateinterval = false;

startUp();

function startUp() {
	getCellID();
	if (localStorage.getItem("updatefrequency") === null) {
		window.localStorage.setItem("updatefrequency", updatefrequency);
	}
	$("#opencellid").val(localStorage.opencellid);
	$("#updatefrequency").val(localStorage.updatefrequency);
	if (localStorage.send_to_opencellid == 'on') {
		navigator.geolocation.getCurrentPosition(successGeolocation, errorNoGeolocation, { enableHighAccuracy: true, maximumAge: 0 });
		$("#send_to_opencellid").attr('checked',true);
		$("#gpslocation").show();
		$("#reportssent_wrapper").show();
		updateinterval = window.setInterval(sendToOpenCellID,localStorage.updatefrequency);
		console.log("Setting update frequency to " + localStorage.updatefrequency);
	}
	window.setInterval(getCellID,1000);
}

function getCellID() {
  var conn = window.navigator.mozMobileConnection;
  if (!conn || !conn.voice || !conn.voice.network) {
    return;
  }
	else {
		var gsmCellId  = conn.voice.cell.gsmCellId;
		var gsmLocationAreaCode = conn.voice.cell.gsmLocationAreaCode;
		var longName = conn.voice.network.longName;
		var mcc = conn.voice.network.mcc;
		var mnc = conn.voice.network.mnc;
		var relSignalStrength = conn.voice.relSignalStrength;
		var signalStrength = conn.voice.signalStrength;

		$("#gsmCellId").html(gsmCellId);
		$("#network_identifiers").html(mcc + "/" + mnc + "/" + gsmLocationAreaCode);
		$("#longName").html(longName);
		$("#relSignalStrength").html(relSignalStrength + "/" + signalStrength);
	}
}

function speakUpdate() {
	var audioupdate = new Audio("audio/location.mp3"); 
	audioupdate.play();
}

function successGeolocation(position) {
		speakUpdate();
    currentPosition = (Math.round(position.coords.latitude * 100) / 100) + ","  + (Math.round(position.coords.longitude * 100) / 100);
    currentPositionLatitude = position.coords.latitude;
    currentPositionLongitude = position.coords.longitude;
    $("#location").html(currentPosition);

    if (positionInterval) {
        navigator.geolocation.clearWatch(positionInterval);
    }
    positionInterval = navigator.geolocation.watchPosition(updatePosition, noPositionFound, { enableHighAccuracy: true, maximumAge: 0 });
    positionError = false;
}

function errorNoGeolocation(error) {
}

function updatePosition(position) {
	currentPosition = (Math.round(position.coords.latitude * 1000) / 1000) + ","  + (Math.round(position.coords.longitude * 1000) / 1000);
	$("#location").html(currentPosition);
	currentPositionLatitude = position.coords.latitude;
	currentPositionLongitude = position.coords.longitude;
}

function noPositionFound() {
}

function sendToOpenCellID() {
	console.log("Running sendToOpenCellID()");

  var conn = window.navigator.mozMobileConnection;
  if (!conn || !conn.voice || !conn.voice.network) {
    return;
  }
	else {
		var gsmCellId  = conn.voice.cell.gsmCellId;
		var gsmLocationAreaCode = conn.voice.cell.gsmLocationAreaCode;
		var longName = conn.voice.network.longName;
		var mcc = conn.voice.network.mcc;
		var mnc = conn.voice.network.mnc;
		var relSignalStrength = conn.voice.relSignalStrength;
		var signalStrength = conn.voice.signalStrength;

		if ((currentPositionLatitude) && (localStorage.opencellid != '') && (localStorage.send_to_opencellid == 'on')) {
			reportssent = reportssent + 1;
			$("#reportssent").html(reportssent);
			var xhr = new XMLHttpRequest({mozSystem: true, responseType: 'json'});
			var geturl = "http://www.opencellid.org/measure/add?key=7259c162f255e25e288c6e991fe11592&cellid=" + gsmCellId + "&lac=" + gsmLocationAreaCode + "&mcc=" + mcc + "&mnc=" + mnc + "&signal=" + relSignalStrength + "&lat=" + currentPositionLatitude + "&lon=" + currentPositionLongitude + "&measured_at=" + moment().format();
			xhr.open('GET', geturl, true);
			xhr.send();
		}
	}
}

$('#settings-btn').bind('click', function () {
	$('#settings-view').removeClass('move-down');
	$('#settings-view').addClass('move-up');
});

$('#close-btn').bind('click', function () {
	$('#settings-view').removeClass('move-up');
	$('#settings-view').addClass('move-down');
});

$('#done-btn').bind('click', function () {
	window.localStorage.setItem("opencellid", $("#opencellid").val());
	window.localStorage.setItem("updatefrequency", $("#updatefrequency").val());
	if ($("#send_to_opencellid").is(':checked')) {
		window.localStorage.setItem("send_to_opencellid", 'on');
		navigator.geolocation.getCurrentPosition(successGeolocation, errorNoGeolocation, { enableHighAccuracy: true, maximumAge: 0 });
		$("#gpslocation").show();
		$("#reportssent_wrapper").show();
		clearInterval(updateinterval);
		updateinterval = window.setInterval(sendToOpenCellID,localStorage.updatefrequency);
		sendToOpenCellID();
	} 
	else {
		window.localStorage.setItem("send_to_opencellid", 'off');
		$("#gpslocation").hide();
		$("#reportssent_wrapper").hide();
		if (positionInterval) {
			navigator.geolocation.clearWatch(positionInterval);
		}
	}
	$('#settings-view').removeClass('move-up');
	$('#settings-view').addClass('move-down');
});

