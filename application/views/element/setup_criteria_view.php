<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('element/setup_criteria', array('id'=>'elem_setup_form')); ?>
		<div class="form_title">Assign Criteria</div>
		<div class="form_content">
			<fieldset id="assign_criteria">
				<legend>Assigned Criteria</legend>
				<?php echo anchor('#', 'Add Criteria', array('id'=>'add_crit')); ?>
				<table class="result_table ui-widget ui-corner-all">
					<tr class="header">
						<th class="action_column"></th>
						<th>Field</th>
						<th>Value</th>
						<th>Accrue Against</th>
					</tr>
					<?php if (count($element->get_sls_crits()) == 0): ?>
					<tr class="no_results">
						<td colspan="4">-&nbsp;No Criteria Assigned&nbsp;-</td>
					</tr>
					<?php else: ?>
					<?php foreach ($element->get_sls_crits() as $index => $sls_crit): ?>
					<tr	id="sls_crits_<?php echo $sls_crit->get_crit_fld().'_'.$sls_crit->get_crit_cd(); ?>">
						<td><a class="remove_tr" href="#">Remove</a></td>
						<td><?php echo $sls_crit->dsp_crit_fld().form_hidden('crit_fld[]', $sls_crit->get_crit_fld()); ?></td>
						<td><?php echo $sls_crit->dsp_crit_cd().form_hidden('crit_cd[]', $sls_crit->get_crit_cd()); ?></td>
						<td><?php echo $sls_crit->dsp_accr_flg().form_hidden('crit_accr_flg[]', $sls_crit->get_accr_flg()); ?></td>
					</tr>
					<?php endforeach; ?>
					<?php endif; ?>
				</table>
			</fieldset>
		</div>
		<?php echo std_form_actions('action','Save Funding Element','',$form_error); ?>
		<?php 
			echo ($element->get_elem_id() !== NULL) ? form_hidden('elem_id',$element->get_elem_id()) : ''; 
			echo form_hidden('elem_nm', $element->get_elem_nm());
			echo form_hidden('elem_desc', $element->get_elem_desc());
			echo form_hidden('elem_rt', $element->get_elem_rt());
			echo form_hidden('app', $element->get_app());
			echo form_hidden('enbl', $element->get_enbl());
			echo form_hidden('on_inv_flg', $element->get_on_inv_flg());
			echo form_hidden('elem_tp', $element->get_elem_tp());
			echo form_hidden('unt_div', $element->get_unt_div());
			echo form_hidden('elem_trgt', $element->get_elem_trgt());
			echo form_hidden('elem_trigr', $element->get_elem_trigr());
			echo form_hidden('pct', $element->get_pct());
			echo form_hidden('pymt_lmt', $element->get_pymt_lmt());
			echo form_hidden('shr', $element->get_shr());
			echo form_hidden('cse_thld', $element->get_cse_thld());
		?>	
	<?php echo form_close(); ?>
</div>
<div class="modal_dialog" id="adv_search_dialog" title="Advanced Criteria Lookup">
	<div id="search_results">
		<?php echo form_multiselect('code', $code_model->get_codes('BUS_TP'), array('1'), 'id="code" data-href="'.site_url('element/get_crit_vals').'"'); ?><br />
		Accrue Against Criteria? <?php foreach(sls_crit_model::$accr_flgs as $accr_cd => $accr_dsp): ?>	
			<label for="accr_<?php echo $accr_cd; ?>">
				<?php echo form_radio(array('name'=>'accr', 'id'=>'accr_'.$accr_cd, 'value'=>$accr_cd, 'checked' => ($accr_cd == $sls_crit_model->get_accr_flg()) ? TRUE : FALSE))." ".$accr_dsp; ?>
			</label>
		<?php endforeach; ?>		
		<button id="add_crit">Add Selected Criteria</button>
	</div>
	<div id="search_options">
		<?php echo form_open('element/get_crit_vals', array('id'=>'adv_search_form')); ?>
			<?php foreach($sls_crit_model->dpdwn_crit_flds() as $field => $display): ?>	
				<label for="fld_<?php echo $field; ?>">
					<?php echo form_radio(array('name'=>'fld', 'id'=>'fld_'.$field, 'value'=>$field, 'checked' => ($field == 'BUS_TP') ? TRUE : FALSE))." ".$display; ?>
				</label>
			<?php endforeach; ?>
		<?php echo form_close(); ?>
	</div>
</div>
<div class="modal_dialog" id="confirm_chnge" title="The following contracts will be affected by this change"></div>
