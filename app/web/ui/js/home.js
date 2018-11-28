$(document).ready(function() {



	getData();
});

function getData() {
	$("#right-panel").html("")

	$.getData("/", $.bbq.getState(), function(data) {

		$("#content-area").jqotesub($("#template-content"), data);



		var availableDates = ["9-5-2011","14-5-2011","15-5-2011"];


		$("#selectDay").datepicker({
			'defaultDate': (data.key),
			'dateFormat': "yymmdd",
			'onSelect': function(s,e) {

				$.bbq.pushState({"day": s});
				getData();
			},
			'icons': {
				time: "fa fa-clock-o",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down",

				previous: 'fa fa-chevron-left',
				next: 'fa fa-chevron-right',
				today: 'fa fa-screenshot',
				clear: 'fa fa-trash',
				close: 'fa fa-remove',
			},
			beforeShowDay: function(date) {

				if ($.inArray(moment(date).format("YMMDD"), data.days) != -1) {
					return [true, "","Available"];
				} else {
					return [false,"","unAvailable"];
				}
			}
		});



	}, "getData");


}

function revdayStr(day) {

	if ( day ) {
		let y = day.substr(0,4);
		let m = day.substr(4,2);
		let d = day.substr(6, 2);

		return new Date(y, m, d)
	}

	return new Date()





}