<table class="dataTable">
	<thead>
		<tr class="header">	
			<th></th>		
			<th>Name</th>
			<th>Description</th>
			<th>Type</th>
			<th>Rate</th>
			<th>On Inv.</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($elements as $element): ?>
		<tr id="<?php echo $element['ELEM_ID']; ?>">
			<td>
				<button type="button" title="Add" class="add" value="<?php echo $element['ELEM_ID']; ?>"><span class="ui-icon ui-icon-plusthick"></span></button>
			</td>
			<td class="elem_nm"><?php echo $element['ELEM_NM']; ?></td>
			<td class="elem_desc"><?php echo $element['ELEM_DESC']; ?></td>
			<td class="elem_tp"><?php echo Element_model::$elem_tps[$element['ELEM_TP']]; ?></td>
			<td class="elem_rt">$<?php echo Element_model::format_elem_rt($element['ELEM_RT']); ?></td>
			<td class="on_inv_flg"><?php echo Element_model::$on_inv_flgs[$element['ON_INV_FLG']]; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>