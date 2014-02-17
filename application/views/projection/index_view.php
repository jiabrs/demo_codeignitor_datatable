<h3><?php echo anchor('contract/dsp/'.$contract->get_cntrct_id(), $contract->get_cntrct_nm()); ?> &gt; <span class="red"><?php echo $element->get_elem_nm(); ?></span></h3>
<?php echo form_open('projection', array("id"=>"proj_crit","class"=>"ui-widget ui-widget-content ui-corner-all")); ?>
	<div class="ui-corner-all div_title">Projection Criteria</div>
	<?php echo form_hidden('cntrct_id', $contract->get_cntrct_id()); ?>
	<?php echo form_hidden('elem_id', $element->get_elem_id()); ?>	
	<?php echo form_dropdown('elem_id', Element_model::get_elem_drpdwn_by_cntrct($contract->get_cntrct_id()), $element->get_elem_id(), array('id'=>'selected_elem')); ?>
	<ul id="locs">
		<?php foreach ($this->location->get_by_div() as $div_cd => $data): ?>
		<li class="div">
			<?php echo form_checkbox(array('name'=>'div[]','class'=>'div'), $div_cd, (in_array($div_cd, $divs)) ? TRUE : FALSE).$data['name']; ?><br />&nbsp;&nbsp;
			<span class="sls_ctr">
				<?php foreach ($data['sls_ctrs'] as $sls_ctr_cd => $names): ?>
					<?php echo form_checkbox(array('name'=>"sls_ctr[]"), $sls_ctr_cd, (in_array($sls_ctr_cd, $sls_ctrs)) ? TRUE : FALSE).$names['short_name']; ?>&nbsp;
				<?php endforeach; ?>
			</span>
		</li>
		<?php endforeach; ?>
	</ul>
	<input type="submit" value="Change Criteria" />
<?php echo form_close(); ?>
<?php echo form_open('projection/update', array('id'=>'proj_update')); ?>
	<?php foreach ($projections as $yr => $months): ?>
		<a href="<?php echo "#".$yr ?>" class="chg_yr"><?php echo $yr; ?></a>&nbsp;&nbsp;
	<?php endforeach; ?><br /><br />
	<?php echo form_hidden('cntrct_id', $contract->get_cntrct_id()); ?>
	<?php echo form_hidden('elem_id', $element->get_elem_id()); ?>
	<?php 
		foreach ($sls_ctrs as $sls_ctr_cd)
		{
			echo form_hidden('sls_ctr[]', $sls_ctr_cd); 
		}
	?>
	<table id="projections">
		<thead>
			<tr><th>Year</th><th>Month</th><th>Last</th><th>This</th><th>% Change</th></tr>
		</thead>
		<tbody>
			<?php foreach ($projections as $yr => $months): ?>
				<?php foreach ($months as $month => $data): ?>
				<?php if ($yr == date('Y') && $month != 1 && $month == date('m')): ?>
				<tr class="total show_<?php echo $yr; ?>"><td colspan="2">YTD</td><td class="amount" id="lastYTDTtl"></td><td class="amount" id="thisYTDTtl"></td><td class="amount" id="chgYTDTtl"></td></tr>
				<?php endif; ?>
				<?php $chg = ($data['last'] == 0) ? 0 : ($data['this'] - $data['last']) * 100 / $data['last']; ?>
				<tr class="proj_data show_<?php echo $yr; ?>" id="mth_<?php echo $month; ?>" >
					<td><?php echo $yr; ?></td>
					<td><?php echo date('M', mktime(0,0,0,$month,1,$yr)); ?></td>
					<td><?php echo form_input('last['.$yr.']['.$month.']', $data['last'], 'class="amount last" disabled="disabled"'); ?></td>
					<td><?php echo form_input('this['.$yr.']['.$month.']', $data['this'], (mktime(0,0,0,$month,1,$yr) < mktime(0,0,0,date('m'),1,date('y'))) ? 'class="amount this" disabled="disabled"' : 'class="amount this"'); ?></td>				
					<td><?php echo form_input('chg['.$yr.']['.$month.']', number_format($chg, 2), (mktime(0,0,0,$month,1,$yr) < mktime(0,0,0,date('m'),1,date('y'))) ? 'class="amount chg" disabled="disabled"' : 'class="amount chg"'); ?></td>
				</tr>			
				<?php endforeach; ?>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr class="total"><td colspan="2">Total</td><td class="amount" id="lastTtl"></td><td class="amount" id="thisTtl"></td><td class="amount" id="chgTtl"></td></tr>
		</tfoot>
	</table>
	<input type="submit" value="Update Projections" /><span id="update_results"></span>
<?php echo form_close(); ?>