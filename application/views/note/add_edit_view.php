<?php echo form_open_multipart('note/update',array('id'=>'note_form')); ?>
	<?php echo ($note->get_note_id() !== NULL) ? form_hidden('note_id', $note->get_note_id()) : ''; ?>
	<?php echo form_hidden('cntrct_id', $note->get_cntrct_id()); ?>
	<label for="body">Note Text:</label><br />
	<?php echo form_textarea(array('name'=> 'body','cols'=>'50','rows'=>'6'), $note->get_body()); ?><br />
	<label for="file">File</label>
	<?php echo form_upload('file'); ?>
<?php echo form_close(); ?>