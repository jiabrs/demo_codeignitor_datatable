<div id="modal_dialog" class="modal_dialog" title="Remove Funding Element">
	<?php echo form_open('element/remove'); ?>
		<strong>Are you sure you want to remove <em><?php echo $element->get_elem_nm(); ?></em>?</strong>
		<input type="hidden" name="elem_id" value="<?php echo $element->get_elem_id(); ?>" />
		<?php echo std_form_actions('confirm','Cancel','Continue'); ?>
	<?php echo form_close(); ?>
</div>