<?php
/**
 * Fixed Unit Class
 *
 *   
 * @package ctm_cma
 * @subpackage controllers
 *
 */
class Fixed_unit extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->authr->authorize('UP', $this->uri->segment(3));
		
		$this->load->model('fixed_unit_model');
		$this->load->model('contract_model');
		$this->load->model('element_model');
		$this->load->model('code_model');
		
		$index_view['fixed_units'] = $this->fixed_unit_model->get_fixed_units_by_cntrct($this->uri->segment(3));
		$index_view['contract'] = $this->contract_model->get($this->uri->segment(3));
		$index_view['element'] = $this->element_model->get($this->uri->segment(4));
		$index_view['element']->load_cntrct_properties($this->uri->segment(3), $this->uri->segment(5));
		$index_view['year'] = $this->uri->segment(5,date('Y'));
		$index_view['locs'] = $this->code_model->get_codes('SLS_CTR');
		
		$loc_filter = array_intersect($this->authr->get_locations(), $index_view['contract']->get_sls_ctr_cds());		
		$this->load->library('location', $loc_filter);
		
		// if requested year is beyond contract start, end, show last year of contract
		$last_yr = $index_view['contract']->get_last_yr();
		
		if ($last_yr < $index_view['year'])
			$index_view['year'] = $last_yr;
			
		$mths = $index_view['element']->get_mths();
		
		$index_view['mths'] = array();
		foreach($mths[$index_view['year']] as $mth)
		{
			$index_view['mths'][intval($mth)] = date('M',mktime(0,0,0,$mth,1,$index_view['year']));
		}
		
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('fixed_unit/index_view', $index_view);
		$this->_add_view('sec_cont_close_view');
		
		$this->_build();
	}

	function update()
	{
		$this->authr->authorize('UP', $this->input->post('cntrct_id'));
		
		$this->load->model('fixed_unit_model');
		
		$cntrct_id = $this->input->post('cntrct_id');
		$elem_id = $this->input->post('elem_id');
		$year = $this->input->post('year');
		$fix_unts = $this->input->post('fix_unts');
		
		foreach ($fix_unts as $elem_id => $sls_ctr_cds)
		{
			foreach ($sls_ctr_cds as $sls_ctr_cd => $mths)
			{
				foreach ($mths as $mth => $fix_unt)
				{
					$fix_unt_obj = $this->fixed_unit_model->get($year, $mth, $sls_ctr_cd, $cntrct_id, $elem_id);
					
					$fix_unt_obj->set_fix_unts($fix_unt);
					
					$fix_unt_obj->save();
				}
			}
		}
		
		// recalculate accrual information 			
		$cmd = '/usr/bin/php-cli -q '.APPPATH.'cli/calc_accr.php -y '.$year;

		$cmd .= ' -c '.$cntrct_id;
		$cmd .= ' -e '.$elem_id;
		
		shell_exec($cmd);
			
		redirect('contract/dsp/'.$cntrct_id);
	}
}
/* End of file fixed_unit.php */
/* Location: ./controllers/fixed_unit.php */