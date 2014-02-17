<?php
/**
 * User_model Class
 *
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage models
 *
 */
class User_model extends CI_Model {

	// display for user status
	public static $enbls_dsp = array(
		'Y' => 'Enabled',
		'N' => 'Disabled'
	);
	
	private $usr_id = NULL;
	private $logn_nm = NULL;
	private $fst_nm = NULL;
	private $lst_nm = NULL;
	private $lst_logn_tm = NULL;
	private $enbl = 'N';
	private $sls_ctr_cd = '';
	private $creat_tm = NULL;
	
	private $roles = array();

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Set user id
	 * 
	 * @param integer $usr_id
	 */
	function set_usr_id($usr_id)
	{
		if (is_int($usr_id))
		{
			$this->usr_id = $usr_id;
		}
	}
	
	/**
	 * Returns user id
	 * 
	 * @return integer
	 */
	function get_usr_id()
	{
		return $this->usr_id;
	}
	
	/**
	 * Sets Login Name
	 * 
	 * @param string $logn_nm
	 */
	function set_logn_nm($logn_nm)
	{
		$this->logn_nm = strtolower($logn_nm);
	}
	
	/**
	 * Returns login name
	 * 
	 * @return string
	 */
	function get_logn_nm()
	{
		return $this->logn_nm;
	}
	
	
	/**
	 * Sets First Name
	 * 
	 * @param string $fst_nm 
	 */
	function set_fst_nm($fst_nm)
	{
		$this->fst_nm = ucfirst(strtolower($fst_nm));
	}
	
	/**
	 * Gets First Name
	 * 
	 * @return string
	 */
	function get_fst_nm()
	{
		return $this->fst_nm;
	}
	
	/**
	 * Sets Last Name
	 * 
	 * @param string $lst_nm 
	 */
	function set_lst_nm($lst_nm)
	{
		$this->lst_nm = ucfirst(strtolower($lst_nm));
	}
	
	/**
	 * Gets Last Name
	 * 
	 * @return string
	 */
	function get_lst_nm()
	{
		return $this->lst_nm;
	}
	
	/**
	 * Returns user's full name
	 * 
	 * @return string
	 */
	function get_full_nm()
	{
		return $this->fst_nm." ".$this->lst_nm;
	}
	
	/**
	 * Returns last login time
	 */
	function get_lst_logn_tm()
	{
		return $this->lst_logn_tm;
	}
	
	/**
	 * Returns last login time formatted m/d/y g:i a
	 * 
	 * @return string
	 */
	function dsp_lst_logn_tm($format = 'm/d/y g:i a')
	{
		$date_array = date_parse(substr($this->lst_logn_tm,0,10)." ".substr($this->lst_logn_tm, 11));
		
		return date($format, mktime($date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year']));
	}
	
	/**
	 * Updates last login time
	 *
	 */
	function upd_lst_logn_tm()
	{
		if ($this->usr_id !== NULL)
		{
			$update = "UPDATE ".APP_SCHEMA.".USR
				SET LST_LOGN_TM = CURRENT_TIMESTAMP 
				WHERE USR_ID = '".$this->usr_id."'";
				
			$this->db2->simple_query($update);
		}
	}
	
	/**
	 * Sets user status
	 * 
	 * @param string $enbl
	 */
	function set_enbl($enbl)
	{
		if (array_key_exists($enbl, self::$enbls_dsp))
		{
			$this->enbl = $enbl;
		}
	}

	/**
	 * Returns user's status
	 * 
	 * @return string
	 */
	function get_enbl()
	{
		return $this->enbl;
	}
	
	/**
	 * Returns user status display
	 */
	function dsp_enbl()
	{
		return self::$enbls_dsp[$this->enbl];
	}
	
	/**
	 * Returns created time
	 * 
	 * @return string
	 */
	function get_creat_tm()
	{
		return $this->creat_tm;
	}
	
	/**
	 * Returns formated user created time
	 * 
	 * @return string
	 */
	function dsp_creat_tm($format = 'm/d/y g:i a')
	{
		$date_array = date_parse($this->creat_tm);
		
		return date($format, mktime($date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year']));
	}
	
	/**
	 * Sets user's roles
	 * 
	 * @param array $roles
	 */
	function set_roles($roles)
	{
		$this->roles = $roles;
	}
	
	/**
	 * Returns assigned roles
	 * 
	 * @return array
	 */
	function get_roles()
	{
		return $this->roles;
	}
	
	public function set_sls_ctr_cd($sls_ctr_cd)
	{
		$this->sls_ctr_cd = $sls_ctr_cd;
	}
	
	public function get_sls_ctr_cd()
	{
		return $this->sls_ctr_cd;
	}
	
	/**
	 * Creates new User_model object, loads user's information
	 * and returns reference to object
	 *
	 * @param string $uid
	 * @return object|bool
	 */
	function &get($logn_nm = NULL)
	{
		$user = new User_model();
		
		// if uid provided load uid's properties
		if ($logn_nm !== NULL)
		{			
			$user->set_logn_nm($logn_nm);
			$user->_load('logn_nm');
		}
		
		// return user_model object
		return $user;
	}
	
	function &get_by_id($usr_id = NULL)
	{
		$user = new User_model();
		
		// if usr_id provided load usr_id's properties
		if ($usr_id !== NULL)
		{
			$user->set_usr_id($usr_id);
			$user->_load('usr_id');
		}
		
		return $user;
	}
	
	/**
	 * Initializes user_model object member vars
	 * if user exists
	 *
	 */
	function _load($ld_by = 'usr_id')
	{
		if ($this->usr_id !== NULL || $this->logn_nm !== NULL)
		{
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".USR";
			
			if ($ld_by == 'usr_id')
			{
				$sql .= " WHERE USR_ID = ".$this->usr_id;
			}
			elseif ($ld_by == 'logn_nm' )
			{
				$sql .= " WHERE LOGN_NM = '".$this->logn_nm."'";
			}
				
			$results = $this->db2->simple_query($sql)->fetch_assoc();
				
			if (count($results) > 0)
			{
				$this->usr_id = $results[0]['USR_ID'];
				$this->fst_nm = $results[0]['FST_NM'];
				$this->lst_nm = $results[0]['LST_NM'];
				$this->logn_nm = $results[0]['LOGN_NM'];
				$this->lst_logn_tm = $results[0]['LST_LOGN_TM'];
				$this->enbl = $results[0]['ENBL'];
				$this->sls_ctr_cd = $results[0]['SLS_CTR_CD'];
				$this->creat_tm = $results[0]['CREAT_TM'];
	
				$this->load_roles();
			}
		}
	}
	
	/**
	 * Confirms user login name assigned to app and is enabled
	 *
	 * @param string $logn_nm
	 * @return bool
	 */
	function logn_nm_has_access($logn_nm = NULL)
	{
		if ($logn_nm !== NULL)
		{
			$sql = "SELECT * 
					FROM ".APP_SCHEMA.".USR 
					WHERE LOGN_NM = ?
					AND ENBL = 'Y'";
				
			if ($this->db2->query($sql, array($logn_nm))->num_rows() > 0)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Confirms if login name already setup
	 *
	 * @return bool
	 */
	public function logn_nm_exists($logn_nm = NULL)
	{
		if ($logn_nm !== NULL)
		{
			$sql = "SELECT * 
					FROM ".APP_SCHEMA.".USR 
					WHERE LOGN_NM = ?";
				
			if ($this->db2->query($sql, array($logn_nm))->num_rows() > 0)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Populates datatable
	 * 
	 * @param array $locs
	 * @param array $apps
	 * @param string $search
	 * @return array
	 */
	public function get_datatable($locs = array(), $search = NULL)
	{
		$params = $wheres = array();
		
		if (is_array($locs) && count($locs) > 0)
		{
			$wheres[] = "SLS_CTR_CD IN (".substr(str_repeat('?,', count($locs)), 0, -1).")"; 
			$params += $locs;
		}
		
		$select = "SELECT U.USR_ID, LST_NM, FST_NM, LOGN_NM, ENBL, 
				COALESCE(VARCHAR_FORMAT(LST_LOGN_TM, 'MM/DD/YYYY HH24:MI'), 'User has never logged in') AS LST_LOGN_TM,
				VARCHAR_FORMAT(CREAT_TM, 'MM/DD/YYYY HH24:MI') AS CREAT_TM
			FROM ".APP_SCHEMA.".USR U";
		
		// add search filters
		if ($search !== NULL && $search != '')
		{
			$wheres[] = "( TOLOWER(U.FST_NM) LIKE ?"
				." OR TOLOWER(U.LST_NM) LIKE ?"
				." OR TOLOWER(U.LOGN_NM) LIKE ? )";
			
			$params += array(
				"%".strtolower($search)."%",
				"%".strtolower($search)."%",
				"%".strtolower($search)."%"
			);
		}
		
		if (count($wheres) > 0)
			$select .= ' WHERE '.implode(' AND ', $wheres);
			
		return $this->db2->query($select, $params)->fetch_object();
	}

	/**
	 * Checks to see if user is active
	 *
	 * @return bool
	 */
	function is_enbl()
	{
		if ($this->active == "Y")
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Checks to see if user has provided role
	 *
	 * @param integer $role_id
	 * @return bool
	 */
	function has_role($role_id)
	{
		foreach ($this->roles as $role)
		{
			if ($role_id == $role->get_role_id())
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}

	/**
	 * Checks to see if user has provided location
	 *
	 * @param string $loc
	 * @return bool
	 */
	function has_loc($loc)
	{
		return in_array($loc, $this->locs);
	}

	/**
	 * Checks to see if user has provided app
	 *
	 * @param string $app
	 * @return bool
	 */
	function has_app($app)
	{
		return in_array($app, $this->apps);
	}

	/**
	 * Checks users status against $status
	 *
	 * @param string $status
	 * @return bool
	 */
	function has_status($status)
	{
		return $status == $this->enbl;
	}

	/**
	 * Commits user info and relationships to db
	 */
	function save()
	{
		$this->db2->begin_transaction();
		
		try {
			if ($this->_exists())
			{
				// updates the user
				$update = "UPDATE ".APP_SCHEMA.".USR
					SET FST_NM = ?,
						LST_NM = ?,
						ENBL = ?,
						SLS_CTR_CD = ?
					WHERE LOGN_NM = ?";
				
				$update_parms = array(
					$this->fst_nm,
					$this->lst_nm,
					$this->enbl,
					$this->sls_ctr_cd,
					$this->logn_nm
				);
				
				$this->db2->query($update, $update_parms);
				
				$this->remove_roles();
			}
			else
			{
				// inserts new user
				$insert = "SELECT * FROM FINAL TABLE (
					INSERT INTO ".APP_SCHEMA.".USR (USR_ID,FST_NM,LST_NM,LOGN_NM,ENBL,SLS_CTR_CD,CREAT_TM)
					VALUES (NEXT VALUE FOR ".APP_SCHEMA.".SQ_USR_PK, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
				)";
				
				$insert_parms = array(
					$this->fst_nm,
					$this->lst_nm,
					$this->logn_nm,
					$this->enbl,
					$this->sls_ctr_cd
				);
				
				$result = $this->db2->query($insert, $insert_parms)->fetch_object();
				
				$this->set_usr_id($result[0]->USR_ID);
			}
		
			// add relationships back
			$this->add_roles();
			
		} catch (Exception $e) {
			$this->db2->rollback();
			
			$error =& load_class('Exceptions', 'core');
			echo $error->show_error("Error saving User", $e->getMessage(), 'error_general');
			exit;
		}		
		
		$this->db2->commit();
	}

	/**
	 * Checks to see if record exists in db before inserting
	 * 
	 * @return boolean
	 */
	private function _exists()
	{
		if ($this->usr_id !== NULL)
		{
			$sql = "SELECT * 
				FROM ".APP_SCHEMA.".USR
				WHERE USR_ID = ?";
				
			if ($this->db2->query($sql,array($this->usr_id))->num_rows() > 0)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Deletes user and user's relationships from db
	 */
	function remove()
	{
		if ($this->usr_id !== NULL)
		{
			$this->db2->begin_transaction();
			try {
				$this->remove_roles();
				
				// remove user info
				$sql = "DELETE FROM ".APP_SCHEMA.".USR
					WHERE USR_ID = ?";
					
				$this->db2->query($sql, array($this->usr_id));
			} catch (Exception $e) {
				$this->db2->rollback();
			
				$error =& load_class('Exceptions', 'core');
				echo $error->show_error("Error removing Element", $e->getMessage(), 'error_general');
				exit;
			}		
			
			$this->db2->commit();
		}
	}

	
	/**
	 * does case insensitive search of uid, firstname & lastname
	 * matching provided search string.  Used with ajax user
	 * autocomplete field
	 *
	 * @param string $search Search string
	 * @return array
	 */
	function search_users($search = NULL)
	{
		if ($search !== NULL)
		{
			// make the string lowercase
			$search = strtolower($search);
				
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".USR
				WHERE LOGN_NM LIKE ?
					OR LCASE(FST_NM) LIKE ?
					OR LCASE(LST_NM) LIKE ?
				ORDER BY LST_NM ASC";
				
			return $this->db2->query($sql, array("%".$search."%"))->fetch_assoc();
		}
	}
	
	/** 
	 * Returns user objects assigned to contract
	 * 
	 * @param string $contract_id
	 * 
	 * @return array
	 */
	function get_by_contract($contract_id)
	{
		$sql = "SELECT U_C.*
			FROM ".APP_SCHEMA.".USR_CONTRACT AS U_C
			WHERE U_C.CONTRACT_ID = ".$contract_id;
		
		$uids = array();
		
		foreach ($this->db2->simple_query($sql)->fetch_object() as $row)
		{
			$uids[$row->LOGN_NM] = $this->get($row->LOGN_NM);
		}
		
		return $uids;
	}
	
	/**
	 * Takes submitted object, checks for $uids and $users property
	 * and assigns user objects to the object
	 * 
	 * @param object $assignee
	 * 
	 * @return nothing
	 */
	function assign(&$assignee)
	{
		$users = array();
		
		foreach ($assignee->get_usr_ids() as $usr_id)
		{
			$users[$usr_id] = $this->get_by_id($usr_id);
		}
		
		$assignee->set_users($users);
	}
	
	/**
	 * Removes user's assigned roles from db
	 */
	function remove_roles()
	{
		if ($this->usr_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".USR_ROLE
				WHERE USR_ID = ?";

			$this->db2->query($sql, array($this->usr_id));
		}		
	}
	
	/**
	 * Loads user's roles from database
	 */
	function load_roles()
	{
		$CI =& get_instance();
		
		$CI->load->model('role_model');
		
		if ($this->usr_id !== NULL)
		{
			$this->set_roles(Role_model::get_by_user($this->usr_id));
		}
	}
	
	/**
	 * Inserts user's roles into db
	 */
	function add_roles()
	{
		if ($this->roles !== FALSE && $this->usr_id !== NULL)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".USR_ROLE (ROLE_ID,USR_ID) VALUES (?, ?)";
				
			$this->db2->set_sql($insert)->prepare();
			
			foreach ($this->roles as $role_id)
			{
				$this->db2->set_params(array($role_id, $this->usr_id))->execute();
			}
		}
	}
	
	public function get_roles_dropdown()
	{
		$roles = array();
		if ($this->usr_id !== NULL)
		{
			$select = "SELECT *
			FROM ".APP_SCHEMA.".ROLE R
				JOIN ".APP_SCHEMA.".USR_ROLE U ON U.ROLE_ID = R.ROLE_ID
			WHERE U.USR_ID = ?";
			
			foreach ($this->db2->query($select, array($this->usr_id))->fetch_object() as $row)
			{
				$roles[$row->ROLE_ID] = $row->ROLE_NM;
			}
		}		
		
		return $roles;
	}
}
/* End of file user_model.php */
/* Location: /ctm_cma/models/user_model.php */