<?php
/**
 * Element_model Class
 * 
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage Models
 *
 */
class Element_model extends CI_Model {
	
	// Element status 
	public static $enbls = array(
		'Y' => 'Yes',
		'N' => 'No'
	);
	
	// Element types
	public static $elem_tps = array(
		'PC' => 'Per Case',
		'TL' => 'Fixed',
		'TU' => 'Fixed Unit',
		'NP' => '% of NSA'
	);
	
	// Element targets
	public static $elem_trgts = array(
		'AC' => 'All Cases',
		'NC' => 'Incremental Cases'
	);
	
	// Element triggers
	public static $elem_trigrs = array(
		'FC' => 'First Case',
		'EL' => 'Greater than or Equal to Last Year',
		'GL' => 'Greater than Last Year',
		'CT' => 'Case Threshold'
	);
	
	// payment frequency - only used in combination with contract
	public static $pymt_freqs = array(
		'00' => 'No Payments',
		'OD' => 'On Demand',
		'1S' => 'Year Start',
		'1E' => 'Year End',
		'02' => 'Bi-annually',
		'03' => 'Quarterly',
		'04' => 'Trimester',
		'12' => 'Monthly'
	);
	
	// On invoice flag.  
	public static $on_inv_flgs = array(
		'Y' => 'Yes',
		'N' => 'No'
	);	
	
	// Funding templates
	public $funding_templates = array(
		'gulfbase' => array(
			'desc' => 'Gulf Base',
			'apps' => array('CT','CM','SC'),
			'options' => array(
				'elem_tp' => 'PC',
				'elem_trgt' => 'AC',
				'elem_trigr' => 'FC',
				'pct' => '100',
				'shr' => '100'
			)
		),
		'eastbase' => array(
			'desc' => 'East Base',
			'apps' => array('CT','CM','SC'),
			'options' => array(
				'elem_tp' => 'PC',
				'elem_trgt' => 'AC',
				'elem_trigr' => 'FC',
				'pct' => '100',
				'shr' => '100'
			)
		),
		'gulfbonus' => array(
			'desc' => 'Gulf Bonus',
			'apps' => array('CT','CM'),
			'options' => array(
				'elem_tp' => 'PC',
				'elem_trgt' => 'AC',
				'elem_trigr' => 'GL',
				'pct' => '100',
				'shr' => '100'
			)
		),
		'gulfincrm' => array(
			'desc' => 'Gulf Incremental',
			'apps' => array('CT','CM'),
			'options' => array(
				'elem_tp' => 'PC',
				'elem_trgt' => 'NC',
				'elem_trigr' => 'GL',
				'pct' => '100',
				'shr' => '100'
			)
		),
		'eastincrm' => array(
			'desc' => 'East Incremental',
			'apps' => array('CT','CM'),
			'options' => array(
				'elem_tp' => 'PC',
				'elem_trgt' => 'NC',
				'elem_trigr' => 'GL',
				'pct' => '100',
				'shr' => '100'
			)
		),
		'gulfincenpct' => array(
			'desc' => 'Gulf Incentive %',
			'apps' => array('CT','CM'),
			'options' => array(
				'elem_tp' => 'PC',
				'elem_trgt' => 'AC',
				'elem_trigr' => 'EL',
				'pct' => '100',
				'shr' => '100'
			)
		),
		'gulfadvert' => array(
			'desc' => 'Gulf Advertisement',
			'apps' => array('CT','CM'),
			'options' => array(
				'elem_tp' => 'PC',
				'elem_trgt' => 'AC',
				'elem_trigr' => 'FC',
				'pct' => '100',
				'pymt_lmt' => '1000',
				'shr' => '100'
			)
		),
		'gulffixed' => array(
			'desc' => 'Gulf Fixed',
			'apps' => array('CT','CM'),
			'options' => array(
				'elem_tp' => 'TL',
				'elem_trgt' => 'AC',
				'elem_trigr' => 'FC',
				'pct' => '100',
				'shr' => '100'
			)
		),
		'eastfixed' => array(
			'desc' => 'East Fixed',
			'apps' => array('CT','CM'),
			'options' => array(
				'elem_tp' => 'TL',
				'elem_trgt' => 'AC',
				'elem_trigr' => 'FC',
				'pct' => '100',
				'shr' => '100'
			)
		),
		'gulffixunit' => array(
			'desc' => 'Gulf Fixed Unit',
			'apps' => array('CT','CM'),
			'options' => array(
				'elem_tp' => 'TU',
				'unt_div' => '12',
				'elem_trgt' => 'AC',
				'elem_trigr' => 'FC',
				'pct' => '100',
				'shr' => '100'
			)
		),
		'eastnsipct' => array(
			'desc' => 'East NSI Percentage',
			'apps' => array('CT','CM','SC'),
			'options' => array(
				'elem_tp' => 'NP',
				'unt_div' => '0',
				'elem_trgt' => 'AC',
				'elem_trigr' => 'FC',
				'pct' => '100',
				'shr' => '100'
			)
		)
	);
	
	// Element properties
	private $elem_id = NULL; // primary key
	private $enbl = 'Y';
	private $app = NULL; // assigned application
	private $elem_nm = ''; // Element name
	private $elem_desc = ''; // Element Description
	private $elem_tp = 'PC'; // Element Type: default is "Per Case"
	private $elem_rt = 0; // Element rate 
	private $elem_trgt = 'AC'; // what rate accrues against
	private $elem_trigr = 'FC'; // what triggers the rate to start accruing
	private $pct = 100; // % of last year cases to trigger rate, or % of nsi to pay
	private $pymt_lmt = 0; // max accrual amount
	private $shr = 100; // Our shr of National contract
	private $on_inv_flg = 'N'; // Element on invoice (discount 40 & 41)?
	private $unt_div = 12; // for fixed unit rate type.  Number of units per year
	private $cse_thld = 0; // 
	private $loaded = FALSE;
	
	private $sls_crits = array(); // Assigned criteria objects
	private $crit_flds = array(); // assigned criteria fields
	private $crit_cds = array(); // assigned criteria codes
	private $crit_accr_flgs = array(); // assigned criteria flags
	
	private $programs = array();
	
	// Contract related properties
	private $cntrct_id = NULL;
	private $strt_dts = NULL;
	private $end_dts = NULL;
	private $pymt_freq = 00;
	private $cntrct_pgm_id = NULL;
	private $ly_sls = array();
	private $sls = array();
	private $prjtns = array();
	private $fixed_unts = array();
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Sets element identifier
	 * 
	 * @return ingeter
	 */
	function set_elem_id($elem_id)
	{
		if (intval($elem_id) != 0)
			$this->elem_id = intval($elem_id);
	}
	
	/**
	 * Set's elem_id to NULL value in preparation for 
	 * creating element copy
	 */
	public function clear_elem_id()
	{
		$this->elem_id = NULL;
	}
	
	/**
	 * Returns element identifier
	 * 
	 * @return integer
	 */
	function get_elem_id()
	{
		return $this->elem_id;
	}
	
	/**
	 * Sets element status
	 * 
	 * @param string $enbl
	 */
	function set_enbl($enbl)
	{
		$this->enbl = $enbl;
	}
	
	/**
	 * Returns element status
	 * 
	 * @return string
	 */
	function get_enbl()
	{
		return $this->enbl;
	}
	
	/**
	 * Returns element status for display
	 * 
	 * @return string
	 */
	function dsp_enbl()
	{
		return self::$enbls[$this->enbl];
	}
	
	/**
	 * Assigns app to element
	 * 
	 * @param string $app
	 */
	function set_app($app)
	{
		$this->app = $app;
	}
	
	/**
	 * Returns application assigned to element
	 * 
	 * @return string
	 */
	function get_app()
	{
		return $this->app;
	}
	
	/**
	 * Sets element name.  truncates to 40 characters
	 * 
	 * @param string $elem_nm
	 */
	function set_elem_nm($elem_nm)
	{
		$this->elem_nm = substr($elem_nm, 0, 80);
	}
	
	/**
	 * Returns element name
	 * 
	 * @param string
	 */
	function get_elem_nm()
	{
		return $this->elem_nm;
	}
	
	/**
	 * Returns element name escaped for db2 query
	 * 
	 * @return string
	 */
	function esc_elem_nm()
	{
		return str_replace("'","''",$this->elem_nm);
	}
	
	/**
	 * Sets element description (twitter style)
	 * 
	 * @param string $elem_desc
	 */
	function set_elem_desc($elem_desc)
	{
		$this->elem_desc = substr($elem_desc,0,140);
	}
	
	/**
	 * Returns element description
	 * 
	 * @return string
	 */
	function get_elem_desc()
	{
		return $this->elem_desc;
	}
	
	/**
	 * Returns element description escaped for db2 query
	 * 
	 * @return string
	 */
	function esc_elem_desc()
	{
		return str_replace("'","''",$this->elem_desc);
	}
	
	/**
	 * Sets element accrual type
	 * 
	 * @param string $elem_tp
	 */
	function set_elem_tp($elem_tp)
	{
		$this->elem_tp = $elem_tp;
	}
	
	/**
	 * Returns element accrual type
	 * 
	 * @return string
	 */
	function get_elem_tp()
	{
		return $this->elem_tp;
	}
	
	/**
	 * Returns display value for element type
	 * 
	 * @return string
	 */
	function dsp_elem_tp()
	{
		return self::$elem_tps[$this->elem_tp];
	}
	
	/**
	 * Sets element rate.  Removes dollar signs and commas
	 * 
	 * @param decimal $elem_rt
	 */
	function set_elem_rt($elem_rt = 0)
	{
		$bad_char = "/,|\$/";
		$this->elem_rt = preg_replace($bad_char, "", $elem_rt);
	}
	
	/**
	 * Returns element rate
	 * 
	 * @return decimal
	 */
	function get_elem_rt()
	{
		return $this->elem_rt;
	}
	
	/**
	 * Returns element rate stripped of 0s after the decimal.  Displays
	 * at least 2 decimals
	 * 
	 * @return string
	 */
	function dsp_elem_rt()
	{
		$dec_len = strlen(rtrim(strrchr($this->elem_rt, '.'), '0')) -1;
		
		if ($dec_len < 2) $dec_len = 2;
		
		return number_format($this->elem_rt, $dec_len);
	}
	
	public static function format_elem_rt($elem_rt)
	{
		$dec_len = strlen(rtrim(strrchr($elem_rt, '.'), '0')) -1;
		
		if ($dec_len < 2) $dec_len = 2;
		
		return number_format($elem_rt, $dec_len);
	}
	
	/**
	 * Sets element accrual target 
	 * 
	 * @param string $elem_trgt
	 */
	function set_elem_trgt($elem_trgt)
	{
		$this->elem_trgt = $elem_trgt;
	}
	
	/**
	 * Returns element accrual target
	 * 
	 * @return string
	 */
	function get_elem_trgt()
	{
		return $this->elem_trgt;
	}
	
	/**
	 * Returns display value for element accrual target
	 * 
	 * @return string
	 */
	function dsp_elem_trgt()
	{
		return self::$elem_trgts[$this->elem_trgt];
	}
	
	/**
	 * Sets element accrual trigger
	 * 
	 * @param string $elem_trigr
	 */
	function set_elem_trigr($elem_trigr)
	{
		$this->elem_trigr = $elem_trigr;
	}
	
	/**
	 * Returns element accrual trigger
	 * 
	 * @return string
	 */
	function get_elem_trigr()
	{
		return $this->elem_trigr;
	}
	
	/**
	 * Returns display value for element accrual trigger
	 * 
	 * @return string
	 */
	function dsp_elem_trigr()
	{
		return self::$elem_trigrs[$this->elem_trigr];
	}
	
	/**
	 * Sets accrual percentage
	 * 
	 * @param decimal $pct
	 */
	function set_pct($pct)
	{
		$this->pct = $pct;
	}
	
	/**
	 * Returns accrual percentage
	 * 
	 * @return decimal
	 */
	function get_pct()
	{
		return $this->pct;
	}
	
	/**
	 * Sets payment limit
	 * 
	 * @param decimal $pymt_lmt
	 */
	function set_pymt_lmt($pymt_lmt)
	{
		$this->pymt_lmt = $pymt_lmt;
	}
	
	/**
	 * Returns payment limit
	 * 
	 * @return decimal
	 */
	function get_pymt_lmt()
	{
		return $this->pymt_lmt;
	}
	
	/**
	 * Sets element share
	 * 
	 * @param decimal $shr
	 */
	function set_shr($shr)
	{
		$this->shr = $shr;
	}
	
	/**
	 * Returns share
	 * 
	 * @return decimal
	 */
	function get_shr()
	{
		return $this->shr;
	}

	/**
	 * Sets element on invoice flag
	 * 
	 * @param string $on_inv_flg
	 */
	function set_on_inv_flg($on_inv_flg)
	{
		$this->on_inv_flg = $on_inv_flg;
	}
	
	/**
	 * Returns element on invoice flag
	 * 
	 * @return string
	 */
	function get_on_inv_flg()
	{
		return $this->on_inv_flg;
	}
	
	/**
	 * Returns display value for element on invoice flag
	 * 
	 * @return string
	 */
	function dsp_on_inv_flg()
	{
		return self::$on_inv_flgs[$this->on_inv_flg];
	}
	
	/**
	 * Alternative display function for on/post invoice
	 * designation
	 * 
	 * @return string
	 */
	function dsp_on_inv_tp()
	{
		if ($this->on_inv_flg == 'Y')
		{
			return 'On Invoice';
		}
		elseif ($this->on_inv_flg == 'N')
		{
			return 'Post Invoice';
		}
	}
	
	/**
	 * Sets unit divider 
	 * 
	 * @param integer $unt_div
	 */
	function set_unt_div($unt_div)
	{
		$this->unt_div = $unt_div;
	}
	
	/**
	 * Returns Unit divider
	 * 
	 * @return integer
	 */
	function get_unt_div()
	{
		return $this->unt_div;
	}
	
	/**
	 * Sets case threshold
	 * @param unknown_type $cse_thld
	 */
	function set_cse_thld($cse_thld)
	{
		$this->cse_thld = $cse_thld;
	}
	
	/**
	 * Returns case threshold
	 * @return number
	 */
	function get_cse_thld()
	{
		return $this->cse_thld;
	}
	
	/**
	 * Assigns sales criteria to element
	 * 
	 * @param array $sls_crits 
	 */
	function set_sls_crits($sls_crits)
	{
		$this->sls_crits = $sls_crits;
	}
	
	/**
	 * Returns sales criteria assigned to element
	 * 
	 * @return array
	 */
	function get_sls_crits()
	{
		return $this->sls_crits;
	}
	
	function get_crit_cds()
	{
		return $this->crit_cds;
	}
	
	function set_crit_cds($crit_cds)
	{
		$this->crit_cds = $crit_cds;
	}
	
	function get_crit_flds()
	{
		return $this->get_crit_flds();
	}
	
	function set_crit_flds($crit_flds)
	{
		$this->crit_flds = $crit_flds;
	}
	
	function set_crit_accr_flgs($crit_accr_flgs)
	{
		$this->crit_accr_flgs = $crit_accr_flgs;
	}
	
	function get_crit_accr_flgs()
	{
		return $this->crit_accr_flgs;
	}
	
	/**
	 * Sets parent cntrct_id for element
	 * @param integer $cntrct_id
	 */
	function set_cntrct_id($cntrct_id)
	{
		if (intval($cntrct_id) != 0)
			$this->cntrct_id = intval($cntrct_id);	
	}
	
	/**
	 * Returns parent cntrct_id for element
	 * @return integer
	 */
	function get_cntrct_id()
	{
		return $this->cntrct_id;
	}
	
	/**
	 * Returns list of contracts associated with Element
	 * @return array:
	 */
	function get_cntrcts()
	{
		if ($this->elem_id != NULL)
		{
			$select = "SELECT C.CNTRCT_ID, C.CNTRCT_NM, C.STRT_DT, C.END_DT
				FROM ".APP_SCHEMA.".CNTRCT_ELEM CE
					JOIN ".APP_SCHEMA.".CNTRCT C ON C.CNTRCT_ID = CE.CNTRCT_ID
				WHERE CE.ELEM_ID = ".$this->elem_id."
				ORDER BY CNTRCT_ID";
			
			return $this->db2->simple_query($select)->fetch_object();
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Sets start dates assigned to element by contract
	 * 
	 * @param array $strt_dts
	 * @param string $format
	 */
	function set_strt_dts($strt_dts, $format = 'Y-m-d')
	{
		foreach ($strt_dts as $index => $strt_dt)
		{
			$dt = conv_date($strt_dt, $format, 'Y-m-d');
			
			list ($yr,$mth,$day) = explode('-',$dt);
			
			if (checkdate($mth, $day, $yr))
			{
				$this->strt_dts[$index] = $dt;
			}
		}
	}
	
	/**
	 * Returns array of start dates
	 * @return array
	 */
	function get_strt_dts()
	{
		return $this->strt_dts;
	}
	
	/**
	 * Returns array of start dates formatted according to $format
	 * @param string $format
	 * @return array 
	 */
	function dsp_strt_dts($format = 'm/d/Y')
	{
		$dsp_dts = array();
		foreach ($this->strt_dts as $index => $strt_dt)
		{
			$dsp_dts[$index] = conv_date($strt_dt, 'Y-m-d', $format);
		}
		return $dsp_dts;
	}
	
	/**
	 * Sets end dates assigned to element by contract
	 * 
	 * @param array $strt_dts
	 * @param string $format
	 */
	function set_end_dts($end_dts, $format = 'Y-m-d')
	{
		foreach ($end_dts as $index => $end_dt)
		{
			$dt = conv_date($end_dt, $format, 'Y-m-d');
			
			list ($yr,$mth,$day) = explode('-',$dt);
			
			if (checkdate($mth, $day, $yr))
			{
				$this->end_dts[$index] = $dt;
			}
		}
	}
	
	/**
	 * Returns array of end dates
	 * @return array
	 */
	function get_end_dts()
	{
		return $this->end_dts;
	}
	
	/**
	 * Returns array of end dates formatted according to $format
	 * @param string $format
	 * @return array 
	 */
	function dsp_end_dts($format = 'm/d/Y')
	{
		$dsp_dts = array();
		foreach ($this->strt_dts as $index => $end_dt)
		{
			$dsp_dts[$index] = conv_date($end_dt, 'Y-m-d', $format);
		}
		return $dsp_dts;
	}
	
	/**
	 * Returns start and end dates as array:
	 * array(
	 * 		0 => array(
	 * 			'strt_dt' => $strt_dt[0],
	 * 			'end_dt' => $end_dt[0]
	 * 		),
	 * 		1 => array(
	 * 			'strt_dt' => $strt_dt[1],
	 * 			'end_dt' => $end_dt[1]
	 * 		),
	 * 		n => array(
	 * 			'strt_dt' => $strt_dt[n],
	 * 			'end_dt' => $end_dt[n]
	 * 		)
	 * )
	 * 
	 * @param string $format
	 * @return array
	 */
	function get_dt_range($format = 'm/d/Y')
	{
		$dt_range = array();
		
		foreach ($this->strt_dts as $index => $strt_dt)
		{
			$dt_range[] = array(
				'strt_dt' => conv_date($strt_dt, 'Y-m-d', $format),
				'end_dt' => conv_date($this->end_dts[$index], 'Y-m-d', $format)
			);
		}
		
		return $dt_range;
	}
	
	/**
	 * Returns list of distinct years the element covers within the contract
	 * 
	 * @return array
	 */
	function get_distinct_yrs()
	{
		$yrs = array();
		
		if ($this->cntrct_id !== NULL && $this->elem_id !== NULL)
		{				
			$select = "SELECT DISTINCT YR
				FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT CED
					JOIN DW.DT DT ON DT.DT BETWEEN CED.STRT_DT AND CED.END_DT
				WHERE CNTRCT_ID = ?
					AND ELEM_ID = ?";
			
			
			foreach ($this->db2->query($select, array($this->cntrct_id, $this->elem_id))->fetch_object() as $row)
			{
				$yrs[] = $row->YR;
			}
		}
		
		return $yrs;
	}
	
	/**
	 * Sets payment frequency assigned by contract
	 * 
	 * @param string $pymt_freq
	 */
	function set_pymt_freq($pymt_freq)
	{
		$this->pymt_freq = $pymt_freq;
	}
	
	/**
	 * Returns payment frequency assigned by contract
	 * @return string
	 */
	function get_pymt_freq()
	{
		return $this->pymt_freq;
	}
	
	/**
	 * Returns description of payment frequency assigned by contract
	 * @return string 
	 */
	function dsp_pymt_freq()
	{
		return self::$pymt_freqs[$this->pymt_freq];
	}
	
	function set_cntrct_pgm_id($cntrct_pgm_id)
	{
		if (intval($cntrct_pgm_id) != 0)
			$this->cntrct_pgm_id = intval($cntrct_pgm_id);
	}
	
	function get_cntrct_pgm_id()
	{
		return $this->cntrct_pgm_id;
	}
	
	/**
	 * Returns an element model object.  If elem_id
	 * provide, loads object with db values
	 * 
	 * @param integer $elem_id
	 * @return object
	 */
	function &get($elem_id = NULL)
	{
		// create new object
		$element = new Element_model();

		// if elem_id provided, load values from db
		if ($elem_id !== NULL && $elem_id !== FALSE)
		{
			$element->set_elem_id($elem_id);
			$element->_load();
		}
		
		// return element_model object
		return $element;
	}
	
	/**
	 * Indicates whether the object has been successfully 
	 * loaded with properties from db
	 * 
	 * @return boolean
	 */
	function is_loaded()
	{
		return $this->loaded;
	}
	
	/**
	 * Returns array of enabled element objects filtered by locations and current app
	 * 
	 * @param string $app	- Application
	 * @param bool $no_obj  - TRUE returns raw data instead of objects
	 * @param string $enbl 	- Only return elements of specific status
	 * @param string $search - Search element names for term
	 * 
	 * @return array
	 */
	function get_elements($app = '', $no_obj = FALSE, $enbl = 'Y', $search = NULL)
	{
		$sql = new Query;
		
		$sql->selects = array('DISTINCT E.*');
		
		$sql->from = APP_SCHEMA.".ELEM AS E";
		
		if ($search !== NULL && $search != '')
		{
			$sql->wheres[] = "LOWER(E.ELEM_NM) LIKE '%".strtolower($search)."%'";
		}
		
		// filter on app if one was specified
		if ($app != '')
		{
			$sql->wheres[] = "E.APP = '".$app."'";
		}
		
		if ($enbl != '')
		{
			// only pull 
			$sql->wheres[] = "E.ENBL = '".$enbl."'";
		}
		
		$sql->extra = " ORDER BY E.ELEM_NM ASC";
		
		return $this->db2->simple_query($sql->build())->fetch_assoc();
	}
	
	/**
	 * Pulls data specifically for datatable
	 * @param string $search
	 * @param integer $usr_id
	 * @param string $app
	 * @param string $sort
	 * @param string $sort_dir
	 * 
	 * return array|object
	 */
	public function get_datatable($search = '', $usr_id = NULL, $app = NULL, $sort = NULL, $sort_dir = NULL)
	{
		$elem_tp_whens = array();
		foreach (Element_model::$elem_tps as $elem_tp => $elem_tp_desc)
		{
			$elem_tp_whens[] = " WHEN '".$elem_tp."' THEN '".$elem_tp_desc."'";
		}
		
		$on_inv_flg_whens = array();
		foreach (Element_model::$on_inv_flgs as $on_inv_flg => $on_inv_flg_desc)
		{
			$on_inv_flg_whens[] = " WHEN '".$on_inv_flg."' THEN '".$on_inv_flg_desc."'";
		}
		
		$params = array($usr_id, $app);
		
		$search_where = '';
		
		if ($search != '')
		{
			$search_where = " WHERE ELEM_ID LIKE ? OR LOWER(ELEM_NM) LIKE ? OR LOWER(ELEM_DESC) LIKE ? OR LOWER(ELEM_TP_DESC) LIKE ? OR LOWER(ON_INV_FLG_DESC) LIKE ? OR ELEM_RT LIKE ?";
			
			$params[] = strtolower('%'.$search.'%');
			$params[] = strtolower('%'.$search.'%');
			$params[] = strtolower('%'.$search.'%');
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
		
		$select = "SELECT * 
			FROM (
				SELECT E.ELEM_ID, ELEM_NM, ELEM_DESC,
					CASE ELEM_TP ".implode(" ", $elem_tp_whens)." ELSE '' END AS ELEM_TP_DESC,
					CASE ON_INV_FLG ".implode(" ", $on_inv_flg_whens)." ELSE '' END AS ON_INV_FLG_DESC,
					ELEM_RT AS ELEM_RT,
					COALESCE(ADT.ACTN_TM, TIMESTAMP('2000001', '00.00.00')) AS TIME_ORDER
				FROM ".APP_SCHEMA.".ELEM E
					LEFT JOIN (
						SELECT ENTY_ID AS ELEM_ID, MAX(ACTN_TM) AS ACTN_TM
						FROM ".APP_SCHEMA.".ADT A
						WHERE ENTY_TP = 'EL'
							AND USR_ID = ?
						GROUP BY ENTY_ID
					) AS ADT ON ADT.ELEM_ID = E.ELEM_ID
				WHERE APP = ? 
			) AS E".$search_where." ".$order_by.", ELEM_NM ASC";
		
		return $this->db2->query($select, $params)->fetch_object();
	}
	
	/**
	 * Gets unfiltered count of elements for use with datatables
	 * @param string $app
	 * @return number
	 */
	public function get_elem_cnt($app = NULL)
	{
		if ($app !== NULL)
		{
			$select = "SELECT COUNT(*) AS ELEM_CNT
				FROM ".APP_SCHEMA.".ELEM
				WHERE APP = ?";
			
			$result = $this->db2->query($select, array($app))->fetch_object();
		
			return $result[0]->ELEM_CNT;
		}
		else
		{
			return 0;
		}
	}
	/**
	 * Extracts values from element table and 
	 * related tables 
	 * 
	 */
	function _load()
	{
		if ($this->elem_id !== NULL)
		{
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".ELEM
				WHERE ELEM_ID = ".$this->elem_id;
				
			$results = $this->db2->simple_query($sql)->fetch_assoc();
				
			if (count($results) > 0)
			{
				// assign values from element table
				$this->elem_id = $results[0]['ELEM_ID'];
				$this->app = $results[0]['APP'];
				$this->elem_nm = $results[0]['ELEM_NM'];
				$this->elem_desc = $results[0]['ELEM_DESC'];
				$this->elem_tp = $results[0]['ELEM_TP'];
				$this->elem_rt = $results[0]['ELEM_RT'];
				$this->elem_trgt = $results[0]['ELEM_TRGT'];
				$this->elem_trigr = $results[0]['ELEM_TRIGR'];
				$this->pct = $results[0]['PCT'];
				$this->pymt_lmt = $results[0]['PYMT_LMT'];
				$this->shr = $results[0]['SHR'];
				$this->on_inv_flg = $results[0]['ON_INV_FLG'];
				$this->unt_div = $results[0]['UNT_DIV'];
				$this->cse_thld = $results[0]['CSE_THLD'];

				// get related tables info
				$this->load_sls_crits();
				$this->load_programs();
				
				$this->loaded = TRUE;
			}
		}
	}
	
	function load_sls_crits()
	{
		$CI =& get_instance();
		
		$CI->load->model('sls_crit_model');
		
		if ($this->elem_id !== NULL && $this->elem_id != '')
		{
			$this->set_sls_crits(Sls_crit_model::get_sls_crit_by_elem_id($this->elem_id));
		}
	}
	
	/**
	 * Gets ids of programs this element is assigned to
	 */
	function load_programs()
	{
		$sql = "SELECT PE.PGM_ID
			FROM ".APP_SCHEMA.".PGM_ELEM AS PE
			WHERE PE.ELEM_ID = ".$this->elem_id;
		
		$results = $this->db2->simple_query($sql)->fetch_assoc();
		
		foreach ($results as $row)
		{
			$this->programs[] = $row['PGM_ID'];
		}
	}
	
	/**
	 * Saves element info to element table 
	 */
	function save()
	{
		$this->db2->begin_transaction();
		
		try {
			if ($this->exists())
			{
				// update element
				$update = "UPDATE ".APP_SCHEMA.".ELEM
					SET APP = ?,
						ENBL = ?,
						ELEM_NM = ?,
						ELEM_DESC = ?,
						ELEM_TP = ?,
						ELEM_RT = ?,
						ELEM_TRGT = ?,
						ELEM_TRIGR = ?,
						PCT = ?,
						PYMT_LMT = ?,
						SHR = ?,
						ON_INV_FLG = ?,
						UNT_DIV = ?,
						CSE_THLD = ?
					WHERE ELEM_ID = ?";
				
				$update_parms = array(
					$this->app,
					$this->enbl,
					$this->get_elem_nm(),
					$this->get_elem_desc(),
					$this->elem_tp,
					$this->elem_rt,
					$this->elem_trgt,
					$this->elem_trigr,
					$this->pct,
					$this->pymt_lmt,
					$this->shr,
					$this->on_inv_flg,
					$this->unt_div,
					$this->cse_thld,
					$this->elem_id
				);
				
				$this->db2->query($update, $update_parms);
				
				$this->remove_sls_crit();
			}
			else
			{
				// insert new element
				$insert = "SELECT * FROM FINAL TABLE (INSERT INTO ".APP_SCHEMA.".ELEM
					(
						ELEM_ID,APP,ENBL,ELEM_NM,ELEM_DESC,ELEM_TP,
						ELEM_RT,ELEM_TRGT,ELEM_TRIGR,PCT,PYMT_LMT,
						SHR,ON_INV_FLG,UNT_DIV,CSE_THLD
					)
					VALUES (NEXT VALUE FOR ".APP_SCHEMA.".SQ_ELEM_PK, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				)";
				
				$insert_parms = array(
					$this->app,
					$this->enbl,
					$this->get_elem_nm(),
					$this->get_elem_desc(),
					$this->elem_tp,
					$this->elem_rt,
					$this->elem_trgt,
					$this->elem_trigr,
					$this->pct,
					$this->pymt_lmt,
					$this->shr,
					$this->on_inv_flg,
					$this->unt_div,
					$this->cse_thld
				);
				
				$result = $this->db2->query($insert, $insert_parms)->fetch_object();
				
				$this->elem_id = $result[0]->ELEM_ID;
			}
	
			// add relationships back
			$this->add_sls_crit();
		} catch (Exception $e) {
			$this->db2->rollback();
			
			$error =& load_class('Exceptions', 'core');
			echo $error->show_error("Error saving Element", $e->getMessage(), 'error_general');
			exit;
		}		
		
		$this->db2->commit();
	}
	
	/**
	 * Checks db table for record with element id
	 * 
	 * @return bool
	 */
	function exists()
	{
		if ($this->elem_id !== NULL)
		{
			$sql = "SELECT E.* 
				FROM ".APP_SCHEMA.".ELEM AS E
				WHERE E.ELEM_ID = ".$this->elem_id;
				
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
	 * Removes any relationships to contracts
	 */
	function remove_cntrct_data()
	{
		if ($this->elem_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".CNTRCT_ELEM
				WHERE ELEM_ID = ".$this->elem_id;
			
			$this->db2->simple_query($sql);
			
			$sql = "DELETE FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT
				WHERE ELEM_ID = ".$this->elem_id;
			
			$this->db2->simple_query($sql);
		} 
	}
	
	/**
	 * Removes element's sls_crits from criteria table
	 */
	function remove_sls_crit()
	{
		if ($this->elem_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".SLS_CRIT
				WHERE ELEM_ID = ".$this->elem_id;
			
			$this->db2->simple_query($sql);
		}
	}
	
	/**
	 * Adds element's sls_crits to the criteria table
	 */
	function add_sls_crit()
	{
		if ($this->elem_id !== NULL && count($this->sls_crits) > 0)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".SLS_CRIT (ELEM_ID,CRIT_FLD,CRIT_CD,ACCR_FLG) VALUES (?, ?, ?, ?)";
				
			$this->db2->set_sql($insert)->prepare();
			
			foreach ($this->sls_crits as $sls_crit)
			{
				$this->db2->set_params(
						array(
							$this->elem_id,
							$sls_crit->get_crit_fld(),
							$sls_crit->get_crit_cd(),
							$sls_crit->get_accr_flg()
						)
					)
					->execute();
			}
		}
	}
	
	/**
	 * Removes element from database and related info
	 */
	function remove()
	{
		if ($this->elem_id !== NULL)
		{
			$this->db2->begin_transaction();
			
			try {
				$this->remove_sls_crit();
				$this->remove_cntrct_data();
				
				$sql = "DELETE FROM ".APP_SCHEMA.".ELEM
					WHERE ELEM_ID = ".$this->elem_id;
				
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
	 * DEPRECATED
	 * Takes submitted object, checks for $elements and $elem_ids property
	 * and assigns element objects to the object
	 * 
	 * @param object $assignee
	 * 
	 * @return nothing
	 *
	function assign(&$assignee)
	{
		$elements = array();
		
		foreach ($assignee->get_elem_ids() as $instnc_cd => $elem_id)
		{
			$elements[$instnc_cd] = $this->get($elem_id);
		}
		
		$assignee->set_elements($elements);
	}
	*/
	
	public static function get_elements_by_cntrct_id($cntrct_id, $accrual_yr = NULL)
	{
		$CI =& get_instance();
		$accrual_yr_clause = $accrual_yr." BETWEEN YEAR(STRT_DT) AND YEAR(END_DT)";
		
		$accrual_yr_clause1 = $accrual_yr_clause2 = '';
		
		if ($accrual_yr !== NULL)
			$accrual_yr_clause1 = " WHERE ".$accrual_yr_clause;
			
		// get properties related to contract
		$select1 = "SELECT CE.*, CP.PGM_ID
			FROM ".APP_SCHEMA.".CNTRCT_PGM CP
				JOIN ".APP_SCHEMA.".PGM_ELEM PE ON PE.PGM_ID = CP.PGM_ID
				RIGHT JOIN ".APP_SCHEMA.".CNTRCT_ELEM CE ON CE.CNTRCT_ID = CP.CNTRCT_ID
					AND CE.ELEM_ID = PE.ELEM_ID
				JOIN ".APP_SCHEMA.".ELEM E ON E.ELEM_ID = CE.ELEM_ID
				JOIN (SELECT DISTINCT CNTRCT_ID, ELEM_ID
					FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT".$accrual_yr_clause1."
				) AS CED ON CED.CNTRCT_ID = CE.CNTRCT_ID
					AND CED.ELEM_ID = CE.ELEM_ID
			WHERE CE.CNTRCT_ID = ".$cntrct_id."
			ORDER BY CP.PGM_ID, E.ELEM_NM";
		
		if ($accrual_yr !== NULL)
			$accrual_yr_clause2 = " AND ".$accrual_yr_clause;
			
		// get date ranges related to contract
		$select2 = "SELECT *
			FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT
			WHERE CNTRCT_ID = ".$cntrct_id.$accrual_yr_clause2."
			ORDER BY ELEM_ID, STRT_DT";
		
		// process date ranges into elem_id keyed array
		$dts = array();

		foreach ($CI->db2->simple_query($select2)->fetch_object() as $row2)
		{
			$dts[$row2->ELEM_ID]['strt_dts'][] = $row2->STRT_DT;
			$dts[$row2->ELEM_ID]['end_dts'][] = $row2->END_DT;
		}
		
		// build element objects to return
		$elements = array();
		
		foreach ($CI->db2->simple_query($select1)->fetch_object() as $row)
		{
			$element = new Element_model();
			
			$element->set_elem_id($row->ELEM_ID);
			
			// build object
			$element->_load();
			
			// load related contract info
			$element->set_strt_dts($dts[$row->ELEM_ID]['strt_dts']);
			$element->set_end_dts($dts[$row->ELEM_ID]['end_dts']);
			$element->set_pymt_freq($row->PYMT_FREQ);		
			$element->set_cntrct_id($row->CNTRCT_ID);
			$element->set_cntrct_pgm_id($row->PGM_ID);
			
			$elements[] = $element;
		}
		
		return $elements;
	}
	
	/**
	 * Returns element objects assigned to pgm_id
	 * 
	 * @param integer $pgm_id
	 * @return Ambigous <multitype:, Element_model>
	 */
	public static function get_elements_by_pgm_id($pgm_id)
	{
		$elements = array();
		
		if ($pgm_id !== NULL)
		{
			$CI =& get_instance();
			
			// get properties related to program
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".PGM_ELEM
				WHERE PGM_ID = ".$pgm_id."
				ORDER BY ELEM_ID";
			
			// build element objects to return		
			foreach ($CI->db2->simple_query($sql)->fetch_object() as $row)
			{
				$element = new Element_model();
				
				$element->set_elem_id($row->ELEM_ID);
				$element->set_cntrct_pgm_id($pgm_id);
				
				// build object
				$element->_load();
				
				$elements[] = $element;
			}
		}
		
		return $elements;
	}
	
	/**
	 * Returns array of elements to be used in dropdown box form control
	 * 
	 * @param integer $cntrct_id
	 * @return multitype:NULL 
	 */
	public static function get_elem_drpdwn_by_cntrct($cntrct_id = NULL)
	{
		$CI = &get_instance();
		
		$elements = array();
		
		$select = "SELECT E.ELEM_ID, E.ELEM_NM
			FROM ".APP_SCHEMA.".CNTRCT_ELEM CE 
				JOIN ".APP_SCHEMA.".ELEM E ON E.ELEM_ID = CE.ELEM_ID
			WHERE CNTRCT_ID = ".$cntrct_id."
			ORDER BY ELEM_NM";
		
		foreach ($CI->db2->simple_query($select)->fetch_object() as $row)
		{
			$elements[$row->ELEM_ID] = $row->ELEM_NM;
		}
		
		return $elements;
	}
	
	/**
	 * Assigns contract related properties to element object
	 * when element is invoked outside of contract context
	 * 
	 * @param integer $cntrct_id
	 * @param integer $instnc_id
	 */
	public function load_cntrct_properties($cntrct_id)
	{
		$sql = "SELECT *
			FROM ".APP_SCHEMA.".CNTRCT_ELEM
			WHERE CNTRCT_ID = ".$cntrct_id."
				AND ELEM_ID = ".$this->elem_id;
		
		$result = $this->db2->simple_query($sql)->fetch_object();
		
		if (count($result) > 0)
		{
			$row = $result[0];
			
			// load object
			$this->_load();
			
			// get date ranges related to contract
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT
				WHERE CNTRCT_ID = ".$cntrct_id."
					AND ELEM_ID = ".$this->elem_id."
				ORDER BY ELEM_ID, STRT_DT";
			
			// process date ranges
			$strt_dts = array();
			$end_dts = array();
			
			foreach ($this->db2->simple_query($sql)->fetch_object() as $row2)
			{
				$strt_dts[] = $row2->STRT_DT;
				$end_dts[] = $row2->END_DT;
			}
			
			// assign contract related info
			$this->set_strt_dts($strt_dts);
			$this->set_end_dts($end_dts);
			$this->set_pymt_freq($row->PYMT_FREQ);			
			$this->set_cntrct_id($row->CNTRCT_ID);
		}
	}
	
	/**
	 * Returns array of months spanned by element
	 * 
	 * @param integer $elem_id
	 * @param integer $year
	 * @return array of unix dates
	 */
	function get_mths()
	{
		$mths = array();
		
		foreach ($this->strt_dts as $index => $strt_dt)
		{
			$strt_prt = explode('-',$strt_dt);
			$end_prt = explode('-', $this->end_dts[$index]);
	
			// calculate number of months spanned by element
			$mth_span = $end_prt[1] - $strt_prt[1] + 1 + ($end_prt[0] - $strt_prt[0]) * 12;
			
			// load up mths array with mths covered by element
			for ($i=0;$i<$mth_span;$i++)
			{
				$yr = date('Y',mktime(0,0,0,$strt_prt[1]+$i,1,$strt_prt[0]));
				$mth = date('m',mktime(0,0,0,$strt_prt[1]+$i,1,$strt_prt[0]));
				$mths[$yr][] = $mth;
			}
		} 
			
		return $mths;
	}
	
	public function process_post()
	{
		$CI =& get_instance();
		
		if ($CI->input->post('elem_nm') !== FALSE)
			$this->set_elem_nm($CI->input->post('elem_nm'));
		
		if ($CI->input->post('elem_desc') !== FALSE)
			$this->set_elem_desc($CI->input->post('elem_desc'));
		
		if ($CI->input->post('elem_rt') !== FALSE)
			$this->set_elem_rt($CI->input->post('elem_rt'));
		
		if ($CI->input->post('app') !== FALSE)
			$this->set_app($CI->input->post('app'));
		
		if ($CI->input->post('enbl') !== FALSE)
			$this->set_enbl($CI->input->post('enbl'));
		
		if ($CI->input->post('on_inv_flg') !== FALSE)
			$this->set_on_inv_flg($CI->input->post('on_inv_flg'));
			
		if ($CI->input->post('location') !== FALSE)
			$this->set_sls_ctrs($CI->input->post('location'));
			
		if ($CI->input->post('elem_tp') !== FALSE)
			$this->set_elem_tp($CI->input->post('elem_tp'));
		
		if ($CI->input->post('unt_div') !== FALSE)
			$this->set_unt_div($CI->input->post('unt_div'));
		
		if ($CI->input->post('elem_trgt') !== FALSE)
			$this->set_elem_trgt($CI->input->post('elem_trgt'));
		
		if ($CI->input->post('elem_trigr') !== FALSE)
			$this->set_elem_trigr($CI->input->post('elem_trigr'));
			
		if ($CI->input->post('pct') !== FALSE)
			$this->set_pct($CI->input->post('pct'));
			
		if ($CI->input->post('pymt_lmt') !== FALSE)
			$this->set_pymt_lmt($CI->input->post('pymt_lmt'));
			
		if ($CI->input->post('shr') !== FALSE)
			$this->set_shr($CI->input->post('shr'));
			
		if ($CI->input->post('cse_thld') !== FALSE)
			$this->set_cse_thld($CI->input->post('cse_thld'));
			
		$sls_crits = array();
		$crit_flds = array();
		
		if ($CI->input->post('crit_fld') !== FALSE)
			$crit_flds = $CI->input->post('crit_fld');
			
		if ($CI->input->post('crit_cd') !== FALSE)
			$crit_cds = $CI->input->post('crit_cd');
		
		if ($CI->input->post('crit_accr_flg') !== FALSE)
			$crit_accr_flgs = $CI->input->post('crit_accr_flg');
			
		$CI->load->model('sls_crit_model');
		
		foreach ($crit_flds as $index => $crit_fld)
		{
			$sls_crit = new Sls_crit_model();
			
			if ($this->elem_id !== NULL)
				$sls_crit->set_elem_id($this->elem_id);
				
			$sls_crit->set_crit_fld($crit_fld);
			$sls_crit->set_crit_cd($crit_cds[$index]);
			$sls_crit->set_accr_flg($crit_accr_flgs[$index]);
			
			$sls_crits[] = $sls_crit;
		}
		
		$this->set_sls_crits($sls_crits);
	}
}