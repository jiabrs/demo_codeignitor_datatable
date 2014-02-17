<div id="modal_dialog" class="modal_dialog" title="Remove Funding Element">
	<?php echo form_open('program/remove'); ?>
		<strong>Are you sure you want to remove <em><?php echo $program->get_pgm_nm(); ?></em>?</strong>
		<input type="hidden" name="pgm_id" value="<?php echo $program->get_pgm_id(); ?>" />
		<?php echo std_form_actions('confirm','Cancel','Continue'); ?>
	<?php echo form_close(); ?>
</div>