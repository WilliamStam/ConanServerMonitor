$(document).ready(function() {



	getData();
});

function getData() {
	$("#right-panel").html("")

	$.getData("/players", $.bbq.getState(), function(data) {

		$("#content-area").jqotesub($("#template-content"), data);




	}, "getData");


}
