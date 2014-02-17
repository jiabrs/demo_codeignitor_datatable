<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('program/setup'); ?>
		<?php echo ($program->get_pgm_id() !== NULL) ? form_hidden('pgm_id', $program->get_pgm_id()) : ''; ?>
		<div class="form_title"><?php echo ($program->get_pgm_id() === NULL) ? 'New Contract Program' : 'Edit Contract Program'; ?></div>
		<div class="form_content">
			<fieldset id="program_info">
				<legend>Info</legend>
				<?php echo std_form_input('pgm_nm','Name',set_value('pgm_nm',$program->get_pgm_nm()),form_error('pgm_nm'),array('class'=>'name req_field')); ?>
			</fieldset>
			<fieldset id="program_elements">
				<legend>Assigned Elements</legend>
				<?php echo anchor('element/elem_lookup', 'Add Element', array('id'=>'add_elem')); ?>
				<table class="result_table ui-widget ui-corner-all">
					<tr class="header">
						<th class="action_column"></th>
						<th>Name</th>
						<th>Desc</th>
						<th>Rate</th>
					</tr>
					<?php if (count($program->get_elements()) == 0): ?>
					<tr class="no_results">
						<td colspan="4">-&nbsp;No Elements Assigned&nbsp;-</td>
					</tr>
					<?php else: ?>
					<?php foreach ($program->get_elements() as $index => $element): ?>
					<tr	id="element_<?php echo $element->get_elem_id(); ?>">
						<td><a class="remove_tr" href="#">Remove</a></td>
						<td><?php echo $element->get_elem_nm(); ?><input type="hidden" name="elem_id[]" value="<?php echo $element->get_elem_id(); ?>" /></td>
						<td><?php echo $element->get_elem_desc(); ?></td>
						<td>$<?php echo $element->dsp_elem_rt(); ?></td>
					</tr>
					<?php endforeach; ?>
					<?php endif; ?>
				</table>
			</fieldset>
		</div>
		<?php echo std_form_actions('action','Save','',$form_error); ?>
	<?php echo form_close(); ?>
</div>
<div id="elem_lookup" class="modal_dialog"></div>
