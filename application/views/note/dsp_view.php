<li id="note_<?php echo $note->get_note_id(); ?>" class="note_entry">
	<p><strong><span class="red"><?php echo $note->dsp_lst_updt_tm(); ?></span></strong>&nbsp;-
	<?php 
		echo substr($note->get_body(), 0, 80); 
		if (strlen($note->get_body()) > 80)
		{
			echo '<a href="#" class="show_more blue">&nbsp; . . . show more</a>';
			echo '<span class="more">'.substr($note->get_body(),80).'</span> <a href="#" class="show_less blue"> (show less)</a>';
		}
	?></p>
	<?php if ($this->authr->authorize('CN', $note->get_cntrct_id(), FALSE)): ?>
	<button class="remove_note ui-button ui-widget ui-state-default ui-corner-all" value="<?php echo $note->get_note_id(); ?>">
		<span class="ui-icon ui-icon-trash"></span>
	</button>
	<button class="edit_note ui-button ui-widget ui-state-default ui-corner-all" value="<?php echo $note->get_note_id(); ?>">
		<span class="ui-icon ui-icon-wrench"></span>
	</button>
	<?php endif; ?>
	<?php if ($note->has_file()): ?>
		File: <?php echo anchor('note/view_file/'.$note->get_note_id(), $note->dsp_file_nm(20), 'title="'.$note->get_file_nm_ext().'"'); ?><br />
	<?php endif; ?>	
</li>