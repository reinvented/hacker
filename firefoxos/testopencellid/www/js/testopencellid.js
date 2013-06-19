var map = L.map('map');
var cellid = false;
var zoomed = false;

L.tileLayer('http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png', {
	maxZoom: 18,
	attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery Â© <a href="http://cloudmade.com">CloudMade</a>'
}).addTo(map);

function onLocationFound(e) {
	var radius = e.accuracy / 2;
	L.circle(e.latlng, 1, { 'color': '#f00' }).addTo(map);
	if (!zoomed) {
		map.setView(e.latlng, 19);
		zoomed = true;
	}
	else {
		map.setView(e.latlng);
	}
}

function onLocationError(e) {
	alert(e.message);
}

map.on('locationfound', onLocationFound);
map.on('locationerror', onLocationError);

map.locate({setView: true, maxZoom: 19, timeout: 900000, enableHighAccuracy: true, watch: true });

setInterval(getOpenCellIDLocation,5000);

function getOpenCellIDLocation() {

	var conn = window.navigator.mozMobileConnection;

	if (!conn || !conn.voice || !conn.voice.network) {
	}
	else {

		var gsmCellId  = conn.voice.cell.gsmCellId;
		var gsmLocationAreaCode = conn.voice.cell.gsmLocationAreaCode;
		var longName = conn.voice.network.longName;
		var mcc = conn.voice.network.mcc;
		var mnc = conn.voice.network.mnc;
		var relSignalStrength = conn.voice.relSignalStrength;
		var signalStrength = conn.voice.signalStrength;

		if (cellid != gsmCellId) {
			var geturl = "http://www.opencellid.org/cell/get?cellid=" + gsmCellId + "&lac=" + gsmLocationAreaCode + "&mcc=" + mcc + "&mnc=" + mnc;
			var xhr = new XMLHttpRequest({mozSystem: true, responseType: 'xml'});
			xhr.addEventListener("load", transferComplete, false);
			xhr.addEventListener("error", transferFailed, false);
			xhr.addEventListener("abort", transferCanceled, false);
			xhr.open('GET', geturl, true);
			xhr.send();
		}
	}

	function transferComplete(evt) {
		if (xhr.status === 200 && xhr.readyState === 4) {
			var location = xmlToJson(xhr.responseXML);
			var lat = (location.rsp.cell['@attributes'].lat);
			var lon = (location.rsp.cell['@attributes'].lon);
			var latlng = new L.LatLng(lat,lon);
			L.circle(latlng, 1, { 'color': '#00f' }).addTo(map);
			if (!zoomed) {
				map.setView(latlng, 19);
				zoomed = true;
			}
			else {
				map.setView(latlng);
			}
		}
	}

	function transferFailed(evt) {
		alert("An error occurred transferring the data.");
		alert(evt);
	}

	function transferCanceled(evt) {
		alert("The transfer has been cancelled by the user.");
	}
}

// Changes XML to JSON -- from http://davidwalsh.name/convert-xml-json
function xmlToJson(xml) {
	
	// Create the return object
	var obj = {};

	if (xml.nodeType == 1) { // element
		// do attributes
		if (xml.attributes.length > 0) {
		obj["@attributes"] = {};
			for (var j = 0; j < xml.attributes.length; j++) {
				var attribute = xml.attributes.item(j);
				obj["@attributes"][attribute.nodeName] = attribute.value;
			}
		}
	} else if (xml.nodeType == 3) { // text
		obj = xml.nodeValue;
	}

	// do children
	if (xml.hasChildNodes()) {
		for(var i = 0; i < xml.childNodes.length; i++) {
			var item = xml.childNodes.item(i);
			var nodeName = item.nodeName;
			if (typeof(obj[nodeName]) == "undefined") {
				obj[nodeName] = xmlToJson(item);
			} else {
				if (typeof(obj[nodeName].push) == "undefined") {
					var old = obj[nodeName];
					obj[nodeName] = [];
					obj[nodeName].push(old);
				}
				obj[nodeName].push(xmlToJson(item));
			}
		}
	}
	return obj;
};