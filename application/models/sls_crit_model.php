<?php
/**
 * Class Sls_crit_model
 * 
 * Retrieves element criteria related info from db
 * 
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage models
 *
 */
class Sls_crit_model extends CI_Model {
	
	// Accrual Flags
	public static $accr_flgs = array(
		'N' => 'No',
		'Y' => 'Yes'
	);
	
	// Criteria Fields
	public static $crit_flds = array(
		'BUS_TP' => array(
			'desc' => 'Business Type',
			'tbl' => 'DWOM01',
		),
		'BUS_TP_EXT' => array(
			'desc' => 'Business Type Ext',
			'tbl' => 'DWOM01',
		),
		'ART_TP' => array(
			'desc' => 'Article Type',
			'tbl' => 'DWAM01',
		),
		'PKG' => array(
			'desc' => 'Package',
			'tbl' => 'DWAM01',
		),
		'CLS' => array(
			'desc' => 'Class',
			'tbl' => 'DWAM01',
		),
		'CAT' => array(
			'desc' => 'Category',
			'tbl' => 'DWAM01',
		),
		'FLVR' => array(
			'desc' => 'Flavor',
			'tbl' => 'DWAM01',
		),
		'TMRK' => array(
			'desc' => 'Trademark',
			'tbl' => 'DWAM01',
		),
		'PARNT_CO' => array(
			'desc' => 'Parent Company',
			'tbl' => 'DWAM01',
		)
	);
	
	// Criteria properties
	private $elem_id = NULL;
	private $crit_fld = NULL;
	private $crit_cd = NULL;
	private $accr_flg = 'Y';
	
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
	 * Sets element id of criteria field
	 * 
	 * @param integer $elem_id 
	 */
	function set_elem_id($elem_id)
	{
		$this->elem_id = $elem_id;
	}
	
	/**
	 * Returns element id assigned to criteria
	 * 
	 * @return integer
	 */
	function get_elem_id()
	{
		return $this->elem_id;
	}
	
	/**
	 * Sets criteria field
	 * 
	 * @param string $crit_fld
	 */
	function set_crit_fld($crit_fld)
	{
		$this->crit_fld = trim($crit_fld);
	}
	
	/**
	 * Returns criteria field
	 * 
	 * @return string
	 */
	function get_crit_fld()
	{
		return $this->crit_fld;
	}
	
	/**
	 * Returns criteria field for display
	 * 
	 * @return string
	 */
	function dsp_crit_fld()
	{
		return self::$crit_flds[$this->crit_fld]['desc'];
	}
	
	/**
	 * Sets criteria code
	 * 
	 * @param string $crit_cd
	 */
	function set_crit_cd($crit_cd)
	{
		$this->crit_cd = trim($crit_cd);
	}
	
	/**
	 * Returns criteria code
	 * 
	 * @return string
	 */
	function get_crit_cd()
	{
		return $this->crit_cd;
	}
	
	/**
	 * Queries criteria code and returns description
	 * 
	 * @return string
	 */
	function dsp_crit_cd()
	{
		$sql = "SELECT ".$this->crit_fld."_NM
			FROM DW.".$this->crit_fld."
			WHERE ".$this->crit_fld."_CD = '".$this->crit_cd."'";
		
		$rows = $this->db2->simple_query($sql)->fetch_assoc();
		
		return $rows[0][$this->crit_fld."_NM"];
	}
	
	/**
	 * Sets accrual flag
	 * 
	 * @param string $accr_flg
	 */
	function set_accr_flg($accr_flg)
	{
		$this->accr_flg = $accr_flg;
	}
	
	/**
	 * Returns accrual flag
	 * 
	 * @return string
	 */
	function get_accr_flg()
	{
		return $this->accr_flg;
	}
	
	/**
	 * Returns accrual flag for display
	 * 
	 * @return string
	 */
	function dsp_accr_flg()
	{
		return self::$accr_flgs[$this->accr_flg];
	}
	
	/**
	 * Returns list of criteria fields for use
	 * in form dropdown
	 * 
	 * @return array
	 */
	function dpdwn_crit_flds()
	{
		$fields = array();
		
		foreach (self::$crit_flds as $crit_fld => $meta)
		{
			$fields[$crit_fld] = $meta['desc'];
		}
		
		return $fields;
	}
	
	/**
	 * Gets criteria object
	 * 
	 * @param integer $elem_id
	 * @param string $crit_fld
	 * @param string $crit_cd
	 * 
	 * @return object
	 */
	function &get ($elem_id = NULL, $crit_fld = NULL, $crit_cd = NULL)
	{
		if ($elem_id !== NULL && $crit_fld !== NULL && $crit_cd !== NULL)
		{
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".SLS_CRIT AS SC
				WHERE SC.CRIT_CD = '".$crit_cd."'
				AND SC.ELEM_ID = ".$elem_id."
				AND SC.CRIT_FLD = '".$crit_fld."'";
			
			$results = $this->db2->simple_query($sql)->fetch_object();
		
			$row = $results[0];
			
			$criteria = new Sls_crit_model();
			
			$criteria->set_elem_id($row->ELEM_ID);
			$criteria->set_crit_fld($row->CRIT_FLD);
			$criteria->set_crit_cd($row->CRIT_CD);
			$criteria->set_accr_flg($row->ACCR_FLG);
			
			return $criteria;
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
		
		foreach ($assignee->get_crit_flds() as $index => $field)
		{
			$sls_crits[] = $this->get($assignee->get_elem_id(), $field, $crit_cds[$index]);
		}
		
		$assignee->set_sls_crits($sls_crits);
	}
	
	/**
	 * Returns table criteria field belongs to
	 * 
	 * @return string
	 */
	function get_crit_tbl()
	{
		return self::$crit_flds[$this->crit_fld]['tbl'];
	}
	
	public static function get_sls_crit_by_elem_id($elem_id)
	{
		$CI =& get_instance();
		$sql = "SELECT *
			FROM ".APP_SCHEMA.".SLS_CRIT AS SC
			WHERE SC.ELEM_ID = ".$elem_id."
			ORDER BY CRIT_FLD, CRIT_CD";
		
		$sls_crits = array();
		
		foreach ($CI->db2->simple_query($sql)->fetch_object() as $row)
		{
			$sls_crit = new Sls_crit_model();
			
			$sls_crit->set_elem_id($elem_id);
			$sls_crit->set_crit_fld($row->CRIT_FLD);
			$sls_crit->set_crit_cd($row->CRIT_CD);
			$sls_crit->set_accr_flg($row->ACCR_FLG);
			
			$sls_crits[] = $sls_crit;
		}
		
		return $sls_crits;
	}
}