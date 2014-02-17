/*
 * Javascript functions associated with element/setup_loc_user
 * controller
 */
$(document).ready(function(){
	/*
	 * Adds autocomplete to user lookup control
	 */
	$('input#user_lookup').autocomplete({
		source: function(request, response) {
			$.ajax({
				url: $('input#user_lookup').attr('data-href'),
				dataType: "json",
				data: {
					lookup_user: request.term
				},
				type: "POST",
				success: function(data) {
					response(data);
				}
			})		
		},
		minLength: 3,
		select: function(event, ui) {
			
			// hide no results row
			$('tr.no_results').hide();
			
			// remove if already exists
			$('tr#usr_'+ui.item.value).remove();
			
			// build new row
			var $new_row = '<tr id="usr_'+ui.item.value+'"><td><a class="remove_tr" href="#">Remove</a></td><td>';
			$new_row += ui.item.label;
			$new_row += '<input type="hidden" name="uid[]" value="'+ui.item.value+'" /></td></tr>';
			
			// add to table
			$('table.result_table').append($new_row);
			
			// clear name from lookup field
			$('input#user_lookup').val('');
		}
	});
});