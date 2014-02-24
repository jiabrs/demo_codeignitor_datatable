<?php
/**
 * Class Customer_model
 * 
 *   
 * @package ctm_cma
 * @subpackage models
 */
class Customer_model extends CI_Model {
	
	public static $cust_tps = array(
		'OT' => 'Outlet',
		'KA' => 'Key Account',
		'TG' => 'Trade Group'
	);
	
	private $cust_tp = NULL;
	private $cust_cd = NULL;
	private $cust_nm = '';
	
	
	/**
	 * Returns customer type
	 * 
	 * @return string $cust_tp
	 */
	public function get_cust_tp() {
		return $this->cust_tp;
	}

	/**
	 * Sets customer type
	 * 
	 * @param string $cust_tp
	 */
	public function set_cust_tp($cust_tp) {
		$this->cust_tp = $cust_tp;
	}

	/**
	 * Returns customer type for display
	 * 
	 * @return string
	 */
	public function dsp_cust_tp()
	{
		return self::$cust_tps[$this->cust_tp];
	}
	
	/**
	 * Returns customer code
	 * 
	 * @return string $cust_cd
	 */
	public function get_cust_cd() {
		return $this->cust_cd;
	}

	/**
	 * Sets customer code
	 * 
	 * @param string $cust_cd
	 */
	public function set_cust_cd($cust_cd) {
		$this->cust_cd = $cust_cd;
	}

	/**
	 * Returns customer name
	 * 
	 * @return string $cust_nm
	 */
	public function get_cust_nm() {
		return $this->cust_nm;
	}

	/**
	 * Sets customer name
	 * 
	 * @param string $cust_nm
	 */
	public function set_cust_nm($cust_nm) {
		$this->cust_nm = trim($cust_nm);
	}

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Searches outlet master or xi01 for customer based on type: 
	 * 
	 * 1. Outlet (OT)
	 * 2. Key Account (KA)
	 * 3. Trade Group ('TG')
	 * 
	 * @param string $search String fragment to search for 
	 * @param string $type Customer type to limit to
	 * @param array $locations Limit search to outlet locations
	 * @param string $return Type of customer to return
	 * @param string $bustype Bus. Type to limit to
	 * @param string $limit how many customers to return
	 * 
	 * @return array
	 */
	function search($search = '', $type = '', $locations = array(), $return = 'OT', $bustype = '', $limit = NULL)
	{
		if ($type != '')
		{
			$sql = new Query;
			
			$sql->from = "DW.DWOM01 AS OM";
			
			// restrict to active outlets
			$sql->wheres[] = "OM.OUT_STS = 'A'";
			
			// join up xi tables for ka and tg
			$sql->joins = array(
				'JOIN DW.TRD_GRP AS TG ON TG.TRD_GRP_CD = OM.TRD_GRP_CD',
				'JOIN DW.KEY_ACCT AS KA ON KA.KEY_ACCT_CD = OM.KEY_ACCT_CD'
			);
			
			if ($locations !== FALSE && count($locations) > 0)
			{
				$sql->wheres[] = "OM.OUT_LOC IN ('".implode("','", $locations)."')";
			}
			
			if ($bustype != '')
			{
				$sql->wheres[] = "OM.BUS_TP_CD = '".$bustype."'";
			}
			
			switch ($type)
			{
				case 'OT': // pull from outlet master
					$sql->wheres[] = "LOWER(OM.OUT_NM) LIKE ?";			
					break;
				case 'KA': // pull from code interp. limit to key accounts
					$sql->wheres[] = "LOWER(KA.KEY_ACCT_NM) LIKE ?";					
					break;
				case 'TG': // pull from code interp.  limit to trade groups
					$sql->wheres[] = "LOWER(TG.TRD_GN) LIKE ?";					
					break;
			}
			
			switch ($return)
			{
				case 'OT': // pull from outlet master
					$sql->selects[] = "DISTINCT OM.OUT_ID AS CODE, OM.OUT_NM AS NAME";	
					$sql->extra = "ORDER BY CODE ASC";				
					break;
				case 'KA': // pull from code interp. limit to key accounts
					$sql->selects[] = "DISTINCT OM.KEY_ACCT_CD AS CODE, KA.KEY_ACCT_NM AS NAME";	
					$sql->extra = "ORDER BY NAME ASC";				
					break;
				case 'TG': // pull from code interp.  limit to trade groups
					$sql->selects[] = "DISTINCT OM.TRD_GRP_CD AS CODE, TG.TRD_GN AS NAME";	
					$sql->extra = "ORDER BY NAME ASC";				
					break;
			}
			
			if ($limit !== NULL)
			{
				$sql->extra .= " FETCH FIRST ".$limit." ROWS ONLY";
			}
			
			return $this->db2->query($sql->build(), array("%".strtolower($search)."%"))->fetch_object();
		}
		else // didn't provide a type
		{
			return array();
		}
	}
	
	/**
	 * Returns loaded customer object
	 * 
	 * @param string $code Customer code (outlet no./key acct no./trade group no)
	 * @param string $type Customer type (OT/KA/TG)
	 * 
	 * @return object
	 */
	function &get($code = NULL, $type = NULL)
	{
		$sql = new Query();
		
		switch ($type)
		{
			case 'OT': // pull from outlet master
				$sql->selects = array("OM.OUT_ID AS CODE","OM.OUT_NM AS NAME");
				$sql->from = "DW.DWOM01 as OM";	
				$sql->wheres[] = "OM.OUT_ID = ".$code;		
				break;
			case 'KA': // pull from code interp. limit to key accounts
				$sql->selects = array("KA.KEY_ACCT_CD AS CODE", "KA.KEY_ACCT_NM AS NAME");	
				$sql->from = "DW.KEY_ACCT as KA";
				$sql->wheres[] = "KA.KEY_ACCT_CD = '".$code."'";			
				break;
			case 'TG': // pull from code interp.  limit to trade groups
				$sql->selects = array("TG.TRD_GRP_CD AS CODE","TG.TRD_GN AS NAME");
				$sql->from = "DW.TRD_GRP AS TG";
				$sql->wheres[] = "TG.TRD_GRP_CD = '".$code."'";			
				break;
		}
		
		$results = $this->db2->simple_query($sql->build())->fetch_object();
		
		$customer = new Customer_model();
		
		if (count($results) > 0)
		{			
			$customer->set_cust_cd($results[0]->CODE);
			$customer->set_cust_tp($type);
			$customer->set_cust_nm($results[0]->NAME);
		}
		
		return $customer;
	}
	
	/**
	 * Returns customer objects tied to contract
	 * 
	 * @param string $contract_id
	 * 
	 * @return array
	 */
	function get_by_contract($contract_id)
	{
		$sql = "SELECT C_C.*
			FROM ".APP_SCHEMA.".CNTRCT_CUST AS C_C
			WHERE C_C.CNTRCT_ID = ".$contract_id;
		
		$customers = array();
		
		foreach ($this->db2->simple_query($sql)->fetch_object() as $row)
		{
			$customers[$row->CUST_CD] = $this->get($row->CUST_CD, $row->CUST_TP);
		}
		
		return $customers;
	}
	
	/**
	 *  Takes submitted object, checks for $customers property
	 * and assigns customers to the object
	 * 
	 * @param object $assignee
	 * 
	 * @return nothing
	 */
	function assign(&$assignee)
	{
		$customers = array();
		
		foreach ($assignee->get_out_ids() as $out_id)
		{
			$customers[] = $this->get($out_id, 'OT');
		}
		foreach ($assignee->get_key_acct_cds() as $key_acct_cd)
		{
			$customers[] = $this->get($key_acct_cd, 'KA');
		}

		foreach ($assignee->get_trd_grp_cds() as $trd_grp_cd)
		{
			$customers[] = $this->get($trd_grp_cd, 'TG');
		}
		
		$assignee->set_customers($customers);
	}
}
/* End of file customer_model.php */
/* Location: /ctm_cma/models/customer_model.php */