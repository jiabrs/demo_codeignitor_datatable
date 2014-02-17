<?php 
	echo form_open('demo/print_check_req', 'id="print_check_req"'); 
	echo '<span class="filter">Approval List:</span>&nbsp;'.implode(', ', $approvers)
		.'<br /><span class="filter">Payment Type:</span>&nbsp;Quarterly'
		.'<br /><span class="filter">Term:</span>&nbsp;1st Quarter 2012'
		.'<br /><span class="filter">Locations:</span>&nbsp;GULFPORT, HATTIESBURG, MCCOMB<br /><br />';
		
	$status_dsp = '';
	switch($status)
	{
		case 'ready':
			$status_dsp = "Ready to print";
			echo '<input type="submit" value="Print Selected Check Requests" />';
			break;
		case 'printed':
			$status_dsp = "Printed";
			echo '<input type="submit" value="Void Selected Check Requests" />';
			break;
	}
?>
<table id="detail_accr">
	<thead>
		<tr class="header">
			<th></th>
			<th>Element</th>
			<th>Sales Center</th>
			<th>Date Range</th>
			<th>Rate</th>
			<th>Cases</th>
			<th>Amount</th>
		</tr>
	</thead>
	<tbody><?php 
		$n = new NumberFormatter("en-US", NumberFormatter::ORDINAL); 
		
		$curr_check_req_id = NULL;
		foreach ($rows as $row) 
		{
			if ($curr_check_req_id != $row->CHECK_REQ_ID)
			{
				$tr = '<tr class="cntrct_summ">'
					.'<td><input type="checkbox" name="check_req_id[]" checked="checked" value="'.$row->CHECK_REQ_ID.'"/></td>'
					.'<td colspan="2">'.$row->CNTRCT_NM.'</td>'
					.'<td>'.$status_dsp.'</td>'
					.'<td colspan="2">';
				
				$text = '';
				switch ($row->PYMT_FREQ)
				{
					case '03':
						$text = $n->format($row->CHECK_TXT).' Quarter Payment CMA';
						break;
					case '12':
						$text = $row->CHECK_TXT.' Payment CMA';
						break;
					case 'OD':
						$text = 'CMA Payment';
						break;
					default:
						$text = $row->CHECK_TXT.' Payment CMA';
						break;
				}
				
				// $tr .= '<input type="text" name="check_txt[]" value="'.$text.'" /></td>'
				$tr .= $text.'</td>'
					.'<td class="amount">$ '.number_format($row->TOT_AMT, 2).'</td></tr>';		

					
				echo $tr;
			}
			
			$tr = '<tr class="elem_summ '.alternator('odd','even').'"><td></td><td>'.$row->ELEM_NM.'</td>'
					.'<td>'.$row->SLS_CTR_NM.'</td>'
					.'<td>'.$row->E_STRT_DT.' - '.$row->E_END_DT.'</td>'
					.'<td>'.$row->ELEM_RT.'</td>'
					.'<td>'.number_format($row->CSE_VOL, 0).'</td>'
					.'<td class="amount">$'.number_format($row->ACCR_AMT, 2).'</td>';
			
			echo $tr;
			
			$curr_check_req_id = $row->CHECK_REQ_ID;
		}		
	?></tbody>
</table>
<?php echo form_close(); ?>