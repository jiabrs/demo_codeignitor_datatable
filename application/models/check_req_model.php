<?php
class Check_req_model extends CI_Model {
	
	private $check_req_id = NULL;
	private $check_req_dt = NULL;
	private $check_req_sts = '0';
	private $check_req_amt = 0;
	private $check_ref = '';
	private $check_text = '';
	private $pymt_freq = '';
	private $cntrct_nm = '';
	private $term = '';
	private $vend_no = '';
	
	private $customers = array();
	private $elements = array();
	private $profit_ctrs = array();
	private $approvers = array();
	private $return_to = '';
	
	/**
	 * @return the $vend_no
	 */
	public function get_vend_no() {
		return $this->vend_no;
	}

	/**
	 * @param field_type $vend_no
	 */
	public function set_vend_no($vend_no) {
		$this->vend_no = $vend_no;
	}

	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * @return the $check_req_id
	 */
	public function get_check_req_id() {
		return $this->check_req_id;
	}

	/**
	 * @return the $check_req_dt
	 */
	public function get_check_req_dt() {
		return $this->check_req_dt;
	}

	/**
	 * @return the $check_req_sts
	 */
	public function get_check_req_sts() {
		return $this->check_req_sts;
	}

	/**
	 * @return the $check_req_amt
	 */
	public function get_check_req_amt() {
		return $this->check_req_amt;
	}

	/**
	 * @return the $check_ref
	 */
	public function get_check_ref() {
		return $this->check_ref;
	}

	/**
	 * @return the $check_text
	 */
	public function get_check_text() {
		return $this->check_text;
	}

	/**
	 * @return the $customers
	 */
	public function get_customers() {
		return $this->customers;
	}

	/**
	 * @return the $elements
	 */
	public function get_elements() {
		return $this->elements;
	}

	/**
	 * @return the $approvers
	 */
	public function get_approvers() {
		return $this->approvers;
	}

	/**
	 * @return the $return_to
	 */
	public function get_return_to() {
		return $this->return_to;
	}

	public function get_profit_ctrs() {
		return $this->profit_ctrs;
	}
	
	/**
	 * @param field_type $check_req_id
	 */
	public function set_check_req_id($check_req_id) {
		$this->check_req_id = $check_req_id;
	}

	/**
	 * @param field_type $check_req_dt
	 */
	public function set_check_req_dt($check_req_dt) {
		$this->check_req_dt = $check_req_dt;
	}

	/**
	 * @param field_type $check_req_sts
	 */
	public function set_check_req_sts($check_req_sts) {
		$this->check_req_sts = $check_req_sts;
	}

	/**
	 * @param field_type $check_req_amt
	 */
	public function set_check_req_amt($check_req_amt) {
		$this->check_req_amt = $check_req_amt;
	}

	/**
	 * @param field_type $check_ref
	 */
	public function set_check_ref($check_ref) {
		$this->check_ref = $check_ref;
	}

	/**
	 * @param field_type $check_text
	 */
	public function set_check_text($check_text) {
		$this->check_text = $check_text;
	}

	/**
	 * @param field_type $customers
	 */
	public function set_customers($customers) {
		$this->customers = $customers;
	}

	/**
	 * @param field_type $elements
	 */
	public function set_elements($elements) {
		$this->elements = $elements;
	}

	/**
	 * @param field_type $approvers
	 */
	public function set_approvers($approvers) {
		$this->approvers = $approvers;
	}

	/**
	 * @param field_type $return_to
	 */
	public function set_return_to($return_to) {
		$this->return_to = $return_to;
	}
	
	public function set_profit_ctrs($profit_ctrs) {
		$this->profit_ctrs = $profit_ctrs;
	}
	
	/**
	 * @return the $cntrct_nm
	 */
	public function get_cntrct_nm() {
		return $this->cntrct_nm;
	}

	/**
	 * @return the $term
	 */
	public function get_term() {
		return $this->term;
	}

	/**
	 * @param field_type $cntrct_nm
	 */
	public function set_cntrct_nm($cntrct_nm) {
		$this->cntrct_nm = $cntrct_nm;
	}

	/**
	 * @param field_type $term
	 */
	public function set_term($term) {
		$this->term = $term;
	}

	/**
	 * Sets check text based on pymt_freq and check_text
	 * 
	 * @param unknown_type $pymt_freq
	 * @param unknown_type $check_txt
	 */
	public function generate_check_txt($pymt_freq, $check_txt)
	{
		$text = '';
		$n = new NumberFormatter("en-US", NumberFormatter::ORDINAL);
		
		switch ($pymt_freq)
		{
			case '03':
				$text = $n->format($check_txt).' Quarter Payment CMA';
				break;
			case '12':
				$text = $check_txt.' Payment CMA';
				break;
			case 'OD':
				$text = 'CMA Payment';
				break;
			default:
				$text = $check_txt.' Payment CMA';
				break;
		}
		$this->set_check_text($text);
	}
	
	/**
	 * Returns first approver in approvers list
	 * @return multitype:
	 */
	public function get_manager()
	{
		return $this->approvers[0];
	}
}