<?php 
	$aaData = array();
	foreach ($contracts as $contract)
	{
		$cp_names = '';
		foreach ($contract->cprograms as $cprogram_id => $cprogram)
		{
			$cp_names .= $cprogram->name."<br />";
		}
		
		$buttons = '';
		
		if ($this->authr->authorize('MC', NULL, FALSE))
			$buttons = '<button type="button" title="Edit" class="edit" value="'.site_url('contract/setup_info/'.$contract->contract_id).'"><span class="ui-icon ui-icon-wrench"></span></button>
				<button type="button" title="Copy" class="copy" value="'.site_url('contract/copy/'.$contract->contract_id).'"><span class="ui-icon ui-icon-copy"></span></button>
				<button type="button" title="Remove" class="remove modal" value="'.site_url('contract/remove/'.$contract->contract_id).'"><span class="ui-icon ui-icon-trash"></span></button>';
			
		$aaData[] = array(
			$buttons,
			$contract->name,
			$cp_names,
			$contract->get_start_date('m/d/Y'),
			$contract->get_end_date('m/d/Y')
		);
	}

	$response = array(
		'sEcho' => $sEcho,
		'iTotalRecords' => $iTotalRecords,
		'iTotalDisplayRecords' => $iTotalDisplayRecords,
		'aaData' => $aaData
	);
	
	echo json_encode($response);
/* End of file index_xhr.php */
/* Location: ctm_cma/views/contract/index_xhr.php */