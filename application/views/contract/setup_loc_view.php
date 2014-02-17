<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('contract/setup_locations'); ?>
		<div class="form_title">Assign Locations</div>
		<div class="form_content">
			<fieldset id="assign_location">
				<legend>Assigned Locations</legend> 
				<ul id="cntrct_setup_locs">
					<?php foreach ($this->location->get_by_div() as $div_cd => $data): ?>
					<li class="div">
						<?php 
							$sls_ctr_span = '<span class="sls_ctr">';
							$div_checked = TRUE;
							foreach ($data['sls_ctrs'] as $sls_ctr_cd => $names)
							{
								$checked = (in_array($sls_ctr_cd, $contract->get_sls_ctr_cds()) ? TRUE : FALSE);
								if (!$checked) $div_checked = FALSE;
								
								$sls_ctr_span .= form_checkbox(array('name'=>"sls_ctr_cd[]"), $sls_ctr_cd, $checked).$names['name']."<br />";
							}
							echo form_checkbox(array('name'=>'div[]','class'=>'div'), $div_cd, $div_checked).$data['name'];
							echo $sls_ctr_span."</span>";
						?>
					</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
		</div>
		<?php echo std_form_actions('action','Next: Assign Customers','',$form_error); ?>
		<?php 
			echo ($contract->get_cntrct_id() !== NULL) ? form_hidden('cntrct_id',$contract->get_cntrct_id()) : ''; 
			echo form_hidden('cntrct_nm', $contract->get_cntrct_nm());
			echo form_hidden('cse_tp', $contract->get_cse_tp());
			echo form_hidden('strt_dt', $contract->get_strt_dt());
			echo form_hidden('end_dt', $contract->get_end_dt());
			echo form_hidden('appr_lst_id', $contract->get_appr_lst_id());
			echo form_hidden('vend_no', $contract->get_vend_no());
			echo form_hidden('dt_fmt','Y-m-d');
		?>		
	<?php echo form_close(); ?>
</div>
