<table class="dataTable">
	<thead>
		<tr>
			<th></th>
			<th>Role ID</th>
			<th>Name</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($roles as $role): ?>
		<tr>
			<td class="actions">
				<?php if ($this->authr->authorize('MU', NULL, FALSE)): ?>
				<button type="button" title="Edit" class="edit" value="<?php echo site_url('role/setup/'.$role->ROLE_ID); ?>"><span class="ui-icon ui-icon-wrench"></span></button>
				<button type="button" title="Remove" class="modal remove" value="<?php echo site_url('role/remove/'.$role->ROLE_ID); ?>"><span class="ui-icon ui-icon-trash"></span></button>
				<?php endif; ?>
			</td>
			<td><?php echo $role->ROLE_ID; ?></td>
			<td><?php echo $role->ROLE_NM; ?></td>
			<td><?php echo $role->ROLE_DESC; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
