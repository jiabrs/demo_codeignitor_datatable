<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('collection/setup'); ?>
		<?php echo ($collection->get_crit_clctn_id() !== NULL) ? form_hidden('crit_clctn_id', $program->get_crit_clctn_id()) : ''; ?>
		<div class="form_title"><?php echo ($collection->get_crit_clctn_id() === NULL) ? 'New Collection' : 'Edit Collection'; ?></div>
		<div class="form_content">
			<fieldset id="program_info">
				<legend>Info</legend>
				<?php echo std_form_input('crit_clctn_nm','Name',set_value('crit_clctn_nm',$collection->get_crit_clctn_nm()),form_error('crit_clctn_nm'),array('class'=>'name req_field')); ?>
			</fieldset>
			<fieldset id="program_elements">
				<legend>Assigned Criteria</legend>
				<?php echo anchor('#', 'Add Criteria', array('id'=>'add_crit')); ?>
				<table class="result_table ui-widget ui-corner-all">
					<tr class="header">
						<th class="action_column"></th>
						<th>Field</th>
						<th>Value</th>
					</tr>
					<?php if (count($collection->get_clctn_crits()) == 0): ?>
					<tr class="no_results">
						<td colspan="4">-&nbsp;No Criteria Assigned&nbsp;-</td>
					</tr>
					<?php else: ?>
					<?php foreach ($collection->get_criteria() as $index => $sls_crit): ?>
					<tr	id="sls_crits_<?php echo $sls_crit->get_crit_fld().'_'.$sls_crit->get_crit_cd(); ?>">
						<td><a class="remove_tr" href="#">Remove</a></td>
						<td><?php echo $sls_crit->dsp_crit_fld().form_hidden('crit_fld[]', $sls_crit->get_crit_fld()); ?></td>
						<td><?php echo $sls_crit->dsp_crit_cd().form_hidden('crit_cd[]', $sls_crit->get_crit_cd()); ?></td>
					</tr>
					<?php endforeach; ?>
					<?php endif; ?>
				</table>
			</fieldset>
		</div>
		<?php echo std_form_actions('action','Save','',$form_error); ?>
	<?php echo form_close(); ?>
</div>
<div class="modal_dialog" id="adv_search_dialog" title="Advanced Criteria Lookup">
	<div id="search_results">
		<?php echo form_multiselect('code', $code_model->get_codes('BUS_TP'), array('1'), 'id="code" data-href="'.site_url('element/get_crit_vals').'"'); ?><br />	
		<button id="add_crit">Add Selected Criteria</button>
	</div>
	<div id="search_options">
		<?php echo form_open('element/get_crit_vals', array('id'=>'adv_search_form')); ?>
			<?php foreach($coll_crit_model->dpdwn_crit_flds() as $field => $display): ?>	
				<label for="fld_<?php echo $field; ?>">
					<?php echo form_radio(array('name'=>'fld', 'id'=>'fld_'.$field, 'value'=>$field, 'checked' => ($field == 'BUS_TP') ? TRUE : FALSE))." ".$display; ?>
				</label>
			<?php endforeach; ?>
		<?php echo form_close(); ?>
	</div>
</div>