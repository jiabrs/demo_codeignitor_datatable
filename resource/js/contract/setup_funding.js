/*
 * element/setup_criteria controller related javascript functions
 */
$(document).ready(function() {
	
	setDatePicker($('input.elem_dt'));
	
	$('a#lu_elem').click(function(event) {
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
				url: $("a#lu_elem").attr('href'),
				data: $("form").serialize(),
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
	});
	
	$('a#lu_pgm').click(function(event) {
		event.preventDefault();
			
		// make it a dialog
		$('div#pgm_lookup').dialog({
			modal: true,
			draggable: true,
			resizable: false,
			autoResize: true,
			position: [180,150],
			height: "auto",
			width: "500px",
			close: function(event, ui) {
				$(this).dialog("destroy"); // hide dialog on close
			}
		});
		
		// if the lookup table doesn't exist, load it from server.
		if ($("div#pgm_lookup table").length == 0)
		{
			$('span#pgm_lookup_loading').show();
			
			$.ajax({
				type: 'POST',
				url: $("a#lu_pgm").attr('href'),
				data: $("form").serialize(),
				beforeSend: function () {
					$('span.ui-dialog-title').html('Loading Programs <span class="ico_working"></span>');
				},
				error: function (xhr, errortext) {
					$('span.ui-dialog-title').html('Loading Programs Failed: '+errortext);
				},
				complete: function () {
					$('span.ui-dialog-title').html('&nbsp;');
				},
				success: function(data) {
					$("div#pgm_lookup").append(data);	
					$('div#pgm_lookup table.dataTable').dataTable({
						"bLengthChange": false,
						"bJQueryUI": true,
						"bAutoWidth": false,
						"iDisplayLength": 10,
						"aoColumns": [
						              {"bSearchable": false,"bSortable": false, "sWidth": "3.3em"}, /* actions */
						              {"sWidth": "450px"} /* Name */              
						]
					});
				},
				dataType: 'HTML'
			});	
		}
	});
	
	/*
	 * Adds date picker to element date fields with special restrictions to prevent 
	 * mixing dates
	 */
	$('input.elem_dt').live('click', function() {
		setDatePicker(this);
	});
	
	$('a.elem_remove_dt').live('click', function(event) {
		event.preventDefault();
		
		$(this).parent('span.dt_range').remove();
	});
	
	$('a.elem_add_dt').live('click', function(event) {
		event.preventDefault();
		
		var elemID = $(this).attr('elem_id');		
		
		var strtDtParts = $('input[name="element['+elemID+'][end_dt][]"]:last').val().split('/');

		strtDt = new Date(strtDtParts[2], strtDtParts[0]-1, strtDtParts[1]);
		
		var endDt = convertDate($('input[name="end_dt"]').val());		
				
		if (strtDt.tommddyyyy('/') == endDt)
		{
			alert('The current element date ranges must be adjusted to make room for another date range.');			
		}
		else
		{
			// add a day to strtDt
			strtDt.setDate(strtDt.getDate()+1);
			
			var dtRange = '<span class="dt_range">';
			dtRange += '<input type="text" class="elem_dt" name="element['+elemID+'][strt_dt][]" value="'+strtDt.tommddyyyy('/')+'" size="10" /> - ';
			dtRange += '<input type="text" class="elem_dt" name="element['+elemID+'][end_dt][]" value="'+endDt+'" size="10" />';
			dtRange += ' <a href="#" class="elem_remove_dt">Remove</a><br /></span>';
			
			$('li#element_'+elemID+' span.dt_range:last').after(dtRange);
			
			$('li#element_'+elemID+' input.elem_dt').each(function(index) {
				setDatePicker($(this));
			});
		}		
	});
	
	$('div#elem_lookup button.add').live('click', function(event) {
		event.preventDefault();
		
		var elem_id = this.value;
		
		if ($('li#element_'+elem_id).length == 0)
		{
			$.ajax({
				type: 'POST',
				url: '/contract/j_get_setup_fnd_row/',
				data: $('#setup_funding_form').serialize()+'&add_elem_id='+elem_id,
				beforeSend: function () {
					$('span.ui-dialog-title').html('Adding element to contract <span class="ico_working"></span>');
				},
				error: function (xhr, errortext) {
					$('span.ui-dialog-title').html('Add element Failed: '+errortext);
				},
				complete: function () {
					$('span.ui-dialog-title').html('&nbsp;');
				},
				success: function(data) {
					$('ul#elements').append(data);
					$('li#element_'+elem_id+' input.elem_dt').each(function(index) {
						setDatePicker($(this));
					});
				},
				dataType: 'HTML'
			});	
		}	
	});	
	
	$('div#pgm_lookup button.add').live('click', function(event) {		
		event.preventDefault();

		var pgm_id = this.value;
		
		var cont = false;
		
		if ($('input[name="pgm_id"]').length > 0)
		{	
			$( "div#confirm_pgm_remove" ).dialog({
				resizable: false,
				height: 'auto',
				modal: true,
				buttons: {
					"Continue": function() {
						add_pgm(pgm_id);
						$( this ).dialog( "close" );
					},
					Cancel: function() {
						cont = false;
						
						$( this ).dialog( "close" );
					}
				}
			}); 
		}
		else
		{
			add_pgm(pgm_id);
		}
	});
	
	$('button.remove_program').live('click', function(event) {
		event.preventDefault();
		$('li.program').remove();
		$('li.program_element').remove();
	});
});

function add_pgm(pgm_id)
{
	$.ajax({
		type: 'POST',
		url: '/program/j_get_elems_for_cntrct/',
		data: $('#setup_funding_form').serialize()+'&add_pgm_id='+pgm_id,
		beforeSend: function () {
			$('span.ui-dialog-title').html('Searching for elements in program <span class="ico_working"></span>');
		},
		error: function (xhr, errortext) {
			$('span.ui-dialog-title').html('Program lookup failed: '+errortext);
		},
		complete: function () {
			$('span.ui-dialog-title').html('&nbsp;');
		},
		success: function(data) {
			// remove existing programs
			$('span.ui-dialog-title').html('Adding Elements <span class="ico_working"></span>');
			
			$('li.program').remove();
			$('li.program_element').remove();
			
			$('ul#elements').prepend(data);
			
			$('span.ui-dialog-title').html('&nbsp;');
			
			$('li.program_element input.elem_dt').each(function(index) {
				setDatePicker($(this));
			});
		},
		dataType: 'html'
	});	
}

function setDatePicker(input)
{
	var strtDt = $('input[name="strt_dt"]').val();
	var endDt = $('input[name="end_dt"]').val();

	$(input).datepicker({
			showOn:'focus',
			maxDate: convertDate(endDt), 
			minDate: convertDate(strtDt),
			autoSize: true,
			constrainInput: true,
			changeMonth: true,
			onSelect: function (selectedDate, inst) {
				// find previous end date
				var prevEndDate = $(this).parents('span.dt_range').prev('span.dt_range').children('input.elem_dt:last').val();

				// convert to date object
				var selectDtPrts = selectedDate.split('/');
				var prevEndDtPrts = prevEndDate.split('/');
				
				var selectDt = new Date(selectDtPrts[2], selectDtPrts[0]-1, selectDtPrts[1]);
				
				var prevEndDt = new Date(prevEndDtPrts[2], prevEndDtPrts[0]-1, prevEndDtPrts[1]);
				
				// make sure they are at least 1 day apart
				if (prevEndDt >= selectDt)
				{
					alert('Start date must be greater than end date of previous date range');
					
					selectDt.setDate(prevEndDt.getDate()+1)
					
					$(this).val(selectDt.tommddyyyy('/'));
				}
			}
	});
}