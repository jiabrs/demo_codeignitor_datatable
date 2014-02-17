/*
 * element/setup_criteria controller related javascript functions
 */
$(document).ready(function() {
	
	/*
	 * reset code and accrue against inputs if
	 * field dropdown changes
	 */  
	$('input[name="fld"]').change(function () {
				
		$.ajax({
			url: $('select#code').attr('data-href'),
			data: {
				field: $('input[name="fld"]:checked').val()
			},
			type: "POST",
			dataType: "json",
			success: function(data) {
				var options = '';
				for (option in data)
				{
					options += '<option value="' + option + '">' + data[option] + '</option>';
				}
				$("select#code").html(options);
			}
		});		
		$('input#accr_Y').attr("checked",true);
	});
	
	/*
	 * Adds click event to add criteria button
	 * Upon click, selected criteria added to form
	 */
	$('button#add_crit').click(function (event) {
		
		// prevent form submission
		event.preventDefault();
		
		// grab criteria values
		var fld = $('input[name="fld"]:checked').val()
		var fld_dsp = $('label[for="fld_'+fld+'"]').text();
		
		var codes = $('select#code option:selected');
		
		if (codes.length > 0)
		{
			// remove "no_results" row
			// hide no results row
			$('tr.no_results').hide();
			
			var $rows = '';
			
			codes.each(function() {
				
				code = this.value;
				
				// remove if already exists
				$('tr#sls_crits_'+fld+'_'+code).remove();
				
				var code_dsp = $(this).text();
				
				// build row
				$rows += '<tr id="criterion_'+fld+'_'+code+'">';
				$rows += '<td><a class="remove_tr" href="#">Remove</a></td>';
				$rows += '<td>'+fld_dsp+'<input type="hidden" name="crit_fld[]" value="'+fld+'" /></td>';
				$rows += '<td>'+code_dsp+'<input type="hidden" name="crit_cd[]" value="'+code+'" /></td>';
				$rows += '</tr>';
			});		
			
			// append to table
			$('table.result_table').append($rows);
		}
	});
	
	$('a#add_crit').click(function(event) {
		
		event.preventDefault();
		
		// make it a dialog
		$('div#adv_search_dialog').dialog({
			modal: true,
			draggable: true,
			resizable: false,
			height: 'auto',
			width: 'auto',
			close: function(event, ui) {
				$(this).dialog("destroy"); // hide dialog on close
			}
		});
	});
	
});