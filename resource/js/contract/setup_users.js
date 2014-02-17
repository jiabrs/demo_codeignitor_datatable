/*
 * element/setup_criteria controller related javascript functions
 */
$(document).ready(function() {
	
	/*
	 * Adds click event to add uid button
	 * Upon click, selected uid added to form
	 */
	$('button#add_usr_id').click(function (event) {
		
		// prevent form submission
		event.preventDefault();
		
		selected = $('select#usr_ids option:selected');
		
		// remove "no_results" row
		// hide no results row
		$('tr.no_results').hide();
		
		// remove if already exists
		$('tr#usr_id_'+selected.val()).remove();
		
		// build row
		var row = '<tr id="usr_id_'+selected.val()+'">';
		row += '<td><a class="remove_tr" href="#">Remove</a></td>';
		row += '<td>'+selected.text()+'<input type="hidden" name="usr_id[]" value="'+selected.val()+'" /></td>';
		row += '</tr>';
		
		// append to table
		$('table.result_table').append(row);
	});
});