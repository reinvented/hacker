var currentPosition = false;
var currentPositionLatitude = false;
var currentPositionLongitude = false;
var positionInterval = false;
var positionError = false;
var cellids = new Object();
var reportssent = 0;

startUp();

function startUp() {
	getCellID();
	$("#opencellid").val(localStorage.opencellid);
	if (localStorage.send_to_opencellid == 'on') {
		navigator.geolocation.getCurrentPosition(successGeolocation, errorNoGeolocation, { enableHighAccuracy: true, maximumAge: 0 });
		$("#send_to_opencellid").attr('checked',true);
		$("#gpslocation").show();
		$("#reportssent").show();
	}
	window.setInterval(getCellID,30000);
}

$('#refresh').click(function(){
  getCellID();
})

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

		if (!cellids[gsmCellId]) {
			cellids[gsmCellId] = true;
		}

		if ((currentPositionLatitude) && (localStorage.opencellid != '') && (localStorage.send_to_opencellid == 'on')) {
			var xhr = new XMLHttpRequest({mozSystem: true, responseType: 'json'});
			var geturl = "http://www.opencellid.org/measure/add?key=7259c162f255e25e288c6e991fe11592&cellid=" + gsmCellId + "&lac=" + gsmLocationAreaCode + "&mcc=" + mcc + "&mnc=" + mnc + "&signal=" + relSignalStrength + "&lat=" + currentPositionLatitude + "&lon=" + currentPositionLongitude + "&measured_at=" + moment().format();
			xhr.open('GET', geturl, true);
			xhr.send();
			reportssent = reportssent + 1;
			$("#reportssent").html(reportssent);
		}
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
    positionInterval = navigator.geolocation.watchPosition(updatePosition, noPositionFound, { enableHighAccuracy: true, maximumAge: 0, frequency: 10000 });
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
	if ($("#send_to_opencellid").is(':checked')) {
		window.localStorage.setItem("send_to_opencellid", 'on');
		navigator.geolocation.getCurrentPosition(successGeolocation, errorNoGeolocation, { enableHighAccuracy: true, maximumAge: 0 });
		$("#gpslocation").show();
		$("#reportssent").show();
	} 
	else {
		window.localStorage.setItem("send_to_opencellid", 'off');
		navigator.geolocation.getCurrentPosition(successGeolocation, errorNoGeolocation, { enableHighAccuracy: true, maximumAge: 0 });
		$("#gpslocation").hide();
		$("#reportssent").hide();
		if (positionInterval) {
			navigator.geolocation.clearWatch(positionInterval);
		}
	}
	$('#settings-view').removeClass('move-up');
	$('#settings-view').addClass('move-down');
});

