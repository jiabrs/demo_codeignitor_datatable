<table class="dataTable">
	<thead>
		<tr class="header">	
			<th></th>		
			<th>Name</th>
			<th>Criteria</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($collections as $collection): ?>
		<tr>
			<td class="actions">
				<button type="button" title="Edit" class="edit" value="<?php echo site_url('collection/setup/'.$collection->get_crit_clctn_id()); ?>"><span class="ui-icon ui-icon-wrench"></span></button>
				<button type="button" title="Copy" class="copy" value="<?php echo site_url('collection/copy/'.$collection->get_crit_clctn_id()); ?>"><span class="ui-icon ui-icon-copy"></span></button>
				<button type="button" title="Remove" class="remove modal" value="<?php echo site_url('collection/remove/'.$collection->get_crit_clctn_id()); ?>"><span class="ui-icon ui-icon-trash"></span></button>
			</td>
			<td><?php echo $collection->get_crit_clctn_nm(); ?></td>
			<td>
				<?php foreach ($collection->get_criteria() as $criteria): ?>
				<?php echo $criteria->dsp_crit_fld().": ".$criteria->dsp_crit_cd()."<br />"; ?>
				<?php endforeach; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>