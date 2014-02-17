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
		              {"bSearchable": false,"bSortable": false,"sWidth": "110px"}, /* actions */
		              {"sWidth": "60px"}, /* User ID */
		              {"sWidth": "140px"}, /* Last */
		              {"sWidth": "140px"}, /* First */
		              {"sWidth": "140px"}, /* Login */
		              {"sWidth": "80px"}, /* Enabled */
		              null, /* Last Login */
		              {"sWidth": "100px"} /* Created */
		]
	});
});
