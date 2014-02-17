/**
 * @author cbrogan
 */
$(document).ready(function(){
	$oTable = $('.dataTable').dataTable({
		"bJQueryUI": true,
		"bAutoWidth": false,
		"iDisplayLength": 10,
		"aaSorting": [[1,'asc']],
		"aoColumns": [
		              {"bSearchable": false,"bSortable": false}, /* actions */
		              null, /* Name */
		              {"bSortable": false} /* Criteria */		              
		]
	});
});
