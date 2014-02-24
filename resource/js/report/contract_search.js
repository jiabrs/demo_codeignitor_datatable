/**
 *  cbrogan
 */
$(document).ready(function(){
	
	$("input#search").focus(function() {
		$("label.placeholder").hide();
	});
	
	$("div#dialog-modal").dialog({
		modal: true,
		disabled: true,
		autoOpen: false,
		height: 350,
		width: 500,
		resizable: false,
		buttons: {
			"Close": function () {
				$(this).dialog('close');
			}
		}
	});	
	
	$("a#lookup_contract").click(function(event) {
		event.preventDefault();
		
		$("div#dialog-modal").dialog('open');
	});
	
	$('form#search').submit(function() {
		
		// need a fix to change accr_yr to yr in post data
		var yr = $('select#accr_yr option:selected').val();
		
		// make sure they have provided a location
		if ($('input[name="sls_ctr[]"]:checked').length == 0)
		{
			$('span#search_result').html('Select a location');
			
			return false;
		}
	
		$.ajax({
			url: this.action,
			type: 'POST',
			dataType: 'json',
			data: $('form#search_form').serialize()+'&'+$(this).serialize()+'&yr='+yr,
			beforeSend: function() {
				$('span#search_result').html('Searching <span class="ico_working"></span>');
			},
			error: function() {
				$('span#search_result').html('Search failed');
			},
			success: function(data) {
				var opts = '';
				var count = 0;
				for (var opt in data)
				{
					opts += '<option value="'+opt+'">'+data[opt]+'</option>';
					count++;
				}
				
				$('select#result').html(opts);
				
				var s = '';
				if (count != 1) s = 's';
				
				// notify user of what we found
				$('span#search_result').text('Found '+count.toString()+' contract'+s)
			}
		});
		
		return false;
	});	
	
	$('form#search_results').submit(function() {
		
		var li = '';
		
		$('select#result option:selected').each( function () {
			li += '<li>';
			li += '<button class="remove_li ui-button ui-widget ui-state-default ui-corner-all"><span class="ui-icon ui-icon-trash"></span></button>&nbsp';
			li += '<input type="hidden" name="cntrct_id[]" value="'+this.value+'" />';
			li += $(this).text()+'</li>';
		});
		
		$('ul#contract_filter').append(li);
		
		return false;
	});
});