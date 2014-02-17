<div id="modal_dialog" class="modal_dialog" title="Remove User">
	<?php echo form_open('user/remove'); ?>
		<strong>Are you sure you want to remove <br /><em><?php echo $fullname; ?></em>?</strong>
		<p class="ui-state-highlight ui-corner-all">Removing a user is not recommended. To prevent user access, <?php echo anchor('user/setup/'.$logn_nm, 'disable their user id'); ?>.</p>
		<?php echo form_hidden('usr_id', $usr_id); ?>
		<?php echo std_form_actions('confirm','Cancel','Continue'); ?>
	<?php echo form_close(); ?>
</div>