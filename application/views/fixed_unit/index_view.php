<h3><?php echo anchor('contract/dsp/'.$contract->get_cntrct_id(), $contract->get_cntrct_nm()); ?> &gt; <span class="red"><?php echo $element->get_elem_nm(); ?></span></h3>
<h4 id="fix_unt_yr_nav">
Select Year:
<?php foreach ($element->get_distinct_yrs() as $cntrct_year): ?>
	<?php if ($cntrct_year == $year): ?>
		<?php echo $year; ?>
	<?php else: ?>
		<?php echo anchor('fixed_unit/index/'.$contract->get_cntrct_id().'/'.$element->get_elem_id().'/'.$cntrct_year, $cntrct_year); ?>
	<?php endif; ?>
<?php endforeach; ?>
</h4>
<?php echo form_open('fixed_unit/update'); ?>
	<div class="fixed_col_cont">
		<table class="table_fixed_col">
			<thead><tr class="header"><th>MTH</th></tr></thead>
			<tbody>
			<?php foreach ($mths as $per => $dsp): ?>
				<tr class="<?php echo alternator('odd','even'); ?>"><td><?php echo $dsp; ?></td></tr>
			<?php endforeach; ?>
				<tr class="<?php echo alternator('odd','even'); ?>"><td><strong>TTL</strong></td></tr>
			</tbody>
		</table>
		<?php alternator('odd','even'); ?>
		<div class="table_scroll_data">
			<table>
				<thead>
					<tr class="header">
						<?php foreach ($this->location->get_by_sls_ctr() as $sls_ctr_cd => $sls_ctr_data): ?>
						<th><?php echo $sls_ctr_data['short_name']; ?></th>
						<th>$</th>
						<?php 
							// initialize col_ttl var
							$col_ttl[$sls_ctr_cd] = 0;
						?>
						<?php endforeach; ?>
						<th>TTL Units</th>
						<th>TTL ACCR</th>
					</tr>
				</thead>
				<tbody>
					<?php $row_ttl = $tbl_ttl = 0; $row_cnt = 1; ?>
					<?php foreach ($mths as $per => $dsp): $col_cnt = 1; ?>
						<tr class="<?php echo alternator('odd','even'); ?>">
						<?php foreach ($this->location->get_by_sls_ctr() as $sls_ctr_cd => $sls_ctr_data): ?>
							<td><?php echo form_input('fix_unts['.$element->get_elem_id().']['.$sls_ctr_cd.']['.$per.']', $fixed_units[$element->get_elem_id()][$sls_ctr_cd][$year][$per], 'class="fix_unt" tabindex="'.intval($row_cnt+(count($mths)* $col_cnt)).'"'); ?></td>
							<td>$<?php echo number_format($fixed_units[$element->get_elem_id()][$sls_ctr_cd][$year][$per] * ($element->get_elem_rt()/12), 2); ?></td>
							<?php 
								$row_ttl += $fixed_units[$element->get_elem_id()][$sls_ctr_cd][$year][$per]; 
								$col_ttl[$sls_ctr_cd] += $fixed_units[$element->get_elem_id()][$sls_ctr_cd][$year][$per];
								$col_cnt++;
							?>
						<?php endforeach; ?>
							<td><strong><?php 
								echo $row_ttl; 
								$tbl_ttl += $row_ttl;
							?></strong></td>
							<td><strong></em>$<?php 
								echo number_format($row_ttl * ($element->get_elem_rt()/12), 2); 
								$row_ttl = 0;
								$row_cnt++;
							?></strong></td>
						</tr>
					<?php endforeach; ?>
						<tr class="<?php echo alternator('odd','even'); ?>">
						<?php foreach ($this->location->get_by_sls_ctr() as $sls_ctr_cd => $sls_ctr_data): ?>
							<td><strong><?php echo $col_ttl[$sls_ctr_cd]; ?></strong></td>
							<td><strong>$<?php echo number_format($col_ttl[$sls_ctr_cd] * ($element->get_elem_rt()/12), 2); ?></strong></td>
						<?php endforeach; ?>	
							<td><strong><?php echo $tbl_ttl; ?></strong></td>
							<td><strong>$<?php echo number_format($tbl_ttl * ($element->get_elem_rt()/12), 2); ?></strong></td>		
						</tr>
				</tbody>
			</table>
		</div>
	</div>
	<input type="submit" value="Update" />
	<?php 
		echo form_hidden('cntrct_id', $contract->get_cntrct_id()); 
		echo form_hidden('elem_id', $element->get_elem_id());
		echo form_hidden('year', $year);
	?>
<?php echo form_close(); ?>