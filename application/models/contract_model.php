<?php
/**
 * Class Contract_model
 * 
 * Handles contract data
 * 
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage models
 *
 */
class Contract_model extends CI_Model {
	
	// code interpretation
	public static $cse_tps = array(
		'PH' => 'Physical Cases',
		'CV' => 'Converted Cases'
	);
	
	// object properties
	private $cntrct_id = NULL;
	private $app = '';
	private $cntrct_nm = '';
	private $cse_tp = 'CV';
	private $strt_dt = '';
	private $end_dt = '';
	private $vend_no = 'XXXXX';
	private $appr_list_id = NULL;
	
	// related item ids
	private $pgm_id = NULL;
	private $sls_ctr_cds = array();
	private $note_ids = array();
	private $out_ids = array();
	private $key_acct_cds = array();
	private $trd_grp_cds = array();
	private $accrual_yr = NULL;
	
	// containers for related objects
	private $notes = array();
	private $customers = array();
	private $locations = array();
	private $users = array();
	private $elements = array();
	private $program = NULL;
	private $stats = array();
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Returns contract id
	 * 
	 * @return integer $cntrct_id
	 */
	public function get_cntrct_id() {
		return $this->cntrct_id;
	}

	/**
	 * Sets contract identifier
	 * 
	 * @param integer $cntrct_id
	 */
	public function set_cntrct_id($cntrct_id) {
		$this->cntrct_id = $cntrct_id;
	}
	
	/**
	 * Sets cntrct_id to null in preparation for 
	 * copying
	 */
	public function clear_cntrct_id()
	{
		$this->cntrct_id = NULL;
	}
	
	/**
	 * Returns assigned application
	 * 
	 * @return string $app
	 */
	public function get_app() {
		return $this->app;
	}

	/**
	 * Sets contract application
	 * 
	 * @param string $app
	 */
	public function set_app($app) {
		$this->app = $app;
	}
	
	/**
	 * Returns contract name
	 * 
	 * @return string $cntrct_nm
	 */
	public function get_cntrct_nm() {
		return $this->cntrct_nm;
	}
	
	/**
	 * Returns escaped contract name for db2 query
	 * 
	 * @return string
	 */
	public function esc_cntrct_nm()
	{
		return str_replace("'","''",$this->cntrct_nm);
	}
	
	/**
	 * Sets contract name.  Truncates to 80 characters
	 * 
	 * @param string $cntrct_nm
	 */
	public function set_cntrct_nm($cntrct_nm) {
		$this->cntrct_nm = substr($cntrct_nm, 0, 80);
	}
	
	/**
	 * Returns case type
	 * 
	 * @return string $cse_tp
	 */
	public function get_cse_tp() {
		return $this->cse_tp;
	}

	/**
	 * Sets case type for contract
	 * 
	 * @param string $cse_tp
	 */
	public function set_cse_tp($cse_tp) {
		$this->cse_tp = $cse_tp;
	}
	
	/**
	 * Returns case type for display
	 * 
	 * @return string
	 */
	public function dsp_cse_tp()
	{
		return self::$cse_tps[$this->cse_tp];
	}
	
	/**
	 * Sets the contract start date
	 * 
	 * @param string $date
	 */
	function set_strt_dt($strt_dt, $format = 'Y-m-d')
	{
		$dt = conv_date($strt_dt, $format, 'Y-m-d');
		
		list ($yr,$mth,$day) = explode('-',$dt);
		
		if (checkdate($mth, $day, $yr))
		{
			$this->strt_dt = $dt;
		}
	}
	
	/**
	 * Returns Contract Start Date
	 * 
	 * @return string
	 */
	function get_strt_dt()
	{
		return $this->strt_dt;
	}
	
	/**
	 * Returns the start date formatted according to $format
	 * Default is YYYY-MM-DD
	 * 
	 * @param string $format 
	 * 
	 * @return string
	 */
	function dsp_strt_dt($format = 'Y-m-d')
	{
		if ($this->strt_dt == '')
		{
			return date($format, mktime(0,0,0,1,1,date('Y')));
		}
		else
		{
			list($year,$month,$day) = explode('-', $this->strt_dt);
			
			return date($format, mktime(0,0,0,$month,$day,$year));
		}
	}
	
	/**
	 * Sets contract end date
	 * 
	 * @param string $date
	 */
	function set_end_dt($end_dt, $format = 'Y-m-d')
	{
		$dt = conv_date($end_dt, $format, 'Y-m-d');
		
		list ($yr,$mth,$day) = explode('-',$dt);
		
		if (checkdate($mth, $day, $yr))
		{
			$this->end_dt = $dt;
		}
	}
	
	/**
	 * Returns contract end date 
	 * 
	 * @return string
	 */
	function get_end_dt()
	{
		return $this->end_dt;
	}
	
	/**
	 * Returns the end date formatted according to $format
	 * Default is YYYY-MM-DD
	 * 
	 * @param string $format 
	 * 
	 * @return string
	 */
	function dsp_end_dt($format = 'Y-m-d')
	{
		if ($this->end_dt == '')
		{
			return date($format, mktime(0,0,0,12,31,date('Y')));
		}
		else
		{
			list($year,$month,$day) = explode('-', $this->end_dt);
			
			return date($format, mktime(0,0,0,$month,$day,$year));
		}
	}

	/**
	 * Returns program id
	 * 
	 * @return integer $pgm_id
	 */
	public function get_pgm_id() {
		return $this->pgm_id;
	}

	/**
	 * Sets contract's program ids
	 * 
	 * @param integer $pgm_id
	 */
	public function set_pgm_id($pgm_id) {
		$this->pgm_id = $pgm_id;
	}
	
	/**
	 * Returns sales center codes
	 * 
	 * @return array $sls_ctr_cds
	 */
	public function get_sls_ctr_cds() {
		return $this->sls_ctr_cds;
	}

	/**
	 * Sets sales center codes 
	 * 
	 * @param array $sls_ctr_cds
	 */
	public function set_sls_ctr_cds($sls_ctr_cds) {
		$this->sls_ctr_cds = $sls_ctr_cds;
		$this->load_locations();
	}

	/**
	 * Return note ids
	 * 
	 * @return array $note_ids
	 */
	public function get_note_ids() {
		return $this->note_ids;
	}

	/**
	 * Sets note ids
	 * 
	 * @param array $note_ids
	 */
	public function set_note_ids($note_ids) {
		$this->note_ids = $note_ids;
	}

	/**
	 * Returns outlets
	 * 
	 * @return array $out_ids
	 */
	public function get_out_ids() {
		return $this->out_ids;
	}

	/**
	 * Sets outlet codes
	 * 
	 * @param array $out_ids
	 */
	public function set_out_ids($out_ids) {
		$this->out_ids = $out_ids;
	}

	/**
	 * Returns key acct codes
	 * 
	 * @return array $key_acct_cds
	 */
	public function get_key_acct_cds() {
		return $this->key_acct_cds;
	}

	/**
	 * Sets key acct codes
	 * 
	 * @param array $key_acct_cds
	 */
	public function set_key_acct_cds($key_acct_cds) {
		$this->key_acct_cds = $key_acct_cds;
	}

	/**
	 * Returns trade group codes
	 * 
	 * @return array $trd_grp_cds
	 */
	public function get_trd_grp_cds() {
		return $this->trd_grp_cds;
	}

	/**
	 * Sets trade group codes
	 * 
	 * @param array $trd_grp_cds
	 */
	public function set_trd_grp_cds($trd_grp_cds) {
		$this->trd_grp_cds = $trd_grp_cds;
	}

	/**
	 * Returns note objects
	 * 
	 * @return array $notes
	 */
	public function get_notes() {
		return $this->notes;
	}

	/**
	 * Sets note objects
	 * 
	 * @param array $notes
	 */
	public function set_notes($notes) {
		$this->notes = $notes;
	}

	/**
	 * Returns customer objects
	 * 
	 * @return array $customers
	 */
	public function get_customers() {
		return $this->customers;
	}

	/**
	 * Sets customer objects
	 * 
	 * @param array $customers 
	 */
	public function set_customers($customers) {
		$this->customers = $customers;
	}

	/**
	 * Takes submitted array of codes with index matched
	 * types ($cust_tps[0] is cust_tp for $cust_cds[0]) and assignes
	 * to customer propertiers based on type
	 * 
	 * @param array $cust_cds
	 * @param array $cust_tps
	 */
	public function set_customer_cds($cust_cds, $cust_tps)
	{
		// Initialize all customer arrays or we'll get double customers
		$this->out_ids = array();
		$this->key_acct_cds = array();
		$this->trd_grp_cds = array();
		
		foreach ($cust_cds as $index => $cust_cd)
		{
			switch ($cust_tps[$index])
			{
				case 'OT':
					$this->out_ids[] = $cust_cd;
					break;
				case 'KA':
					$this->key_acct_cds[] = $cust_cd;
					break;
				case 'TG':
					$this->trd_grp_cds[] = $cust_cd;
					break;
			}
		}
		
		$this->load_customer_objs();
	}
	
	public function get_cust_cds()
	{
		$cust_cds = array();
		
		foreach ($this->out_ids as $cust_cd)
		{
			$cust_cds[] = $cust_cd;
		}
		
		foreach ($this->key_acct_cds as $cust_cd)
		{
			$cust_cds[] = $cust_cd;
		}
		
		foreach ($this->trd_grp_cds as $cust_cd)
		{
			$cust_cds[] = $cust_cd;
		}
		
		return $cust_cds;
	}
	
	public function get_cust_tps()
	{
		$cust_tps = array();
		
		foreach ($this->out_ids as $index => $cust_cd)
		{
			$cust_tps[] = 'OT';
		}
		
		foreach ($this->key_acct_cds as $index => $cust_cd)
		{
			$cust_tps[] = 'KA';
		}
		
		foreach ($this->trd_grp_cds as $index => $cust_cd)
		{
			$cust_tps[] = 'TG';
		}
		
		return $cust_tps;
	}
	
	/**
	 * Returns location array (sls_ctr_cd => sls_ctr_nm)
	 * 
	 * @return array $locations
	 */
	public function get_locations() {
		return $this->locations;
	}

	/**
	 * Returns array of locations with shortened names 
	 * 
	 * @param integer $len
	 * @return array
	 */
	public function get_short_locations($len = 3) {
		$short_locs = array();
		
		foreach ($this->locations as $code => $location)
		{
			$short_locs[$code] = substr($location, 0, $len);
		}
		
		return $short_locs;
	}
	
	/**
	 * Sets location array (sls_ctr_cd => sls_ctr_nm)
	 * 
	 * @param array $locations
	 */
	public function set_locations($locations) {
		$this->locations = $locations;
	}

	/**
	 * Returns user objects 
	 * 
	 * @return array $users
	 */
	public function get_users() {
		return $this->users;
	}

	/**
	 * Sets user objects
	 * 
	 * @param array $users
	 */
	public function set_users($users) {
		$this->users = $users;
	}

	/**
	 * Returns element objects
	 * 
	 * @return array $elements
	 */
	public function get_elements() {
		return $this->elements;
	}

	/**
	 * Sets element objects
	 * 
	 * @param array $elements
	 */
	public function set_elements($elements) {
		$this->elements = $elements;
	}

	/**
	 * Returns program objects
	 * 
	 * @return array $programs
	 */
	public function get_programs() {
		return $this->programs;
	}

	/**
	 * Sets program objects
	 * 
	 * @param array $programs
	 */
	public function set_programs($programs) {
		$this->programs = $programs;
	}

	public function set_accrual_yr($accrual_yr)
	{
		if (intval($accrual_yr) > intval(substr($this->end_dt, 0, 4)))
		{
			$this->accrual_yr = substr($this->end_dt, 0, 4);
		}
		else {
			$this->accrual_yr = $accrual_yr;
		}
	}
	
	public function get_accrual_yr()
	{
		return $this->accrual_yr;
	}
	
	public function set_vend_no($vend_no)
	{
		$this->vend_no = $vend_no;
	}
	
	public function get_vend_no()
	{
		return $this->vend_no;
	}
	
	public function set_appr_lst_id($appr_lst_id)
	{
		$this->appr_list_id = $appr_lst_id;
	}
	
	public function get_appr_lst_id()
	{
		if ($this->appr_list_id === NULL)
		{
			return 0;
		}
		else {
			return $this->appr_list_id;
		}
	}
	
	/**
	 * Returns contract object.  Loads with db data
	 * if cntrct_id provided
	 */
	function &get($cntrct_id = NULL)
	{
		$contract = new Contract_model();
		
		if ($cntrct_id !== NULL && $cntrct_id !== FALSE)
		{
			$contract->cntrct_id = $cntrct_id;
			
			$contract->_load();
		}
		
		return $contract;
	}
	
	/**
	 * Loads object with db data
	 */
	function _load()
	{
		if ($this->cntrct_id !== NULL)
		{
			$select = "SELECT C.*, CP.PGM_ID
				FROM ".APP_SCHEMA.".CNTRCT AS C
					LEFT JOIN ".APP_SCHEMA.".CNTRCT_PGM CP ON CP.CNTRCT_ID = C.CNTRCT_ID
				WHERE C.CNTRCT_ID = ?";
			
			$rows = $this->db2->query($select, array($this->cntrct_id))->fetch_assoc();
			
			if (count($rows) > 0)
			{
				$row = $rows[0];
				
				$this->app = $row['APP'];
				$this->cntrct_nm = $row['CNTRCT_NM'];
				$this->cse_tp = $row['CSE_TP'];
				$this->strt_dt = $row['STRT_DT'];
				$this->end_dt = $row['END_DT'];
				$this->appr_list_id = $row['APPR_LST_ID'];
				$this->vend_no = $row['VEND_NO'];
				$this->set_pgm_id($row['PGM_ID']);
				
				$this->load_customers();
				$this->load_sls_ctr_cds();
				$this->load_locations();
			}
		}
	}
	
	/**
	 * Loads customers
	 */
	function load_customers()
	{		
		$select = "SELECT * 
			FROM ".APP_SCHEMA.".CNTRCT_CUST
			WHERE CNTRCT_ID = ?";
		
		// load results into the object keyed by customer type and code
		foreach ($this->db2->query($select, array($this->cntrct_id))->fetch_object() as $row)
		{
			switch ($row->CUST_TP)
			{
				case 'OT':
					$this->out_ids[] = $row->CUST_CD;
					break;
				case 'KA':
					$this->key_acct_cds[] = $row->CUST_CD;
					break;
				case 'TG':
					$this->trd_grp_cds[] = $row->CUST_CD;
					break;
			}
		}	
		
		$this->load_customer_objs();
	}
	
	/**
	 * Populates the $customers property with customer_model objects
	 */
	function load_customer_objs()
	{
		$this->load->model('customer_model');
		
		// clear existing customer objects
		$this->set_customers(array());
		
		foreach ($this->out_ids as $out_id)
		{
			$this->customers[] = $this->customer_model->get($out_id, 'OT');
		}
		
		foreach ($this->key_acct_cds as $key_acct_cd)
		{
			$this->customers[] = $this->customer_model->get($key_acct_cd, 'KA');
		}
		
		foreach ($this->trd_grp_cds as $trd_grp_cd)
		{
			$this->customers[] = $this->customer_model->get($trd_grp_cd, 'TG');
		}
	}
	
	function load_sls_ctr_cds()
	{
		$select = "SELECT C_L.SLS_CTR_CD
			FROM ".APP_SCHEMA.".CNTRCT_LOC AS C_L
			WHERE C_L.CNTRCT_ID = ?
			ORDER BY C_L.SLS_CTR_CD";		
		
		foreach ($this->db2->query($select, array($this->cntrct_id))->fetch_object() as $row)
		{
			$this->sls_ctr_cds[] = $row->SLS_CTR_CD;
		}
	}
	
	/**
	 * Loads locations and their names into locations property
	 */
	function load_locations()
	{
		$result = array();
		
		if ($this->cntrct_id !== NULL)
		{
			$select = "SELECT SC.*
				FROM DW.SLS_CTR AS SC
				JOIN ".APP_SCHEMA.".CNTRCT_LOC AS CL ON CL.SLS_CTR_CD = SC.SLS_CTR_CD
				WHERE CL.CNTRCT_ID = ?";
			
			$result = $this->db2->query($select, array($this->cntrct_id));
		} 
		elseif (count($this->sls_ctr_cds) > 0) {
			
			$select = "SELECT SC.*
				FROM DW.SLS_CTR AS SC
				WHERE SLS_CTR_CD IN ('".implode("','", $this->sls_ctr_cds)."')";
			
			$result = $this->db2->simple_query($select);
		}
		
		foreach ($result->fetch_object() as $row)
		{
			$this->locations[$row->SLS_CTR_CD] = $row->SLS_CTR_NM;
		}
	}
	
	function load_elements()
	{
		$CI =& get_instance();
		
		$CI->load->model('element_model');
		
		if ($this->cntrct_id !== NULL && $this->cntrct_id != FALSE)
		{
			$this->set_elements(Element_model::get_elements_by_cntrct_id($this->cntrct_id, $this->accrual_yr));
		}
	}
	
	function load_notes()
	{
		$CI =& get_instance();
		
		$CI->load->model('note_model');
		
		if ($this->cntrct_id !== NULL && $this->cntrct_id != FALSE)
		{
			$this->set_notes(Note_model::get_by_cntrct_id($this->cntrct_id));
		}
	}
	
	/**
	 * Checks to see if record for cntrct_id already exists
	 * 
	 * @return boolean
	 */
	function exists()
	{
		if ($this->cntrct_id !== NULL)
		{
			$select = "SELECT * FROM ".APP_SCHEMA.".CNTRCT WHERE CNTRCT_ID = ?";
				
			if (count($this->db2->query($select, array($this->cntrct_id))->fetch_assoc()) > 0)
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
	 * Saves object to the database
	 */
	function save()
	{
		$this->db2->begin_transaction();
		
		try {
			if ($this->exists())
			{
				// create update sql statement
				$update = "UPDATE ".APP_SCHEMA.".CNTRCT
					SET APP = ?,
						CNTRCT_NM = ?,
						CSE_TP = ?,
						STRT_DT = ?,
						END_DT = ?,
						APPR_LST_ID = ?,
						VEND_NO = ?
					WHERE CNTRCT_ID = ?";
				
				$update_parms = array(
					$this->app,
					$this->get_cntrct_nm(),
					$this->cse_tp,
					$this->strt_dt,
					$this->end_dt,
					$this->appr_list_id,
					$this->vend_no,
					$this->cntrct_id
				);
				
				$this->db2->query($update, $update_parms);
				
				$this->remove_program();
				$this->remove_elements();
				$this->remove_customers();
				$this->remove_locations();
			}
			else
			{
				// create insert statement
				$insert = "SELECT * FROM FINAL TABLE 
					( 
						INSERT INTO ".APP_SCHEMA.".CNTRCT (CNTRCT_ID,APP,CNTRCT_NM,CSE_TP,STRT_DT,END_DT,APPR_LST_ID,VEND_NO)
						VALUES (NEXT VALUE FOR ".APP_SCHEMA.".SQ_CNTRCT_PK, ?, ?, ?, ?, ?, ?, ?)
					)";
				
				$insert_parms = array(
					$this->app,
					$this->get_cntrct_nm(),
					$this->cse_tp,
					$this->strt_dt,
					$this->end_dt,
					$this->appr_list_id,
					$this->vend_no
				);
				
				$result = $this->db2->query($insert, $insert_parms)->fetch_object();
				
				$this->cntrct_id = $result[0]->CNTRCT_ID;
			}
			
			// add relationships back
			$this->add_program();
			$this->add_elements();
			$this->add_customers();
			$this->add_locations();
			
			// clear out any related data
			$this->clear_elem_stats();
			
		} catch (Exception $e) {
			
			$this->db2->rollback();
			
			$error =& load_class('Exceptions', 'core');
			echo $error->show_error("Error saving contract", $e->getMessage(), 'error_general');
			exit;
		}		
		
		$this->db2->commit();
	}
	
	/**
	 * Removes contract information from database
	 */
	function remove()
	{
		if ($this->cntrct_id !== NULL)
		{
			$this->remove_elements();
			$this->remove_customers();
			$this->remove_locations();
			
			$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT WHERE CNTRCT_ID = ?";
			
			$this->db2->query($delete, array($this->cntrct_id));
		}
		
	}
	
	function remove_program()
	{
		if ($this->cntrct_id !== NULL && $this->pgm_id !== NULL)
		{
			$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_PGM
				WHERE CNTRCT_ID = ?";
			
			$this->db2->query($delete, array($this->cntrct_id));
		}
	}
	
	function add_program()
	{
		if ($this->cntrct_id !== NULL && $this->pgm_id !== NULL)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_PGM (CNTRCT_ID,PGM_ID)
				VALUES (?,?)";
			
			$this->db2->query($insert, array($this->cntrct_id, $this->pgm_id));
		}
	}
	
	/**
	 * Removes elements assigned to contract
	 */
	function remove_elements()
	{
		if ($this->cntrct_id !== NULL)
		{
			$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_ELEM
				WHERE CNTRCT_ID = ?";
			
			$this->db2->query($delete, array($this->cntrct_id));
						
			$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT
				WHERE CNTRCT_ID = ?";
			
			$this->db2->query($delete, array($this->cntrct_id));
		}
	}
	
	/**
	 * Removes projections, fixed units and contract sales for any 
	 * element no longer attached to the contract
	 */
	function clear_elem_stats()
	{
		if ($this->cntrct_id !== NULL)
		{
			$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_PRJTN T1
				WHERE CNTRCT_ID = ?
					AND ELEM_ID NOT IN (
					SELECT DISTINCT ELEM_ID
					FROM ".APP_SCHEMA.".CNTRCT_ELEM CE
					WHERE CE.CNTRCT_ID = T1.CNTRCT_ID
				)";
			
			$this->db2->query($delete, array($this->cntrct_id));
			
			$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_FIX_UNT T1
				WHERE CNTRCT_ID = ?
					AND ELEM_ID NOT IN (
					SELECT DISTINCT ELEM_ID
					FROM ".APP_SCHEMA.".CNTRCT_ELEM CE
					WHERE CE.CNTRCT_ID = T1.CNTRCT_ID
				)";
			
			$this->db2->query($delete, array($this->cntrct_id));
			
			$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_SLS T1
				WHERE CNTRCT_ID = ?
					AND ELEM_ID NOT IN (
					SELECT DISTINCT ELEM_ID
					FROM ".APP_SCHEMA.".CNTRCT_ELEM CE
					WHERE CE.CNTRCT_ID = T1.CNTRCT_ID
				)";
			
			$this->db2->query($delete, array($this->cntrct_id));
		}
	}
	
	/*
	 * Adds elements to contract
	 */
	function add_elements()
	{
		if ($this->cntrct_id !== NULL && count($this->elements) > 0)
		{
			// insert for general info
			$insert_elem = "INSERT INTO ".APP_SCHEMA.".CNTRCT_ELEM (CNTRCT_ID,ELEM_ID,PYMT_FREQ) VALUES (?, ?, ?)";
			
			$this->db2->set_sql($insert_elem)->prepare();
			
			$elem_dts = array();
			
			foreach ($this->elements as $element)
			{
				$this->db2->set_params(
						array(
							$this->cntrct_id, 
							$element->get_elem_id(), 
							$element->get_pymt_freq()
						)
					)
					->execute();
				
				// Build element dates to insert 
				$end_dts = $element->get_end_dts();
				
				foreach ($element->get_strt_dts() as $index => $strt_dt)
				{
					$elem_dts[] = array(
						$this->cntrct_id, 
						$element->get_elem_id(), 
						$strt_dt,
						$end_dts[$index]
					);
				}
			}
			
			// insert for dates
			$insert_dt = "INSERT INTO ".APP_SCHEMA.".CNTRCT_ELEM_DT (CNTRCT_ID,ELEM_ID,STRT_DT,END_DT) VALUES (?, ?, ?, ?)";
			
			// insert dates
			$this->db2->set_sql($insert_dt)->prepare();
			
			foreach ($elem_dts as $elem_dt)
			{
				$this->db2->set_params($elem_dt)->execute();
			}
		}
	}
	
	/**
	 * Removes customers assigned to contract
	 */
	function remove_customers()
	{
		if ($this->cntrct_id !== NULL)
		{
			$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_CUST
				WHERE CNTRCT_ID = ?";
			
			$this->db2->query($delete, array($this->cntrct_id));
		}
	}
	
	/*
	 * Adds customers to contract
	 */
	function add_customers()
	{
		if ($this->cntrct_id !== NULL && count(array_merge($this->out_ids, $this->key_acct_cds, $this->trd_grp_cds)) > 0)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_CUST (CNTRCT_ID,CUST_CD,CUST_TP) VALUES (?, ?, ?)";
			
			$this->db2->set_sql($insert)->prepare();
			
			$rows = array();
			
			foreach ($this->out_ids as $code)
			{
				$this->db2->set_params(array($this->cntrct_id, $code, 'OT'))->execute();
			}
			
			foreach ($this->key_acct_cds as $code)
			{
				$this->db2->set_params(array($this->cntrct_id, $code, 'KA'))->execute();
			}
			
			foreach ($this->trd_grp_cds as $code)
			{
				$this->db2->set_params(array($this->cntrct_id, $code, 'TG'))->execute();
			}
		}
	}
	
	/**
	 * Removes locations assigned to contract
	 */
	function remove_locations()
	{
		if ($this->cntrct_id !== NULL)
		{
			$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_LOC
				WHERE CNTRCT_ID = ?";
			
			$this->db2->query($delete, array($this->cntrct_id));
		}
	}
	
	/**
	 * Adds locations to contract
	 */
	function add_locations()
	{
		if ($this->cntrct_id !== NULL && count($this->sls_ctr_cds) > 0)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_LOC (CNTRCT_ID,SLS_CTR_CD) VALUES (?, ?)";
			
			$this->db2->set_sql($insert)->prepare();
			
			foreach ($this->sls_ctr_cds as $sls_ctr_cd)
			{
				$this->db2->set_params(array($this->cntrct_id, $sls_ctr_cd))->execute();
			}
		}
	}

	/**
	 * Searches contract by name
	 * 
	 * @param string $search String fragment to search for
	 * @param array $locations Outlet Locations to restrict search to
	 * @param string $app Application to restrict search to
	 * @param bool $active Restrict to active contracts
	 * @param bool $no_obj Set to TRUE, returns db result instead of cprogram_model objects
	 * 
	 * return array|object
	 */
	function get_datatable($search = '', $role_id = NULL, $app = '', $sort = NULL, $sort_dir = NULL)
	{
		$params = array($role_id, $app);
		
		$search_where = '';
		
		if ($search != '')
		{
			$search_where = " AND (C.CNTRCT_ID LIKE ? OR LOWER(CNTRCT_NM) LIKE ? OR LOWER(SLS_CTRS) LIKE ?)";
			$params[] = strtolower('%'.$search.'%');
			$params[] = strtolower('%'.$search.'%');
			$params[] = strtolower('%'.$search.'%');
		}
		
		if ($sort == 'TIME_ORDER')
		{
			$order_by = "ORDER BY TIME_ORDER DESC";
		}
		else {
			$order_by = "ORDER BY ".$sort." ".$sort_dir;
		}
		
		$select = "WITH 
			R AS (
				SELECT *
				FROM ".APP_SCHEMA.".ROLE
				WHERE ROLE_ID = ?
			),
			RC AS (
				SELECT RC.*
				FROM ".APP_SCHEMA.".ROLE_CNTRCT RC
					JOIN R ON R.ROLE_ID = RC.ROLE_ID
			),
			SCS (ROWNUM, CNTRCT_ID, SLS_CTR_NM) AS (
				SELECT ROW_NUMBER() OVER(PARTITION BY CNTRCT_ID ORDER BY CNTRCT_ID, DIV_CD, CL.SLS_CTR_CD) AS ROWNUM, 
					CNTRCT_ID, 
					CASE CL.SLS_CTR_CD 
						WHEN '12' THEN 'WAL'
						ELSE SUBSTR(REPLACE(SLS_CTR_NM, 'LAUREL MOUNTAIN', 'LM'), 1, 3)
					END AS SLS_CTR_NM
				FROM ".APP_SCHEMA.".CNTRCT_LOC CL
					JOIN DW.SLS_CTR SC ON SC.SLS_CTR_CD = CL.SLS_CTR_CD
			),
			AGG_SCS (ROWNUM, CNTRCT_ID, SCS) AS (
				SELECT ROWNUM, CNTRCT_ID, CAST(SLS_CTR_NM AS VARCHAR(100))
				FROM SCS
				WHERE ROWNUM = 1
				UNION ALL
				SELECT AGG_SCS.ROWNUM + 1 AS ROWNUM, AGG_SCS.CNTRCT_ID, 
					AGG_SCS.SCS || ', ' || SCS.SLS_CTR_NM AS SCS
				FROM AGG_SCS
					JOIN SCS ON SCS.CNTRCT_ID = AGG_SCS.CNTRCT_ID
						AND SCS.ROWNUM = AGG_SCS.ROWNUM + 1
			)
			SELECT C.*, SLS_CTRS,
				COALESCE(ADT.ACTN_TM, CURRENT_TIMESTAMP - 1 YEARS) AS TIME_ORDER
			FROM R, ".APP_SCHEMA.".CNTRCT AS C
				LEFT JOIN ".APP_SCHEMA.".CNTRCT_PGM CP ON CP.CNTRCT_ID = C.CNTRCT_ID
				LEFT JOIN RC ON RC.CNTRCT_ID = C.CNTRCT_ID
				LEFT JOIN (
					SELECT ENTY_ID AS CNTRCT_ID, MAX(ACTN_TM) AS ACTN_TM
					FROM ".APP_SCHEMA.".ADT A
						JOIN ".APP_SCHEMA.".USR_ROLE UR ON UR.USR_ID = A.USR_ID
						JOIN R ON R.ROLE_ID = UR.ROLE_ID
					WHERE ENTY_TP = 'CT'
					GROUP BY ENTY_ID
				) AS ADT ON ADT.CNTRCT_ID = C.CNTRCT_ID
				JOIN (
					SELECT DISTINCT CNTRCT_ID 
					FROM ".APP_SCHEMA.".CNTRCT_LOC CL 
						JOIN ".APP_SCHEMA.".ROLE_LOC RL ON RL.SLS_CTR_CD = CL.SLS_CTR_CD
						JOIN R ON R.ROLE_ID = RL.ROLE_ID
				) CL ON CL.CNTRCT_ID = C.CNTRCT_ID
				JOIN (
					SELECT CNTRCT_ID, MAX(SCS) AS SLS_CTRS
					FROM AGG_SCS
					GROUP BY CNTRCT_ID
				) AS L ON L.CNTRCT_ID = C.CNTRCT_ID
			WHERE C.APP = ?
				AND (
					((SELECT COUNT(*) FROM RC) > 0 AND RC.CNTRCT_ID IS NOT NULL)
					OR
					((SELECT COUNT(*) FROM RC) = 0 AND RC.CNTRCT_ID IS NULL)
				)".$search_where."
			".$order_by.", C.CNTRCT_NM";	
		
		return $this->db2->query($select, $params)->fetch_object();
	}
	
	/**
	 * Searches contracts by name. 
	 * @param string $name
	 * @param array $filters	optional filters: app, sls_ctr_cd
	 * @return array
	 */
	public static function search_by_name($name = '', $filters = array())
	{
		$sub_sel = "SELECT DISTINCT C.CNTRCT_ID 
			FROM ".APP_SCHEMA.".CNTRCT C";
		
		$joins = $wheres = $params = array();		
		
		foreach ($filters as $filter => $values)
		{
			switch ($filter)
			{
				case 'role_id':
					$joins[] = "JOIN ".APP_SCHEMA.".ROLE R ON 1 = 1";
					$joins[] = "LEFT JOIN ".APP_SCHEMA.".ROLE_CNTRCT RC ON RC.CNTRCT_ID = C.CNTRCT_ID AND RC.ROLE_ID = R.ROLE_ID";
					$joins[] = "LEFT JOIN ".APP_SCHEMA.".ROLE_CNTRCT RC2 ON RC2.ROLE_ID = RC.ROLE_ID";
					
					$wheres[] = "( R.ROLE_ID = ? AND 
							( 
								(RC.CNTRCT_ID IS NOT NULL AND RC2.CNTRCT_ID IS NOT NULL) 
								OR 
								(RC.CNTRCT_ID IS NULL AND RC2.CNTRCT_ID IS NULL) 
							) 
						)";
					
					$params[] = $values;
					break;
				case 'sls_ctr_cd':
					$joins[] = "JOIN ".APP_SCHEMA.".CNTRCT_LOC CL ON CL.CNTRCT_ID = C.CNTRCT_ID";
					if (is_array($values))
					{
						$wheres[] = "CL.SLS_CTR_CD IN (".substr(str_repeat('?,', count($values)), 0, -1).")";
						$params = array_merge($params, $values);
					}
					else
					{
						$wheres[] = "CL.SLS_CTR_CD = ?";
						$params[] = $values;
					}
					break;
				case 'app':
					if (is_array($values))
					{
						$wheres[] = "C.APP IN (".substr(str_repeat('?,', count($values)), 0, -1).")";
						$params = array_merge($params, $values);
					}
					else
					{
						$wheres[] = "C.APP = ?";
						$params[] = $values;
					}
					break;
				case 'yr':
					$wheres[] = "? BETWEEN YEAR(C.STRT_DT) AND YEAR(C.END_DT)";
					$params[] = $values;
					break;
			}			
		}
		
		$select = "SELECT C.*
			FROM ".APP_SCHEMA.".CNTRCT C";
		
		// add filters if provided
		if (count($filters) > 0)
		{
			$select .= ' JOIN ('.$sub_sel.' '.implode(' ', $joins).' WHERE '.implode(' AND ', $wheres).' ) AS FILTER ON FILTER.CNTRCT_ID = C.CNTRCT_ID';
		}
		
		if ($name != '')
		{
			$select .= " WHERE LOWER(C.CNTRCT_NM) LIKE ?
				OR LOWER(C.CNTRCT_ID) LIKE ?";
			$params[] = "%".strtolower($name)."%";
			$params[] = "%".strtolower($name)."%";
		}
		
		$CI =& get_instance();
		log_message('debug', $select."\nParams: ".print_r($params, TRUE));
		
		return $CI->db2->query($select, $params)->fetch_object();
	}
	
	
	/**
	 * Get's unfiltered count of contracts for use with datatables
	 * 
	 * @param integer $usr_id
	 * @param string $app
	 * @return integer
	 */
	public function get_cntrct_cnt($role_id = NULL, $app = NULL)
	{
		if ($role_id !== NULL && $app !== NULL)
		{
			$select = "WITH 
				R AS (
					SELECT *
					FROM ".APP_SCHEMA.".ROLE
					WHERE ROLE_ID = ?
				),
				RC AS (
					SELECT RC.*
					FROM ".APP_SCHEMA.".ROLE_CNTRCT RC
						JOIN R ON R.ROLE_ID = RC.ROLE_ID
				)
				SELECT COUNT(*) AS CNTRCT_CNT
				FROM ".APP_SCHEMA.".CNTRCT C
					LEFT JOIN RC ON RC.CNTRCT_ID = C.CNTRCT_ID
				WHERE C.APP = ?
					AND (
						((SELECT COUNT(*) FROM RC) > 0 AND RC.CNTRCT_ID IS NOT NULL)
						OR
						((SELECT COUNT(*) FROM RC) = 0 AND RC.CNTRCT_ID IS NULL)
					)";
			
			$result = $this->db2->query($select, array($role_id, $app))->fetch_object();
		
			return $result[0]->CNTRCT_CNT;
		}
		else
		{
			return 0;
		}
		
	}
	
	/**
	 * Returns total dollars accrued for element in
	 * this contract 
	 * 
	 * @param integer $elem_id
	 */
	function get_accr_ttl($elem_id)
	{
		$accr_ttl = 0;
		
		foreach ($this->elements as $element)
		{
			$accr_ttl += $element->get_accrued();
		}
		
		return $accr_ttl;
	}
	
	/**
	 * Pulls sales info from data warehouse
	 */
	function pull_sls()
	{
		if ($this->cntrct_id !== NULL && count($this->elements) > 0)
		{
			$sls_sqls = array();
			
			foreach ($this->elements as $element)
			{
				$sql = new Query;
				
				$sql->selects = array(
					"S.STLMNT_DT", 
					"OM.OUT_LOC AS SLS_CTR_CD", 
					$this->cntrct_id." AS CNTRCT_ID",
					$element->get_elem_id()." AS ELEM_ID",
					($this->cse_tp == 'CV') ? "SUM(S.CSE_VOL * AM.EQUIV_CNVRT_FCTR) AS CSE_VOL" : "SUM(S.CSE_VOL) AS CSE_VOL",
					"CURRENT_TIMESTAMP AS LST_UPDT_TM",
					"'N' AS MAN_SLS_FLG",
					"SUM(S.WHSLE_AMT + S.UPCHRG_AMT - TX_LBLTY_AMT - CUST_COMSN_AMT - DISC_AMT) AS NET_SELL_AMT"
				);
				
				$sql->from = "DW.DWSALES AS S";
				
				$sls_crits = array();
				
				// parse sls_crits objects for relevant info
				foreach ($element->get_sls_crits() as $sls_crit)
				{
					$sls_crits[$sls_crit->get_crit_tbl()][$sls_crit->get_crit_fld()][$sls_crit->get_accr_flg()][] = $sls_crit->get_crit_cd();
				}	
							
				// add wheres for critiera fields
				foreach ($sls_crits as $tbl => $flds)
				{
					foreach ($flds as $fld => $flgs)
					{
						foreach ($flgs as $flg => $cds)
						{
							$not = '';
							
							if ($tbl == 'DWAM01')
							{
								$syn = 'AM';
							}
							elseif ($tbl == 'DWOM01')
							{
								$syn = 'OM';
							}
							
							if (count($cds) > 1)
							{							
								if ($flg == 'N') $not = ' NOT';
								$sql->wheres[] = $syn.".".$fld."_CD".$not." IN ('".implode("','",$cds)."')";
							}
							elseif (count($cds) == 1)
							{
								if ($flg == 'N') $not = '!';
								$sql->wheres[] = $syn.".".$fld."_CD ".$not."= '".$cds[0]."'";
							}
						}
					}
				}
				
				// Join locatoins
				$sql->joins[] = "JOIN DW.DWOM01 AS OM ON OM.OUT_ID = S.OUT_ID";
				$sql->joins[] = "JOIN ".APP_SCHEMA.".CNTRCT_LOC AS CL ON CL.SLS_CTR_CD = OM.OUT_LOC";
				$sql->wheres[] = "CL.CNTRCT_ID = ".$this->cntrct_id;
				
				// join article master if needed
				if (array_key_exists('DWAM01',$sls_crits) || $this->cse_tp == 'CV')
				{
					$sql->joins[] = "JOIN DW.DWAM01 AS AM ON AM.ART_ID = S.ART_ID";
				}
				
				// restrict to customers
				if (count($this->out_ids) > 1)
				{
					$sql->wheres[] = "S.OUT_ID IN (".implode(',',$this->out_ids).")";
				}
				elseif (count($this->out_ids) == 1)
				{
					$sql->wheres[] = "S.OUT_ID = ".$this->out_ids[0];
				}
				
				if (count($this->key_acct_cds) > 1)
				{
					$sql->wheres[] = "OM.KEY_ACCT_CD IN ('".implode("','",$this->key_acct_cds)."')";
				}
				elseif (count($this->key_acct_cds) == 1)
				{
					$sql->wheres[] = "OM.KEY_ACCT_CD = '".$this->key_acct_cds[0]."'";
				}
				
				if (count($this->trd_grp_cds) > 1)
				{
					$sql->wheres[] = "OM.TRD_GRP_CD IN ('".implode("','",$this->trd_grp_cds)."')";
				}
				elseif (count($this->trd_grp_cds) == 1)
				{
					$sql->wheres[] = "OM.TRD_GRP_CD = '".$this->trd_grp_cds[0]."'";
				}
				
				$sql->wheres[] = "( 
					S.STLMNT_DT BETWEEN '".$this->get_strt_dt()."' AND '".$this->get_end_dt()."' 
					OR S.STLMNT_DT BETWEEN DATE('".$this->get_strt_dt()."') - 1 YEARS AND DATE('".$this->get_end_dt()."') - 1 YEARS 
					)";
				
				// add group by
				$sql->extra = "GROUP BY S.STLMNT_DT, OM.OUT_LOC";
				
				// wrap in insert statement
				$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_SLS (STLMNT_DT,SLS_CTR_CD,CNTRCT_ID,ELEM_ID,CSE_VOL,LST_UPDT_TM,MAN_SLS_FLG,NET_SELL_AMT)";
				
				// remove any sales the insert query will grab
				$remove = "DELETE FROM ".APP_SCHEMA.".CNTRCT_SLS
					WHERE CNTRCT_ID = ".$this->cntrct_id."
					AND ELEM_ID = ".$element->get_elem_id()."
					AND ( 
					STLMNT_DT BETWEEN '".$this->get_strt_dt()."' AND '".$this->get_end_dt()."' 
					OR STLMNT_DT BETWEEN DATE('".$this->get_strt_dt()."') - 1 YEARS AND DATE('".$this->get_end_dt()."') - 1 YEARS 
					)";				
				
				$this->db2->simple_query($remove);
				
				// execute query
				$this->db2->simple_query($insert." ".$sql->build());
			}
		}
	}
	
	/**
	 * Assigns posted values to contract properties.  Uses set_value to default property back to current
	 * property if posted value is not present
	 */
	public function process_post()
	{
		$CI =& get_instance();
		
		if ($CI->input->post('cntrct_nm') !== FALSE)
			$this->set_cntrct_nm($CI->input->post('cntrct_nm'));
			
		if ($CI->input->post('cse_tp') !== FALSE)
			$this->set_cse_tp($CI->input->post('cse_tp'));
			
		if ($CI->input->post('strt_dt') !== FALSE && $CI->input->post('dt_fmt') !== FALSE)
			$this->set_strt_dt($CI->input->post('strt_dt'), $CI->input->post('dt_fmt'));
			
		if ($CI->input->post('end_dt') !== FALSE && $CI->input->post('dt_fmt') !== FALSE)
			$this->set_end_dt($CI->input->post('end_dt'), $CI->input->post('dt_fmt'));
			
		if ($CI->input->post('appr_lst_id') !== FALSE )
			$this->set_appr_lst_id($CI->input->post('appr_lst_id'));
			
		if ($CI->input->post('vend_no') !== FALSE )
			$this->set_vend_no($CI->input->post('vend_no'));
			
		if ($CI->input->post('sls_ctr_cd') !== FALSE)
			$this->set_sls_ctr_cds($CI->input->post('sls_ctr_cd'));
			
		if ($CI->input->post('cust_cd') !== FALSE && $CI->input->post('cust_tp') !== FALSE)
			$this->set_customer_cds($CI->input->post('cust_cd'), $CI->input->post('cust_tp'));
			
		if ($CI->input->post('pgm_id') !== FALSE)
			$this->set_pgm_id($CI->input->post('pgm_id'));
			
		$posted_elements = array();
		if ($CI->input->post('element') !== FALSE)
			$posted_elements = $CI->input->post('element');
			
		if ($CI->input->post('elem_dt_fmt') !== FALSE)
			$elem_dt_fmt = $CI->input->post('elem_dt_fmt');
			
		$elements = array();
		
		foreach ($posted_elements as $elem_id => $elem_info)
		{
			$element = new Element_model();
			
			$element->set_elem_id($elem_id);
			$element->set_cntrct_id($this->get_cntrct_id());
			$element->set_strt_dts($elem_info['strt_dt'], $elem_dt_fmt);
			$element->set_end_dts($elem_info['end_dt'], $elem_dt_fmt);
			$element->set_pymt_freq($elem_info['pymt_freq']);
			$element->set_cntrct_pgm_id($this->get_pgm_id());
			
			$elements[] = $element;
		}

		$this->set_elements($elements);
		
		if ($CI->input->post('usr_id') !== FALSE)
			$this->set_usr_ids($CI->input->post('usr_id'));
			
		$this->set_app($CI->session->userdata('app'));
	}
	
	/**
	 * Returns last year of contract
	 * 
	 * @return number
	 */
	public function get_last_yr()
	{
		return intval(substr($this->end_dt,0,4));
	}
	
	public function get_cntrct_yrs()
	{
		$strt_yr = substr($this->strt_dt, 0, 4);
		$end_yr = substr($this->end_dt, 0, 4);
		
		$yrs = array();
		for ($i=$strt_yr;$i<=$end_yr;$i++)
		{
			$yrs[] = $i;
		}
		
		return $yrs;
	}
	
	public function get_proj_cost()
	{
		return array(
			'oi' => $this->stats['oi'],
			'pst' => $this->stats['pst']
		);
	}
	
	public function get_elem_sls_stats($elem_id)
	{
		if (isset($this->stats['elem_dtl'][$elem_id]))
		{
			return $this->stats['elem_dtl'][$elem_id];
		}
		else
		{
			return array();
		}
	}
	
	public function get_elem_acr($elem_id)
	{
		if (isset($this->stats['elem_acr'][$elem_id]))
		{
			return $this->stats['elem_acr'][$elem_id];
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * Queries Database and loads sales statistics for display in contract
	 */
	public function load_stats()
	{
		$params = array(
			$this->get_accrual_yr(), 
			$this->get_cntrct_id(),
			$this->session->userdata('role_id')
		);
		
		$select = "SELECT E.ELEM_ID, E.ON_INV_FLG, CASE WHEN D.REGN_CD = 'G' THEN 'GULF' ELSE D.DIV_NM END AS DIV_NM, 
				SC.SLS_CTR_NM, SC.SLS_CTR_CD, A.ACR, A.LY, A.YTD, A.PRJ, A.TTL,
				DECIMAL(ROUND(DECIMAL(COALESCE(A.FIX_UNTS, 0), 13, 2) / DECIMAL(E.UNT_DIV, 13, 2), 2), 13, 2) AS FIX_UNTS,
				CASE WHEN A.LY = 0 THEN 0 ELSE DECIMAL(ROUND(DECIMAL(A.TTL - A.LY, 13, 4) / A.LY, 4), 13, 4) END AS CHG
			FROM (
				SELECT A.ELEM_ID, A.SLS_CTR_CD, A.YR, 
					SUM(CASE WHEN END_DT <= CURRENT_DATE - DAYOFMONTH(CURRENT_DATE) DAYS THEN CSE_VOL ELSE 0 END) AS YTD,
					SUM(CASE WHEN END_DT > CURRENT_DATE - DAYOFMONTH(CURRENT_DATE) DAYS THEN CSE_VOL ELSE 0 END) AS PRJ,
					SUM(CSE_VOL) AS TTL, SUM(LY_VOL) AS LY, SUM(ACCR_AMT) AS ACR, SUM(FIX_UNTS) AS FIX_UNTS
				FROM ".APP_SCHEMA.".ACCR A
					JOIN ".APP_SCHEMA.".ROLE_LOC RL ON RL.SLS_CTR_CD = A.SLS_CTR_CD
				WHERE A.YR = ?
					AND A.CNTRCT_ID = ?
					AND RL.ROLE_ID = ?
				GROUP BY A.ELEM_ID, A.SLS_CTR_CD, A.YR
			) AS A
				JOIN ".APP_SCHEMA.".ELEM E ON E.ELEM_ID = A.ELEM_ID
				JOIN DW.SLS_CTR SC ON SC.SLS_CTR_CD = A.SLS_CTR_CD
				JOIN DW.DIV D ON D.DIV_CD = SC.DIV_CD
			ORDER BY E.ELEM_ID, D.REGN_CD, D.DIV_NM, SC.SLS_CTR_NM";
		
		$cntrct_acr_oi = 0;
		$cntrct_acr_pst = 0;
		
		$elem_acr = array();
		
		$elem_dtl = array();
		
		foreach ($this->db2->query($select, $params)->fetch_object() as $row)
		{
			if ($row->ON_INV_FLG == 'Y')
			{
				$cntrct_acr_oi += $row->ACR;
			} else {
				$cntrct_acr_pst += $row->ACR;
			}
			
			if (isset($elem_acr[$row->ELEM_ID]))
			{
				$elem_acr[$row->ELEM_ID] += $row->ACR;
			}
			else
			{
				$elem_acr[$row->ELEM_ID] = $row->ACR;
			}
			
			$elem_dtl[$row->ELEM_ID][] = $row;
		}
		
		$this->stats = array(
			'oi' => $cntrct_acr_oi,
			'pst' => $cntrct_acr_pst,
			'elem_acr' => $elem_acr,
			'elem_dtl' => $elem_dtl
		);
	}
}

/* End of file contract.php */
/* Location: ./ctm_cma/models/contract.php */