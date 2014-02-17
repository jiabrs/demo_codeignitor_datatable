<div class="std_form width_large ui-widget ui-widget-content ui-corner-all">
	<?php echo form_open('role/setup', array('id'=>'search_form')); ?>
	<?php echo form_hidden('role_id', $role->get_role_id()); ?>
	<div class="form_title ui-corner-all">Setup Role</div>
	<div class="form_content">
		<fieldset id="role_info">
			<legend>Role Details</legend>
			<?php echo std_form_input('role_nm', 'Name', set_value('role_nm', $role->get_role_nm()), form_error('role_nm')); ?>
			<?php echo std_form_textarea('role_desc', 'Description', set_value('role_desc', $role->get_role_desc()), form_error('role_desc'), array('cols'=>'30', 'rows'=>'4')); ?>
		</fieldset>
		<fieldset id="role_sec">
			<legend>Role Security</legend> 
			<?php echo std_form_checkbox('app','Apps',$apps,set_value('app', $role->get_apps()),form_error('app[]'),TRUE); ?>
			<?php echo std_form_checkbox('perm','Permissions',Role_model::$perm_dsp, set_value('perm', $role->get_perms()), form_error('role[]'),TRUE); ?>
			<fieldset class="field_row">
				<legend>Locations</legend>
				<div class="field_grouping">
					<ul id="usr_locs">
						<?php foreach ($this->location->get_by_div() as $div_cd => $data): ?>
						<li class="div">
							<?php 
								$sls_ctr_span = '<span class="sls_ctr">';
								$div_checked = TRUE;
								foreach ($data['sls_ctrs'] as $sls_ctr_cd => $names)
								{
									$checked = (in_array($sls_ctr_cd, $role->get_locs()) ? TRUE : FALSE);
									if (!$checked) $div_checked = FALSE;
									
									$sls_ctr_span .= form_checkbox(array('name'=>"sls_ctr_cd[]",'id'=>'sls_ctr_cd_'.$sls_ctr_cd), $sls_ctr_cd, $checked);
									$sls_ctr_span .= '<label for="sls_ctr_cd_'.$sls_ctr_cd.'">'.$names['name']."</label><br />";
								}
								echo form_checkbox(array('name'=>'div[]','class'=>'div', 'id'=>'div_'.$div_cd), $div_cd, $div_checked);
								echo '<label for="div_'.$div_cd.'">'.$data['name'].'</label>';
								echo $sls_ctr_span."</span>";
							?>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</fieldset>
		</fieldset>
		<fieldset id="role_cntrct">
			<legend>Contracts</legend>
			<fieldset class="field_row">
				<legend>Restrict to the following contracts</legend>
				<div class="field_grouping">
					<ul id="contract_filter">
					<?php foreach ($role->get_cntrcts_for_setup_dsp() as $cntrct_id => $cntrct_nm): ?>
						<li>
							<button class="remove_li ui-button ui-widget ui-state-default ui-corner-all"><span class="ui-icon ui-icon-trash"></span></button>&nbsp;
							<input type="hidden" name="cntrct_id[]" value="<?php echo $cntrct_id; ?>" />#<?php echo $cntrct_id.'&nbsp;-&nbsp;'.$cntrct_nm; ?>
						</li>
					<?php endforeach; ?>
					</ul>
					<a href="#" id="lookup_contract" title="Limit report to specific contracts">Add Contract</a>
				</div>
			</fieldset>
		</fieldset>
	</div>
	<?php echo std_form_actions('action','Setup Role','',$form_error); ?> <?php echo form_close(); ?>
</div>
<div id="dialog-modal" class="css-hide" title="Lookup contracts">
	<?php echo form_open('role/search_contracts', 'id="search"'); ?>
		<label class="placeholder" for="search">Enter contract name</label><input type="text" name="search" id="search" />&nbsp;<input type="submit" value="Search" /><span id="search_result"></span>
	<?php echo form_close(); ?>
	<?php echo form_open('', 'id="search_results"'); ?>
		<?php echo form_multiselect('cntrct_result[]', array(), array(), 'id="result"'); ?>
		<input type="submit" value="Add Selected Contracts" />
	<?php echo form_close(); ?>
</div>