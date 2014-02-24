/**
 *  cbrogan
 */
$(document).ready(function(){
	
	hide_older_notes();
	
	$('a.show_more').live('click', function(event) {		
		event.preventDefault();
		
		$(this).siblings('span.more').show();
		$(this).siblings('a.show_less').show();
		$(this).hide();
		
	});
	
	$('a.show_less').live('click', function(event) {		
		event.preventDefault();		
		
		$(this).siblings('a.show_more').show();
		$(this).siblings('span.more').hide();
		$(this).hide();
		
	});
	
	$('a#add_note').click(function(event) {
		event.preventDefault();
		
		// make it a dialog
		$('div#note_dialog')
			.load(event.target.href)
			.dialog({
				modal: true,
				draggable: true,
				resizable: false,
				autoResize: true,
				position: 'center',
				height: "auto",
				width: "auto",
				buttons: {
					"Add Note": function () {
						// submit form
						$('form#note_form').ajaxSubmit({
							beforeSend: function () {
								$('span.ui-dialog-title').html('Adding Note <span class="ico_working"></span>');
							},
							error: function (xhr, errortext) {
								$('span.ui-dialog-title').html('Add Note Failed: '+errortext);
							},
							complete: function () {
								$('span.ui-dialog-title').html('&nbsp;');
							},
							success: function(data) {
								$("ul#cont_notes").prepend(data);
								
								hide_older_notes();
							},
							dataType: 'HTML'							
						});					

						// close dialog
						$(this).dialog("destroy"); // hide dialog on close
					},
					"Cancel": function () {
						$(this).dialog("destroy"); // hide dialog on close
					}
				},
				close: function(event, ui) {
					$(this).dialog("destroy"); // hide dialog on close
				}
			});
	});
	
	$('li.note_entry button.edit_note').live('click', function(event) {	
		event.preventDefault();
		
		var note_id = this.value
		// make it a dialog
		$('div#note_dialog')
			.load('/note/edit/'+note_id)
			.dialog({
				modal: true,
				draggable: true,
				resizable: false,
				autoResize: true,
				position: 'center',
				height: "auto",
				width: "auto",
				buttons: {
					"Update Note": function () {
						// submit form
						$('form#note_form').ajaxSubmit({
							beforeSend: function () {
								$('span.ui-dialog-title').html('Updating Note <span class="ico_working"></span>');
							},
							error: function (xhr, errortext) {
								$('span.ui-dialog-title').html('Update Note Failed: '+errortext);
							},
							complete: function () {
								$('span.ui-dialog-title').html('&nbsp;');
							},
							success: function(data) {
								
								$('li#note_'+note_id).remove();
								
								$("ul#cont_notes").prepend(data);
								
								hide_older_notes();
							},
							dataType: 'HTML'							
						});				
												
						// close dialog
						$(this).dialog("destroy"); // hide dialog on close
					},
					"Cancel": function () {
						$(this).dialog("destroy"); // hide dialog on close
					}
				},
				close: function(event, ui) {
					$(this).dialog("destroy"); // hide dialog on close
				}
			});
	});
	
	$('li.note_entry button.remove_note').live('click', function(event) {	
		event.preventDefault();
		
		var note_id = this.value
		
		$('div#note_dialog')
		.html("Are you sure you want to remove this note?")
		.dialog({
			modal: true,
			draggable: true,
			resizable: false,
			autoResize: true,
			position: 'center',
			height: "auto",
			width: "auto",
			buttons: {
				"Cancel": function () {
					$(this).dialog("destroy"); // hide dialog on close
				},
				"Remove Note": function () {
					// submit form
					$.ajax({
						url: "/note/remove/"+note_id,
						beforeSend: function () {
							$('span.ui-dialog-title').html('Removing Note <span class="ico_working"></span>');
						},
						error: function (xhr, errortext) {
							$('span.ui-dialog-title').html('Remove Note Failed: '+errortext);
						},
						complete: function () {
							$('span.ui-dialog-title').html('&nbsp;');
						},
						success: function(data) {	
							if (data)
							{
								$('li#note_'+note_id).remove();
								
								hide_older_notes();
							}
						},
						dataType: 'json'							
					});				
											
					// close dialog
					$(this).dialog("destroy"); // hide dialog on close
				}
			},
			close: function(event, ui) {
				$(this).dialog("destroy"); // hide dialog on close
			}
		});
	});
	
	$('a#more_notes').click(function(event) {
		event.preventDefault();
		
		$('li.note_entry').show();	
		$('a#more_notes').hide();
		$('a#less_notes').show();
	});
	
	$('a#less_notes').click(function (event) {
		event.preventDefault();
		
		hide_older_notes();
	});
});

function hide_older_notes()
{
	$('a#less_notes').hide();
	$('li#note_opts').show();
	
	$('ul#cont_notes li.note_entry').each(function (index) {
		if (index < 2)
		{	
			$(this).show();
		}
		else
		{
			$(this).hide();
		}
	});
	
	noteCnt = $('ul#cont_notes li.note_entry').length;
	
	if (noteCnt < 3)
	{
		$('a#more_notes').hide();
	}
	else
	{
		$('a#more_notes').show();
	}
	
	if (noteCnt == 0)
	{
		$('li#no_notes').show();
	}
	else
	{
		$('li#no_notes').hide();
	}
}