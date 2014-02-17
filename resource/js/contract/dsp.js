/**
 * @author cbrogan
 */
$(document).ready(function(){
	$('div.elem_stats').tabs();
	
	/* Toggle on parent elements kills event propagation, 
	 * so live click event is never triggered. Explicitly assign click event
	 */
	$('button.link').click( function(event) {
		event.preventDefault();
		event.stopPropagation();
		window.location.href = this.value;
	});
	
	$('div.elem_summ').toggle(
		function() {
			$(this).siblings('.elem_dtls').show();
		},
		function() {
			$(this).siblings('.elem_dtls').hide();
		}
	);	
	
	$('a#show_usrs').click(function(event) {
		event.preventDefault();
		event.stopPropagation();
		
		$.ajax({
			url: event.target.href,
			type: "GET",
			dataType: "html",
			success: function(data) {
				$(data).dialog({
					modal: true,
					draggable: false,
					resizable: false,
					height: 'auto',
					maxHeight: 400,
					width: 900,
					close: function(event, ui) {
						$(this).dialog("destroy"); // hide dialog on close
					}
				});
			}
		});
	});
	
	$('a#pull_sls').click(function (event) {
		event.preventDefault();
		event.stopPropagation();
		var cntrctId = $(this).attr('data-cntrct_id');
		$.ajax({
			url: '/contract/pull_sls',
			data: {
				cntrct_id: cntrctId
			},
			type: "POST",
			dataType: "text",
			beforeSend: function () {
				$('span#strt_sls_pull_sts').html(': Starting sales pull <span class="ico_working"></span>');
			},
			error: function (xhr, errortext) {
				$('span#strt_sls_pull_sts').html(': Failed to start sales pull: '+errortext);
			},
			success: function(data) {

				$('span#strt_sls_pull_sts').html(': Sales pull started');
			}
		});
	});
	
	$('div#sls_pull_sts').dialog({
		autoOpen: false, 
		modal: true,
		draggable: true,
		resizable: false,
		height: 400,
		width: 600,
		close: function(event, ui) {
			$(this).html('');			
		},
		buttons: {
			"Refresh Log": function () {
				get_pull_sls_log();
			}
		},
	});
	
	$('a#show_sls_pull_log').click(function (event) {
		event.preventDefault();
		event.stopPropagation();
		
		get_pull_sls_log();
			
		$('div#sls_pull_sts').dialog("open");
	});
	
	function get_pull_sls_log()
	{
		$.ajax({
			url: $('a#show_sls_pull_log').attr('href'),
			type: "GET",
			dataType: "html",
			beforeSend: function () {
				$('div#sls_pull_sts').html('Refreshing log <span class="ico_working"></span>');
			},
			error: function () {
				$('div#sls_pull_sts').html('Error pulling log');
			},
			success: function(data) {
				$('div#sls_pull_sts').html(data);
			}
		});
	}
});
