<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * authr Class
 *
 * Authorizes user access to specific resources
 *
 *   
 * @package ctm_cma
 * @subpackage Custom Libraries
 *
 */
class authr {

	private $CI = NULL;
	public $user = NULL;
	public $role = NULL;
	
	public function __construct()
	{
		// Get CI Super Object
		$this->CI =& get_instance();
		
		$this->CI->load->model('user_model');
		$this->CI->load->model('role_model');
		log_message('debug', 'Begin authorization. Session data: '.print_r($this->CI->session->all_userdata(), TRUE));
		
		// grab requested controller and method
		$controller = $this->CI->uri->segment(1);
		$method = $this->CI->uri->segment(2);
		
		// grab custom userdata vars
		$usr_id = $this->CI->session->userdata('usr_id');
		$role_id = $this->CI->session->userdata('role_id');
		$app = $this->CI->session->userdata('app');
		
		// send to login page if we're not logged in and we're not trying to login
		if ($usr_id === FALSE && !($controller == 'user' && $method == 'login'))
		{
			// kill the session to make sure
			$this->CI->session->sess_destroy();
			
			log_message('debug', '-- Not logged in. Redirecting to user/login');
			
			// send to login page
			redirect('user/login');
		}
		else {
			// load model for authenticated user		
			$this->user = $this->CI->user_model->get_by_id($usr_id);
		}
		
		/*
		 * send to environment page if we're logged in, haven't set our role/app 
		 * and we're not trying to logout
		 */ 
		if ($usr_id !== FALSE && ($role_id === FALSE || $app === FALSE)
			&& !($controller == 'user' && ($method == 'environment' || $method == 'logout')))
		{
			log_message('debug', '-- Environment not set. Redirecting to user/environment');
			redirect('user/environment');
		}
		else {
			// get active role
			$this->role = new Role_model($role_id);
		}
	}
	
	/**
	 * Determines if user has permission
	 * 
	 * @param string $permission	2 digit permission code
	 * @param integer $cntrct_id	Contract Identifier
	 * @param bool $redirect		Enable redirection to no access page
	 * @return bool
	 */
	function authorize($permission = NULL, $cntrct_id = NULL, $redirect = TRUE)
	{
		$has_app = in_array($this->CI->session->userdata('app'), $this->role->get_apps());
			
		/**
		 * assume user has permission and contract if not provided
		 */
		$has_permission = TRUE; 
		if ($permission !== NULL)
			$has_permission = in_array($permission, $this->role->get_perms());
		
		$has_contract = TRUE;
		if ($cntrct_id !== NULL)
			$has_contract = $this->role->has_cntrct($cntrct_id);
		
		if ($redirect && !($has_app && $has_permission && $has_contract))
		{
			if (is_ajax())
			{
				header('Status: 403 Forbidden');
			}
			else {
				redirect('user/access_denied');
			}
		}
		
		return $has_app && $has_permission && $has_contract;
	}	
	
	/**
	 * Indicates whether the current user is logged in
	 * 
	 * @return boolean
	 */
	function is_logged_in()
	{
		if ($this->CI->session->userdata('usr_id') !== FALSE)
		{
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	 * Returns information about currently logged in user
	 * 
	 * @param string $key
	 * @return string
	 */
	function get_user_info($key = NULL)
	{
		if ($key !== NULL && method_exists($this->user, 'get_'.$key))
		{
			return $this->user->{"get_".$key}();
		}
		else
		{
			return 'User info does not exist';
		}
	}
	
	function get_apps()
	{
		return $this->role->get_apps();
	}
	
	function get_locations()
	{
		return $this->role->get_locs();
	}
	
	function get_roles()
	{
		return $this->user->get_roles();
	}
	
	function get_role_nm()
	{
		return $this->role->get_role_nm();
	}
}