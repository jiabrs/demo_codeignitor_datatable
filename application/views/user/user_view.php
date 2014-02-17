<table class="dataTable">
	<thead>
		<tr>
			<th></th>
			<th>User ID</th>
			<th>Last</th>
			<th>First</th>
			<th>Login</th>
			<th>Enabled</th>
			<th>Last Login</th>
			<th>Created</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($users as $user): ?>
		<tr>
			<td class="actions">
				<?php if ($this->authr->authorize('MU', NULL, FALSE)): ?>
				<button type="button" title="Edit" class="edit" value="<?php echo site_url('user/setup/'.$user->LOGN_NM); ?>"><span class="ui-icon ui-icon-wrench"></span></button>
				<button type="button" title="Remove" class="modal remove" value="<?php echo site_url('user/remove/'.$user->LOGN_NM); ?>"><span class="ui-icon ui-icon-trash"></span></button>
				<button type="button" title="Assigned Contracts" class="link" value="<?php echo site_url('user/contract/'.$user->LOGN_NM); ?>"><span class="ui-icon ui-icon-document"></span></button>
				<?php endif; ?>
			</td>
			<td><?php echo $user->USR_ID; ?></td>
			<td><?php echo $user->LST_NM; ?></td>
			<td><?php echo $user->FST_NM; ?></td>
			<td><?php echo $user->LOGN_NM; ?></td>
			<td><?php echo User_model::$enbls_dsp[$user->ENBL]; ?></td>
			<td><?php echo $user->LST_LOGN_TM; ?></td>
			<td><?php echo $user->CREAT_TM; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
