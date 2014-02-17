<h3><?php echo $user->get_full_nm(); ?> &gt; <span class="red">Contract Restrictions</span></h3>
<?php echo form_open('user/search_contracts', 'id="cntrct_lookup"'); ?>
	<?php echo form_hidden('logn_nm', $user->get_logn_nm()); ?>
	<?php echo form_hidden('sls_ctr_cd', $user->get_locs()); ?>
	<label for="search">Lookup Contract:</label>&nbsp;<input type="text" name="search" id="search" />
	<?php foreach ($user->get_apps() as $app): ?>
		<?php echo form_radio('app', $app, ($app == $checked_app) ? TRUE : FALSE).$apps[$app]; ?> 
	<?php endforeach; ?>
	<input type="submit" value="Search" /><span id="search_result"></span>
<?php echo form_close(); ?>
<?php echo form_open('', 'class="assign_widget"'); ?>
	<div id="left">
	<label for="cntrct_result">Search Results:</label><br />
	<?php echo form_multiselect('cntrct_result[]', array(), array(), 'id="result"'); ?>
	</div>
	<div id="center">
	<button type="submit" name="action" value="assign" title="Add Selected Contracts"><span class="ui-icon ui-icon-carat-1-e"></span></button><br />
	<button type="submit" name="action" value="remove" title="Remove Selected Contracts"><span class="ui-icon ui-icon-carat-1-w"></span></button>
	</div>
	<div id="right">
	<label for="usr_cntrct"><?php echo $user->get_full_nm(); ?> may only view:</label><span id="assign_result"></span><br />
	<?php echo form_multiselect('cntrct_id[]', $usr_cntrcts, array(), 'id="assign"'); ?>
	</div>
<?php echo form_close(); ?>