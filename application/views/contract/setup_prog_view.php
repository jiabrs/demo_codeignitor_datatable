<div id="elem_setup_prog" class="ui-widget ui-widget-content ui-corner-all">
	<div class="ui-corner-all div_title">Setup Contract</div>
	<ol>
		<li <?php echo ($progress == 'info') ? 'class="current"' : ''; ?>>Enter General Info</li>
		<?php if ($contract->get_cntrct_nm() != ''): ?>
			<li class="cntrct_prop"><span>Name:</span><br />&nbsp;&nbsp;<?php echo $contract->get_cntrct_nm(); ?></li>
		<?php endif; ?>
		<?php if ($contract->get_strt_dt() != ''): ?>
			<li class="cntrct_prop"><span>Dates:</span><br />&nbsp;&nbsp;<?php echo $contract->dsp_strt_dt('m/d/Y')." - ".$contract->dsp_end_dt('m/d/Y'); ?></li>
		<?php endif; ?>
		<li <?php echo ($progress == 'location') ? 'class="current"' : ''; ?>>Assign Locations</li>
		<?php if (count($contract->get_sls_ctr_cds()) > 0): ?>
			<li class="cntrct_prop"><span>Locations:
			<?php foreach($contract->get_locations() as $location): ?>
				</span><br />&nbsp;&nbsp;<?php echo $location; ?>
			<?php endforeach; ?>
			</li>
		<?php endif; ?>
		<li <?php echo ($progress == 'customer') ? 'class="current"' : ''; ?>>Assign Customers</li>
		<?php if (count($contract->get_customers()) > 0): ?>
			<li class="cntrct_prop"><span>Customers:</span>
			<?php foreach ($contract->get_customers() as $customer): ?>
				<br />&nbsp;&nbsp;<?php echo $customer->get_cust_nm(); ?>
			<?php endforeach; ?>
			</li>
		<?php endif; ?>
		<li <?php echo ($progress == 'funding') ? 'class="current"' : ''; ?>>Assign Funding</li>
	</ol>
</div>
