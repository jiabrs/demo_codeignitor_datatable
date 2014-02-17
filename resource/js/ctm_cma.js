$(document).ready(function(){
	
	/*
	 * Makes remove buttons modal
	 */
	$('button.remove').live('click', function(event) {
		// prevent default from happening
		event.preventDefault();
		event.stopPropagation();
		
		// grab the parent row.  need it for later
		var $row = $(this).parents('tr').get(0);
		
		// grab href to load form
		var $target = this.value;
		
		// load form via xhr
		$.get($target, function(data) {
			
			// append to end of doc
			$(event.currentTarget).append(data);
			
			// make it a dialog
			var $dialog = $('div#modal_dialog').dialog({
				modal: true,
				draggable: false,
				resizable: false,
				close: function(event, ui) {
					$(this).dialog("destroy"); // hide dialog on close
					$(this).remove(); // remove from dom as well
				}
			});
			
			// override submit to remove item
			$('div#modal_dialog input:submit[value="Continue"]').click(function() {
				// get form 
				var $modform = $('div#modal_dialog form');

				// send response as confirmed removal
				$.ajax({
					type: 'POST',
					url: $modform.attr('action'),
					data: $modform.serialize()+'&confirm=Continue',
					success: function(data, status, xhr) {
						// if successful . . .
						if (data)
						{
							// get position of row
							var $pos = $oTable.fnGetPosition($row)
							// . . . and remove it
							$oTable.fnDeleteRow($pos, null, true);
						}
						// kill dialog
						$dialog.dialog("close");							
					},
					dataType: 'JSON'
				});
				return false;
			});
			
			$('div#modal_dialog input:submit[value="Cancel"]').click(function(event) {
				$dialog.dialog("close");
				return false;
			});
		});
	});
	
	$('button.edit').live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		window.location.href = this.value;
	});
	
	$('button.copy').live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		window.location.href = this.value;
	});
	
	$('button.link').live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		window.location.href = this.value;
	});
	/*
	 * Checks all checkbox inputs when a is clicked
	 */
	$('a.ui_check')
		.show()
		.click(function(event) {
			event.preventDefault();
			$(this).siblings("input:checkbox").attr('checked','checked');
		});
	
	/*
	 * unchecks all checkbox inputs when a is clicked
	 */
	$('a.ui_uncheck')
		.show()
		.click(function(event) {
			event.preventDefault();
			$(this).siblings("input:checkbox").removeAttr('checked');
		});
	
	/*
	 * enables nav accordion 
	 */
	$('#app_nav').accordion({
		autoHeight: false,
		navigation: true,
		navigationFilter: function () {
			var currentHref = location.href.split("/");
			var navHref = this.href.split("/");

			return currentHref[3] == navHref[3];
		}
	});
	
	/*
	 * Removes parent tr from table when anchor is clicked
	 */
	$('a.remove_tr').live('click',function(event) {
		event.preventDefault();
		$(this).parents('tr').remove();
	});
	
	/*
	 * Removes parent li from ol when anchor is clicked
	 */
	$('button.remove_tr').live('click',function(event) {
		event.preventDefault();
		if ($(this).parents('tr').siblings('tr:not(.no_results, .header)').length == 0)
		{
			$('tr.no_results').show()
		}
		
		$(this).parents('tr').remove();		
	});
	
	/*
	 * Removes parent li from ol when anchor is clicked
	 */
	$('button.remove_li').live('click',function(event) {
		event.preventDefault();
		if ($(this).parents('li').siblings('li:not(.no_results)').length == 0)
		{
			$('li.no_results').show()
		}
		
		$(this).parents('li').remove();		
	});
	
	/*
	 * Adds date picker to any date fields
	 */
	$('input.date').live('click', function() {
		$(this).datepicker({showOn:'focus'}).focus();
	});
	
	/*
	 * Used in all location selection elements to select all locations
	 * under specific division
	 */
	$('input.div').click(function() {
		
		if ($(this).attr("checked"))
		{
			$(this).siblings('span.sls_ctr').children('input[type="checkbox"]').attr('checked','checked');
		}
		else {
			$(this).siblings('span.sls_ctr').children('input[type="checkbox"]').removeAttr('checked');
		}
	});
	
	$('div.switch_menu').click(function() {
		$('.tooltip_menu').hide();
		$(this).children('.tooltip_menu').toggle();
	});
	
	$('body').click(function(event) {
		if (event.target.className !== 'switch_menu')
		{
			$('.tooltip_menu').hide();
		}
	});
});

function convertDate(dt)
{
	dtParts = dt.split("-");
		
	return dtParts[1] + "/" + dtParts[2] + "/" + dtParts[0];
}

String.prototype.padLeft = function (length, character) {
    return new Array(length - this.length + 1).join(character || ' ') + this;
};

String.prototype.numberFormat = function () {
	x = this.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

Date.prototype.tommddyyyy = function(sep)
{
	return [
	        String(this.getUTCMonth()+1).padLeft(2, '0'),
	        String(this.getUTCDate()).padLeft(2, '0'),
	        String(this.getUTCFullYear())	        
	].join(sep || '');
}