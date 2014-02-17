<div class="std_form width_large ui-widget ui-corner-all ui-widget-content">
	<?php echo form_open('report/detailed_accrual', array('id'=>'search_form')); ?>
	<div class="form_title">Detailed Accrual Report Options</div>
	<div class="form_content">
		<fieldset class="field_row">
			<legend>Locations:</legend>
			<div class="field_grouping">
				<ul id="locs">
					<?php foreach ($this->location->get_by_div() as $div_cd => $data): ?>
					<li class="div">
						<?php echo form_checkbox(array('name'=>'div[]','class'=>'div'), $div_cd, FALSE).$data['name']; ?><br />&nbsp;&nbsp;
						<span class="sls_ctr">
							<?php foreach ($data['sls_ctrs'] as $sls_ctr_cd => $names): ?>
								<?php echo form_checkbox(array('name'=>"sls_ctr[]"), $sls_ctr_cd, FALSE).$names['short_name']; ?>&nbsp;
							<?php endforeach; ?>
						</span>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</fieldset>
		<?php echo std_form_dropdown('accr_yr', 'Accrual Year', $accrual_years, date('Y')); ?>
		<?php echo std_form_checkbox('inv', 'Show On Invoice', array('N'=>'Post Invoice', 'Y'=>'On Invoice'), array('N')); ?>
		<?php echo std_form_radio('accr_tp', 'Show Accrual Amounts', $accr_tps, 'ttl'); ?>
		<fieldset class="field_row">
			<legend>Restrict to contracts</legend>
			<div class="field_grouping">
				<ul id="contract_filter"></ul>
				<a href="#" id="lookup_contract" title="Limit report to specific contracts">Lookup Contract</a>
			</div>
		</fieldset>
	</div>
	<div class="actions"><input type="submit" name="run" value="Run Report" /></div>
	<?php echo form_close(); ?>
</div>
<div id="dialog-modal" class="css-hide" title="Lookup contracts">
	<?php echo form_open('contract/search', 'id="search"'); ?>
		<label class="placeholder" for="search">Enter contract name</label><input type="text" name="search" id="search" />&nbsp;<input type="submit" value="Search" /><span id="search_result"></span>
	<?php echo form_close(); ?>
	<?php echo form_open('', 'id="search_results"'); ?>
		<?php echo form_multiselect('cntrct_result[]', array(), array(), 'id="result"'); ?>
		<input type="submit" value="Add Selected Contracts" />
	<?php echo form_close(); ?>
</div>