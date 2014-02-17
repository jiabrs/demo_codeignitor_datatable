<table class="simple">
	<thead>
		<tr><th>Contract #</th><th>Name</th><th>Start</th><th>End</th></tr>
	</thead>
	<tbody>
	<?php foreach ($contracts as $contract): ?>
		<tr class="<?php echo alternator('odd','even'); ?>">
			<td><?php echo $contract->CNTRCT_ID; ?></td>
			<td><?php echo $contract->CNTRCT_NM; ?></td>
			<td><?php echo $contract->STRT_DT; ?></td>
			<td><?php echo $contract->END_DT; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>