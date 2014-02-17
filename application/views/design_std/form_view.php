
<div class="std_form width_med"><?php echo form_open_multipart('design_std/form'); ?>
<div class="form_title">Test Form</div>
<div class="form_content">
<fieldset id="text_fields"><legend>Text Fields</legend> <?php echo std_form_input('ex_name','Name Example',set_value('ex_name'),form_error('ex_name'), array('class'=>'name req_field')); ?>
<?php echo std_form_textarea('ex_desc','Description Example',set_value('ex_desc'),form_error('ex_desc'), array('class'=>'desc req_field')); ?>
<?php echo std_form_input('ex_currency','Currency Example',set_value('ex_currency'),form_error('ex_currency'), array('class'=>'currency req_field')); ?>
<?php echo std_form_input('ex_amount','Amount Example',set_value('ex_amount'),form_error('ex_amount'), array('class'=>'amount req_field')); ?>
</fieldset>
<fieldset id="control_fields"><legend>Control Fields</legend> <?php echo std_form_dropdown('ex_dropdown','Dropdown Example',$ex_dropdowns,'',form_error('ex_dropdown')); ?>
<?php echo std_form_radio('ex_radio','Radio Example',$ex_radio_opts,'',form_error('ex_radio')); ?>
<?php echo std_form_checkbox('ex_checkbx','Checkbox Example',$ex_checkbx_opts,array(),form_error('ex_checkbx'), TRUE); ?>
</fieldset>
<fieldset id="ex_table"><legend>Table Example</legend>
<div class="field_row">
<table class="form_table">
	<tr>
		<th class="action_column"></th>
		<?php foreach ($table_data[0] as $col_name => $col_val): ?>
		<th><?php echo $col_name; ?></th>
		<?php endforeach; ?>
	</tr>
	<?php foreach ($table_data as $row_id => $row_data): ?>
	<tr class="<?php echo alternator('odd','even'); ?>">
		<td class="action_column"><?php echo anchor('#', '<span class="ico_edit"></span>',array('title'=>'Edit')); ?>
		<?php echo anchor('#', '<span class="ico_remove"></span>',array('title'=>'Remove')); ?>
		</td>
		<?php foreach ($row_data as $row_col => $row_val): ?>
		<td><?php echo $row_val; ?></td>
		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
</table>
</div>
</fieldset>
<fieldset id="upload_fields"><legend>Upload Example</legend> <?php echo std_form_upload('ex_upload','Upload Example',form_error('ex_upload'),'upload'); ?>
</fieldset>
</div>
	<?php echo std_form_actions('action', 'Primary Action', 'Second Action', $form_error); ?>
	<?php echo form_close(); ?></div>
