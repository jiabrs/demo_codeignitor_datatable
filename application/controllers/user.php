<?php
/**
 * User class
 *
 * Handles User management, authorization, and ldap integration
 *
 *  cbrogan
 * @package ctm_cma
 * @subpackage Controllers
 *
 */
class User extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Loads user list for user management
	 */
	function index()
	{
		$this->authr->authorize('MU');

		// get collection of user objects
		$user_view['users'] = $this->user_model->get_datatable($this->authr->get_locations());

		$this->_add_view('sec_cont_open_view');
		$this->_add_view('user/user_view', $user_view);
		$this->_add_view('sec_cont_close_view');

		$this->_add_css('std_form.css');
		$this->_add_js('user/index.js');
			
		$this->_build();
	}

	/**
	 * Process Login form
	 */
	function login()
	{
		if ($this->authr->is_logged_in())
		{
			if ($this->session->userdata('app') !== FALSE && $this->session->userdata('role_id') !== FALSE)
			{
				redirect('');
			}
			else {
				redirect('user/environment');
			}
		}	
		
		// load validation library
		$this->load->library('form_validation');
		$this->load->library('ldap');
		$this->load->helper('form');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');

		if ($this->form_validation->run() == FALSE)
		{
			$login_view['auth_error'] = validation_errors();
				
			$this->_add_css('std_form.css');
			$this->_add_view('user/user_pass_view', $login_view);
			$this->_build();
		}
		else
		{
			// pull uid, password and selected app
			$logn_nm = $this->input->post('logn_nm');
			$password = $this->input->post('password');
				
			// check uid and password against ldap server
			if($this->ldap->authenticate($logn_nm, $password))
			{
				$redirect = $this->session->userdata('redirected_from');
				
				// update last login time
				$user = $this->user_model->get($logn_nm);

				$user->upd_lst_logn_tm();
				
				$userdata['usr_id'] = $user->get_usr_id();
				
				/*
				 * Once we've authenticated, additional environmental variables need
				 * to be set: role and application. If user has multiple roles and/or
				 * multiple applications, we need to prompt them on a second page.
				 * (maybe someday this will all be ajax based . . .) 
				 */
				$roles = $user->get_roles();	
				
				if (count($roles) > 1) 
				{
					// multiple roles, so redirect to set environment
					$redirect = '/user/environment';
				}
				elseif (count($roles) == 1) // just one role, so get list of apps
				{ 
					$userdata['role_id'] = $roles[0]->get_role_id();
					
					$apps = $roles[0]->get_apps();
					if (count($apps) > 1) 
					{
						// multiple apps, so redirect to set environment
						$redirect = '/user/environment';
					}	
					else 
					{
						// just one app for role, so we can continue on to app
						$userdata['app'] = $apps[0];
					}				
				}
				
				// set userdatas
				$this->session->set_userdata($userdata);
				
				log_message('debug', '-- User authenticated. Redirecting to '.$redirect);
				log_message('debug', 'Post authentication session info: '.print_r($this->session->all_userdata(), TRUE));
				// send user back to page they were redirected from
				redirect($redirect);
			}
			else // auth failed, so reload login form
			{
				$login_view['auth_error'] = $this->ldap->auth_error;
				
				$this->_add_css('std_form.css');
				$this->_add_view('user/user_pass_view', $login_view);
				$this->_build();
			}
		}
	}
	
	
	/**
	 * Sets user's current role and application
	 */
	public function environment()
	{	
		$this->load->library('form_validation');
			
		$user = $this->user_model->get_by_id($this->session->userdata('usr_id'));
		
		if (is_ajax() && $this->uri->segment(3) !== FALSE)
		{
			$role = Role_model::get($this->uri->segment(3));
			
			echo json_encode($role->get_apps_dropdwn());
		}
		elseif ($this->form_validation->run() === FALSE)
		{
			$this->load->helper('form');			
			
			$role_id = $this->session->userdata('role_id');
			$apps = array();
			$code = $this->config->item('code');
			$roles = $user->get_roles_dropdown();
			
			/* if we have at least one role, get the first one
			 * from the list and pull the apps so we can start
			 * the form with some defaults.
			 */
			if (count($roles) > 0)
			{
				$role = Role_model::get(key($roles));
				
				foreach ($role->get_apps() as $app)
				{
					$apps[$app] = $code['app'][$app];
				}
			}
			
			$env_view['roles'] = $roles;
			$env_view['role_id'] = $role_id;
			$env_view['apps'] = $apps;
			$env_view['error'] = validation_errors();
			
			$this->_add_css('std_form.css');
			$this->_add_js('user/environment.js');
			
			$this->_add_view('user/env_view', $env_view);
			$this->_build();
		}
		else {
			
			$app = $this->input->post('app');
			$role_id = $this->input->post('role');	
			
			$this->session->set_userdata('app', $app);
			$this->session->set_userdata('role_id', $role_id);
			
			redirect('');
		}
	}
	
	/**
	 * Validation function for user/environment
	 * Verifies posted app is available for role
	 * 
	 * @param string $app
	 * @return boolean
	 */
	function role_has_app($app)
	{
		$role = Role_model::get($this->input->post('role'));
		
		if (in_array($app, $role->get_apps()))
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('role_has_app', 'Application not assigned to role');
			return FALSE;
		}
	}
	
	/**
	 * Form validation callback function to check if uid
	 * has access to the application
	 *
	 * @param string $uid
	 * @return bool
	 */
	function _logn_nm_exists($logn_nm)
	{
		if ($this->user_model->logn_nm_has_access($logn_nm))
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('_logn_nm_exists', $logn_nm.' is not setup or has been disabled');
			return FALSE;
		}
	}

	/**
	 * Destroys user session information and redirects to login
	 *
	 */
	function logout()
	{
		$this->session->sess_destroy();

		redirect('user/login');
	}

	/**
	 * Set's requested application and redirects to default page
	 */
	function set_app()
	{
		$app = $this->uri->segment(3);

		if (in_array($app, $this->authr->get_apps()))
		{
			$this->session->set_userdata('app',$app);
		}

		redirect();
	}
	
	/**
	 * Set's requested application and redirects to default page
	 */
	function set_role()
	{
		$role_id = $this->uri->segment(3);

		log_message('debug', 'Available roles: '.print_r($this->authr->get_roles(), TRUE));
		// does user have access to this role?
		if ($this->authr->user->has_role($role_id))
		{
			log_message('debug', 'User has role');
			$this->session->set_userdata('role_id', $role_id);
			
			$new_role = Role_model::get($role_id);
			$apps = $new_role->get_apps();
			
			// based on role, need to set app appropriately
			if (in_array($this->session->userdata('app'), $apps))
			{
				log_message('debug', 'Role has app');
				// current app is available to role, so redirect
				redirect();
			}
			elseif (count($apps) == 1)
			{
				log_message('debug', 'Role does not have app, switching to new app');
				// current app not assigned to role, but only one app
				// available, so switch to that app
				$this->session->set_userdata('app', $apps[0]);
			}
			else {
				log_message('debug', 'Role has multiple other apps, redirecting to environment');
				// multiple apps available, so let user choose
				redirect('user/environment');
			}
		}
		
		redirect();
	}

	/**
	 * Loads search form for user lookup via ldap
	 *
	 */
	function lookup()
	{
		$this->load->library('ldap');
		$this->authr->authorize('MU');

		// grab submitted user name
		$first = trim($this->input->post('firstname'));
		$last = trim($this->input->post('lastname'));

		$this->load->helper('form');
		$this->load->helper('ldap');
			
		// if names submitted, do ldap search
		if ($first == FALSE && $last == FALSE)
		{
			$lookup_view['found_users'] = array();
		}
		else
		{
			$lookup_view['found_users'] = $this->ldap->search_users($first, $last);
		}
		
		// $lookup_view['ldap'] = array_map('strtolower', $this->config->item('ldap'));
		$lookup_view['ldap'] = $this->config->item('ldap');
		$lookup_view['form_error'] = '';

		// on ldap error, send message
		if ($this->ldap->auth_error !== FALSE)
		{
			$lookup_view['form_error'] = $this->ldap->auth_error;
		}

		
		$this->view_builder->tab_title .= "User > Lookup";
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('user/lookup_view', $lookup_view);
		$this->_add_view('sec_cont_close_view');
		$this->_add_css('std_form.css');

		$this->_build();
	}

	/**
	 * Takes uid from 3rd uri segment.  Loads user
	 * configuration screen
	 *
	 */
	function setup()
	{
		$this->authr->authorize('MU');
		$logn_nm = $this->uri->segment(3, $this->input->post('logn_nm'));
		
		// check for uid in URI segment
		if ($logn_nm !== FALSE)
		{
			$this->load->library('form_validation');
			$this->load->library('ldap');
			
			if ($this->form_validation->run() == FALSE)
			{
				// load necessary helpers/models
				$this->load->helper('form');
				$this->load->model('code_model');
					
				// grab app codes
				$codes = $this->config->item('code');

				// Load user object
				$user = $this->user_model->get($logn_nm);
				
				// Get latest user information from ldap
				$ldap_user = $this->ldap->get_user_info($logn_nm);

				// ldap user didn't exist, so send back to lookup form
				if ($ldap_user === FALSE)
				{
					redirect('user/lookup');
				}
				else
				{
					$this->load->library('location');
					
					// load defaults for view vars
					$setup_view['form_error'] = "";
					$setup_view['apps'] = $codes['app'];					
	
					// override cached user info with latest ldap info
					$user->set_fst_nm($ldap_user['fst_nm']);
					$user->set_lst_nm($ldap_user['lst_nm']);
	
					$setup_view['user'] = $user;
	
					// load setup view
					$this->view_builder->tab_title .= " > User > Setup";
					$this->_add_view('sec_cont_open_view');
					$this->_add_view('user/setup_view', $setup_view);
					$this->_add_view('sec_cont_close_view');
	
					$this->_add_css('std_form.css');
					$this->_add_js('user/role_search.js');
	
					$this->_build();
				}
			}
			else
			{
				$user = $this->user_model->get($this->input->post('logn_nm'));

				$user->set_fst_nm($this->input->post('fst_nm'));
				$user->set_lst_nm($this->input->post('lst_nm'));
				$user->set_enbl($this->input->post('enbl'));
				$user->set_roles($this->input->post('role_id'));
				$user->set_sls_ctr_cd($this->input->post('sls_ctr_cd'));

				$user->save();

				if ($user->get_usr_id() !== NULL)
				{
					$usr_actn = 'U';
				}
				else
				{
					$usr_actn = 'C';
				}
				
				$this->audit_model->log_activity(
					'US',
					$user->get_usr_id(), 
					$usr_actn,
					$this->session->userdata('usr_id')
				);
			
				redirect('user');
			}
		}
		else // redirect back to lookup if no login id was passed
		{
			redirect('user/lookup');
		}
	}

	/**
	 * confirms deletion of user
	 */
	function remove()
	{
		$this->authr->authorize('MU');
		
		// if not confirmed and uid provided in uri . . .
		if ($this->input->post('confirm') === FALSE && $this->uri->segment(3) !== FALSE)
		{				
			$user = $this->user_model->get($this->uri->segment(3));
				
			$remove_view['fullname'] = $user->get_full_nm();
			$remove_view['logn_nm'] = $user->get_logn_nm();
			$remove_view['usr_id'] = $user->get_usr_id();
				
			$this->load->helper('form');

			// check for ajax request
			if (is_ajax())
			{
				// just send confirmation view
				$this->load->view('user/remove_view',$remove_view);
			}
			else // build view normally
			{
				$this->_add_view('user/action_view', array());
				$this->_add_view('sec_cont_open_view');
				$this->_add_view('user/remove_view', $remove_view);
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
				// get user object
				$user = $this->user_model->get_by_id($this->input->post('usr_id'));

				// remove user
				$user->remove();
				
				// log activity
				$this->audit_model->log_activity(
					'US',
					$user->get_usr_id(), 
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
				redirect('user');
			}
		}
	}
	
	/**
	 * Ajax only function for populating autocomplete
	 * field with usernames
	 */
	function get_users()
	{
		// only respond if ajax request
		if (is_ajax())
		{
			// grab search string
			$search = $this->input->post('search');
				
			// return search results as json
			$users = array();
			
			foreach ($this->user_model->search_users($search) as $row)
			{
				$users[] = array(
					'value' => $row['FIRSTNAME']." ".$row['LASTNAME'],
					'label' => $row['FIRSTNAME']." ".$row['LASTNAME'],
					'name' => $row['FIRSTNAME']." ".$row['LASTNAME'],
					'uid' => $row['UID']
				);
			}
			echo json_encode($users);
		}
		else
		{
			redirect();
		}
	}
	
	function contract()
	{
		$logn_nm = $this->uri->segment(3);
		
		if ($logn_nm !== FALSE)
		{
			switch ($this->input->post('action'))
			{			
				case FALSE:
					$this->authr->authorize('MU');
				
					$user = $this->user_model->get($logn_nm);
					
					$apps = $user->get_apps();
					
					$codes = $this->config->item('code');
					
					$usr_cntrcts = array();
					
					foreach ($user->get_cntrcts() as $row)
					{
						$usr_cntrcts[$row->CNTRCT_ID] = $row->CNTRCT_NM." (#".$row->CNTRCT_ID.")";
					}
					
					$contract_view['user'] = $user;
					$contract_view['usr_cntrcts'] = $usr_cntrcts;
					$contract_view['apps'] = $codes['app'];
					$contract_view['checked_app'] = $apps[0];
					
					$this->_add_view('sec_cont_open_view');
					$this->_add_view('user/contract_view', $contract_view);
					$this->_add_view('sec_cont_close_view');
			
					$this->_add_css('std_form.css');
					$this->_add_js('user/contract.js');
						
					$this->_build();
					break;
					
				case 'assign':
					$user = $this->user_model->get($logn_nm);
					
					foreach ($this->input->post('cntrct_result') as $cntrct_id)
					{
						$user->add_cntrct($cntrct_id);
					}
					
					echo 'assign';
					break;
					
				case 'remove':
					$user = $this->user_model->get($logn_nm);
					
					foreach ($this->input->post('cntrct_id') as $cntrct_id)
					{
						$user->remove_cntrct($cntrct_id);
					}
					
					echo 'remove';					
					break;
			}
		}		
	}
	
	public function search_contracts()
	{
		$codes = $this->config->item('code');
		
		$logn_nm = $this->input->post('logn_nm');
		$search = $this->input->post('search');
		$sls_ctrs = $this->input->post('sls_ctr_cd');
		$apps = $this->input->post('app');
		
		$user = $this->user_model->get($logn_nm);
		
		$contracts = array();
		
		foreach ($user->search_contract_to_assign($search, $sls_ctrs, $apps) as $row)
		{
			$contracts[$row->CNTRCT_ID] = $row->CNTRCT_NM." (#".$row->CNTRCT_ID.")";
		}
		
		echo json_encode($contracts);
	}
	
	public function access_denied()
	{
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('user/access_denied_view');
		$this->_add_view('sec_cont_close_view');
			
		$this->_build();
	}
}
/* End of file user.php */
/* Location: ./user.php */