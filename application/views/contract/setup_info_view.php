<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('contract/setup_info'); ?>
		<?php echo ($contract->get_cntrct_id() !== NULL) ? form_hidden('cntrct_id',$contract->get_cntrct_id()) : ''; ?>
		<div class="form_title">Setup Contract</div>
		<div class="form_content">
			<fieldset id="contract_info">
				<legend>Contract Basics</legend> 
				<?php echo std_form_input('cntrct_nm','Name',set_value('cntrct_nm', $contract->get_cntrct_nm()),form_error('cntrct_nm'),array('class'=>'name req_field')); ?>
				<?php echo std_form_radio('cse_tp','Case Type', contract_model::$cse_tps, set_value('cse_tp', $contract->get_cse_tp()), form_error('cse_tp')); ?>
				<?php echo std_form_input('strt_dt', 'Start', set_value('strt_dt', $contract->dsp_strt_dt('m/d/Y')), form_error('strt_dt'),array('class'=>'date req_field')); ?>
				<?php echo std_form_input('end_dt', 'End', set_value('end_dt', $contract->dsp_end_dt('m/d/Y')), form_error('end_dt'),array('class'=>'date req_field')); ?>
			</fieldset>
			<?php if ($app == 'CM'): ?>
			<fieldset id="cntrct_check">
				<legend>Check Request Settings</legend>
				<?php echo std_form_input('vend_no', 'Vendor #', set_value('vend_no', $contract->get_vend_no()), form_error('vend_no')); ?>
				<?php echo std_form_dropdown('appr_lst_id', 'Approvers List', $appr_lst, set_value('appr_list_id', $contract->get_appr_lst_id()), form_error('appr_lst_id')); ?>
			</fieldset>
			<?php endif; ?>
		</div>
		<?php echo std_form_actions('action','Next: Assign Locations','',$form_error); ?>
		<?php echo form_hidden('dt_fmt','m/d/Y'); ?>
	<?php echo form_close(); ?>
</div>
