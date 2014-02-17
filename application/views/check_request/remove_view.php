<div id="modal_dialog" class="modal_dialog" title="Remove Approval List">
	<?php echo form_open('approval/remove'); ?>
		<strong>Are you sure you want to remove this approval list?</strong>
		<input type="hidden" name="appr_lst_id" value="<?php echo $approval->get_appr_lst_id(); ?>" />
		<?php echo std_form_actions('confirm','Cancel','Continue'); ?>
	<?php echo form_close(); ?>
</div>
