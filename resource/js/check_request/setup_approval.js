/*
 * element/setup_criteria controller related javascript functions
 */
$(document).ready(function() {
	
	
	
	$('button#add_usr').click(function(event) {
		
		event.preventDefault();
		
		// make it a dialog
		$('div#adv_search_dialog').dialog({
			modal: true,
			draggable: true,
			resizable: true,
			height: 'auto',
			width: 'auto',
                       	close: function(event, ui) {
				$(this).dialog("destroy"); // hide dialog on close
                           
			        
                        }
                        
                        
     
                        
		});
	});
	
	
	
	$('form#adv_search_form').submit( function() {
		
		// send form via ajax
		$.ajax({
			url: this.action,
			data: $(this).serialize(),
			type: "POST",
			dataType: "json",
                        beforeSend: function() {
				$('span#adv_usr_search_loading').show();
				$('span#search_info').text('');
			},
			complete: function() {
				$('span#adv_usr_search_loading').hide();
			},
			success: function(data) {
				// append results to select
				var $opts = '';
				var $count = 0;
				for (var $opt in data)
				{
					$opts += '<option value="'+$opt+'">'+data[$opt]+'</option>';
					$count++;
				}
				
				$('select#usr_found').html($opts);
				
				var $s = '';
				if ($count > 1) $s = 's';
				
				// notify user of what we found
				$('span#search_info').text('Found '+$count.toString()+' Users')
			}
		});	
		
		// prevent non-ajax form submission
		return false;
	});
        
        
        
        $('button#adv_add_usr').click(function (event) {
		
		// prevent form submission
		event.preventDefault();
		
		var $usrs= $('select#usr_found option:selected');
		
		if ($usrs.length > 0)
		{
			// hide no results row
			$('li.no_results').hide();
			
			var lis = '';
			$usrs.each(function() {
				
				// remove if already exists
				$('li#usr_'+this.value).remove();
				
				var label = $(this).text();              	                                                                           
                       
                                
                                             
                                
                                
                                lis += '<li  id="usr_'+this.value+'" class="lststynone">';
				lis += '<button class="remove_li ui-button ui-widget ui-state-default ui-corner-all"><span class="css-inline_block  ui-icon ui-icon-trash"></span></button>&nbsp;';
				lis += label;
                         	lis += '<input type="hidden" name="usr_lst[]" value="'+this.value+'" /></li>';
                                
                                
				
			});
			
			$('ul#usrs').append(lis);
		}
		
	});
        
});