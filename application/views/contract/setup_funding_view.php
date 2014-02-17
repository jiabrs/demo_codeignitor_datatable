<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('contract/setup_funding',array('id'=>'setup_funding_form')); ?>
		<div class="form_title">Assign Funding</div>
		<div class="form_content">
			<fieldset id="assign_cust">
				<legend>Assigned Elements</legend>
				<?php echo anchor('element/elem_lookup', 'Lookup Element', array('id'=>'lu_elem')); ?>
				<?php echo anchor('program/pgm_lookup', 'Lookup Program', array('id'=>'lu_pgm')); ?>
				<ul id="elements">
					<?php if ($program !== NULL): ?>
					<li class="program">
						<button class="remove_program ui-button ui-widget ui-state-default ui-corner-all">
							<span class="ui-icon ui-icon-trash"></span>
						</button>
						<?php echo $program->get_pgm_nm(); ?>
						<?php echo form_hidden('pgm_id', $program->get_pgm_id()); ?>
					</li>
					<?php endif; ?>
					<?php 
						foreach ($contract->get_elements() as $element)
						{
							$this->load->view('contract/setup_fnd_row_view', array('element'=> $element));
						}
					?>
				</ul>
			</fieldset>
		</div>
		<?php if ($contract->get_cntrct_id() === NULL && $multi_setup == 'multi'): ?>
			<?php echo std_form_actions('action','Create Contracts',$form_error); ?>
		<?php else: ?>
			<?php echo std_form_actions('action','Save & View Contract','Save & Return to Contracts',$form_error); ?>
		<?php endif; ?>
		<?php 
			echo ($contract->get_cntrct_id() !== NULL) ? form_hidden('cntrct_id',$contract->get_cntrct_id()) : ''; 
			echo form_hidden('cntrct_nm', $contract->get_cntrct_nm());
			echo form_hidden('cse_tp', $contract->get_cse_tp());
			echo form_hidden('strt_dt', $contract->get_strt_dt());
			echo form_hidden('end_dt', $contract->get_end_dt());
			echo form_hidden('appr_lst_id', $contract->get_appr_lst_id());
			echo form_hidden('vend_no', $contract->get_vend_no());
			echo form_hidden('dt_fmt','Y-m-d');
			echo form_hidden('elem_dt_fmt','m/d/Y');
			foreach ($contract->get_sls_ctr_cds() as $sls_ctr_cd)
			{
				echo form_hidden('sls_ctr_cd[]', $sls_ctr_cd);
			}
			foreach ($contract->get_out_ids() as $out_id)
			{
				echo form_hidden('cust_cd[]', $out_id);
				echo form_hidden('cust_tp[]', 'OT');
			}	
			foreach ($contract->get_key_acct_cds() as $key_acct_cd)
			{
				echo form_hidden('cust_cd[]', $key_acct_cd);
				echo form_hidden('cust_tp[]', 'KA');
			}
			foreach ($contract->get_trd_grp_cds() as $trd_grp_cd)
			{
				echo form_hidden('cust_cd[]', $trd_grp_cd);
				echo form_hidden('cust_tp[]', 'TG');
			}
			echo form_hidden('multi_setup', $multi_setup);
		?>		
	<?php echo form_close(); ?>
</div>
<div id="elem_lookup" class="modal_dialog"></div>
<div id="pgm_lookup" class="modal_dialog"></div>
<div id="confirm_pgm_remove">
	<span class="ui-icon ui-icon-alert" style="display: inline-block;"></span>
	Are you sure you want to replace the current program?
</div>