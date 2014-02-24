<?php
/**
 * Program Class
 * 
 *   
 * @package ctm_cma
 * @subpackage controllers
 *
 */
class Program extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
		
		$this->authr->authorize('MC');
	}
	
	function index()
	{
		$this->load->model('program_model');
		$this->load->model('element_model');
		
		$index_view['programs'] = $this->program_model->get_programs($this->session->userdata('app'));
		
		$this->tab_title .= " > Contract Programs";
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('program/index_view',$index_view);
		$this->_add_view('sec_cont_close_view');
			
		$this->_add_css('std_form.css');
		$this->_add_js('program/index.js');
			
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
		$this->load->model('program_model');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');
		
		if ($this->form_validation->run() == FALSE)
		{
			// grab program object
			$program = $this->program_model->get($this->uri->segment(3));
			
			$program->process_post();
			
			// load view
			$setup_view['program'] = $program;
			$setup_view['form_error'] = '';
			
			$this->tab_title .= " > Program > Setup";
			
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('program/setup_view', $setup_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('program/setup.js');
				
			$this->_build();
		}
		else
		{
			$program = $this->program_model->get($this->input->post('pgm_id'));
			
			$program->process_post();
			
			$program->save();
			
			// Log activity
			if (!$this->input->post('pgm_id'))
			{
				$usr_actn = 'U';
			}
			else
			{
				$usr_actn = 'C';
			}
			
			$this->audit_model->log_activity(
				'PG',
				$program->get_pgm_id(), 
				$usr_actn,
				$this->session->userdata('usr_id')
			);
			
			redirect('program');
		}
	}
	
	/**
	 * Ajax only function for autocompleted field in setup view
	 */
	function get_elements()
	{
		if (is_ajax())
		{
			$this->load->model('element_model');
			$search = $this->input->post('search');
			
			// grab elements for app and location
			$elements = $this->element_model->search_elements($search, $this->authr->get_locations(), $this->session->userdata('app'), TRUE);
			
			$elems_to_return = array();
			
			foreach ($elements as $row)
			{
				$elems_to_return[] = array(
					'label' => $row->NAME,
					'value' => $row->NAME,
					'element_id' => $row->ELEMENT_ID,
					'pgm_nm' => $row->NAME,
					'desc' => $row->DESC,
					'rate' => $row->RATE
				);
			}
			
			echo json_encode($elems_to_return);
		}
	}
	
	/**
	 * Checks program changes for at least one assigned funding element.
	 * Used by form validation
	 * 
	 * @return boolean
	 */
	function _assigned_elements($elements)
	{
		if (count($elements) > 0)
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('_assigned_elements','Contract Program must have at least one funding element assigned.');
			return FALSE;
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
			$program->clear_pgm_id();
			
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
	
	/**
	 * Returns json_encoded list of element ids for 
	 * provide program
	 * 
	 */
	function j_get_elem_ids()
	{
		if (is_ajax())
		{
			$this->load->model('program_model');
			
			$program = $this->program_model->get($this->uri->segment(3));
			
			echo json_encode($program->get_elem_ids(), JSON_FORCE_OBJECT);
		}
	}
	
	function j_get_elems_for_cntrct()
	{
		if (is_ajax())
		{
			$this->load->helper('form');
			$this->load->model('program_model');
			$this->load->model('element_model');
			
			$program = $this->program_model->get($this->input->post('add_pgm_id'));
			
			$html = '<li class="program">
						<button class="remove_program ui-button ui-widget ui-state-default ui-corner-all css_right">
							<span class="ui-icon ui-icon-trash"></span>
						</button>'.$program->get_pgm_nm().form_hidden('pgm_id', $program->get_pgm_id()).'</li>';
			
			foreach (Element_model::get_elements_by_pgm_id($program->get_pgm_id()) as $element)
			{
				$element->set_strt_dts(array(0 => $this->input->post('strt_dt')));
				$element->set_end_dts(array(0 => $this->input->post('end_dt')));
				
				$html .= $this->load->view(
					'contract/setup_fnd_row_view', 
					array(
						'element'=>$element,
						'app' => $this->session->userdata('app')
					), 
					TRUE
				);
			}
			
			echo $html;
		}
	}
	
	function pgm_lookup()
	{
		// Ajax only
		if(is_ajax())
		{
			$this->load->model('program_model');
			
			$app = $this->session->userdata('app');
			
			$pgm_lookup_view['programs'] = $this->program_model->get_programs($app);
			
			$this->load->view('program/pgm_lookup_view', $pgm_lookup_view);
		}
	}
}

/* End of file program.php */
/* Location: /ctm_cma/controllers/program.php */