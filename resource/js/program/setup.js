/*
 * element/setup_criteria controller related javascript functions
 */
$(document).ready(function() {
	
	$('a#add_elem').click(function(event) {
		event.preventDefault();
			
		// make it a dialog
		$('div#elem_lookup').dialog({
			modal: true,
			draggable: true,
			resizable: false,
			autoResize: true,
			position: [180,150],
			height: "auto",
			width: "750px",
			close: function(event, ui) {
				$(this).dialog("destroy"); // hide dialog on close
			}
		});
		
		// if the lookup table doesn't exist, load it from server.
		if ($("div#elem_lookup table").length == 0)
		{
			$.ajax({
				type: 'POST',
				url: $("a#add_elem").attr('href'),
				beforeSend: function () {
					$('span.ui-dialog-title').html('Loading Elements <span class="ico_working"></span>');
				},
				error: function (xhr, errortext) {
					$('span.ui-dialog-title').html('Loading Elements Failed: '+errortext);
				},
				complete: function () {
					$('span.ui-dialog-title').html('&nbsp;');
				},
				success: function(data) {
					$("div#elem_lookup").html(data);	
					$('div#elem_lookup table.dataTable').dataTable({
						"bLengthChange": false,
						"bJQueryUI": true,
						"bAutoWidth": false,
						"iDisplayLength": 10,
						"aoColumns": [
						              {"bSearchable": false,"bSortable": false, "sWidth": "40px"}, /* actions */
						              {"sWidth": "180px"}, /* Name */
						              {"sWidth": "200px"}, /* Description */
						              {"sWidth": "80px"}, /* Type */
						              {"sWidth": "80px"}, /* Rate */
						              {"sWidth": "75px"} /* On invoice flag */		              
						]
					});				
				},
				dataType: 'HTML'
			});
		}
		
		$('button.add').live('click', function(event) {
			event.preventDefault();
			
			// remove "no_results" row
			// hide no results row
			$('table.result_table tr.no_results').hide();
			
			// remove if already exists
			$('tr#element_'+this.value).remove();
			
			var elem_nm = $(".dataTable tr#"+this.value+" td.elem_nm").text();
			var elem_desc = $(".dataTable tr#"+this.value+" td.elem_desc").text();
			var elem_rt = $(".dataTable tr#"+this.value+" td.elem_rt").text();
			
			// build row
			$row = '<tr id="element_'+this.value+'">';
			$row += '<td><a class="remove_tr" href="#">Remove</a></td>';
			$row += '<td>'+elem_nm+'<input type="hidden" name="elem_id[]" value="'+this.value+'" /></td>';
			$row += '<td>'+elem_desc+'</td>';
			$row += '<td>'+elem_rt+'</td>';
			$row += '</tr>';
			
			// append to table
			$('table.result_table').append($row);
		});
	});
});