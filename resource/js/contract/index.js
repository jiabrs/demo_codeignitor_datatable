/**
 *  cbrogan
 */
$(document).ready(function(){
	$oTable = $('.dataTable').dataTable({
		"bJQueryUI": true,
		"bAutoWidth": false,
		"iDisplayLength": 10,
		"aoColumns": [
		              {"bSearchable": false,"bSortable": false}, /* actions */
		              {"sClass": "right", "sWidth": "50px"}, /* ID */
		              {"sWidth": "225px"}, /* Name */
		              null, /* Program */
		              {"bSearchable": false, "sWidth": "80px"}, /* Start */
		              {"bSearchable": false, "sWidth": "80px"} /* End */		              
		],
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "/contract/get_datatable",
		"bStateSave": true,
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			$.ajax( {
				"dataType": 'json', 
				"type": "POST", 
				"url": sSource, 
				"data": aoData, 
				"success": fnCallback
			} );
		}
	});
	
	$oTable.fnSetFilteringDelay(600);
	
	$('table.dataTable tr').live('click', function (event) {
		var row = this;
		
		if ($(row).hasClass('detail_open'))
		{
			$oTable.fnClose(row);
			$(row).removeClass('detail_open');
		}
		else if ($(row).hasClass('detail_row_data'))
		{
			$oTable.fnClose(row);
			event.stopPropagation();
		}
		else 
		{
			var aData = $oTable.fnGetData(row)
			
			$.post('/contract/get_datatable_more_info/'+aData[1], function(data) {
				$(row).addClass('detail_open');
				$oTable.fnOpen(row, data, 'detail_row');
			});	
		}
		
	});

});
