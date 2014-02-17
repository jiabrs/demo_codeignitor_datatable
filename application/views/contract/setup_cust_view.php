<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('contract/setup_customer'); ?>
		<div class="form_title">Assign Customers</div>
		<div class="form_content">
			<fieldset id="cust_lookup">
				<legend>Setup Options</legend>
				<?php if ($contract->get_cntrct_id() === NULL): ?>
					<?php echo std_form_radio('multi_setup','Contract Assignment', $multi_setups, $multi_setup, '<span id="multi_setup_name">All contracts will be named <strong>'.substr($contract->get_cntrct_nm(), 0, 47).' - <em>customer name</em></strong></span>'); ?>
				<?php else: ?>
					<?php echo form_input('multi_setup', 'single'); ?>
				<?php endif; ?>
				<?php echo std_form_textarea('quick_add', 'Customer', '', '', array('class'=>'desc','data-href'=>site_url('customer/cust_quick_add'))); ?>
				<div class="field_row">
					<div class="field_grouping">
						<button id="add_cust">Quick Add</button> <?php echo anchor('element/element_lookup', 'Advanced Search', array('id'=>'adv_search')); ?>
						<span id="quick_add_sts"></span>
					</div>
				</div>
			</fieldset>
			<fieldset id="assign_cust">
				<legend>Assigned Customers</legend>
				<ul id="customers">
					<?php if (count($contract->get_customers()) == 0): ?>
					<li class="no_results">-&nbsp;No Customers Assigned&nbsp;-</li>
					<?php else: ?>
						<?php foreach ($contract->get_customers() as $customer): ?>
						<li	id="customer_<?php echo $customer->get_cust_cd(); ?>">
							<button class="remove_li ui-button ui-widget ui-state-default ui-corner-all"><span class="ui-icon ui-icon-trash"></span></button>&nbsp;
							<?php echo $customer->get_cust_nm().' ('.$customer->get_cust_tp().': '.$customer->get_cust_cd().')'; ?>
							<?php echo form_hidden('cust_cd[]', $customer->get_cust_cd()); ?>
							<?php echo form_hidden('cust_tp[]', $customer->get_cust_tp()); ?>
						</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</fieldset>
		</div>
		<?php echo std_form_actions('action','Next: Assign Funding','',validation_errors()); ?>
		<?php 
			echo ($contract->get_cntrct_id() !== NULL) ? form_hidden('cntrct_id',$contract->get_cntrct_id()) : ''; 
			echo form_hidden('cntrct_nm', $contract->get_cntrct_nm());
			echo form_hidden('cse_tp', $contract->get_cse_tp());
			echo form_hidden('strt_dt', $contract->get_strt_dt());
			echo form_hidden('end_dt', $contract->get_end_dt());
			echo form_hidden('appr_lst_id', $contract->get_appr_lst_id());
			echo form_hidden('vend_no', $contract->get_vend_no());
			echo form_hidden('dt_fmt','Y-m-d');
			foreach ($contract->get_sls_ctr_cds() as $sls_ctr_cd)
			{
				echo form_hidden('sls_ctr_cd[]', $sls_ctr_cd);
			}
		?>		
	<?php echo form_close(); ?>
</div>
<div class="modal_dialog" id="adv_search_dialog" title="Advanced Customer Lookup">
	<div id="search_results">
		<select name="cust_found" multiple="multiple" id="cust_found"></select><br />
		<button id="adv_add_cust">Add Selected Customers</button>
	</div>
	<div id="search_options">
		<?php echo form_open('customer/advanced_search', array('id'=>'adv_search_form')); ?>	
			<?php 
				foreach ($contract->get_sls_ctr_cds() as $sls_ctr_cd)
				{
					echo form_hidden('sls_ctr_cd[]', $sls_ctr_cd);
				}
			?>
			<label for="customer">Customer Name:</label>
			<input type="text" id="customer" name="customer" value="" />
			<label for="search_for">Search for:</label>
			<?php echo form_dropdown('search_for', customer_model::$cust_tps, $cust_tp,'id="search_for"'); ?>
			<label for="bustype">Limit to Bus. Type:</label>
			<?php echo form_dropdown('bustype', $bustypes, 1, 'id="bustype"'); ?>
			<label for="cust_return">Return Customers as:</label>
			<?php echo form_dropdown('cust_return', customer_model::$cust_tps, $cust_tp,'id="cust_return"'); ?><br />
			<input type="submit" id="search" value="Search" />
			<span class="loading" id="adv_cust_search_loading"><img src="<?php echo site_url('resource/images/ajax-loader.gif'); ?>" align="bottom" alt="Searching" /></span>
			<span id="search_info"></span>
		<?php echo form_close(); ?>
	</div>
</div>
