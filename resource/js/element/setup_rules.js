/*
 * Javascript functions associated with element/setup_rules controller
 */
$(document).ready(function(){
	
	/*
	 * Rate type option may affect which fields are visible
	 * or how the fields are labeled
	 */
	$('input[name="elem_tp"]').change(function () {
		
		// get the currently selected value
		var $sel_opt = $('input[name="elem_tp"]:checked').val()
		
		switch ($sel_opt)
		{
			case 'TU':
				// set pct label to '% of Last Year'
				$('label[for="pct"]').text('% of Last Year');
				break;
			case 'NP':
				// set pct label to '% of NSI'
				$('label[for="pct"]').text('% of NSI');
				break;
			default:
				// set pct label to '% of Last Year'
				$('label[for="pct"]').text('% of Last Year');
		}
	});
	
	/*
	 * Hide fields not relevant to cma elements
	 */
	if ($('input[name="app"]').val() == 'CM')
	{
		$('input[name="share"]').parents('.field_row').hide();
	}
	
	/*
	 * Adds autocomplete to funding template lookup
	 * When user selects options, template options
	 * are applied to page
	 */
	$('input#lookup_template').autocomplete({
		source: function(request, response) {
			$.ajax({
				url: $('input#lookup_template').attr('data-href'),
				data: {
					app: $('input[name="app"]').val(),
					search: request.term
				},
				type: "POST",
				dataType: "json",
				success: function(data) {
					response(data);
				}
			})		
		},
		minLength: 2,
		select: function(event, ui) {
			// selection made, retrieve template options
			$.ajax({
				url: 'get_fund_temp_options',
				data: {
					template: $('input#lookup_template').val()
				},
				type: "POST",
				dataType: "json",
				success: function(data) {
					// for each template option . . .
					for (option in data)
					{
						// find field in form
						$('input[id^="'+option+'"]')
							// set field's value
							.val([data[option]])
							// trigger change event on field 
							.change()
							// highlight for 10 seconds so user knows field value was changed
							.effect('highlight','',10000);
					}
				}
			})
		}
	});
});