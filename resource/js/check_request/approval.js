
$(document).ready(function(){

	$oTable = $('.dataTable').dataTable({
		"bJQueryUI": true,
		"bAutoWidth": false,
		"iDisplayLength": 10,
		"aoColumns": [
                    {"bSearchable": false,"bSortable": false, "sWidth": "90px"}, /* actions */		              
		              null, /* User List */
                              null, /* Location */
                              {"sWidth": "180px"}/* Return User */
		        	              		              
		],
                
		"bStateSave": true,
                
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "/approval/get_datatable",
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
	
        
        /*
        $('div.form_title').toggle(
		
		function() {
			$(this).siblings('.form_content').show();
                   $(this).find("img").attr({src:'/resource/images/16-arrow-DESC.png'});

		},
                function() {
			$(this).siblings('.form_content').hide();
                      $(this).find("img").attr({src:'/resource/images/16-arrow-ASC.png'});
		}
	);
            
            
            	
	$('a#download_pdf_1').click(function (event) {
		event.preventDefault();
		
		$('form#download_pdf_form_1').submit();
	})
        
        $('a#download_pdf_2').click(function (event) {
		event.preventDefault();
		
		$('form#download_pdf_form_2').submit();
	})
        
        */
});
