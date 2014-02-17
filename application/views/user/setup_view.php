<div class="std_form width_med ui-widget ui-widget-content ui-corner-all">
	<?php echo form_open('user/setup'); ?>
	<?php echo form_hidden('usr_id',$user->get_usr_id()); ?>
	<div class="form_title ui-corner-all">Setup User</div>
	<div class="form_content">
		<fieldset id="usr_info">
			<legend>User Information</legend> 
			<?php echo std_form_input('fst_nm', 'First Name', set_value('firstname', $user->get_fst_nm()), form_error('fst_nm'), array('class'=>'name req_field','readonly'=>'readonly')); ?>
			<?php echo std_form_input('lst_nm', 'Last Name', set_value('lastname', $user->get_lst_nm()), form_error('lst_nm'), array('class'=>'name req_field','readonly'=>'readonly')); ?>
			<?php echo std_form_input('logn_nm','Login ID',set_value('uid',$user->get_logn_nm()),form_error('logn_nm'),array('class'=>'name req_field','readonly'=>'readonly')); ?>
			<?php echo std_form_radio('enbl','User Status',user_model::$enbls_dsp, $user->get_enbl(),form_error('enbl')); ?>
			<?php echo std_form_dropdown('sls_ctr_cd', 'Location', $this->location->get_sls_ctr_drop_dwn(), set_value('sls_ctr_cd', $user->get_sls_ctr_cd()), form_error('sls_ctr_cd')); ?>
		</fieldset>
		<fieldset id="usr_roles">
			<legend>Roles</legend>
			<ul id="assigned_roles">
				<?php foreach ($user->get_roles() as $role): ?>
				<li class="ui-widget ui-corner-all">
					<button class="remove_li ui-button ui-widget ui-state-default ui-corner-all"><span class="ui-icon ui-icon-trash"></span></button>&nbsp;
					<?php echo $role->get_role_nm().": ".$role->get_role_desc(); ?>
					<?php echo form_hidden('role_id[]', $role->get_role_id()); ?>
				</li>
				<?php endforeach; ?>
			</ul>
			<a href="#" id="lookup_role" title="Add Role">Add Role</a>
		</fieldset>
	</div>
	<?php echo std_form_actions('action','Setup User','',$form_error); ?> <?php echo form_close(); ?>
</div>
<div id="dialog-modal" class="css-hide" title="Lookup Roles">
	<?php echo form_open('role/search', 'id="search"'); ?>
		<label class="placeholder" for="search">Enter role name</label><input type="text" name="search" id="search" />&nbsp;<input type="submit" value="Search" /><span id="search_result"></span>
	<?php echo form_close(); ?>
	<?php echo form_open('', 'id="search_results"'); ?>
		<?php echo form_multiselect('role_result[]', array(), array(), 'id="result"'); ?>
		<input type="submit" value="Add Selected Roles" />
	<?php echo form_close(); ?>
</div>