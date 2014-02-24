/**
 *  cbrogan
 */
$(document).ready(function(){
	
	$("div#dialog-modal").dialog({
		modal: true,
		disabled: true,
		autoOpen: false,
		height: 350,
		width: 800,
		resizable: false,
		buttons: {
			"Cancel": function () {
				$(this).dialog('close');
			},
			"OK": function () {
				
				$('ul#contract_filter').empty();
				
				$('select#assign option').each( function () {
					var li = '<li><input type="hidden" name="cntrct_id[]" value="'+$(this).val()+'" />'+$(this).text()+'</li>';
					$('ul#contract_filter').append(li);
				});
				$(this).dialog('close');
			}
		},
		open: function (event, ui) {
			searchContracts($('input#search').val());
			
			if ($('input#search').val() != '')
			{
				$("label.placeholder").hide();
			}
		}
	});	
	
	$("a#lookup_contract").click(function(event) {
		event.preventDefault();
		
		$("div#dialog-modal").dialog('open');
	});
	
	var keyDelay;
	$("input#search")
		.focus(function() {
			$("label.placeholder").hide();
		})
		.keyup(function (event) {
			clearTimeout(keyDelay);
			var delay = 650;
			var search = this.value;
						
			keyDelay = setTimeout(function() {
				searchContracts(search);				
			}, delay);
			
		});
	
	$('div#center button[type="submit"]').click(function (event) {
		var val = $(this).val();
		if (val == 'assign')
		{
			$('select#result option:selected').each( function () {
				$('select#assign').append($(this));
			});
		} else if (val == 'remove') {
			$('select#assign option:selected').each( function () {
				$('select#result').append($(this));
			});
		}
		
		return false;
	});
	
});

function searchContracts(search)
{
	$.ajax({
		url: $('input#search').data("url"),
		type: 'POST',
		dataType: 'json',
		data: $('form#search_form').serialize()+'&search='+search,
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
	})
}