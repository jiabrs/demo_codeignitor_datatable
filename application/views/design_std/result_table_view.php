
<table class="result_table">
	<tr class="header">
		<th class="action_column"></th>
		<?php foreach ($table_data[0] as $col_name => $col_val): ?>
		<th><?php echo $col_name; ?></th>
		<?php endforeach; ?>
	</tr>
	<?php for ($i=1;$i<10;$i++): ?>
	<tr>
		<th colspan="<?php echo 1+count($table_data[0]); ?>">Sub Header <?php echo $i; ?></th>
	</tr>
	<?php foreach ($table_data as $row_id => $row_data): ?>
	<tr class="<?php echo alternator('odd','even'); ?>">
		<td class="action_column"><?php echo anchor('#', '<span class="ico_edit"></span>', array('title'=>'Edit')); ?>
		<?php echo anchor('#', '<span class="ico_remove"></span>', array('title'=>'Remove')); ?>
		</td>
		<?php foreach ($row_data as $row_col => $row_val): ?>
		<td><?php echo $row_val; ?></td>
		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
	<?php endfor; ?>
</table>
