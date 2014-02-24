<?php
/**
 * Projection Class
 *
 *   
 * @package ctm_cma
 * @subpackage controllers
 *
 */
class Projection extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{
		$cntrct_id = $this->uri->segment(3, $this->input->post('cntrct_id'));
		
		$this->authr->authorize('UP', $cntrct_id);
		
		$this->load->model('projection_model');
		$this->load->model('contract_model');
		$this->load->model('element_model');		
		
		$elem_id = $this->uri->segment(4, $this->input->post('elem_id'));
		$sls_ctrs = $this->input->post('sls_ctr');
		$divs = $this->input->post('div');
		
		$contract = $this->contract_model->get($cntrct_id);
		
		$loc_filter = array_intersect($this->authr->get_locations(), $contract->get_sls_ctr_cds());
		
		if (!$sls_ctrs) $sls_ctrs = $loc_filter;
			
		$this->load->library('location', $loc_filter);
		
		if (!$divs) $divs = $this->location->get_divs();
		
		$index_view['contract'] = $contract;
		$index_view['element'] = $this->element_model->get($elem_id);
		$index_view['sls_ctrs'] = $sls_ctrs;
		$index_view['divs'] = $divs;
		$index_view['projections'] = Projection_Model::get_by_cntrct_elem($cntrct_id, $elem_id, array_intersect($loc_filter, $sls_ctrs));
		
		$this->tab_title .= " > Projections";
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('projection/index_view',$index_view);
		$this->_add_view('sec_cont_close_view');
			
		$this->_add_css('std_form.css');
		$this->_add_js('projection/index.js');
			
		$this->_build();
	}
	
	function update()
	{
		if (is_ajax())
		{
			$this->authr->authorize('UP', $this->input->post('cntrct_id'));
			
			$this->load->model('projection_model');
			
			$projections = $this->input->post('this');
			$cntrct_id = $this->input->post('cntrct_id');
			$elem_id = $this->input->post('elem_id');
			$sls_ctrs = $this->input->post('sls_ctr');
			$usr_id = $this->session->userdata('usr_id');
			
			foreach ($projections as $yr => $mths)
			{
				foreach ($mths as $mth => $cse_vol)
				{
					if (mktime(0,0,0,date('m'),1,date('Y')) <= mktime(0,0,0,$mth,1,$yr))
					{
						try {
							$this->projection_model->spread(intval(str_replace(',', '', $cse_vol)), $yr, $mth, $cntrct_id, $elem_id, $sls_ctrs, $usr_id);
						} catch (Exception $e) {
							echo $e->getMessage();
							exit;
						}
					}
				}	
			}
			
			// recalculate accrual information 			
			$cmd = '/usr/bin/php-cli -q '.APPPATH.'cli/calc_accr.php -y '.date('Y');

			$cmd .= ' -c '.$cntrct_id;
			$cmd .= ' -e '.$elem_id;
			
			shell_exec($cmd);
		}	
	}
	
	function contract()
	{
		$cntrct_id = $this->uri->segment(3);
		$this->authr->authorize('MC', $cntrct_id);
		
		$this->load->model('projection_model');
		$this->load->model('contract_model');
		$this->load->model('element_model');
		
		$contract = $this->contract_model->get($cntrct_id);
		
		$loc_filter = array_intersect($this->authr->get_locations(), $contract->get_sls_ctr_cds());
		
		$contract_view['locations'] = $this->load->library('location', $loc_filter);
		$contract_view['contract'] = $contract;
		$contract_view['exist_projs'] = $this->projection_model->get_usr_existing_proj($this->uri->segment(3));
		
		$this->tab_title .= " > Projections";
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('projection/contract_view', $contract_view);
		$this->_add_view('sec_cont_close_view');
			
		$this->_add_css('std_form.css');
		$this->_add_js('projection/contract.js');
			
		$this->_build();
	}
	
	function update_contract()
	{
		if (is_ajax())
		{
			$cntrct_id = $this->input->post('cntrct_id');
			$this->authr->authorize('MC', $cntrct_id);
			
			$this->load->model('projection_model');
			
			$sls_ctr_cds = $this->input->post('sls_ctr');
			
			$elem_ids = $this->input->post('elem_id');
			$chg = $this->input->post('chg');
			$usr_id = $this->session->userdata('usr_id');
			
			try {
				$this->projection_model->spread_cntrct_chg($chg, $cntrct_id, $elem_ids, $sls_ctr_cds, $usr_id);
			} catch (Exception $e) {
				echo $e->getMessage();
			}
			
			// recalculate accrual information 			
			$cmd = '/usr/bin/php-cli -q '.APPPATH.'cli/calc_accr.php -y '.date('Y');

			$cmd .= ' -c '.$cntrct_id;
			
			shell_exec($cmd);
		}	
	}
}
?>