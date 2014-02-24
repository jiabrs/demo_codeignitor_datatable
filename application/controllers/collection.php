<?php
/**
 * Collection Class
 * 
 *   
 * @package ctm_cma
 * @subpackage controllers
 *
 */
class Collection extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
		
		$this->authr->authorize('MC');
	}
	
	function index()
	{
		$this->load->model('crit_coll_model');
		$this->load->model('coll_crit_model');
		
		$collections = $this->crit_coll_model->get_crit_clctns();
		
		$index_view['collections'] = array();
		
		foreach ($collections as $collection)
		{
			$this->coll_crit_model->assign($collection);
			$index_view['collections'][] = $collection;
		}
		
		$this->tab_title .= " > Criteria Collection";
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('collection/index_view',$index_view);
		$this->_add_view('sec_cont_close_view');
			
		$this->_add_css('std_form.css');
		$this->_add_js('collection/index.js');
			
		$this->_build();
	}
	
	/**
	 * Adds a new or edits an existing program
	 */
	function setup()
	{
		// load validation library
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->model('crit_coll_model');
		$this->load->model('coll_crit_model');
		$this->load->model('code_model');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');
		
		if ($this->form_validation->run() == FALSE)
		{
			// grab program object
			$collection = $this->crit_coll_model->get($this->uri->segment(3));
			
			$this->coll_crit_model->assign($collection);
			
			// load view
			$setup_view['collection'] = $collection;
			$setup_view['coll_crit_model'] = $this->coll_crit_model;
			$setup_view['code_model'] = $this->code_model;
			
			$setup_view['form_error'] = '';
			
			$this->tab_title .= " > Collection > Setup";
			
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('collection/setup_view', $setup_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('collection/setup.js');
				
			$this->_build();
		}
		else
		{
			$collection = $this->crit_coll_model->get($this->input->post('crit_coll_id'));
			
			$collection->set_crit_clctn_nm($this->input->post('crit_clctn_nm'));
			
			$crit_flds = $this->input->post('crit_fld');
			$crit_cds = $this->input->post('crit_cd');
			
			$coll_crits = array();
			foreach ($crit_flds as $index => $crit_fld)
			{
				$coll_crits[] = array(
					'crit_fld' => $crit_fld,
					'crit_cd' => $crit_cds[$index]
				);
			}
			
			$collection->set_clctn_crits($coll_crits);
			
			$collection->save();
			
			// Log activity
			if (!$this->input->post('crit_coll_id'))
			{
				$usr_actn = 'U';
			}
			else
			{
				$usr_actn = 'C';
			}
			
			$this->audit_model->log_activity(
				'CL',
				$collection->get_crit_coll_id(), 
				$usr_actn,
				$this->session->userdata('usr_id')
			);
			
			redirect('collection');
		}
	}
	
	/**
	 * Takes an existing program and makes a new copy
	 */
	function copy()
	{
		$this->load->model('program_model');
		
		$pgm_id = $this->uri->segment(3);
		
		// did we get a program id?
		if ($pgm_id !== FALSE)
		{
			// grab existing program
			$program = $this->program_model->get($pgm_id);
			
			// remove id
			$program->set_pgm_id(NULL);
			
			// change name to indicate it's a copy
			$program->set_pgm_nm($program->get_pgm_nm()." (COPY)");
			
			// save new program
			$program->save();
		}
		
		redirect('program');
	}
	
	/**
	 * Confirms removal of program
	 */
	function remove()
	{
		$this->load->model('program_model');
		
		// if not confirmed and uid provided in uri . . .
		if ($this->input->post('confirm') === FALSE && $this->uri->segment(3) !== FALSE)
		{	
			$remove_view['program'] = $this->program_model->get($this->uri->segment(3));
				
			$this->load->helper('form');

			// check for ajax request
			if (is_ajax())
			{
				// just send confirmation view
				$this->load->view('program/remove_view',$remove_view);
			}
			else // build view normally
			{
				$this->_add_view('sec_cont_open_view');
				$this->_add_view('program/remove_view', $remove_view);
				$this->_add_view('sec_cont_close_view');
					
				$this->_build();
			}
		}
		else
		{
			// get user's response
			$confirm = $this->input->post('confirm');
				
			$removed = FALSE;
			
			// user confirmed removal
			if ($confirm == "Continue")
			{
				// get program object
				$program = $this->program_model->get($this->input->post('pgm_id'));

				// remove program
				$program->remove();
				
				$this->audit_model->log_activity(
					'PG',
					$program->get_pgm_id(), 
					'D',
					$this->session->userdata('usr_id')
				);
				
				$removed = TRUE;
			}
				
			if (is_ajax())
			{
				echo json_encode($removed);
			}
			else
			{
				// send back to users list
				redirect('program');
			}
		}
	}
	
	function coll_lookup()
	{
		// Ajax only
		if(is_ajax())
		{
			$this->load->model('program_model');
			
			$locs = $this->input->post('locs');
			
			if ($locs === FALSE) $locs = $this->authr->get_locations();
				
			$app = $this->session->userdata('app');
			
			$pgm_lookup_view['programs'] = $this->program_model->get_programs($locs, $app);
			
			$this->load->view('program/pgm_lookup_view', $pgm_lookup_view);
		}
	}
}

/* End of file program.php */
/* Location: /ctm_cma/controllers/program.php */