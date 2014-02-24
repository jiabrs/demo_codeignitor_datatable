<?php
/**
 * Class Coll_crit_model
 * 
 * Retrieves element criteria related info from db
 * 
 *   
 * @package ctm_cma
 * @subpackage models
 *
 */
class Coll_crit_model extends CI_Model {
	
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
		'PARNT_CO' => array(
			'desc' => 'Parent Company',
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
	private $crit_clctn_id = NULL;
	private $crit_fld = NULL;
	private $crit_cd = NULL;
	
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
	 * @param integer $crit_clctn_id 
	 */
	function set_crit_clctn_id($crit_clctn_id)
	{
		$this->crit_clctn_id = $crit_clctn_id;
	}
	
	/**
	 * Returns element id assigned to criteria
	 * 
	 * @return integer
	 */
	function get_crit_clctn_id()
	{
		return $this->crit_clctn_id;
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
		$select = "SELECT ".$this->crit_fld."_NM
			FROM DW.".$this->crit_fld."
			WHERE ".$this->crit_fld."_CD = ?";
		
		$select_parms = array($this->crit_cd);
		
		$rows = $this->db2->query($select, $select_parms)->fetch_assoc();
		
		return $rows[0][$this->crit_fld."_NM"];
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
	 * @param integer $crit_clctn_id
	 * @param string $crit_fld
	 * @param string $crit_cd
	 * 
	 * @return object
	 */
	function &get ($crit_clctn_id = NULL, $crit_fld = NULL, $crit_cd = NULL)
	{
		if ($crit_clctn_id !== NULL && $crit_fld !== NULL && $crit_cd !== NULL)
		{
			$select = "SELECT *
				FROM ".APP_SCHEMA.".CLCTN_CRIT
				WHERE CRIT_CD = ?
				AND CRIT_CLCTN_ID = ?
				AND CRIT_FLD = ?";
			
			$select_parms = array($crit_cd, $crit_clctn_id, $crit_fld);
			
			$results = $this->db2->query($select, $select_parms)->fetch_object();
		
			$row = $results[0];
			
			$criteria = new Coll_crit_model();
			
			$criteria->set_crit_clctn_id($row->CRIT_CLCTN_ID);
			$criteria->set_crit_fld($row->CRIT_FLD);
			$criteria->set_crit_cd($row->CRIT_CD);
			
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
		$criteria = array();
		
		foreach ($assignee->get_clctn_crits() as $index => $clctn_crit)
		{
			$criteria[] = $this->get($assignee->get_crit_clctn_id(), $clctn_crit['crit_fld'], $clctn_crit['crit_cd']);
		}
		
		$assignee->set_criteria($criteria);
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
}