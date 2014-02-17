$(document).ready(function(){

	$('select#app').after('<span class="lkup_sts"></span>');
	$('select#role').change(function () {
		
		// get the currently selected value
		var sel_opt = $('select#role option:selected').val();
		
		$.ajax({
			type: 'GET',
			url: '/user/environment/'+sel_opt,
			beforeSend: function () {
				$('span.lkup_sts').html('<span class="ico_working"></span>');
			},
			error: function (xhr, errortext) {
				$('span.lkup_sts').html('Error refreshing application list: '+errortext);
			},
			complete: function () {
				$('span.lkup_sts').html('&nbsp;');
			},
			success: function(data) {
				var options = '';
				for (option in data)
				{
					options += '<option value="' + option + '">' + data[option] + '</option>';
				}
				$("select#app").html(options);
			},
			dataType: 'json'
		});
	});
});