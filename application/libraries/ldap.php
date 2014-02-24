<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ldap Class
 *
 * Provides ldap tools specific to CCBCU environment
 *
 *   
 * @package ctm_cma
 * @subpackage Custom Libraries
 *
 */
class ldap {

	// CI super object
	var $CI = null;

	// ldap connection
	private $ldap_conn = NULL;

	private $host = 'localhost';
	private $port = 389;
	private $dn = '';
	private $logn_nm = 'uid';
	private $frst_nm = 'givenname';
	private $lst_nm = 'sn';
	private $base_filter = array();
	private $bind_pfx = '';
	private $bind_usr = '';
	private $bind_pass = '';

	public $auth_error = '';

	/**
	 * Init class
	 *
	 * Accepts array of settings in following format:
	 * 	'host' => ldap server,
	 *	'port' => ldap port,
	 *	'dn' => dn of user group,
	 *	'logn_nm' => username field,
	 *	'frst_nm' => first name field,
	 *	'lst_nm' => last name field,
	 *	'objectClass' => user class name
	 *
	 * @param array $params
	 */
	function __construct($params = NULL)
	{
		// Get CI Super Object
		$this->CI =& get_instance();

		// CI is going to pass array of params. Set appropriate object props
		foreach (array('host','port','dn','logn_nm','frst_nm','lst_nm','base_filter','bind_pfx','bind_usr','bind_pass') as $key)
		{
			$ldap_settings = $this->CI->config->item('ldap');
			
			if (isset($params[$key]))
			{
				$this->{"set_".$key}($params[$key]);
			}
			else // no param, so pull from config
			{
				$this->{"set_".$key}($ldap_settings[$key]);
			}
		}
	}

	function set_host($host)
	{
		$this->host = $host;
	}
	
	function set_port($port)
	{
		$this->port = $port;
	}
	
	function set_dn($dn)
	{
		$this->dn = $dn;
	}
	
	function set_logn_nm($logn_nm)
	{
		$this->logn_nm = $logn_nm;
	}
	
	function set_frst_nm($frst_nm)
	{
		$this->frst_nm = $frst_nm;
	}
	
	function set_lst_nm($lst_nm)
	{
		$this->lst_nm = $lst_nm;
	}
	
	function set_base_filter($base_filter)
	{
		$this->base_filter = $base_filter;
	}
	
	function set_bind_pfx($bind_pfx)
	{
		$this->bind_pfx = $bind_pfx;
	}
	
	function set_bind_usr($bind_usr)
	{
		$this->bind_usr = $bind_usr;
	}
	
	function set_bind_pass($bind_pass)
	{
		$this->bind_pass = $bind_pass;
	}
	
	/**
	 * Checks for ldap connection, then confirms user/password combo.
	 *
	 * @param string $logn_nm
	 * @param string $password
	 * @return bool
	 */
	function authenticate($logn_nm = NULL, $password = NULL)
	{
		// Did we get logn_nm and password?
		if(!isset($logn_nm) || !isset($password))
		{
			// set error
			$this->auth_error = "Please provide login name and password";
				
			return FALSE;
		}

		if ($this->_get_conn()) // Connection successful!
		{
			// check logn_nm and password
			$ldap_logn_nm_bind = @ldap_bind($this->ldap_conn, $this->bind_pfx.$logn_nm, $password);
				
			if ($ldap_logn_nm_bind) // logn_nm and password correct!
			{
				// kill connection
				ldap_close($this->ldap_conn);

				return TRUE;
			}
			else // logn_nm/password check fail
			{
				// set ldap error
				if (ldap_errno($this->ldap_conn) == 49)
				{
					$this->auth_error = "Incorrect Username or Password.  Please try again!";
				}
				else
				{
					$this->auth_error = "ldap error #".ldap_errno($this->ldap_conn).": ".ldap_error($this->ldap_conn);
				}

				// kill connection
				ldap_close($this->ldap_conn);

				return FALSE;
			}
		}
		else // connection failed
		{
			// kill connection
			// ldap_close($this->ldap_conn);
				
			return FALSE;
		}
	}

	

	/**
	 * Pulls list of users sorted by last name from ldap server
	 *
	 * @return array
	 */
	function get_users_list()
	{
		$users = array();

		if ($this->_get_conn())
		{
			// restrict search to Person documents
			$search = "(objectClass=Person)";
				
			// run search for lastname(sn), firstname(givenname), and logn_nm(uid)
			$search_results = ldap_search($this->ldap_conn, $this->dn, $search, array($this->lst_nm, $this->frst_nm, $this->logn_nm));
				
			// sort results by last name
			ldap_sort($this->ldap_conn, $search_results, $this->lst_nm);

			// retrieve user list
			$users = ldap_get_entries($this->ldap_conn, $search_results);
		}

		return $users;
	}

	/**
	 * Runs ldap query on first and last name
	 *
	 * @param string $first
	 * @param string $last
	 * @return string|bool
	 */
	function search_users($first, $last)
	{
		if ($this->_get_conn() && ($first !== FALSE || $last !== FALSE))
		{
			// this fixes operations error when going against Active Directory
			if ($this->bind_usr != '')
				ldap_bind($this->ldap_conn, $this->bind_pfx.$this->bind_usr, $this->bind_pass);
			
			$search = '(&';
			
			foreach ($this->base_filter as $attr => $value)
			{
				$search .= '('.$attr.'='.$value.')';
			}
			
			$search .= "(|(displayName=*".trim($first.' '.$last)."*)";
			
			if ($first !== FALSE && $first != '')
				$search .= "(".$this->frst_nm."=".$first."*)";
				
			if ($last !== FALSE && $last != '')
				$search .= "(".$this->lst_nm."=".$last."*)";
				
			$search .= "))";

			// run search for lastname(sn), firstname(givenname), and logn_nm(uid)
			if (!$search_results = ldap_search($this->ldap_conn, $this->dn, $search, array($this->lst_nm, $this->frst_nm, $this->logn_nm, 'displayname')))
			{
				$this->auth_error = "ldap error #".ldap_errno($this->ldap_conn).": ".ldap_error($this->ldap_conn);
				return FALSE;
			}
			else {
				// sort results by last name
				ldap_sort($this->ldap_conn, $search_results, $this->lst_nm);
	
				// retrieve user list
				// $users = ldap_get_entries($this->ldap_conn, $search_results);
					
				$users = array();
				
				// some users in active directory are missing sn and givenname attributes
				// we'll split the display name by the first blank and use it for first and last name
				for ($entry = ldap_first_entry($this->ldap_conn, $search_results);
					$entry != FALSE;
					$entry = ldap_next_entry($this->ldap_conn, $entry)
				)
				{
					// get the display name first
					$dsp_nms = ldap_get_values($this->ldap_conn, $entry, 'displayname');
					
					// split the display name on the first space for first and last name
					list($dsp_frst_nm, $dsp_lst_nm) = explode(' ', $dsp_nms[0], 2);
					
					// if first name attribute doesn't exist, set to display first name
					if (($frst_nms = @ldap_get_values($this->ldap_conn, $entry, $this->frst_nm)) === FALSE)
					{
						$user[$this->frst_nm] = $dsp_frst_nm;
					}
					else {
						$user[$this->frst_nm] = $frst_nms[0];
					}
					
					// if last name attribute doesn't exist, set to display last name
					if (($lst_nms = @ldap_get_values($this->ldap_conn, $entry, $this->lst_nm)) === FALSE)
					{
						$user[$this->lst_nm] = $dsp_lst_nm;
					}
					else {
						$user[$this->lst_nm] = $lst_nms[0];
					}
					
					// get the login name
					$logn_nms = ldap_get_values($this->ldap_conn, $entry, $this->logn_nm);
					
					$user[$this->logn_nm] = $logn_nms[0];
					
					$users[] = $user;
				}
				
				if (ldap_count_entries($this->ldap_conn, $search_results) == 0)
				{
					$this->auth_error = "No users found";
					return FALSE;
				}
				else
				{
					return $users;
				}
			}			
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Runs through available ldap servers until connection is made
	 *
	 * @return bool
	 */
	function _get_conn()
	{
		// connect to ldap server
		$ldap_conn = ldap_connect($this->host, $this->port);
		
		if ($ldap_conn > 0)
		{
			$this->ldap_conn = $ldap_conn;
			
			ldap_set_option($this->ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_set_option($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			
			return TRUE;
		}
		else {
			// We never got a connection
			// $this->auth_error = ldap_error($ldap_conn);
			$this->auth_error = 'Could not connect to ldap server(s): '.implode(', ', $tried_servs);
	
			return FALSE;
		}
	}

	/**
	 * Get's user's first and last name matching user id from ldap
	 * servers
	 *
	 * @param string $logn_nm
	 * @return array|bool
	 */
	function get_user_info($logn_nm = NULL)
	{
		// do we have logn_nm and valid conn?
		if ($logn_nm !== NULL && $this->_get_conn() !== FALSE)
		{
			// this fixes operations error when going against Active Directory
			if ($this->bind_usr != '')
				ldap_bind($this->ldap_conn, $this->bind_pfx.$this->bind_usr, $this->bind_pass);
			
			$search = '(&';
			
			foreach ($this->base_filter as $attr => $value)
			{
				$search .= '('.$attr.'='.$value.')';
			}
			
			$search .= "(".$this->logn_nm."=".$logn_nm."))";
			
			// run search for lastname(sn), firstname(givenname), and logn_nm(uid)
			$search_results = ldap_search($this->ldap_conn, $this->dn, $search, array($this->lst_nm, $this->frst_nm, $this->logn_nm, 'displayname'));
				
			// did we get any results?
			if (ldap_count_entries($this->ldap_conn, $search_results) == 0)
			{
				// no user found, set error and return false
				$this->auth_error = "User Not Found";

				ldap_close($this->ldap_conn);

				return FALSE;
			}
			else
			{
				// grab user info from first entry (should only be one)
				$entry = ldap_first_entry($this->ldap_conn, $search_results);

				// assign info and return array
				$firstname = @ldap_get_values($this->ldap_conn, $entry, $this->frst_nm);
				$lastname = @ldap_get_values($this->ldap_conn, $entry, $this->lst_nm);
				
				// not all users have first/last name assigned
				// if either are false use display name
				$dspname = ldap_get_values($this->ldap_conn, $entry, 'displayname');
				
				list($dsp_f, $dsp_l) = explode(' ', $dspname[0], 2);
								
				$user['fst_nm'] = (!$firstname[0]) ? $dsp_f : $firstname[0];
				$user['lst_nm'] = (!$lastname[0]) ? $dsp_l : $lastname[0];
				$user['logn_nm'] = $logn_nm;

				ldap_close($this->ldap_conn);

				return $user;
			}
		}
		else // nope . . .
		{
			return FALSE;
		}
	}
}
/* End of file ccbcu_auth.php */