<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('element/setup_rules'); ?>
		<div class="form_title">Setup Accrual Rules</div>
		<div class="form_content">
			<fieldset id="funding_temps">
				<legend>Funding Template</legend>
				<div class="field_row">
					<label class="preField">This Element works like</label>
					<?php echo form_input(array('id'=>'lookup_template','data-href'=>site_url('element/get_fund_tmplt'))); ?>
				</div>
			</fieldset>
			<fieldset id="acc_opts">
				<legend>Accrual Options</legend> 
				<?php echo std_form_radio('elem_tp','Rate Type',element_model::$elem_tps,set_value('elem_tp',$element->get_elem_tp()), form_error('elem_tp')); ?>
				<?php echo std_form_radio('elem_trgt','Rate Target',element_model::$elem_trgts,set_value('elem_trgt',$element->get_elem_trgt()),form_error('elem_trgt')); ?>
				<?php echo std_form_radio('elem_trigr','Rate Trigger',element_model::$elem_trigrs,set_value('elem_trigr',$element->get_elem_trigr()),form_error('elem_trigr')); ?>
				<?php echo std_form_input('pct','% of Last Year',set_value('pct',$element->get_pct()),form_error('pct'),array('class'=>'amount')); ?>
				<?php echo std_form_input('cse_thld','Case Threshold',set_value('cse_thld',$element->get_cse_thld()),form_error('cse_thld'),array('class'=>'amount')); ?>
			</fieldset>
			<fieldset id="acc_paymnts">
				<legend>Payment Options</legend> 
				<?php echo std_form_input('pymt_lmt','Pay Limit',set_value('pymt_lmt',$element->get_pymt_lmt()),form_error('pymt_lmt'),array('class'=>'currency')); ?>
				<?php echo std_form_input('shr','Bottler Share',set_value('shr',$element->get_shr()),form_error('shr'),array('class'=>'amount')); ?>
			</fieldset>
		</div>
		<?php echo std_form_actions('action','Next: Assign Criteria','',validation_errors()); ?>
		<?php 
			echo ($element->get_elem_id() !== NULL) ? form_hidden('elem_id',$element->get_elem_id()) : ''; 
			echo form_hidden('elem_nm', $element->get_elem_nm());
			echo form_hidden('elem_desc', $element->get_elem_desc());
			echo form_hidden('elem_rt', $element->get_elem_rt());
			echo form_hidden('app', $element->get_app());
			echo form_hidden('enbl', $element->get_enbl());
			echo form_hidden('on_inv_flg', $element->get_on_inv_flg());
			echo form_hidden('unt_div','12');
		?>	
	<?php echo form_close(); ?>
</div>
