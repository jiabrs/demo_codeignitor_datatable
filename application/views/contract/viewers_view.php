<table id="cntrct_viewers">
	<tr class="header">
		<th></th>
		<?php foreach (Role_model::$perm_dsp as $perm_dsp): ?>
		<th><?php echo $perm_dsp; ?></th>
		<?php endforeach;?>
		<?php foreach ($this->location->get_by_div() as $div_cd => $div_data): ?>
			<?php foreach ($div_data['sls_ctrs'] as $sls_ctr_data): ?>
			<th><?php echo $sls_ctr_data['short_name']; ?></th>
			<?php endforeach; ?>
		<?php endforeach;?>
	</tr>
	<?php foreach ($roles as $role): ?>
	<tr class="<?php echo alternator('odd', 'even'); ?>">
		<td><?php echo $role->get_role_nm(); ?></td>
		<?php foreach (Role_model::$perm_dsp2 as $perm_cd => $perm_dsp): ?>
			<td><?php if ($role->has_perm($perm_cd)): ?>
			<span class="ui-icon ui-icon-check"></span>
			<?php endif;?></td>
		<?php endforeach;?>
		<?php foreach ($this->location->get_by_div() as $div_cd => $div_data): ?>
			<?php foreach ($div_data['sls_ctrs'] as $sls_ctr_cd => $sls_ctr_data): ?>
			<td><?php if ($role->has_loc($sls_ctr_cd)): ?>
			<span class="ui-icon ui-icon-check"></span>
			<?php endif; ?></td>
			<?php endforeach; ?>
		<?php endforeach;?>
	</tr>
	<?php endforeach; ?>
</table>