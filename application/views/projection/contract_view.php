<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('projection/update_contract'); ?>
	<?php echo form_hidden('cntrct_id', $contract->get_cntrct_id()); ?>
	<div class="form_title">Set Contract Projections</div>
	<div class="form_content">
		<fieldset class="field_row">
			<legend>Elements:</legend>
			<div class="field_grouping">
		 	<?php foreach(Element_model::get_elem_drpdwn_by_cntrct($contract->get_cntrct_id()) as $elem_id => $elem_nm): ?>
				<?php echo form_checkbox('elem_id[]', $elem_id, TRUE).'&nbsp;'.$elem_nm; ?><br />
			<?php endforeach; ?>
			</div>
		</fieldset>
		<fieldset class="field_row">
			<legend>Locations:</legend>
			<div class="field_grouping">
				<ul id="locs">
					<?php foreach ($this->location->get_by_div() as $div_cd => $data): ?>
					<li class="div">
						<?php echo form_checkbox(array('name'=>'div[]','class'=>'div'), $div_cd, TRUE).$data['name']; ?><br />&nbsp;&nbsp;
						<span class="sls_ctr">
							<?php foreach ($data['sls_ctrs'] as $sls_ctr_cd => $names): ?>
								<?php echo form_checkbox(array('name'=>"sls_ctr[]"), $sls_ctr_cd, TRUE).$names['short_name']; ?>&nbsp;
							<?php endforeach; ?>
						</span>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</fieldset>
		<?php echo std_form_input('chg','% Change:','0','',array('class'=>'amount')); ?>
		<?php if (count($exist_projs) > 0): ?>
		<fieldset class="field_row">
			<legend>Existing Projections</legend>
			<table class="result_table ui-widget ui-corner-all">
				<tr class="header"><th>User</th><th>Last Updated</th><th>Projected Through</th></tr>
				<?php foreach ($exist_projs as $exist_proj): ?>
					<tr class="<?php echo alternator('odd','even'); ?>">
						<td><?php echo $exist_proj['name']; ?></td>
						<td><?php echo $exist_proj['lst_updt_tm']; ?></td>
						<td><?php echo $exist_proj['max_proj_fp']; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</fieldset>
		<?php endif; ?>
	</div>
	<div class="actions"><span id="update_results"></span><input type="submit" value="Set Projections" /></div>
	<?php echo form_close(); ?>
</div>