$(function(){
	console.log("onload");
	$('#start').click(function() {
		start()
	});
	$('#end').click(function() {
		end()
	});
});

function start() {
	var desc = $('#description').val();
	if (desc == '') {
		$('.msg').html("description is empty");
		return;
	}
	else {
		$('.msg').html("");
	}
	var date = new Date();
	$('#starttime').val(date.getTime());
	
	var data = {
			'start' : true,
			'description' : desc
	};
	$.post('timesheet.php', data, function(data) {
		var response = jQuery.parseJSON(data);
		if (response.ok == true) {
			$('#timesheet_id').val(response.id);
			$('#start').hide();
			$('#end').show();
			$('.msg').html("started: " + date.toLocaleString());
		}
		else {
			$('.msg').html(response.msg);
		}
	});
}

function end() {
	var data = {
		'end' : true,
		'id' : $('#timesheet_id').val()
	};
	$.post('timesheet.php', data, function(data) {
		var response = jQuery.parseJSON(data);
		if (response.ok == true) {
			var date = new Date();
			var endTime = date.getTime();
			var startTime = $('#starttime').val();
			var dif = endTime - startTime;
			//var sec = Math.round(dif / 1000);
			//var mins = Math.round(dif / 60000)
			//var hours = Math.round(dif / 3600000)
			
			var difference_ms = dif/1000;
			var seconds = Math.floor(difference_ms % 60);
			difference_ms = difference_ms/60; 
			var minutes = Math.floor(difference_ms % 60);
			difference_ms = difference_ms/60; 
			var hours = Math.floor(difference_ms % 24);  
			
			$('#description').val("");
			$('#timesheet_id').val("");
			$('#end').hide();
			$('#start').show();
			$('.msg').html("ended: " + date.toLocaleString() + ", total time: " + hours + ":" + minutes + ":" + seconds);
			
			
		}
		else {
			$('.msg').html(response.msg);
		}
	});
}