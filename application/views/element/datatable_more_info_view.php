<table class="elem_detail">
	<tr class="detail_row_data">
		<td class="label">Criteria:</td>
		<td class="data">
			<div class="scrollY"><?php 
				// prepare element sales criteria for display
				$sls_crits = array();
				
				foreach ($element->get_sls_crits() as $sls_crit)
				{
					$sls_crits[$sls_crit->get_accr_flg()][$sls_crit->dsp_crit_fld()][] = $sls_crit->dsp_crit_cd();
				}
				
				$includes = array();
				$excludes = array();
				$include_title = $exclude_title = '';
				foreach ($sls_crits as $accr_flg => $fields)
				{
					foreach ($fields as $field => $codes)
					{
						if ($accr_flg == 'Y')
						{
							$includes[] = nbs(3).$field.": ".implode(', ', $codes);
							$include_title = "Accrues Against:<br />";
						}	
						if ($accr_flg == 'N')
						{
							$excludes[] = nbs(3).$field.": ".implode(', ', $codes);
							$exclude_title = "Excludes:<br />";
						}
					}
				}
				
				echo $include_title.implode("<br />", $includes);
				echo (count($includes) > 0 && count($excludes) > 0) ? '<br /><br />' : '';
				echo $exclude_title.implode("<br />", $excludes);
					
			?></div>
		</td>
		<td class="label">Accrual Options:</td>
		<td class="data">
			Target:&nbsp;<?php echo $element->dsp_elem_trgt(); ?><br />
			Trigger:&nbsp;<?php echo $element->dsp_elem_trigr(); ?>
			<?php if (in_array($element->get_elem_trigr(), array('EL','GL')) && $element->get_pct() <> 100): ?>
			&nbsp;(<?php echo $element->dsp_elem_trigr(); ?>%)
			<?php elseif ($element->get_elem_trigr() == 'CT'): ?>
			&nbsp;(<?php echo $element->get_cse_thld(); ?>&nbsp;cases)
			<?php endif; ?><br />
			<?php if ($element->get_pymt_lmt() <> 0): ?>
			<br />Payment Limit:&nbsp;$<?php echo number_format($element->get_pymnt_lmt(), 2); ?>
			<?php endif; ?>
			<?php if ($element->get_shr() <> 100): ?>
			<br />Share:&nbsp;<?php echo number_format($element->get_shr(), 2); ?>%
			<?php endif; ?>
		</td>
	</tr>
</table>