/**
 * @author cbrogan
 */
$(document).ready(function(){
	
	$('a.view_note').cluetip({
		activation: 'click',
		attribute: 'href',
		showTitle: false,
		sticky: true,
		closeText: '<span class="ui-icon ui-icon-close"></span>',
		closePosition: 'title',
		arrows: true,
		cursor: 'hand',
		ajaxSettings: {
			dataType: "json"
		},
		ajaxProcess: function(data) {
			var popUp = '<ul class="tooltip">';
			
			for (i in data)
			{
				popUp += '<li><em><b>' + data[i]['lst_updt_tm'] + '</b></em>: ' + data[i]['body'];
				
				if (data[i]['file_nm'] != '') 
					popUp += '<a href="note/view_file/' + i + '" title="' + data[i]['file_nm'] + '"><span class="css-inline_block ui-icon ui-icon-document-b"></span></a>';
				
				popUp += '</li>'
			}
			
			popUp += '</ul>';
			
			return popUp
		}
	});
	
	$('a#download_xls').click(function (event) {
		event.preventDefault();
		
		$('form#download_xls_form').submit();
	});
	
	$('a#download_pdf').click(function (event) {
		event.preventDefault();
		
		$('form#download_pdf_form').submit();
	});
});