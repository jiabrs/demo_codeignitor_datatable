$(document).ready(function(){
	
	$('form').submit(function() {

		var returnLink = '&nbsp;<a href="/contract/dsp/' + $('input[name="cntrct_id"]').val() + '">Return to Contract</a>';
		
		$.ajax({
			url: $(this).attr('action'),
			data: $(this).serialize(),
			type: "POST",
			dataType: "html",
			beforeSend: function() {
				$('span#update_results').html('Updating Projections <span class="ico_working"></span>');
			},
			error: function() {
				$('span#update_results').html('Projection Update Failed'+returnLink);
			},
			success: function() {
				$('span#update_results').html('Projections Saved'+returnLink);
			}
		});

		return false;
	});
});