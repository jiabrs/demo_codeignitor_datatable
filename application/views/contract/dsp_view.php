<h2 id="cont_title">
	<?php echo $contract->get_cntrct_nm(); ?>
	<?php if ($this->authr->authorize('MC',$contract->get_cntrct_id(), FALSE)): ?>
	<?php echo anchor('contract/setup_info/'.$contract->get_cntrct_id(), '<span class="ui-icon ui-icon-wrench"></span>', 'class="css-inline_block"'); ?>
	<?php endif; ?>
	<?php echo anchor('contract/pdf/'.$contract->get_cntrct_id().'/'.$contract->get_accrual_yr(), img('resource/images/doc_pdf.png'), 'title="Download PDF"'); ?>
</h2>
<ul id="cont_notes">
	<li id="no_notes">There are no notes for this contract</li>
	<?php 
		foreach ($contract->get_notes() as $index => $note)
		{
			$this->load->view('note/dsp_view', array('index'=>$index,'note'=>$note));
		}
	?>
	<li id="note_opts">
		<div class="modal_dialog" id="note_dialog"></div>
		<?php if ($this->authr->authorize('CN', $contract->get_cntrct_id(), FALSE)): ?>
			<?php echo anchor('note/add/'.$contract->get_cntrct_id(), 'Add Note', array('id'=>'add_note')); ?>
		<?php endif; ?>
		<a href="#" id="more_notes">View Older Notes</a>
		<a href="#" id="less_notes">Hide Older Notes</a>
	</li>
</ul>
<ul id="cont_info">
	<li><span class="label">Start:</span><?php echo $contract->dsp_strt_dt('m/d/Y'); ?></li>
	<li><span class="label">End:</span><?php echo $contract->dsp_end_dt('m/d/Y'); ?></li>
	<li><span class="label">Case Type:</span><?php echo $contract->dsp_cse_tp(); ?></li>
	<li>
		<span class="label">Customers:</span>
		<div class="field_grouping customer-list">
			<?php foreach ($contract->get_customers() as $customer): ?>
				<?php echo $customer->get_cust_nm().' ('.$customer->get_cust_tp().': '.$customer->get_cust_cd().')'; ?><br />
			<?php endforeach; ?>
		</div>
	</li>
	<li>
		<span class="label">Divisions:</span>
		<div class="field_grouping location-list">
			<?php 
				$div_cnt = 0;
				foreach ($this->location->get_by_div() as $div_cd => $div_data)
				{
					if ($div_cnt > 0) echo "<br />";
					
					echo $div_data['name'].":&nbsp;&nbsp;(";
					$count = 0;
					foreach ($div_data['sls_ctrs'] as $sls_ctr_cd => $sls_ctr_data)
					{
						if ($count > 0) echo ", ";
						echo $sls_ctr_data['short_name'];
						$count++;
					}
					echo ')';
					$div_cnt++;
				}			
			?>
		</div>
	</li>
	<li>
		<span class="label">Accrual Year</span>
		<div class="field_grouping">
			<?php foreach ($contract->get_cntrct_yrs() as $yr): ?>
				<?php if ($yr <> $contract->get_accrual_yr()): ?>
					<?php echo anchor('contract/dsp/'.$contract->get_cntrct_id().'/'.$yr, $yr); ?>&nbsp;
				<?php else: ?>
					<span class="red"><?php echo $contract->get_accrual_yr(); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</li>
	<li>
		<span class="label">Projected Cost: </span>
		<table><?php $prj_cost = $contract->get_proj_cost(); ?>
			<tbody>
				<tr><td>On Invoice:</td><td class="amount">$ <?php echo number_format($prj_cost['oi'], 2); ?></td></tr>
				<tr><td>Post Invoice:</td><td class="amount">$ <?php echo number_format($prj_cost['pst'], 2); ?></td></tr>
				<tr><td>Total:</td><td class="amount">$ <?php echo number_format($prj_cost['oi'] + $prj_cost['pst'], 2); ?></td></tr>
			</tbody>
		</table>
	</li>
	<?php if ($this->session->userdata('app') == 'CM'): ?>
	<li>
		<span class="label">Check Approval: </span>
		<div class="field_grouping"><?php echo implode('<br />', $appr_lst); ?></div>
	</li>
	<div class="flt_clr"></div>
	<li><span class="label">Check Return: </span><?php echo $retrn_to; ?></li>
	<div class="flt_clr"></div>
	<?php endif; ?>
	<li><?php echo anchor('contract/viewers/'.$contract->get_cntrct_id(), 'Show who can view contract', 'id="show_usrs"'); ?></li>
	<?php if ($this->authr->authorize('MC', $contract->get_cntrct_id(), FALSE)): ?>
	<li>
		<?php echo anchor('#', 'Start Sales Pull', array('id'=>'pull_sls', 'data-cntrct_id'=> $contract->get_cntrct_id())); ?>
		<span id="strt_sls_pull_sts"><?php echo $sls_pull_sts; ?></span>
		<?php echo anchor('contract/pull_sls_log/'.$contract->get_cntrct_id(), 'Show Sales Pull Log', array('id'=>'show_sls_pull_log')); ?>
	</li>
	<li><?php echo anchor('projection/contract/'.$contract->get_cntrct_id(), 'Set Contract Projections'); ?></li>
	<?php endif; ?>
</ul>
<ul id="cont_elems">
	<?php if ($program !== NULL): ?>
	<li class="program">Program: <?php echo $program->get_pgm_nm()?></li>
	<?php endif; ?>
	<?php foreach ($contract->get_elements() as $element): ?>
	<li id="elem_id_<?php echo $element->get_elem_id(); ?>"<?php echo ($element->get_cntrct_pgm_id() !== NULL) ? ' class="program_element"' : ""; ?>>
		<div class="elem_summ">
			<?php echo $element->get_elem_nm(); ?>
			<span class="span-col-actn">
				<?php if ($this->authr->authorize('UP', $contract->get_cntrct_id(), FALSE)): ?>
					<?php if ($element->get_elem_tp() == 'TU'): ?>
				<button type="button" title="Update Fixed Units" class="link text" value="<?php echo site_url('fixed_unit/index/'.$contract->get_cntrct_id().'/'.$element->get_elem_id()); ?>"><span class="ui-icon-txt">f</span></button>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ($this->authr->authorize('MC', $contract->get_cntrct_id(), FALSE)): ?>
				<button type="button" title="Edit Element" class="link text" value="<?php echo site_url('element/setup_info/'.$element->get_elem_id()); ?>"><span class="ui-icon-txt">e</span></button>
				<?php endif; ?>
				<?php if ($this->authr->authorize('UP', $contract->get_cntrct_id(), FALSE)): ?>
				<button type="button" title="Project Element" class="link text" value="<?php echo site_url('projection/index/'.$contract->get_cntrct_id().'/'.$element->get_elem_id()); ?>"><span class="ui-icon-txt">p</span></button>
				<?php endif; ?>
			</span>
			<span class="span-col"><?php echo "$".number_format($contract->get_elem_acr($element->get_elem_id()), 2); ?></span>
			<span class="span-col">Proj. Cost:</span>
			<span class="span-col"><?php echo "$".$element->dsp_elem_rt(); ?></span>
			<span class="span-col">Rate:</span>
			<div class="flt_clr"></div>
		</div>
		<div class="elem_dtls">
			<div class="elem_stats">
				<ul>
					<li><a href="#sls_crit_<?php echo $element->get_elem_id(); ?>">Sales Criteria</a></li>
					<li><a href="#sls_<?php echo $element->get_elem_id(); ?>">Sales</a></li>
				</ul>
				<div id="sls_crit_<?php echo $element->get_elem_id(); ?>" class="stats_tab">
					<table class="result_table">
						<tr class="header">
							<th>Criteria</th>
							<th>Value</th>
							<th>Accrue</th>
						</tr>
						<?php foreach ($element->get_sls_crits() as $sls_crit): ?>
						<tr>
							<td><?php echo $sls_crit->dsp_crit_fld(); ?></td>
							<td><?php echo $sls_crit->dsp_crit_cd(); ?></td>
							<td><?php echo $sls_crit->dsp_accr_flg(); ?></td>
						</tr>
						<?php endforeach; ?>
					</table>
				</div>
				<div id="sls_<?php echo $element->get_elem_id(); ?>" class="stats_tab">
					<table class="result_table">
						<tr class="header">
							<th>DIV</th>
							<th>LOC</th>
							<?php if ($element->get_elem_tp() == 'TU'): ?>
							<th>UNTS</th>
							<?php endif; ?>
							<th>LY</th>	
							<th>YTD</th>
							<th>PROJ</th>
							<th>TTL</th>
							<th>% CHG</th>						
							<th>ACCR</th>
						</tr>
						<?php $fix_unts = $ly = $ytd = $prj = $ttl = $acr = 0; ?>
						<?php foreach ($contract->get_elem_sls_stats($element->get_elem_id()) as $row): ?>
						<?php 
							$fix_unts += $row->FIX_UNTS;
							$ly += $row->LY;
							$ytd += $row->YTD;
							$prj += $row->PRJ;
							$ttl += $row->TTL;
							$acr += $row->ACR;
						?>
						<tr class="<?php echo alternator('odd','even'); ?>">
							<td><?php echo $row->DIV_NM; ?></td>
							<td><?php echo $row->SLS_CTR_NM; ?></td>
							<?php if ($element->get_elem_tp() == 'TU'): ?>
							<td class="amount"><?php echo number_format($row->FIX_UNTS, 2); ?></td>
							<?php endif; ?>
							<td class="amount"><?php echo number_format($row->LY, 0); ?></td>
							<td class="amount"><?php echo number_format($row->YTD, 0); ?></td>
							<td class="amount"><?php echo number_format($row->PRJ, 0); ?></td>
							<td class="amount"><?php echo number_format($row->TTL, 0); ?></td>
							<td class="amount"><?php echo number_format($row->CHG*100, 2); ?></td>
							<td class="amount">$<?php echo number_format($row->ACR, 0); ?></td>
						</tr>
						<?php endforeach; ?>
						<tr class="total">
							<td colspan="2">Total</td>
							<?php if ($element->get_elem_tp() == 'TU'): ?>
							<td class="amount"><?php echo number_format($fix_unts, 2); ?></td>
							<?php endif; ?>
							<td class="amount"><?php echo number_format($ly, 0); ?></td>
							<td class="amount"><?php echo number_format($ytd, 0); ?></td>
							<td class="amount"><?php echo number_format($prj, 0); ?></td>
							<td class="amount"><?php echo number_format($ttl, 0); ?></td>
							<td class="amount"><?php echo number_format(($ly == 0) ? 0 : ($ttl - $ly) / $ly * 100, 2); ?></td>
							<td class="amount">$<?php echo number_format($acr, 0); ?></td>
						</tr>
					</table>
				</div>
			</div>
			<ul class="elem_info">
				<li><span class="label">Date Range:</span><br />
				<?php foreach ($element->get_dt_range() as $index => $dts): ?>
					<?php echo ($index > 0) ? '<br />' : ''; ?>
					&nbsp;&nbsp;<?php echo $dts['strt_dt']." - ".$dts['end_dt']; ?>
				<?php endforeach; ?>
				</li>
				<?php if ($contract->get_app() == 'CM'): ?>
				<li><span class="label">Pay Sched:</span><?php echo $element->dsp_pymt_freq(); ?></li>
				<?php endif; ?>
				<li><span class="label">On Invoice:</span><?php echo $element->dsp_on_inv_flg(); ?></li>
				<?php if ($element->get_shr() < 100): ?>
				<li><span class="label">Share:</span><?php echo number_format($element->get_shr(), 0); ?>%</li>
				<?php endif; ?>
				<?php if (trim($element->get_elem_desc()) != ''): ?>
				<li><span class="label">Description:</span><br /><p><?php echo $element->get_elem_desc(); ?></p></li>	
				<?php endif; ?>		
			</ul>
			<div class="flt_clr"></div>
		</div>
	</li>
	<?php endforeach; ?>
</ul>
<div id="sls_pull_sts" class="modal_dialog" title="Sales Pull Status"></div>