<?php
/**
 * Class Crit_coll_model
 * 
 * Retrieves element criteria related info from db
 * 
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage models
 *
 */
class Crit_coll_model extends CI_Model {
	
	// Criteria properties
	private $crit_clctn_id = NULL;
	private $crit_clctn_nm = NULL;
	
	private $criteria = array();
	private $clctn_crits = array();
	
	/**
	 * Initiates parent class
	 * 
	 * @return unknown_type
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Sets criteria collection id of criteria field
	 * 
	 * @param integer $crit_clctn_id 
	 */
	function set_crit_clctn_id($crit_clctn_id)
	{
		$this->crit_clctn_id = $crit_clctn_id;
	}
	
	/**
	 * Returns criteria collection id assigned to criteria
	 * 
	 * @return integer
	 */
	function get_crit_clctn_id()
	{
		return $this->crit_clctn_id;
	}
	
	/**
	 * Sets criteria collection name
	 * 
	 * @param string $crit_clctn_nm
	 */
	function set_crit_clctn_nm($crit_clctn_nm)
	{
		$this->crit_clctn_nm = trim($crit_clctn_nm);
	}
	
	/**
	 * Returns criteria collection name
	 * 
	 * @return string
	 */
	function get_crit_clctn_nm()
	{
		return $this->crit_clctn_nm;
	}
	
	/**
	 * Returns collection name escaped for db2 query
	 * 
	 * @return string
	 */
	function esc_crit_clctn_nm()
	{
		return str_replace("'","''",$this->crit_clctn_nm);
	}
	
	/**
	 * Sets collection's criteria
	 * 
	 * @param array $clctn_crits
	 */
	function set_clctn_crits($clctn_crits)
	{
		$this->clctn_crits = $clctn_crits;
	}
	
	/**
	 * Returns collection's criteria
	 * 
	 * @return array
	 */
	function get_clctn_crits()
	{
		return $this->clctn_crits;
	}
	
	/**
	 * Sets collection's criteria objects
	 * 
	 * @param array $criteria
	 */
	function set_criteria($criteria)
	{
		$this->criteria = $criteria;
	}
	
	/**
	 * Returns collection's criteria objects
	 * 
	 * @return array:
	 */
	function get_criteria()
	{
		return $this->criteria;
	}
	
	/**
	 * Gets criteria object
	 * 
	 * @param integer $crit_clctn_id
	 * 
	 * @return object
	 */
	function &get ($crit_clctn_id = NULL)
	{
		$criteria = new Crit_coll_model();
		
		if ($crit_clctn_id !== NULL && $crit_clctn_id !== FALSE)
		{			
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".CRIT_CLCTN
				WHERE CRIT_CLCTN_ID = ".$crit_clctn_id;
			
			$results = $this->db2->simple_query($sql)->fetch_object();
		
			$row = $results[0];
			
			$criteria->set_crit_clctn_id($row->CRIT_CLCTN_ID);
			$criteria->set_crit_clctn_nm($row->CRIT_CLCTN_NM);
			
			$criteria->load_coll_crits();
		}		
		
		return $criteria;
	}
	
	function load_coll_crits()
	{
		if ($this->crit_clctn_id !== NULL)
		{
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".CLCTN_CRIT
				WHERE CRIT_CLCTN_ID = ".$this->crit_clctn_id;
			
			$clctn_crits = array();
			
			foreach ($this->db2->simple_query($sql)->fetch_object() as $row)
			{
				$clctn_crits[] = array(
					'crit_fld' => $row->CRIT_FLD,
					'crit_cd' => $row->CRIT_CD
				);
			}
			
			$this->clctn_crits = $clctn_crits;
		}
	}
	/**
	 * Returns array of collection objects
	 * 
	 * @return array
	 */
	function get_crit_clctns()
	{
		$sql = "SELECT *
			FROM ".APP_SCHEMA.".CRIT_CLCTN";
		
		$crit_clctns = array();
		
		foreach ($this->db2->simple_query($sql)->fetch_object() as $row)
		{
			$crit_clctns[] = $this->get($row->CRIT_CLCTN_ID);
		}
		
		return $crit_clctns;
	}
	
	/**
	 * Saves collection to database
	 */
	function save()
	{
		$this->db2->begin_transaction();
		
		try {
			if ($this->exists())
			{
				// create update sql statement
				$update = "UPDATE ".APP_SCHEMA.".CRIT_CLCTN
					SET CRIT_CLCTN_NM = ?
					WHERE CRIT_CLCTN_ID = ?";
				
				$update_parms = array($this->get_crit_clctn_nm(), $this->crit_clctn_id);
				
				$this->db2->query($update, $update_parms);
				
				$this->remove_criteria();
			}
			else
			{
				// create insert statement
				$insert = "SELECT * FROM FINAL TABLE ( 
					INSERT INTO ".APP_SCHEMA.".CRIT_CLCTN (CRIT_CLCTN_ID,CRIT_CLCTN_NM)
					VALUES (NEXT VALUE FOR ".APP_SCHEMA.".SQ_CRIT_CLCTN_PK, ?) 
				)";
				
				$insert_parms = array($this->get_crit_clctn_nm());
				
				$result = $this->db2->query($insert, $insert_parms)->fetch_object();
				
				$this->set_crit_clctn_id($result[0]->CRIT_CLCTN_ID);
			}
						
			// add relationships back
			$this->add_criteria();
			
		} catch (Exception $e) {
			$this->db2->rollback();
			
			$error =& load_class('Exceptions', 'core');
			echo $error->show_error("Error saving Criteria collection", $e->getMessage(), 'error_general');
			exit;
		}		
		
		$this->db2->commit();
	}
	
	/**
	 * Checks db table for record with collection id
	 * 
	 * @return bool
	 */
	function exists()
	{
		if ($this->crit_clctn_id !== NULL)
		{
			$sql = "SELECT * 
				FROM ".APP_SCHEMA.".CRIT_CLCTN
				WHERE CRIT_CLCTN_ID = ".$this->crit_clctn_id;
				
			if (count($this->db2->simple_query($sql)->fetch_assoc()) > 0)
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
	 * creates collection criteria record in db 
	 * for each assigned criteria
	 */
	function add_criteria()
	{
		if ($this->crit_clctn_id !== NULL && count($this->clctn_crits) > 0)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".CLCTN_CRIT (CRIT_CLCTN_ID,CRIT_CD,CRIT_FLD) VALUES (?, ?, ?)";
			
			$this->db2->set_sql($insert)->prepare();
			
			foreach ($this->clctn_crits as $clctn_crit)
			{
				$this->db2->set_params(
					array(
						$this->crit_clctn_id,
						$clctn_crit['crit_cd'],
						$clctn_crit['crit_fld']
					))
					->execute();
			}
		}
	}
	
	/**
	 * Removes collection and relationships from db
	 * 
	 */
	function remove()
	{
		if ($this->crit_clctn_id !== NULL)
		{
			$this->remove_criteria();
			$this->remove_elements();
			
			$sql = "DELETE FROM ".APP_SCHEMA.".CRIT_CLCTN 
				WHERE CRIT_CLCTN_ID = ".$this->crit_clctn_id;
			
			$this->db2->simple_query($sql);
		}
	}
	
	/**
	 * Removes related criteria from db
	 */
	function remove_criteria()
	{
		if ($this->crit_clctn_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".CLCTN_CRIT
				WHERE CRIT_CLCTN_ID = ".$this->crit_clctn_id;
			
			$this->db2->simple_query($sql);
		}
	}
	
	/**
	 * Removes element relationships from db
	 */
	function remove_elements()
	{
		if ($this->crit_clctn_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".ELEM_CRIT_CLCTN
				WHERE CRIT_CLCTN_ID = ".$this->crit_clctn_id;
			
			$this->db2->simple_query($sql);
		}
	}
	
	/**
	 * Assigns sales criteria objects to $assignee's sls_crits property
	 * 
	 * @param object $assignee
	 */
	function assign(&$assignee)
	{
		$sls_crits = array();
		$crit_cds = $assignee->get_crit_cds();
		
		foreach ($assignee->get_crit_clctn_nms() as $index => $field)
		{
			$sls_crits[] = $this->get($assignee->get_crit_clctn_id(), $field, $crit_cds[$index]);
		}
		
		$assignee->set_sls_crits($sls_crits);
	}
}