<?php echo form_open('report/detailed_accrual_xls', 'id="download_xls_form"'); ?>
	<?php echo form_hidden('accr_yr', $accrual_yr); ?>
	<?php echo form_hidden('sls_ctr', $sls_ctrs); ?>
	<?php echo form_hidden('inv', $inv); ?>
	<?php echo form_hidden('accr_tp', $accr_tp); ?>
	<?php echo form_hidden('cntrct_id', $cntrct_ids); ?>
<?php echo form_close();?>
<?php echo form_open('report/detailed_accrual_pdf', 'id="download_pdf_form"'); ?>
	<?php echo form_hidden('accr_yr', $accrual_yr); ?>
	<?php echo form_hidden('sls_ctr', $sls_ctrs); ?>
	<?php echo form_hidden('inv', $inv); ?>
	<?php echo form_hidden('accr_tp', $accr_tp); ?>
	<?php echo form_hidden('cntrct_id', $cntrct_ids); ?>
<?php echo form_close();?>
<table id="detail_accr">
	<thead>
		<tr>
			<td colspan="10">
				<em><b>Locations:</b></em> <?php foreach ($this->location->get_by_sls_ctr() as $sls_ctr_cd => $sc_data) { echo $sc_data['short_name'].", "; } ?><br />
				<em><b>Accrual Year:</b></em> <?php echo $accrual_yr; ?><br />
				<em><b>Showing:</b></em> <?php 
					$inv_opts = array();
					foreach ($inv as $type) 
					{
						if ($type == 'Y') $inv_opts[] = 'On Invoice';
						if ($type == 'N') $inv_opts[] = 'Post Invoice';
					}
					echo implode(', ', $inv_opts);	
				?><br />
				<em><b>Accrual Amount:</b></em> <?php echo $accr_tps[$accr_tp]; ?>
			</td>
			<td>
				<?php echo anchor('#', img('resource/images/doc_excel_csv.png'), 'title="Download Excel" id="download_xls"'); ?>&nbsp;&nbsp;
				<?php echo anchor('#', img('resource/images/doc_pdf.png'), 'title="Download PDF" id="download_pdf"'); ?>
			</td>
		</tr>
		<tr class="header">
			<th colspan="2">Element</th>
			<th>OI</th>
			<th>Last Year</th>
			<th>Year-to-date</th>
			<th>Projected</th>
			<th>Total</th>
			<th>Change</th>
			<th>Units</th>
			<th>Rate</th>
			<th>Share</th>
			<th>Accrued</th>
		</tr>
	</thead>
	<tbody>
	<?php 
		$accr_pst = $accr_oi = $cntrct_accr = 0; 
		$cntrct_id = 0;
		$cntrct_row = $elem_row = '';
		
		foreach ($data as $row)
		{
			if ($row->CNTRCT_ID <> $cntrct_id)
			{
				if ($cntrct_id <> 0) $cntrct_row .= '<td class="amount">$ '.number_format($cntrct_accr, 2).'</td></tr>';
				echo $cntrct_row;
				echo $elem_row;
				
				$cntrct_accr = 0;
				$cntrct_id = $row->CNTRCT_ID;
				$cntrct_row = '<tr class="cntrct_summ" data-cntrct_id="'.$row->CNTRCT_ID.'"><td colspan="3">'.anchor('contract/dsp/'.$row->CNTRCT_ID, $row->CNTRCT_NM);
				
				if ($row->NOTE_CNT > 0) 
					$cntrct_row .= anchor('contract/j_get_notes/'.$row->CNTRCT_ID, '<span class="css-inline_block ui-icon ui-icon-document"></span>', 'class="view_note" title="Contract Notes"');
				
				$cntrct_row .= '</td><td colspan="8">'.$row->DIVS.'</td>';
				
				$elem_row = "";
			}

			if ($row->ON_INV_FLG == 'Y') 
			{
				$accr_oi += $row->ACR;
			}
			else
			{
				$accr_pst += $row->ACR;
			}
			
			$cntrct_accr += $row->ACR;
			
			$elem_row .= '<tr class="elem_summ '.alternator('odd','even').'" data-cntrct_parnt="'.$row->CNTRCT_ID.'"><td></td>';
			$elem_row .= '<td>'.$row->ELEM_NM.'</td>';
			
			if ($row->ON_INV_FLG == 'Y')
			{
				$elem_row .= '<td><span class="ui-icon ui-icon-flag"></span></td>';
			}
			else
			{
				$elem_row .= '<td></td>';
			}
			
			$fix_unts = '';
			if ($row->ELEM_TP == 'TU') $fix_unts = number_format($row->FIX_UNTS, 2);
			
			$elem_row .= '<td class="amount">'.number_format($row->LY, 0).'</td>';
			$elem_row .= '<td class="amount">'.number_format($row->YTD, 0).'</td>';
			$elem_row .= '<td class="amount">'.number_format($row->PRJ, 0).'</td>';
			$elem_row .= '<td class="amount">'.number_format($row->TTL, 0).'</td>';
			$elem_row .= '<td class="amount">'.round($row->CHG*100, 2).'%</td>';
			$elem_row .= '<td class="amount">'.$fix_unts.'</td>';
			$elem_row .= '<td class="amount">$ '.number_format($row->ELEM_RT, 5).'</td>';
			$elem_row .= '<td class="amount">'.number_format($row->SHR, 2).'%</td>';
			$elem_row .= '<td class="amount">$ '.number_format($row->ACR, 2).'</td></tr>';
		}
		
		$cntrct_row .= '<td class="amount">$ '.number_format($cntrct_accr, 2).'</td></tr>';
		echo $cntrct_row;
		echo $elem_row;
	?>
	</tbody>
	<tfoot>
		<?php if (in_array('Y', $inv)): ?>
		<tr class="header">
			<td colspan="11">On Invoice Total</td>
			<td class="amount">$<?php echo number_format($accr_oi, 2); ?></td>
		</tr>
		<?php endif; ?>
		<?php if (in_array('N', $inv)): ?>
		<tr class="header">
			<td colspan="11">Post Invoice Total</td>
			<td class="amount">$<?php echo number_format($accr_pst, 2); ?></td>
		</tr>
		<?php endif; ?>
		<tr class="header">
			<td colspan="11">Report Total</td>
			<td class="amount">$<?php echo number_format($accr_oi + $accr_pst, 2); ?></td>
		</tr>
	</tfoot>
</table>