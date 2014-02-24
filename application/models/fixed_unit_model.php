<?php
/**
 * Fixed_unit_model Class
 * 
 *   
 * @package ctm_cma
 * @subpackage Models
 *
 */
class Fixed_unit_model extends CI_Model {
	
	private $yr = NULL;
	private $mth = NULL;
	private $sls_ctr_cd = NULL;
	private $cntrct_id = NULL;
	private $elem_id = NULL;
	private $fix_unts = NULL;
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Returns fixed units year
	 * 
	 * @return integer $yr
	 */
	public function get_yr() {
		return $this->yr;
	}

	/**
	 * Sets fixed unit year
	 * 
	 * @param integer $yr 
	 */
	public function set_yr($yr) {
		$this->yr = $yr;
	}

	/**
	 * Returns fixed units month
	 * 
	 * @return integer $mth
	 */
	public function get_mth() {
		return $this->mth;
	}
	
	/**
	 * Sets fixed unit month
	 * 
	 * @param integer $mth
	 */
	public function set_mth($mth) {
		$this->mth = $mth;
	}
	
	/**
	 * Returns fixed unit location
	 * 
	 * @return string $sls_ctr_cd
	 */
	public function get_sls_ctr_cd() {
		return $this->sls_ctr_cd;
	}

	/**
	 * Sets fixed unit location 
	 * 
	 * @param string $sls_ctr_cd
	 */
	public function set_sls_ctr_cd($sls_ctr_cd) {
		$this->sls_ctr_cd = $sls_ctr_cd;
	}

	/**
	 * Returns fixed unit contract id
	 * 
	 * @return integer $cntrct_id
	 */
	public function get_cntrct_id() {
		return $this->cntrct_id;
	}

	/**
	 * Sets fixed unit contract id
	 * 
	 * @param integer $cntrct_id 
	 */
	public function set_cntrct_id($cntrct_id) {
		$this->cntrct_id = $cntrct_id;
	}

	/**
	 * Return fixed unit element id
	 * 
	 * @return integer $elem_id
	 */
	public function get_elem_id() {
		return $this->elem_id;
	}

	/**
	 * Sets fixed unit element id
	 * 
	 * @param integer $elem_id 
	 */
	public function set_elem_id($elem_id) {
		$this->elem_id = $elem_id;
	}

	/**
	 * Returns fixed units 
	 * 
	 * @return integer $fix_unts
	 */
	public function get_fix_unts() {
		return $this->fix_unts;
	}

	/**
	 * Sets fixed units
	 * 
	 * @param integer $fix_unts
	 */
	public function set_fix_unts($fix_unts) {
		$this->fix_unts = $fix_unts;
	}

	/**
	 * Return loaded fixed unit model
	 * 
	 * @param integer $per
	 * @param string $sls_ctr_cd
	 * @param integer $cntrct_id
	 * @param integer $elem_id
	 * 
	 * @return Fixed_unit_model
	 */
	function &get($yr = NULL, $mth = NULL, $sls_ctr_cd = NULL, $cntrct_id = NULL, $elem_id = NULL)
	{
		$fixed_unit = new Fixed_unit_model();
		
		if ($yr !== NULL && $mth !== NULL && $sls_ctr_cd !== NULL && $cntrct_id !== NULL && $elem_id !== NULL)
		{
			$fixed_unit->set_yr($yr);
			$fixed_unit->set_mth($mth);
			$fixed_unit->set_sls_ctr_cd($sls_ctr_cd);
			$fixed_unit->set_cntrct_id($cntrct_id);
			$fixed_unit->set_elem_id($elem_id);
			
			$fixed_unit->_load();			
		}
		
		return $fixed_unit;
	}
	
	/**
	 * Load db record into model
	 */
	function _load()
	{
		$sql = "SELECT * 
			FROM ".APP_SCHEMA.".CNTRCT_FIX_UNT
			WHERE YR = ".$this->yr."
				AND MTH = ".$this->mth."
				AND SLS_CTR_CD = '".$this->sls_ctr_cd."'
				AND CNTRCT_ID = ".$this->cntrct_id."
				AND ELEM_ID = ".$this->elem_id;
		
		$results = $this->db2->simple_query($sql)->fetch_assoc();
		
		if (count($results) > 0)
		{
			$row = $results[0];
			
			$this->yr = $row['YR'];
			$this->mth = $row['MTH'];
			$this->sls_ctr_cd = $row['SLS_CTR_CD'];
			$this->cntrct_id = $row['CNTRCT_ID'];
			$this->elem_id = $row['ELEM_ID'];
			$this->fix_unts = $row['FIX_UNTS'];
		}
	}
	
	/**
	 * Returns list of fixed units for contract id
	 * keyed by:
	 * 
	 * 	- element_id
	 *  - sls_ctr_cd
	 *  - yr
	 *  - mth
	 *  
	 * @param integer $contrct_id
	 * @return array $fixed_units
	 */
	function get_fixed_units_by_cntrct($cntrct_id = NULL)
	{
	 	$sql = "SELECT T1.*, COALESCE(T2.FIX_UNTS,0) AS FIX_UNTS
			FROM (
				SELECT DISTINCT CL.CNTRCT_ID, CED.ELEM_ID, CL.SLS_CTR_CD, DT.YR, DT.MTH
				FROM ".APP_SCHEMA.".CNTRCT_LOC CL
					JOIN ".APP_SCHEMA.".CNTRCT_ELEM_DT CED ON CED.CNTRCT_ID = CL.CNTRCT_ID
					JOIN ".APP_SCHEMA.".ELEM E ON E.ELEM_ID = CED.ELEM_ID
					JOIN DW.DT DT ON DT.DT BETWEEN CED.STRT_DT AND CED.END_DT
				WHERE CL.CNTRCT_ID = ".$cntrct_id."
					AND E.ELEM_TP = 'TU'
			) AS T1
				LEFT JOIN ".APP_SCHEMA.".CNTRCT_FIX_UNT AS T2 ON T2.CNTRCT_ID = T1.CNTRCT_ID
					AND T2.ELEM_ID = T1.ELEM_ID 
					AND T2.SLS_CTR_CD = T1.SLS_CTR_CD 
					AND T2.MTH = T1.MTH 
					AND T2.YR = T1.YR";
		
		$fixed_units = array();
		
		foreach ($this->db2->simple_query($sql)->fetch_object() as $row)
		{
			$fixed_units[$row->ELEM_ID][$row->SLS_CTR_CD][$row->YR][$row->MTH] = $row->FIX_UNTS;
		}
		
		return $fixed_units;
	}
	
	/**
	 * Returns list of fixed units for contract id and element id
	 * in array structure:
	 * 
	 * array(sls_ctr_cd => yr => mth => fixed units)
	 * 
	 * @param integer $cntrct_id
	 * @param integer $elem_id
	 * @return array:
	 */
	function get_fixed_units_for_dsp($cntrct_id = NULL, $elem_id = NULL)
	{
		$sql = "SELECT * 
			FROM ".APP_SCHEMA.".CNTRCT_FIX_UNT
			WHERE CNTRCT_ID = ".$this->cntrct_id."
				AND ELEM_ID = ".$this->elem_id;
		
		$fixed_units = array();
		
		foreach ($this->db2->simple_query($sql)->fetch_object() as $row)
		{
			$fixed_units[$row->SLS_CTR_CD][$row->YR][$row->MTH] = $row->FIX_UNTS;
		}
		
		return $fixed_units;
	}
	
	/**
	 * Saves object to db record
	 */
	function save()
	{
		if ($this->yr !== NULL && $this->mth !== NULL && $this->sls_ctr_cd !== NULL && $this->cntrct_id !== NULL && $this->elem_id !== NULL)
		{
			$this->db2->begin_transaction();
			
			try {
				if ($this->exists())
				{
					$update = "UPDATE ".APP_SCHEMA.".CNTRCT_FIX_UNT SET FIX_UNTS = ?
						WHERE YR = ?
							AND MTH = ?
							AND SLS_CTR_CD = ?
							AND CNTRCT_ID = ? 
							AND ELEM_ID = ?";
					
					$update_parms = array(
						$this->fix_unts,
						$this->yr,
						$this->mth,
						$this->sls_ctr_cd,
						$this->cntrct_id,
						$this->elem_id
					);
					
					$this->db2->query($update, $update_parms);
				}
				else
				{
					$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_FIX_UNT (YR,MTH,SLS_CTR_CD,CNTRCT_ID,ELEM_ID,FIX_UNTS)
						VALUES (?, ?, ?, ?, ?, ?)";
					
					$insert_parms = array(
						$this->yr,
						$this->mth,
						$this->sls_ctr_cd,
						$this->cntrct_id,
						$this->elem_id,
						$this->fix_unts
					);
					
					$this->db2->query($insert, $insert_parms);
				}
			} catch (Exception $e) {
				$this->db2->rollback();
				
				$error =& load_class('Exceptions', 'core');
				echo $error->show_error("Error saving Fixed Units", $e->getMessage(), 'error_general');
				exit;
			}		
			
			$this->db2->commit();
		}
	}
	
	/**
	 * Check to see if db record exists
	 * 
	 * @return bool
	 */
	function exists()
	{
		if($this->yr !== NULL && $this->mth !== NULL && $this->sls_ctr_cd !== NULL && $this->cntrct_id !== NULL && $this->elem_id !== NULL)
		{
			$sql = "SELECT * 
					FROM ".APP_SCHEMA.".CNTRCT_FIX_UNT
					WHERE YR = ".$this->yr."
						AND MTH = ".$this->mth."
						AND SLS_CTR_CD = '".$this->sls_ctr_cd."'
						AND CNTRCT_ID = ".$this->cntrct_id." 
						AND ELEM_ID=".$this->elem_id;
			
			if(count($this->db2->simple_query($sql)->fetch_assoc()) > 0)
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
	 * Removes fixed unit db record
	 */
	function remove()
	{
		if($this->yr !== NULL && $this->mth !== NULL && $this->sls_ctr_cd !== NULL && $this->cntrct_id !== NULL && $this->elem_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".CNTRCT_FIX_UNT
					WHERE YR = ".$this->yr."
						AND MTH = ".$this->mth."
						AND SLS_CTR_CD = '".$this->sls_ctr_cd."'
						AND CNTRCT_ID = ".$this->cntrct_id." 
						AND ELEM_ID=".$this->elem_id;
			
			$this->db2->simple_query($sql);
			
		}
	}
}