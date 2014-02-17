/*
 * element/setup_criteria controller related javascript functions
 */
$(document).ready(function() {
	
	/*
	 * Highlight field if on invoice is selected
	 */  	
	flag_on_inv();
	
	$('input[name="on_inv_flg"]').change(function () {
		flag_on_inv();
	});	
});

function flag_on_inv()
{
	if ($('input[name="on_inv_flg"]:checked').val() == 'Y')
	{
		$('label[for="on_inv_flg_N"]').after('<span id="on_inv_warn" class="ui-state-error" style="padding: 3px;margin-left: 5px;">On Invoice Selected</span>')
	}
	else
	{
		$('span#on_inv_warn').remove();
	}
}