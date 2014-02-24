<?php
/**
 * Audit_model Class
 *
 *  Eric Nies <enies@nsc-inc.com>
 * @package ctm_cma
 * @subpackage models
 *
 */
class Audit_model extends CI_Model {

	public static $entities = array(
		'US' => 'User',
		'RL' => 'Role',
		'EL' => 'Element',
		'PG' => 'Program',
		'CT' => 'Contract',
		'CL' => 'Collection',
		'CN' => 'Contract Note',
                'AL' => 'Approval List'
	);
	
	public static $actions = array(
		'C' => 'Created',
		'R' => 'Read',
		'U' => 'Updated',
		'D' => 'Deleted'
	);
	
	function __construct()
	{
		parent::__construct();
	}
	

	/**
	 * Inserts log information into the database.  User activity tracked -
	 * creation, deletion and changing - of the following entities:
	 * 		Users
	 * 		Elements
	 * 		Programs
	 * 		Contracts
	 *
	 * @param string $seq		Sequence Number
	 * @param string $enty_tp 	Entity Type (US/EL/PG/CT/CL)
	 * @param string $enty_id 	Entity ID
	 * @param string $usr_actn 	Action taken (C/R/U/D)
	 * @param string $usr_id	User ID
	 * 
	 * @return nothing
	 */
	function log_activity($enty_tp,$enty_id,$usr_actn,$usr_id)
	{		
		$insert = "INSERT INTO ".APP_SCHEMA.".ADT 
				(ADT_ID,ENTY_TP,ENTY_ID,USR_ACTN,ACTN_TM,USR_ID) 
				VALUES (NEXT VALUE FOR ".APP_SCHEMA.".SQ_ADT_PK, ?, ?, ?, CURRENT_TIMESTAMP, ?)";
		
		$insert_parms = array($enty_tp, $enty_id, $usr_actn, $usr_id);
		
		$this->db2->query($insert, $insert_parms);
	}
	
	
	/**
	 * Returns list of actions for filtered on non-null parameters
	 * 
	 * @param string $enty_tp 	Entity Type (US/EL/PG/CT/CL)
	 * @param string $enty_id 	Entity ID
	 * @param string $usr_actn 	Action taken (C/R/U/D)
	 * @param string $usr_id	User ID
	 * 
	 * @return array $actions
	 */
	function get_activity($enty_tp = NULL, $enty_id = NULL, $usr_actn = NULL, $usr_id = NULL)
	{
		$select = "SELECT *
				FROM ".APP_SCHEMA.".ADT
				WHERE ENTY_TP = ? 
					OR ENTY_ID = ?  
					OR USR_ACTN = ? 
					OR USR_ID = ?
				ORDER BY ACTN_TM DESC";
		
		$select_parms = array(strtoupper($enty_tp), $enty_id, strtoupper($usr_actn), $usr_id);
		
		return $this->db2->query($select, $select_parms)->result_assoc();
        }
}