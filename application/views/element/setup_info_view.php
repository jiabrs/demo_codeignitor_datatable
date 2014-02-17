<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('element/setup_info'); ?>
		<?php echo ($element->get_elem_id() !== NULL) ? form_hidden('elem_id',$element->get_elem_id()) : ''; ?>
		<div class="form_title">Setup Element</div>
		<div class="form_content">
			<fieldset id="element_info">
				<legend>Element Info</legend> 
				<?php echo std_form_input('elem_nm','Name',set_value('elem_nm', $element->get_elem_nm()),form_error('elem_nm'),array('class'=>'name req_field')); ?>
				<?php echo std_form_textarea('elem_desc','Description',set_value('elem_desc', $element->get_elem_desc()), form_error('elem_desc'), array('class'=>'desc req_field')); ?>
				<?php echo std_form_input('elem_rt','Rate',set_value('elem_rt', $element->dsp_elem_rt()),form_error('elem_rt'),array('class'=>'currency req_field')); ?>
				<?php echo std_form_dropdown('app','Application',$apps,set_value('app',$element->get_app()),form_error('app')); ?>
				<?php echo std_form_radio('enbl', 'Enabled', element_model::$enbls, set_value('enbl',$element->get_enbl()), form_error('enbl')); ?>
				<?php echo std_form_radio('on_inv_flg', 'On Invoice', element_model::$on_inv_flgs, set_value('on_inv_flg',$element->get_on_inv_flg()),form_error('on_inv_flg')); ?>
			</fieldset>
		</div>
		<?php echo std_form_actions('action','Next: Setup Accrual Rules','',$form_error); ?>
	<?php echo form_close(); ?>
</div>
