/*
 * element/setup_criteria controller related javascript functions
 */
$(document).ready(function() {
	
	/*
	 * notifies user what the contract naming pattern will be when multi-setup
	 * has been enabled
	 */
	$('input[name="multi_setup"]').change(function (event) {
		if ($('input[name="multi_setup"]:checked').val() == 'multi')
		{
			$('span#multi_setup_name').show();
		}
		else
		{
			$('span#multi_setup_name').hide();
		}
	});
	
	/*
	 * Grabs entered customer codes and submits to be 
	 * turned into customers and added to form
	 */
	$('button#add_cust').click(function (event) {
		
		// prevent form submission
		event.preventDefault();
		
		$.ajax({
			url: $('textarea#quick_add').attr('data-href'),
			data: $('div.std_form form').serialize(),
			type: "POST",
			dataType: "json",
			beforeSend: function () {
				$('span#quick_add_sts').html('Searching customers <span class="ico_working"></span>');
			},
			error: function (xhr, errortext) {
				$('span#quick_add_sts').html('Error searching customers: '+errortext);
			},
			success: function(data) {
				var customers = data;
				
				var custCount = 0;
				
				for (i in customers)
				{
					// hide no results row
					$('li.no_results').hide();
					
					// remove if already exists
					$('li#customer_'+customers[i].cust_cd).remove();
					
					var li = '<li id="customer_'+customers[i].cust_cd+'">';
					li += '<button class="remove_li ui-button ui-widget ui-state-default ui-corner-all"><span class="ui-icon ui-icon-trash"></span></button>&nbsp;';
					li += customers[i].cust_nm + ' (' + customers[i].cust_tp + ':' + customers[i].cust_cd +')';
					li += '<input type="hidden" name="cust_cd[]" value="'+customers[i].cust_cd+'" />';
					li += '<input type="hidden" name="cust_tp[]" value="'+customers[i].cust_tp+'" /></li>';
					
					
					$('ul#customers').append(li);
					custCount++;				
				}
				
				if (custCount > 0)
				{
					$('textarea#quick_add').html("");
					var s = '';
					if (custCount > 1) s = 's';
					$('span#quick_add_sts').html('Added '+custCount.toString()+' customer'+s);
				}
				else
				{
					$('span#quick_add_sts').html('No customers found');
				}
			}
		});	
	});
	
	/*
	 * Loads advanced customer search form as modal window
	 */
	$('#adv_search').click(function(event) {
		event.preventDefault();
		
		// make it a dialog
		$('div#adv_search_dialog').dialog({
			modal: true,
			draggable: false,
			resizable: false,
			height: 'auto',
			width: 'auto',
			close: function(event, ui) {
				$(this).dialog("destroy"); // hide dialog on close
			}
		});
		
		// Put the cursor in the customer field
		$('input#customer').focus();
	});
	
	/*
	 * submit form via ajax
	 */
	$('form#adv_search_form').submit( function() {
		
		// send form via ajax
		$.ajax({
			url: this.action,
			data: $(this).serialize(),
			type: "POST",
			dataType: "json",
			beforeSend: function() {
				$('span#adv_cust_search_loading').show();
				$('span#search_info').text('');
			},
			complete: function() {
				$('span#adv_cust_search_loading').hide();
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
				
				$('select#cust_found').html($opts);
				
				var $s = '';
				if ($count > 1) $s = 's';
				
				// notify user of what we found
				$('span#search_info').text('Found '+$count.toString()+' '+$('select#cust_return option:selected').text()+$s)
			}
		});	
		
		// prevent non-ajax form submission
		return false;
	});
	
	/*
	 * Adds adv search customers to contract
	 */
	$('button#adv_add_cust').click(function (event) {
		
		// prevent form submission
		event.preventDefault();
		
		var $customers = $('select#cust_found option:selected');
		
		if ($customers.length > 0)
		{
			// hide no results row
			$('li.no_results').hide();
			
			var type = $('select#cust_return option:selected').val();
			var lis = '';
			$customers.each(function() {
				
				// remove if already exists
				$('li#customer_'+this.value).remove();
				
				var label = $(this).text();
				
				lis += '<li id="customer_'+this.value+'">';
				lis += '<button class="remove_li ui-button ui-widget ui-state-default ui-corner-all"><span class="ui-icon ui-icon-trash"></span></button>&nbsp;';
				lis += label;
				lis += '<input type="hidden" name="cust_cd[]" value="'+this.value+'" />';
				lis += '<input type="hidden" name="cust_tp[]" value="'+type+'" /></li>';
			});
			
			$('ul#customers').append(lis);
		}
		
	});
});