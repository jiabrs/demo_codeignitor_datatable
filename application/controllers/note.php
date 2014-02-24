<?php
/**
 * Note Class
 * 
 *   
 * @package ctm_cma
 * @subpackage controllers
 *
 */
class Note extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		
		$this->load->model('note_model');
	}
	
	function add()
	{
		$this->authr->authorize('CN', $this->uri->segment(3));
		
		if (!is_ajax()) redirect();
		
		// grab program object
		$note = new Note_model();
		
		$note->set_cntrct_id($this->uri->segment(3));
		
		// load view
		$add_edit_view['note'] = $note;
		
		$this->load->view('note/add_edit_view', $add_edit_view);
	}
	
	function edit()
	{
		$this->authr->authorize('CN', $this->uri->segment(3));
		
		if (!is_ajax()) redirect();
		
		// grab program object
		$note = Note_model::get($this->uri->segment(3));
		
		// load view
		$add_edit_view['note'] = $note;
		
		$this->load->view('note/add_edit_view', $add_edit_view);
	}
	
	function update()
	{
		if ($this->input->post('body') !== FALSE)
		{
			$note = Note_model::get($this->input->post('note_id'));
			
			$this->authr->authorize('CN', $note->get_cntrct_id());
			
			$note->process_post();
			
			$note->save();
			
			// Log activity
			if (!$this->input->post('note_id'))
			{
				$usr_actn = 'U';
			}
			else
			{
				$usr_actn = 'C';
			}
			
			if ($note->get_note_id() !== NULL)
			{
				$this->audit_model->log_activity(
					'CN',
					$note->get_note_id(), 
					$usr_actn,
					$this->session->userdata('usr_id')
				);
			}
			
			$this->load->view('note/dsp_view', array('index'=>0,'note'=>$note));
		}		
	}
	
	function remove()
	{
		// if not confirmed and uid provided in uri . . .
		if (is_ajax() && $this->uri->segment(3) !== FALSE)
		{	
			// get note object
			$note = Note_model::get($this->uri->segment(3));

			$this->authr->authorize('CN', $note->get_cntrct_id());
			
			// remove note
			$note->remove();
			
			$this->audit_model->log_activity(
				'CN',
				$note->get_note_id(), 
				'D',
				$this->session->userdata('usr_id')
			);
				
			echo json_encode(TRUE);
		}
	}
	
	function view_file()
	{
		$this->load->helper('download');
		
		$note = Note_model::get($this->uri->segment(3));
		
		$data = file_get_contents($note->get_file_path()); // Read the file's contents
		$name = $note->get_file_nm().".".$note->get_file_ext();
		
		force_download($name, $data);
	}
}