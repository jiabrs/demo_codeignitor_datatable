<?php
/**
 * Role_model Class
 *
 *   
 * @package ctm_cma
 * @subpackage models
 *
 */
class Role_model extends CI_Model {
	
	// display for user role
	public static $perm_dsp = array(
		'MC' => 'Manage Contracts',
		'MU' => 'Manage Users',
		'PC' => 'Print Check Requests',
		'UP' => 'Update Projections and Fixed Units',
		'CN' => 'Change Contract Notes'	
	);
	
	// display for user role
	public static $perm_dsp2 = array(
		'MC' => 'Change contract',
		'MU' => 'Give others access',
		'PC' => 'Print check requests',
		'UP' => 'Update projections/fixed units',
		'CN' => 'Edit notes'
	);
	
	private $role_id = NULL;
	private $role_nm = '';
	private $role_desc = '';
	private $locs = array();
	private $apps = array();
	private $perms = array();
	private $cntrct_ids = array();
	
	public function __construct($role_id = NULL)
	{
		parent::__construct();
		
		if ($role_id !== NULL)
		{
			$this->set_role_id($role_id); 
			$this->_load();
		}
	}
	
	function set_role_id($role_id)
	{
		if (intval($role_id) != 0)
			$this->role_id = intval($role_id);
	}
	
	function get_role_id()
	{
		return $this->role_id;
	}
	
	function set_role_nm($role_nm)
	{
		$this->role_nm = substr($role_nm, 0, 80);
	}	
	
	function get_role_nm()
	{
		return $this->role_nm;
	}
	
	function set_role_desc($role_desc)
	{
		$this->role_desc = substr($role_desc, 0, 140);
	}
	
	function get_role_desc()
	{
		return $this->role_desc;
	}
	
	/**
	 * Sets user's locations
	 * 
	 * @param array $locs
	 */
	function set_locs($locs)
	{
		$this->locs = $locs;
	}
	
	/**
	 * Returns assigned locations
	 * 
	 * @return array
	 */
	function get_locs()
	{
		return $this->locs;
	}
		
	/**
	 * Sets user's applications
	 */
	function set_apps($apps)
	{
		$this->apps = $apps;
	}
	
	/**
	 * Returns assigned applications
	 * 
	 * @return array
	 */
	function get_apps()
	{
		return $this->apps;
	}
	
	/**
	 * Sets user's applications
	 */
	function set_perms($perms)
	{
		$this->perms = $perms;
	}
	
	/**
	 * Returns assigned applications
	 * 
	 * @return array
	 */
	function get_perms()
	{
		return $this->perms;
	}
	
	function set_cntrct_ids($cntrct_ids)
	{
		$this->cntrct_ids = $cntrct_ids;
	}
	
	function get_cntrct_ids()
	{
		return $this->cntrct_ids;
	}
	
	/**
	 * Returns role object
	 * @param integer $role_id
	 * @return Role_model
	 */
	public static function get($role_id = NULL)
	{
		return new Role_model($role_id);
	}
	
	private function _load()
	{
		if ($this->role_id !== NULL)
		{
			$select = "SELECT *
				FROM ".APP_SCHEMA.".ROLE
				WHERE ROLE_ID = ?";
			
			$result = $this->db2->query($select, array($this->role_id))->row();
			
			if ($result !== NULL)
			{
				$this->set_role_nm($result->ROLE_NM);
				$this->set_role_desc($result->ROLE_DESC);
				
				$this->load_apps();
				$this->load_locs();
				$this->load_perms();
				$this->load_cntrct_ids();
			}
		}
	}
	
	/**
	 * Returns array of role objects by user id
	 * 
	 * @param unknown_type $user_id
	 */
	public static function get_by_user($user_id = NULL)
	{
		$CI =& get_instance();
		
		$select = "SELECT ROLE_ID
			FROM ".APP_SCHEMA.".USR_ROLE
			WHERE USR_ID = ?";
		
		$roles = array();
		
		foreach ($CI->db2->query($select, array($user_id))->fetch_object() as $row)
		{
			$roles[] = new Role_model($row->ROLE_ID);
		}
		
		return $roles;
	}
	
	function role_exists()
	{
		if ($this->role_id !== NULL)
		{
			$select = "SELECT *
				FROM ".APP_SCHEMA.".ROLE
				WHERE ROLE_ID = ?";
			
			if ($this->db2->query($select, array($this->role_id))->num_rows() > 0)
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
	function save()
	{
		$this->db2->begin_transaction();
		
		try {
			if ($this->role_exists())
			{
				// updates the user
				$update = "UPDATE ".APP_SCHEMA.".ROLE
					SET ROLE_NM = ?,
						ROLE_DESC = ?
					WHERE ROLE_ID = ?";
				
				$update_parms = array(
					$this->role_nm,
					$this->role_desc,
					$this->role_id
				);
				
				$this->db2->query($update, $update_parms);
				
				$this->remove_locs();
				$this->remove_apps();
				$this->remove_perms();
				$this->remove_cntrct_ids();
			}
			else
			{
				// inserts new user
				$insert = "SELECT * FROM FINAL TABLE (
					INSERT INTO ".APP_SCHEMA.".ROLE (ROLE_ID,ROLE_NM,ROLE_DESC)
					VALUES (NEXT VALUE FOR ".APP_SCHEMA.".SQ_ROLE_PK, ?, ?)
				)";
				
				$insert_parms = array(
					$this->role_nm,
					$this->role_desc
				);
				
				$result = $this->db2->query($insert, $insert_parms)->fetch_object();
				
				$this->set_role_id($result[0]->ROLE_ID);
			}
		
			// add relationships back
			$this->add_cntrct_ids();
			$this->add_locs();
			$this->add_apps();
			$this->add_perms();
			
		} catch (Exception $e) {
			$this->db2->rollback();
			
			$error =& load_class('Exceptions', 'core');
			echo $error->show_error("Error saving User", $e->getMessage(), 'error_general');
			exit;
		}		
		
		$this->db2->commit();
	}

	/**
	 * Loads user's locations from database
	 */
	function load_locs()
	{
		$sql = "SELECT SLS_CTR_CD
			FROM ".APP_SCHEMA.".ROLE_LOC
			WHERE ROLE_ID = ?";

		foreach ($this->db2->query($sql, array($this->role_id))->fetch_assoc() as $row)
		{
			$this->locs[] = $row['SLS_CTR_CD'];
		}
	}

	/**
	 * Loads user's apps from database
	 */
	function load_apps()
	{
		$sql = "SELECT APP
			FROM ".APP_SCHEMA.".ROLE_APP
			WHERE ROLE_ID = ?";

		foreach ($this->db2->query($sql, array($this->role_id))->fetch_assoc() as $row)
		{
			$this->apps[] = $row['APP'];
		}
	}
	
	/**
	 * Loads user's apps from database
	 */
	function load_cntrct_ids()
	{
		$sql = "SELECT CNTRCT_ID
			FROM ".APP_SCHEMA.".ROLE_CNTRCT
			WHERE ROLE_ID = ?";

		foreach ($this->db2->query($sql, array($this->role_id))->fetch_assoc() as $row)
		{
			$this->cntrct_ids[] = $row['CNTRCT_ID'];
		}
	}
	
	/**
	 * Loads user's apps from database
	 */
	function load_perms()
	{
		$sql = "SELECT PERM
			FROM ".APP_SCHEMA.".ROLE_PERM
			WHERE ROLE_ID = ?";

		foreach ($this->db2->query($sql, array($this->role_id))->fetch_assoc() as $row)
		{
			$this->perms[] = $row['PERM'];
		}
	}
	
	/**
	 * Deletes user and user's relationships from db
	 */
	function remove()
	{
		if ($this->role_id !== NULL)
		{
			$this->db2->begin_transaction();
			try {
				$this->remove_apps();
				$this->remove_locs();
				$this->remove_cntrct_ids();
				$this->remove_usrs();
				
				// remove user info
				$sql = "DELETE FROM ".APP_SCHEMA.".ROLE
					WHERE ROLE_ID = ".$this->role_id;
					
				$this->db2->simple_query($sql);
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
	 * Removes user's assigned locations from db
	 */
	function remove_locs()
	{
		if ($this->role_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".ROLE_LOC
				WHERE ROLE_ID = ?";
	
			$this->db2->query($sql, array($this->role_id));
		}
	}

	/**
	 * Removes user's assigned apps from db
	 */
	function remove_apps()
	{
		if ($this->role_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".ROLE_APP
				WHERE ROLE_ID = ?";
	
			$this->db2->query($sql, array($this->role_id));
		}
	}

	/**
	 * Removes permissions
	 */
	function remove_perms()
	{
		if ($this->role_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".ROLE_PERM
				WHERE ROLE_ID = ?";
	
			$this->db2->query($sql, array($this->role_id));
		}
	}
	
	/**
	 * Inserts user's locations into db
	 */
	function add_locs()
	{
		if ($this->locs !== FALSE && $this->role_id !== NULL)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".ROLE_LOC (SLS_CTR_CD,ROLE_ID) VALUES (?, ?)";
				
			$this->db2->set_sql($insert)->prepare();
			
			foreach ($this->locs as $loc)
			{
				$this->db2->set_params(array($loc, $this->role_id))->execute();
			}
		}
	}

	/**
	 * Inserts user's apps into db
	 */
	function add_apps()
	{
		if ($this->apps !== FALSE && $this->role_id !== NULL)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".ROLE_APP (APP,ROLE_ID) VALUES (?, ?)";

			$this->db2->set_sql($insert)->prepare();
			
			foreach ($this->apps as $app)
			{
				$this->db2->set_params(array($app, $this->role_id))->execute();
			}
		}
	}
	
	/**
	 * Inserts user's apps into db
	 */
	function add_perms()
	{
		if ($this->perms !== FALSE && $this->role_id !== NULL)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".ROLE_PERM (PERM,ROLE_ID) VALUES (?, ?)";

			$this->db2->set_sql($insert)->prepare();
			
			foreach ($this->perms as $perm)
			{
				$this->db2->set_params(array($perm, $this->role_id))->execute();
			}
		}
	}
	
	function add_cntrct_ids()
	{
		if ($this->role_id !== NULL)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".ROLE_CNTRCT (CNTRCT_ID, ROLE_ID)
				VALUES (?, ?)";
			
			$this->db2->set_sql($insert)->prepare();
			
			foreach ($this->cntrct_ids as $cntrct_id)
			{
				$this->db2->set_params(array($cntrct_id, $this->role_id))->execute();
			}
		}
	}
	
	function remove_cntrct_ids()
	{
		if ($this->role_id !== NULL)
		{
			$delete = "DELETE FROM ".APP_SCHEMA.".ROLE_CNTRCT
				WHERE ROLE_ID = ?";
			
			$this->db2->query($delete, array($this->role_id));
		}
	}
	
	/**
	 * Returns contract list for assigning contract to user
	 * 
	 * @param string $search
	 * @param array $sls_ctrs
	 * @param array $apps
	 * @return multitype:multitype:string NULL  
	 */
	public function search_contract_to_assign($search = '', $sls_ctrs = array(), $app = '')
	{
		$contracts = array();
		
		if ($this->role_id !== NULL && $search != '' && is_array($sls_ctrs) && count($sls_ctrs) > 0 && $app != '')
		{
			$select = "SELECT DISTINCT C.CNTRCT_ID, C.CNTRCT_NM, C.APP
				FROM ".APP_SCHEMA.".CNTRCT C
					JOIN ".APP_SCHEMA.".CNTRCT_LOC CL ON CL.CNTRCT_ID = C.CNTRCT_ID
					JOIN ".APP_SCHEMA.".ROLE_LOC UL ON UL.SLS_CTR_CD = CL.SLS_CTR_CD
					LEFT JOIN ".APP_SCHEMA.".ROLE_CNTRCT UC ON UC.CNTRCT_ID = C.CNTRCT_ID 
						AND UC.ROLE_ID = UL.ROLE_ID
				WHERE UL.ROLE_ID = ? 
					AND LOWER(CNTRCT_NM) LIKE ?
					AND APP = ?
					AND CL.SLS_CTR_CD IN (".rtrim(str_repeat('?,', count($sls_ctrs)), ',').")
					AND UC.CNTRCT_ID IS NULL
				ORDER BY CNTRCT_NM";
			
			$params = array_merge(array($this->role_id, "%".strtolower($search)."%", $app), $sls_ctrs);
			
			$contracts = $this->db2->query($select, $params)->fetch_object();
		}
		
		return $contracts;
	}
	
	/**
	 * Removes user/role relationship
	 */
	public function remove_usrs()
	{
		if ($this->role_id !== NULL)
		{
			$delete = "DELETE FROM ".APP_SCHEMA.".USR_ROLE
				WHERE ROLE_ID = ?";
			
			$this->db2->query($delete, array($this->role_id));
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
	public function get_datatable($locs = array(), $apps = array(), $search = NULL)
	{
		// Build a filter to restrict users by location and apps (if provided
		$select = "SELECT DISTINCT R.ROLE_ID, ROLE_NM, ROLE_DESC
			FROM ".APP_SCHEMA.".ROLE R
				JOIN ".APP_SCHEMA.".ROLE_LOC RL ON RL.ROLE_ID = R.ROLE_ID
				JOIN ".APP_SCHEMA.".ROLE_APP RA ON RA.ROLE_ID = R.ROLE_ID";
		
		$params = array();
		$wheres = array();
		
		if (is_array($locs) && count($locs) > 0)
		{
			$wheres[] = "RL.SLS_CTR_CD IN (".substr(str_repeat('?,', count($locs)), 0, -1).")"; 
			$params += $locs;
		}
		
		if (is_array($apps) && count($apps) > 0)
		{
			$wheres[] = "RA.APP IN (".substr(str_repeat('?,', count($apps)), 0, -1).")"; 
			$params += $apps;
		}
		
		if (count($wheres) > 0)
			$usr_id_filter .= " WHERE ".implode(' AND ', $wheres);
		
		return $this->db2->query($select, $params)->fetch_object();
	}
	
	public function get_cntrcts_for_setup_dsp()
	{
		$cntrcts = array();
		
		if ($this->role_id !== NULL)
		{
			$select = "SELECT C.CNTRCT_ID, C.CNTRCT_NM
				FROM ".APP_SCHEMA.".CNTRCT C
					JOIN ".APP_SCHEMA.".ROLE_CNTRCT RC ON RC.CNTRCT_ID = C.CNTRCT_ID
				WHERE RC.ROLE_ID = ?";
			
			
			foreach ($this->db2->query($select, array($this->role_id))->fetch_object() as $row)
			{
				$cntrcts[$row->CNTRCT_ID] = $row->CNTRCT_NM;
			}
		}
		
		return $cntrcts;
	}
	
	/**
	 * Searches Roles that user has access to. Used to lookup roles on the user/setup page
	 * 
	 * @param string $search
	 * @param array $sls_ctr_cds
	 * @param array $apps
	 */
	public static function search_to_assign_user($search = '', $sls_ctr_cds = array(), $apps = array())
	{
		$sub_sel = "SELECT DISTINCT R.ROLE_ID FROM ".APP_SCHEMA.".ROLE R";
		$joins = $wheres = $params = array();
		
		if (count($sls_ctr_cds) > 0)
		{
			$joins[] = "JOIN ".APP_SCHEMA.".ROLE_LOC RL ON RL.ROLE_ID = R.ROLE_ID";
			$wheres[] = "RL.SLS_CTR_CD NOT IN (".substr(str_repeat('?,', count($sls_ctr_cds)), 0, -1).")";
			$params = array_merge($params, $sls_ctr_cds);
		}
		
		if (count($apps) > 0)
		{
			$joins[] = "JOIN ".APP_SCHEMA.".ROLE_APP RA ON RA.ROLE_ID = R.ROLE_ID";
			$wheres[] = "RA.APP NOT IN (".substr(str_repeat('?,', count($apps)), 0, -1).")";
			$params = array_merge($params, $apps);
		}
		
		$select = "SELECT R.*
			FROM ".APP_SCHEMA.".ROLE R";
		
		// add filters if provided
		if (count($sls_ctr_cds) > 0 || count($apps) > 0)
		{
			$select .= ' JOIN ('.$sub_sel.' '.implode(' ', $joins).' WHERE '.implode(' AND ', $wheres).' ) AS FILTER ON FILTER.ROLE_ID = R.ROLE_ID';
		}
		
		if ($search != '')
		{
			$select .= " WHERE LOWER(R.ROLE_NM) LIKE ?
				OR LOWER(R.ROLE_ID) LIKE ?";
			$params[] = "%".strtolower($search)."%";
			$params[] = "%".strtolower($search)."%";
		}
		
		$CI =& get_instance();
		log_message('debug', $select."\nParams: ".print_r($params, TRUE));
		return $CI->db2->query($select, $params)->fetch_object();
	}
	
	/**
	 * Returns assigned apps as app code => app description array
	 * for use in dropdowns
	 * 
	 * @return array
	 */
	public function get_apps_dropdwn()
	{
		$codes = $this->config->item('code');
		return array_intersect_key($codes['app'], array_flip($this->apps));
	}
	
	/**
	 * Returns true if role has been assigned contract
	 * @param integer $cntrct_id
	 * @return boolean
	 */
	public function has_cntrct($cntrct_id)
	{
		if (count($this->cntrct_ids) > 0 && in_array($cntrct_id, $this->cntrct_ids))
		{
			return TRUE;
		}
		else // run query to check contract access
		{
			$select = "SELECT COUNT(DISTINCT C.CNTRCT_ID) AS CNT
				FROM ".APP_SCHEMA.".CNTRCT C
					JOIN ".APP_SCHEMA.".ROLE_APP RA ON RA.APP = C.APP
					JOIN ".APP_SCHEMA.".CNTRCT_LOC CL ON CL.CNTRCT_ID = C.CNTRCT_ID
					JOIN ".APP_SCHEMA.".ROLE_LOC RL ON RL.ROLE_ID = RA.ROLE_ID
						AND RL.SLS_CTR_CD = CL.SLS_CTR_CD
				WHERE RL.ROLE_ID = ?
					AND C.CNTRCT_ID = ?";
			
			$row = $this->db2->query($select, array($this->role_id, $cntrct_id))->row();
			
			if ($row->CNT == 1)
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Returns true if role has location
	 * @param unknown_type $sls_ctr_cd
	 * @return boolean
	 */
	public function has_loc($sls_ctr_cd)
	{
		if (in_array($sls_ctr_cd, $this->locs))
		{
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	 * Returns true if role has permission
	 * @param unknown_type $perm
	 * @return boolean
	 */
	public function has_perm($perm)
	{
		if (in_array($perm, $this->perms))
		{
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	public function get_by_cntrct($cntrct_id = NULL)
	{
		$roles = array();
		
		if ($cntrct_id !== NULL)
		{
			$select = "SELECT DISTINCT R.ROLE_ID
				FROM ".APP_SCHEMA.".ROLE R
					JOIN ".APP_SCHEMA.".USR_ROLE UR ON UR.ROLE_ID = R.ROLE_ID
					JOIN ".APP_SCHEMA.".ROLE_APP RA ON RA.ROLE_ID = R.ROLE_ID
					JOIN ".APP_SCHEMA.".ROLE_LOC RL ON RL.ROLE_ID = R.ROLE_ID
					JOIN ".APP_SCHEMA.".CNTRCT_LOC CL ON CL.SLS_CTR_CD = RL.SLS_CTR_CD
					JOIN ".APP_SCHEMA.".CNTRCT C ON C.CNTRCT_ID = CL.CNTRCT_ID
						AND C.APP = RA.APP
					LEFT JOIN ".APP_SCHEMA.".ROLE_CNTRCT RC ON RC.ROLE_ID = R.ROLE_ID
						AND RC.CNTRCT_ID = C.CNTRCT_ID
					LEFT JOIN (
						SELECT DISTINCT ROLE_ID
						FROM ".APP_SCHEMA.".ROLE_CNTRCT
					) AS RC2 ON RC2.ROLE_ID = R.ROLE_ID
				WHERE C.CNTRCT_ID = ?
					AND (RC2.ROLE_ID IS NULL
						OR (RC.ROLE_ID IS NOT NULL AND RC2.ROLE_ID IS NOT NULL)
					)";
			
			foreach ($this->db2->query($select, array($cntrct_id))->fetch_object() as $row)
			{
				$roles[] = new Role_model($row->ROLE_ID);
			}
		}
		
		return $roles;
	}
}