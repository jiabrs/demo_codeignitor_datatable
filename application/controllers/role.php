<?php
/**
 * Handles Role maintenance
 * 
 * @author cbrogan
 * @package ctm_cma
 * @subpackage Controllers
 */
class Role extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{
		$this->authr->authorize('MU');

		// get collection of user objects
		$user_view['roles'] = $this->role_model->get_datatable();

		$this->_add_view('sec_cont_open_view');
		$this->_add_view('role/role_view', $user_view);
		$this->_add_view('sec_cont_close_view');

		$this->_add_css('std_form.css');
		$this->_add_js('role/index.js');
			
		$this->_build();
	}
	
	function setup()
	{
		$this->authr->authorize('MU');
		
		$this->load->library('form_validation');
		$role_id = $this->uri->segment(3, $this->input->post('role_id'));
		
		if ($this->form_validation->run() == FALSE)
		{
			// load necessary helpers/models
			$this->load->helper('form');
			$this->load->model('code_model');
				
			// grab app codes
			$codes = $this->config->item('code');

			$this->load->library('location', $this->authr->get_locations());
				
			// load defaults for view vars
			$setup_view['form_error'] = "";
			$setup_view['apps'] = $codes['app'];
			$setup_view['role'] = Role_model::get($role_id);
			
			// load setup view
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('role/setup_view', $setup_view);
			$this->_add_view('sec_cont_close_view');

			$this->_add_css('std_form.css');
			$this->_add_js('role/contract_search.js');

			$this->_build();
		}
		else
		{
			$role = Role_model::get($role_id);
			
			$role->set_role_nm($this->input->post('role_nm'));
			$role->set_role_desc($this->input->post('role_desc'));
			$role->set_perms($this->input->post('perm'));
			$role->set_apps($this->input->post('app'));
			$role->set_locs($this->input->post('sls_ctr_cd'));
			$role->set_cntrct_ids($this->input->post('cntrct_id'));

			$role->save();

			if ($role->get_role_id() !== NULL)
			{
				$usr_actn = 'U';
			}
			else
			{
				$usr_actn = 'C';
			}
			
			$this->audit_model->log_activity(
				'RL',
				$role->get_role_id(), 
				$usr_actn,
				$this->session->userdata('usr_id')
			);
		
			redirect('role');
		}
	}
	
	function remove()
	{
		if (is_ajax())
		{
			// if not confirmed and id provided in uri . . .
			if ($this->input->post('confirm') === FALSE && $this->uri->segment(3) !== FALSE)
			{	
				$remove_view['role'] = Role_model::get($this->uri->segment(3));
					
				$this->load->helper('form');
	
				$this->load->view('role/remove_view',$remove_view);
			}
			else
			{
				// get user's response
				$confirm = $this->input->post('confirm');
					
				$removed = FALSE;
				
				// user confirmed removal
				if ($confirm == "Continue")
				{
					$role = Role_model::get($this->input->post('role_id'));
	
					// remove user
					$role->remove();
					
					// log activity
					$this->audit_model->log_activity(
						'RL',
						$role->get_role_id(), 
						'D',
						$this->session->userdata('usr_id')
					);
					
					$removed = TRUE;
				}
					
				echo json_encode($removed);
			}
		}
	}
	
	function apps()
	{
		if (is_ajax())
		{
			$role = Role_model::get($this->uri->segment(3));
			
			json_encode($role->get_apps());
		}
	}
	
	function search_contracts()
	{
		if (is_ajax())
		{
			$this->load->model('contract_model');
			
			$search = $this->input->post('search');
			$filters = array(
				'app' => $this->input->post('app'),
				'sls_ctr_cd' => $this->input->post('sls_ctr_cd')
			);
			
			$select_opts = array();
			
			foreach (Contract_model::search_by_name($search, $filters) as $row)
			{
				$select_opts[$row->CNTRCT_ID] = "#".$row->CNTRCT_ID." - ".$row->CNTRCT_NM;
			}
			
			echo json_encode($select_opts);
		}
	}
	
	function search()
	{
		if (is_ajax())
		{
			$search = $this->input->post('search');
			$codes = $this->config->item('code');
			$this->load->library('location');
			
			$apps = array_diff(array_keys($codes['app']), $this->authr->get_apps());
			$sls_ctr_cds = array_diff(array_keys($this->location->get_by_sls_ctr()), $this->authr->get_locations());
			
			$select_opts = array();
			
			foreach (Role_model::search_to_assign_user($search, $sls_ctr_cds, $apps) as $row)
			{
				$select_opts[$row->ROLE_ID] = "#".$row->ROLE_ID." - ".$row->ROLE_NM.": ".$row->ROLE_DESC;
			}
			
			echo json_encode($select_opts);
		}
	}
}