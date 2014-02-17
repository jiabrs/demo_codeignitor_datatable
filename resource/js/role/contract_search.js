/**
 * @author cbrogan
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
		$.ajax({
			url: this.action,
			type: 'POST',
			dataType: 'json',
			data: $('form#search_form').serialize()+'&'+$(this).serialize(),
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