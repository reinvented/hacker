getEnergyDataXHR();

$('#refresh').click(function(){
  getEnergyDataXHR();
})

function getEnergyDataXHR() {
	$("#refreshing").show();
	$("#refresh").hide();

	var xhr = new XMLHttpRequest({mozSystem: true, responseType: 'json'});

	xhr.addEventListener("load", transferComplete, false);
	xhr.addEventListener("error", transferFailed, false);
	xhr.addEventListener("abort", transferCanceled, false);

	xhr.open('GET', "http://energy.reinvented.net/pei-energy/govpeca/get-govpeca-data.php?format=json-noheaders", true);

	function transferComplete(evt) {
		if (xhr.status === 200 && xhr.readyState === 4) {
			var data = JSON.parse(xhr.response);
			$("#onislandload").html(data["on-island-load"] + " MW");
			$("#onislandwind").html(data["on-island-wind"] + " MW");
			$("#percentagewind").html(data["percentage-wind"] + "%");
			$("#refreshing").hide();
			$("#refresh").show();
		}
	}

	function transferFailed(evt) {
		console.log("An error occurred transferring the data.");
	}

	function transferCanceled(evt) {
		console.log("The transfer has been cancelled by the user.");
	}

	xhr.onerror = function (e) {
		console.log(e);
	};

	xhr.send();
}
