/**
 *  cbrogan
 */
$(document).ready(function(){
	$oTable = $('.dataTable').dataTable({
		"bJQueryUI": true,
		"bAutoWidth": false,
		"iDisplayLength": 10,
		"aaSorting": [[1,'asc']],
		"aoColumns": [
		              {"bSearchable": false,"bSortable": false, "sWidth": "110px"}, /* actions */
		              {"sWidth": "200px"}, /* Name */
		              {"bSortable": false} /* Criteria */		              
		]
	});
});