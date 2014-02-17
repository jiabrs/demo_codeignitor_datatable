<table class="dataTable">
	<thead>
		<tr class="header">	
			<th></th>		
			<th>Name</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($programs as $program): ?>
		<tr id="<?php echo $program->get_pgm_id(); ?>">
			<td>
				<button type="button" title="Add" class="add" value="<?php echo $program->get_pgm_id(); ?>"><span class="ui-icon ui-icon-plusthick"></span></button>
			</td>
			<td class="elem_nm"><?php echo $program->get_pgm_nm(); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>