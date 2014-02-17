<?php 
$view = '';
$curr_vend_no = NULL;
$n = new NumberFormatter("en-US", NumberFormatter::ORDINAL); 

foreach ($rows as $row) 
{
	if ($curr_vend_no != $row->VEND_NO)
	{
		if ($curr_vend_no !== NULL)
			$view .= '<tr class="total"><td></td><td></td><td></td><td>Check Total . . .</td>'
				.'<td class="amount">$'.number_format($check_tot, 2).'</td></tr>'
				.'</tbody></table>';
		
		$check_tot = 0;
		$view .= '<p>Check Stub for Vendor # '.$row->VEND_NO.'</p>
			<table class="check_stub">
			<thead>
				<tr class="header">
					<th>YOUR INVOICE NUMBER</th>
					<th>INVOICE DATE</th>
					<th>INVOICE AMOUNT</th>
					<th>DISCOUNT TAKEN</th>
					<th>NET CHECK AMOUNT</th>
				</tr>
			</thead>
			<tbody>';
	}
	
	$check_tot += $row->TOT_AMT;
	
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
				
	$tr = '<tr><td>'.$row->CHECK_REQ_REF.'<br /><span class="check_txt">'.$text.'</span></td>'
		.'<td>'.date('m/d/Y').'</td>'
		.'<td class="amount">'.number_format($row->TOT_AMT, 2).'</td>'
		.'<td class="amount">'.number_format(0, 2).'</td>'
		.'<td class="amount">'.number_format($row->TOT_AMT, 2).'</td>';
				
	$view .= $tr;
					
	$curr_vend_no = $row->VEND_NO;
}		

$view .= '<tr class="total"><td></td><td></td><td></td><td>Check Total . . .</td>'
	.'<td class="amount">$'.number_format($check_tot, 2).'</td></tr>'
	.'</tbody></table>';
	
echo $view;