<div id="modal_dialog" class="modal_dialog" title="Remove Funding Element">
	<?php echo form_open('role/remove'); ?>
		<strong>Are you sure you want to remove role <em>#<?php echo $role->get_role_id().": ".$role->get_role_nm(); ?></em>?</strong>
		<input type="hidden" name="role_id" value="<?php echo $role->get_role_id(); ?>" />
		<?php echo std_form_actions('confirm','Cancel','Continue'); ?>
	<?php echo form_close(); ?>
</div>