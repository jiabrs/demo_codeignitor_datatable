<li 
	id="element_<?php echo $element->get_elem_id(); ?>" 
	class="ui-widget ui-corner-all<?php echo ($element->get_cntrct_pgm_id() !== NULL) ? " program_element" : ""; ?>"
	<?php echo ($element->get_cntrct_pgm_id() !== NULL) ? ' pgm_id="'.$element->get_cntrct_pgm_id().'"' : ""; ?>
>
	<?php echo form_hidden('element['.$element->get_elem_id().'][elem_id]',$element->get_elem_id()); ?>
	<?php if ($element->get_cntrct_pgm_id() == NULL): ?>
	<button class="remove_li ui-button ui-widget ui-state-default ui-corner-all css_right">
		<span class="ui-icon ui-icon-trash"></span>
	</button>
	<?php else: ?>
		<?php echo form_hidden('element['.$element->get_elem_id().'][pgm_id]', $element->get_cntrct_pgm_id()); ?>
	<?php endif; ?>
	<strong><?php echo "#".$element->get_elem_id()."&nbsp;-&nbsp;".$element->get_elem_nm()." - ".$element->dsp_on_inv_tp(); ?></strong><br />
	<div class="field_row">
		<label class="preField">Rate:</label><span class="red">$<?php echo $element->dsp_elem_rt(); ?></span><br />
		<div class="flt_clr"></div>
	</div>
	<?php if ($app == 'CM'): ?>
	<div class="field_row">
		<label class="preField">Payment Freq:</label><?php echo form_dropdown('element['.$element->get_elem_id().'][pymt_freq]', element_model::$pymt_freqs, $element->get_pymt_freq()); ?><br />
		<div class="flt_clr"></div>
	</div>
	<?php else: ?>
	<?php echo form_hidden('element['.$element->get_elem_id().'][pymt_freq]','00'); ?>
	<?php endif; ?>
	<fieldset class="field_row">
		<legend>Date Range:</legend>
		<div class="field_grouping">	
			<?php foreach ($element->get_dt_range() as $index => $dts): ?>						
			<span class="dt_range">
				<input type="text" class="elem_dt" name="element[<?php echo $element->get_elem_id(); ?>][strt_dt][]" value="<?php echo $dts['strt_dt']; ?>" /> - 
				<input type="text" class="elem_dt" name="element[<?php echo $element->get_elem_id(); ?>][end_dt][]" value="<?php echo $dts['end_dt']; ?>" />
				<?php echo ($index > 0) ? anchor('#', 'Remove', array('class'=>'elem_remove_dt','elem_id'=>$element->get_elem_id())) : ''; ?>
				<br />
			</span>
			<?php endforeach; ?>
			<?php echo anchor('#', 'Add Dates', array('class'=>'elem_add_dt','elem_id'=>$element->get_elem_id())); ?>
		</div>
	</fieldset>
</li>