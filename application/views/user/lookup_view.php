
<div
	class="std_form width_med ui-widget ui-widget-content ui-corner-all"><?php echo form_open_multipart('user/lookup'); ?>
<div class="form_title ui-corner-all">Search Users</div>
<div class="form_content">
<fieldset id="usr_info"><legend>Lookup User</legend> <?php echo std_form_input('firstname','By First Name',set_value('firstname'),form_error('firstname'),array('class'=>'name req_field')); ?>
<?php echo std_form_input('lastname','By Last Name',set_value('lastname'),form_error('lastname'),array('class'=>'name req_field')); ?>
</fieldset>
<?php if (count($found_users) > 0): ?>
<fieldset id="matches"><legend>Matches</legend>
<div class="field_row">
<table class="result_table ui-widget ui-corner-all">
	<tr class="header">
		<th class="action_column"></th>
		<th>Last</th>
		<th>First</th>
		<th>Login</th>
	</tr>
	<?php foreach ($found_users as $user): ?>
	<tr class="<?php echo alternator('odd','even'); ?>">
		<td class="action_column"><?php echo anchor('user/setup/'.$user[$ldap['logn_nm']], 'Setup'); ?>
		</td>
		<td><?php echo $user[$ldap['lst_nm']]; ?></td>
		<td><?php echo $user[$ldap['frst_nm']]; ?></td>
		<td><?php echo $user[$ldap['logn_nm']]; ?></td>
	</tr>
	<?php endforeach; ?>
</table>
</div>
</fieldset>
	<?php endif; ?></div>
	<?php echo std_form_actions('action','Search','',$form_error); ?> <?php echo form_close(); ?>
</div>
