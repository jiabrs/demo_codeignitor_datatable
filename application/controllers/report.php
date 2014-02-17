<?php
/**
 * Report Class
 *
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage controllers
 *
 */
class Report extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Shows list of available reports
	 */
	function index()
	{
		redirect('report/detailed_accrual');
	}
	
	function detailed_accrual()
	{
		$accr_tps = array('ytd'=>'Year-to-Date', 'ttl'=>'Total');
		
		if ($this->input->post('run') === FALSE)
		{
			$this->load->library('location', $this->authr->get_locations());
			$detailed_accrual_view['accrual_years'] = array('2010'=>'2010','2011'=>'2011','2012'=>'2012');
			$detailed_accrual_view['accr_tps'] = $accr_tps;
			
			$this->tab_title .= " > Reports";
			
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('report/detailed_accrual_view', $detailed_accrual_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('report/contract_search.js');
			
			$this->_build();
		}
		else
		{
			$this->load->model('report_model');
			
			$sls_ctr_cds = $this->input->post('sls_ctr');
			$inv = $this->input->post('inv');
			$accr_yr = $this->input->post('accr_yr');
			$accr_tp = $this->input->post('accr_tp');
			$cntrct_ids = $this->input->post('cntrct_id');
			
			$this->load->library('location', $sls_ctr_cds);
			
			$detailed_accrual_report_view['inv'] = $inv;
			$detailed_accrual_report_view['accrual_yr'] = $accr_yr;
			$detailed_accrual_report_view['sls_ctrs'] = $sls_ctr_cds;
			$detailed_accrual_report_view['accr_tp'] = $accr_tp;
			$detailed_accrual_report_view['accr_tps'] = $accr_tps;
			$detailed_accrual_report_view['cntrct_ids'] = $cntrct_ids;
			$detailed_accrual_report_view['data'] = $this->report_model->run_detailed_accrual($sls_ctr_cds, $inv, $accr_yr, $accr_tp, $cntrct_ids, $this->session->userdata('role_id'));
			
			$this->tab_title .= " > Reports";
			
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('report/detailed_accrual_report_view', $detailed_accrual_report_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('jquery.cluetip.css');
			
			$this->_add_js('jquery.cluetip.min.js');
			$this->_add_js('report/detailed_accrual_report.js');
			
			$this->_build();
		}
	}
	
	function detailed_accrual_xls()
	{
		$this->load->model('report_model');
			
		$sls_ctr_cds = $this->input->post('sls_ctr');
		$inv = $this->input->post('inv');
		$accr_yr = $this->input->post('accr_yr');
		$accr_tp = $this->input->post('accr_tp');
		$cntrct_ids = $this->input->post('cntrct_id');
		
		$detailed_accrual_report_xls['data'] = $this->report_model->run_detailed_accrual($sls_ctr_cds, $inv, $accr_yr, $accr_tp, $cntrct_ids, $this->session->userdata('role_id'));
		
		$this->load->view('report/detailed_accrual_report_xls', $detailed_accrual_report_xls);
	}
	
	function detailed_accrual_pdf()
	{
		$this->load->model('report_model');
		$this->load->library('pdf_report');
		
		$sls_ctr_cds = $this->input->post('sls_ctr');
		$inv = $this->input->post('inv');
		$accr_yr = $this->input->post('accr_yr');
		$accr_tp = $this->input->post('accr_tp');
		$cntrct_ids = $this->input->post('cntrct_id');
		
		$this->load->library('location', $sls_ctr_cds);
		
		$detailed_accrual_report_pdf['inv'] = $inv;
		$detailed_accrual_report_pdf['accrual_yr'] = $accr_yr;
		$detailed_accrual_report_pdf['accr_tp'] = $accr_tp;
		$detailed_accrual_report_pdf['accr_tps'] = array('ytd'=>'Year-to-Date', 'ttl'=>'Total');
		$detailed_accrual_report_pdf['sls_ctrs'] = $sls_ctr_cds;
		$detailed_accrual_report_pdf['code'] = $this->config->item('code');
		$detailed_accrual_report_pdf['data'] = $this->report_model->run_detailed_accrual($sls_ctr_cds, $inv, $accr_yr, $accr_tp, $cntrct_ids, $this->session->userdata('role_id'));
		
		$this->load->view('report/detailed_accrual_report_pdf', $detailed_accrual_report_pdf);
	}
}