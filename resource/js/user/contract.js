/**
 * 
 */
$(document).ready(function(){
	$("#cntrct_lookup").submit(function () {
		$.ajax({
			url: $(this).attr("action"),
			type: 'POST',
			dataType: 'json',
			data: $(this).serialize(),
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
		return false;
	});
	
	$('button[type="submit"]').click(function (event) {

		$.ajax({
			url: $('form.assign_widget').attr("action"),
			type: 'POST',
			dataType: 'text',
			data: $('form.assign_widget').serialize()+'&action='+this.value,
			beforeSend: function() {
				$('span#assign_result').html('Saving changes <span class="ico_working"></span>');
			},
			error: function() {
				$('span#assign_result').html('Save failed');
			},
			success: function(data) {
				$('span#assign_result').html('');

				if (data == 'assign')
				{
					$('select#result option:selected').each( function () {
						$('select#assign').append($(this));
					});
				} else if (data == 'remove') {
					$('select#assign option:selected').each( function () {
						$('select#result').append($(this));
					});
				}
				
			}
		});
		
		return false;
	});
	
	$('form.assign_widget').submit(function () {
		return false;
	});
});