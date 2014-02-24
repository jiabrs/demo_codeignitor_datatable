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
		              {"bSearchable": false,"bSortable": false, "sWidth": "80px"}, /* actions */
		              {"sWidth": "60px"}, /* ID */
		              {"sWidth": "200px"}, /* Name */
		              null /* Description */
		]
	});
});
