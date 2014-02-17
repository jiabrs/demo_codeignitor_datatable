<div id="login_form" class="std_form width_med ui-widget ui-widget-content ui-corner-all">
	<?php echo form_open('user/login'); ?>
		<?php echo std_form_input('logn_nm','User Name',set_value('uid'),form_error('uid'),array('class'=>'name req_field')); ?>
		<?php echo std_form_password('password','Password','',form_error('password'),array('class'=>'name req_field')); ?>
	<?php echo std_form_actions('action','Sign In','',$auth_error); ?> <?php echo form_close(); ?>
</div>
