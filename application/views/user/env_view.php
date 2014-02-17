<div id="login_form" class="std_form width_med ui-widget ui-widget-content ui-corner-all">
	<div class="form_title ui-corner-all">Set Environment</div>
	<?php echo form_open('user/environment'); ?>
		<?php if (count($roles) == 1 && $role_id !== FALSE): ?>
			<?php echo std_form_hiddentext('role', 'Role', $role_id, $roles[$role_id]); ?>
		<?php else: ?>
			<?php echo std_form_dropdown('role','Role',$roles,$role_id,form_error('role')); ?>
		<?php endif; ?>
		<?php echo std_form_dropdown('app','Application',$apps, $this->session->userdata('app'),form_error('app')); ?>
	<?php echo std_form_actions('action','Continue','',$error); ?> <?php echo form_close(); ?>
</div>
