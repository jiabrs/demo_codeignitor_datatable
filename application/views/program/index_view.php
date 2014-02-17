<table class="dataTable">
	<thead>
		<tr class="header">	
			<th></th>		
			<th>Name</th>
			<th>Elements</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($programs as $program): ?>
		<tr>
			<td class="actions">
				<button type="button" title="Edit" class="edit" value="<?php echo site_url('program/setup/'.$program->get_pgm_id()); ?>"><span class="ui-icon ui-icon-wrench"></span></button>
				<button type="button" title="Copy" class="copy" value="<?php echo site_url('program/copy/'.$program->get_pgm_id()); ?>"><span class="ui-icon ui-icon-copy"></span></button>
				<button type="button" title="Remove" class="remove modal" value="<?php echo site_url('program/remove/'.$program->get_pgm_id()); ?>"><span class="ui-icon ui-icon-trash"></span></button>
			</td>
			<td><?php echo $program->get_pgm_nm(); ?></td>
			<td>
				<?php foreach ($program->get_elements() as $element): ?>
				<?php echo $element->get_elem_nm()." ($ ".$element->dsp_elem_rt().")<br />"; ?>
				<?php endforeach; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>