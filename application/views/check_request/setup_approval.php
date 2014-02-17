
<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('approval/setup_approval', array('id'=>'approval_setup_form')); ?>
    <?php echo ($approval->get_appr_lst_id() !== NULL) ? form_hidden('appr_lst_id',$approval->get_appr_lst_id()) : ''; ?>
		<div class="form_title">Setup User Approval</div>
		<div class="form_content">
			
				<fieldset id="assign_criteria">
				<legend>Approval User List</legend>
                        <ul id="usrs">
					<?php if (count($approval->get_usr_id_lst()) == 0): ?>
					<li class="no_results lststynone" >-&nbsp;No Users &nbsp;-</li>                          
               
				       <?php else: ?>
						<?php foreach ($approval->get_usr_id_lst() as $users): ?>
						<li id="usr_<?php echo $users;?>" class="lststynone">
							<button class="remove_li ui-button ui-widget ui-state-default ui-corner-all"><span class="css-inline_block ui-icon ui-icon-trash"></span></button>&nbsp;
							<?php echo $approval->get_usr_nm($users); ?>
							<?php echo form_hidden('usr_lst[]', $users); ?>
							 
						</li>
						<?php endforeach; ?>
					<?php endif; ?>
                                                

                                                </ul>
                                
                                  <div> 
                                 <button title="Add New User" id='add_usr' value="" class="ui-button ui-widget ui-state-default ui-corner-all"><span class="css-inline_block ui-icon ui-icon-plusthick"></span></button>&nbsp;     
                                     <strong> Add New User </strong>
                                 </div>
                                <br/>
                                        <div> <strong>Return Checks to</strong> <?php echo std_form_dropdown('return_usr','',$usr_list, $approval->get_return_usr_id())?></div>
				       
                                        <?php echo std_form_actions('action','Save Changes','',validation_errors()); ?>
                                </fieldset>
			
		</div>
		
	<?php echo form_close(); ?>
</div>

<div class="modal_dialog" id="adv_search_dialog" title="User Lookup">
	<div id="search_results">
                 <?php $uid = 'id="usr_found"';  echo form_multiselect('usr_found[]',$usr_list,'',$uid)?>
		<div class="flt_clr"></div><button id="adv_add_usr">Add User</button>
	</div>
	<div id="search_options">
		<?php echo form_open('approval/usr_appr_search', array('id'=>'adv_search_form')); ?>	
			
			<label for="user">User Name:</label>
			<input type="text" id="usr" name="usr" value="" />
			<input type="submit" id="search" value="Search" />
			<span class="loading" id="adv_usr_search_loading"><img src="<?php echo site_url('resource/images/ajax-loader.gif'); ?>" align="bottom" alt="Searching" /></span>
			<span id="search_info"></span>
		<?php echo form_close(); ?>
	</div>
</div>


