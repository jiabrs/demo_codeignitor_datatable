<div id="modal_dialog" class="modal_dialog" title="Remove Contract">
	<?php echo form_open('contract/remove'); ?>
		<strong>Are you sure you want to remove contract <em><?php echo $contract->get_cntrct_nm(); ?></em>?</strong>
		<input type="hidden" name="contract_id" value="<?php echo $contract->get_cntrct_id(); ?>" />
		<?php echo std_form_actions('confirm','Cancel','Continue'); ?>
	<?php echo form_close(); ?>
</div>