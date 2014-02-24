<?php
/**
 * Contract Class
 *
 *   
 * @package ctm_cma
 * @subpackage controllers
 *
 */
class Contract extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->load->model('contract_model');
		$this->load->model('program_model');
		
		$index_view['contracts'] = array();
		
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('contract/index_view',$index_view);
		$this->_add_view('sec_cont_close_view');
			
		$this->_add_css('std_form.css');
		$this->_add_js('contract/index.js');		
			
		$this->_build();
	}
	
	/**
	 * Handles server-side processing for datatable
	 */
	function get_datatable()
	{
		if (is_ajax())
		{
			$this->load->model('contract_model');
			
			$sort_cols = array(
				'TIME_ORDER',
				'CNTRCT_ID',
				'CNTRCT_NM',
				'PGM_NM',
				'STRT_DT',
				'END_DT'
			);
			
			$iDisplayStart = $this->input->post('iDisplayStart');
			$iDisplayLength = $this->input->post('iDisplayLength');
			$iColumns = $this->input->post('iColumns');
			$sSearch = $this->input->post('sSearch');
			$bRegex = $this->input->post('bRegex');
			$iSortingCols = $this->input->post('iSortingCols');
			$sEcho = intval($this->input->post('sEcho'));
			$iSortCol_0 = $this->input->post('iSortCol_0');
			$sSortDir_0 = strtoupper($this->input->post('sSortDir_0'));
			
			for ($i=0;$i<$iColumns;$i++)
			{
				$bSearchable_{$i} = $this->input->post('bSearchable_'.$i);
				$bSortable{$i} = $this->input->post('bSortable_'.$i);
			}			
			
			$aaData = array();
			
			$rec_cnt = 0;
			foreach ($this->contract_model->get_datatable($sSearch, $this->session->userdata('role_id'), $this->session->userdata('app'), $sort_cols[$iSortCol_0], $sSortDir_0) as $row)
			{
				if ($rec_cnt >= $iDisplayStart && $rec_cnt <= ($iDisplayStart + $iDisplayLength - 1))
				{
					$buttons = '<button type="button" title="Display" class="edit" value="'.site_url('contract/dsp/'.$row->CNTRCT_ID).'"><span class="ui-icon ui-icon-document"></span></button>';
					
					if ($this->authr->authorize('MC', NULL, FALSE))
						$buttons .= 
							'<button type="button" title="Edit" class="edit" value="'
								.site_url('contract/setup_info/'.$row->CNTRCT_ID).'">'
								.'<span class="ui-icon ui-icon-wrench"></span></button>'
							.'<button type="button" title="Copy" class="copy" value="'
								.site_url('contract/copy/'.$row->CNTRCT_ID).'">'
								.'<span class="ui-icon ui-icon-copy"></span></button>'
							.'<button type="button" title="Remove" class="remove modal" value="'
								.site_url('contract/remove/'.$row->CNTRCT_ID).'">'
								.'<span class="ui-icon ui-icon-trash"></span></button>';
						
					$aaData[] = array(
						$buttons,
						$row->CNTRCT_ID,
						$row->CNTRCT_NM,
						$row->SLS_CTRS,
						conv_date($row->STRT_DT, 'Y-m-d', 'm/d/Y'),
						conv_date($row->END_DT, 'Y-m-d', 'm/d/Y')
					);
				}
				
				$rec_cnt++;
			}		

			echo json_encode(array(
				'iTotalRecords' => $this->contract_model->get_cntrct_cnt($this->session->userdata('role_id'), $this->session->userdata('app')),
				'iTotalDisplayRecords' => $rec_cnt,
				'sEcho' => $sEcho,
				'aaData' => $aaData
			));
		}
	}

	function get_datatable_more_info()
	{
		if (is_ajax())
		{
			$this->load->model('contract_model');
			
			$contract = $this->contract_model->get($this->uri->segment(3));
			
			$loc_filter = array_intersect($this->authr->get_locations(), $contract->get_sls_ctr_cds());
		
			$this->load->library('location', $loc_filter);
			
			$this->load->view('contract/datatable_more_info_view', array('contract' => $contract));
		}
	}
	/**
	 * Shows individual contract
	 */
	function dsp()
	{
		$cntrct_id = $this->uri->segment(3);
		$this->authr->authorize(NULL, $cntrct_id, TRUE);
		
		// $this->output->enable_profiler(TRUE);
		
		$this->load->model('contract_model');
		$this->load->model('program_model');
		$this->load->model('customer_model');
		$this->load->model('user_model');
		$this->load->model('sls_crit_model');
		$this->load->model('code_model');
		
		$contract = $this->contract_model->get($cntrct_id);
		
		$contract->set_accrual_yr($this->uri->segment(4, date('Y')));
		
		$contract->load_elements();
		$contract->load_notes();
		$contract->load_stats();
		
		// $this->customer_model->assign($contract);
		// $this->user_model->assign($contract);
		
		// Until approval list module in place, do temporary approval list lookup
		$appr_lst = array();
		$retrn_to = '';
		
		if ($contract->get_appr_lst_id() != 0)
		{
			$select = "WITH U AS (
					SELECT USR_ID, LOGN_NM, FST_NM || ' ' || LST_NM AS USR_NM
					FROM ".APP_SCHEMA.".USR
				),
				AU (APPR_LST_ID, USR_NM, ORD) AS (
					SELECT APPR_LST_ID, U.USR_NM, ORDER AS ORD
					FROM ".APP_SCHEMA.".APPR_LST_USR AU
						JOIN U ON U.USR_ID = AU.USR_ID
				)
				SELECT AU.USR_NM AS APPR_NM, ORD, U.USR_NM AS RETRN_USR_NM
				FROM ".APP_SCHEMA.".APPR_LST A
					JOIN U ON U.USR_ID = A.RETRN_USR_ID
					JOIN AU ON AU.APPR_LST_ID = A.APPR_LST_ID
				WHERE A.APPR_LST_ID = ?
				ORDER BY ORD";
			
			foreach ($this->db2->query($select, array($contract->get_appr_lst_id()))->fetch_object() as $row)
			{
				$appr_lst[$row->ORD] = $row->APPR_NM;
				$retrn_to = $row->RETRN_USR_NM;
			}			
		}
		
		$sls_pull_sts = '';
		// check sales pull status
		if ($this->authr->authorize('MC', $contract->get_cntrct_id(), FALSE))
		{
			$select = "SELECT *
				FROM ".APP_SCHEMA.".CNTRCT_BTCH_JOB
				WHERE CNTRCT_ID = ?
					AND JOB_STS = 0
					AND JOB_NM = 'Pull Sales'
				ORDER BY LST_UPDT_TM DESC
				FETCH FIRST 1 ROWS ONLY";
			
			$row = $this->db->query($select, array($cntrct_id))->row();
			
			if ($row !== FALSE && $row !== NULL)
				$sls_pull_sts = $row->JOB_MSG;
		}
		
		$loc_filter = array_intersect($this->authr->get_locations(), $contract->get_sls_ctr_cds());
		
		$this->load->library('location', $loc_filter);
		
		$dsp_view['contract'] =& $contract;
		$dsp_view['program'] = $this->program_model->get_by_contract($contract->get_cntrct_id());
		$dsp_view['appr_lst'] = $appr_lst;
		$dsp_view['retrn_to'] = $retrn_to;
		$dsp_view['sls_pull_sts'] = $sls_pull_sts;
		
		$this->tab_title = $contract->get_cntrct_nm();
		
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('contract/dsp_view',$dsp_view);
		$this->_add_view('sec_cont_close_view');
			
		$this->_add_css('std_form.css');
		
		$this->_add_js('jquery.form.js');
		$this->_add_js('contract/dsp.js');
		$this->_add_js('note/dsp.js');
			
		$this->_build();
	}
	
	function pdf()
	{
		$cntrct_id = $this->uri->segment(3);
		
		$this->authr->authorize(NULL, $cntrct_id, TRUE);
		
		$this->load->model('contract_model');
		$this->load->model('program_model');
		$this->load->model('customer_model');
		$this->load->model('user_model');
		$this->load->model('sls_crit_model');
		$this->load->model('code_model');
		
		$this->load->library('pdf_report');
		
		$contract = $this->contract_model->get($cntrct_id);
		
		$contract->set_accrual_yr($this->uri->segment(4, date('Y')));
		
		$contract->load_elements();
		$contract->load_notes();
		$contract->load_stats();
		
		// $this->customer_model->assign($contract);
		// $this->user_model->assign($contract);
		
		// Until approval list module in place, do temporary approval list lookup
		$appr_lst = array();
		$retrn_to = '';
		
		if ($contract->get_appr_lst_id() != 0)
		{
			$select = "WITH U AS (
					SELECT USR_ID, LOGN_NM, FST_NM || ' ' || LST_NM AS USR_NM
					FROM ".APP_SCHEMA.".USR
				),
				AU (APPR_LST_ID, USR_NM, ORD) AS (
					SELECT APPR_LST_ID, U.USR_NM, ORDER AS ORD
					FROM ".APP_SCHEMA.".APPR_LST_USR AU
						JOIN U ON U.USR_ID = AU.USR_ID
				)
				SELECT AU.USR_NM AS APPR_NM, ORD, U.USR_NM AS RETRN_USR_NM
				FROM ".APP_SCHEMA.".APPR_LST A
					JOIN U ON U.USR_ID = A.RETRN_USR_ID
					JOIN AU ON AU.APPR_LST_ID = A.APPR_LST_ID
				WHERE A.APPR_LST_ID = ?
				ORDER BY ORD";
			
			foreach ($this->db2->query($select, array($contract->get_appr_lst_id()))->fetch_object() as $row)
			{
				$appr_lst[$row->ORD] = $row->APPR_NM;
				$retrn_to = $row->RETRN_USR_NM;
			}			
		}
		
		$loc_filter = array_intersect($this->authr->get_locations(), $contract->get_sls_ctr_cds());
		
		$this->load->library('location', $loc_filter);
		
		$dsp_pdf['contract'] =& $contract;
		$dsp_pdf['program'] = $this->program_model->get_by_contract($contract->get_cntrct_id());
		$dsp_pdf['report_title'] = 'Contract Detail Report';
		$dsp_pdf['code'] = $this->config->item('code');
		$dsp_pdf['appr_lst'] = $appr_lst;
		$dsp_pdf['retrn_to'] = $retrn_to;
		
		$this->load->view('contract/dsp_pdf', $dsp_pdf);
	}
	
	function pull_sls()
	{
		if (is_ajax())
		{
			$cntrct_id = $this->input->post('cntrct_id');
		
			$cmd = "nohup /usr/bin/php-cli -q";
			$cmd .= " ".APPPATH."cli/pull_sales.php -c ".$cntrct_id;
			$cmd .= " > ".APPPATH."cli/".$cntrct_id."_sales_pull.log 2>&1 & echo $!";
			
			$pid = shell_exec($cmd);
			
			echo $pid;
		}		
	}
	
	function pull_sls_log()
	{
		$file_path = APPPATH."cli/".$this->uri->segment(3)."_sales_pull.log";
			
		if (file_exists($file_path))
		{
			echo "<pre>".file_get_contents($file_path)."</pre>";
		}
		else {
			echo 'Could not find sales pull log';
		}
	}
	
	function is_pull_sls_running()
	{
		passthru("ps ".$this->uri->segment(3));
	}
	
	/*
	 * Starts contract creation wizard
	 */
	function setup_info()
	{
		// load libraries and models
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->model('contract_model');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');
		
		if ($this->form_validation->run() === FALSE)
		{
			$this->load->model('user_model');
			
			$contract = $this->contract_model->get($this->uri->segment(3));
			
			$contract->process_post();
			
			$setup_info_view['contract'] = $contract;
			$setup_info_view['form_error'] = "";
			$setup_info_view['app'] = $this->session->userdata('app');
			
			/*
			 * build approval list drop down. This is a temporary solution 
			 * until approval list module is implemented
			 */ 
			$appr_lst = array(0=> 'No approval list');
			
			$select = "WITH U AS (
					SELECT USR_ID, LOGN_NM, FST_NM || ' ' || LST_NM AS USR_NM
					FROM ".APP_SCHEMA.".USR
				),
				AU (APPR_LST_ID, USR_NM, ORD) AS (
					SELECT APPR_LST_ID, U.USR_NM, ORDER AS ORD
					FROM ".APP_SCHEMA.".APPR_LST_USR AU
						JOIN U ON U.USR_ID = AU.USR_ID
				),
				AL (APPR_LST_ID, USR_LST, ORD) AS (
					SELECT APPR_LST_ID, CAST(USR_NM AS VARCHAR(256)) AS USR_LST, ORD
					FROM AU
					WHERE ORD = 1
					UNION ALL
					SELECT AL.APPR_LST_ID, AL.USR_LST || ' > ' || AU.USR_NM AS USR_LST, AU.ORD
					FROM AL
						JOIN AU ON AU.APPR_LST_ID = AL.APPR_LST_ID
							AND AU.ORD = AL.ORD + 1
				)
				SELECT APPR_LST_ID, MAX(USR_LST) AS APPR_LST
				FROM AL
				GROUP BY APPR_LST_ID
				ORDER BY APPR_LST_ID";
			
			foreach ($this->db2->simple_query($select)->fetch_object() as $row)
			{
				$appr_lst[$row->APPR_LST_ID] = $row->APPR_LST;
			}
			
			$setup_info_view['appr_lst'] = $appr_lst;
			
			$this->tab_title .= " > Contracts > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('contract/setup_prog_view',array('progress'=>'info','contract'=>$contract));
			$this->_add_view('contract/setup_info_view', $setup_info_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('contract/setup_info.js');
				
			$this->_build();
		}
		else // process info and load locations
		{
			$this->load->model('code_model');
			$this->load->library('location', $this->authr->get_locations());
			
			// get new contract object and load with submitted settings
			$contract = $this->contract_model->get($this->input->post('cntrct_id'));
			
			$contract->process_post();	
			
			$setup_loc_view['contract'] = $contract;
			
			// restrict to locations assigned to user
			$setup_loc_view['form_error'] = "";
			
			$this->tab_title .= " > Contracts > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('contract/setup_prog_view',array('progress'=>'location','contract'=>$contract));
			$this->_add_view('contract/setup_loc_view', $setup_loc_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
				
			$this->_build();
		}
	}
	
	function _chk_vndr_no($vndr_no)
	{
		
		if ($this->session->userdata('app') != 'CM')
		{
			return TRUE;
		}
		elseif ($this->session->userdata('app') == 'CM' && strlen($vndr_no) == 5)
		{
			return TRUE;
		}
		elseif ($this->session->userdata('app') == 'CM' && strlen($vndr_no) == 0)
		{
			$this->form_validation->set_message('_chk_vndr_no','Vendor Number is required for CMA contracts');
			return FALSE;
		}
		elseif ($this->session->userdata('app') == 'CM' && strlen($vndr_no) > 0 && strlen($vndr_no) != 5)
		{
			$this->form_validation->set_message('_chk_vndr_no','Vendor Number must be 5 digits');
			return FALSE;
		}
	}
	/**
	 * Reloads location setup upon validation failure, or loads customer setup
	 */
	function setup_locations()
	{
		// load libraries and models
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->model('contract_model');
		$this->load->model('code_model');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');
		
		if ($this->form_validation->run() === FALSE)
		{		
			$this->load->library('location', $this->authr->get_locations());
				
			// get contract object and load with submitted settings
			$contract = $this->contract_model->get($this->input->post('cntrct_id'));
			
			$contract->process_post();
			
			$setup_loc_view['contract'] = $contract;
			
			// restrict locations to those assigned to current user
			$setup_loc_view['form_error'] = "";
			
			$this->tab_title .= " > Contracts > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('contract/setup_prog_view',array('progress'=>'location','contract'=>$contract));
			$this->_add_view('contract/setup_loc_view', $setup_loc_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
				
			$this->_build();
		}
		else // process submitted locations and load customers
		{
			$this->load->model('customer_model');
			
			// get contract object and load with submitted settings

			$contract = $this->contract_model->get($this->input->post('cntrct_id'));

			$contract->process_post();
			
			$setup_cust_view['contract'] = $contract;
			
			$setup_cust_view['multi_setups'] = array(
				'single' => 'Customers assigned one contract',
				'multi' => 'Customers assigned individual contracts'
			);
			
			$setup_cust_view['multi_setup'] = 'single';
			
			$setup_cust_view['bustypes'] = $this->code_model->get_codes('BUS_TP');
			
			if ($this->session->userdata('app') == 'CM')
			{
				$setup_cust_view['cust_tp'] = 'OT';
			}
			else
			{
				$setup_cust_view['cust_tp'] = 'KA';
			}	
			
			$setup_cust_view['form_error'] = "";
			
			$this->tab_title .= " > Contracts > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('contract/setup_prog_view',array('progress'=>'customer','contract'=>$contract));
			$this->_add_view('contract/setup_cust_view', $setup_cust_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('contract/setup_cust.js');
				
			$this->_build();
		}
	}
	
	/**
	 * Contract Setup:  loads customer setup upon form validation failure, or 
	 * loads funding setup
	 */
	function setup_customer()
	{
		// load libraries and models
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->model('contract_model');
		$this->load->model('customer_model');
		$this->load->model('code_model');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');
		
		if ($this->form_validation->run() === FALSE)
		{			
			// get contract object and load with submitted settings
			$contract = $this->contract_model->get($this->input->post('cntrct_id'));
			
			$contract->process_post();
			
			$setup_cust_view['contract'] = $contract;
			
			$setup_cust_view['multi_setups'] = array(
				'single' => 'Customers assigned one contract',
				'multi' => 'Customers assigned individual contracts'
			);
			
			$setup_cust_view['bustypes'] = $this->code_model->get_codes('BUS_TP');
			$setup_cust_view['multi_setup'] = $this->input->post('multi_setup');
			
			if ($this->session->userdata('app') == 'CM')
			{
				$setup_cust_view['cust_tp'] = 'OT';
			}
			else
			{
				$setup_cust_view['cust_tp'] = 'KA';
			}			
			
			$this->tab_title .= " > Contracts > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('contract/setup_prog_view',array('progress'=>'customer','contract'=>$contract));
			$this->_add_view('contract/setup_cust_view', $setup_cust_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('contract/setup_cust.js');
				
			$this->_build();
		}
		else
		{
			$this->load->model('element_model');
			$this->load->model('program_model');
			
			$contract = $this->contract_model->get($this->input->post('cntrct_id'));
			
			$contract->process_post();
			
			$contract->load_elements();
			
			$setup_funding_view['contract'] = $contract;
			
			$setup_funding_view['elem_srcs'] = array(
				'program' => 'From Program',
				'element' => 'Individual Elements'
			);
			
			$setup_funding_view['app'] = $this->session->userdata('app');
			
			$setup_funding_view['form_error'] = '';
			$setup_funding_view['program'] = $this->program_model->get_by_contract($contract->get_cntrct_id());
			$setup_funding_view['multi_setup'] = $this->input->post('multi_setup');
			
			$this->tab_title .= " > Contracts > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('contract/setup_prog_view',array('progress'=>'funding','contract'=>$contract));
			$this->_add_view('contract/setup_funding_view', $setup_funding_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('contract/setup_funding.js');
				
			$this->_build();
		}
	}
	
	/** 
	 * Contract setup: reload funding view on validation failure, or load 
	 * assign user view
	 */
	function setup_funding()
	{
		// load libraries and models
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->model('contract_model');
		$this->load->model('element_model');
		$this->load->model('program_model');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');
		
		if ($this->form_validation->run() === FALSE)
		{			
			$contract = $this->contract_model->get($this->input->post('cntrct_id'));
			
			$contract->process_post();
			
			$setup_funding_view['contract'] = $contract;
			$setup_funding_view['elem_srcs'] = array(
				'program' => 'From Program',
				'element' => 'Individual Elements'
			);
			
			$setup_funding_view['form_error'] = '';
			$setup_funding_view['program'] = $this->program_model->get_by_contract($contract->get_cntrct_id());
			
			$setup_funding_view['app'] = $this->session->userdata('app');
			$setup_funding_view['multi_setup'] = $this->input->post('multi_setup');
			
			$this->tab_title .= " > Contracts > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('contract/setup_prog_view',array('progress'=>'funding','contract'=>$contract));
			$this->_add_view('contract/setup_funding_view', $setup_funding_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('contract/setup_funding.js');
				
			$this->_build();
		}
		else
		{
			$action = $this->input->post('action');
			$contract = $this->contract_model->get($this->input->post('cntrct_id'));
			
			$contract->process_post();
			
			// Check for multi-setup and only run if new contract
			if ($this->input->post('multi_setup') == 'multi' && $contract->get_cntrct_id() === NULL)
			{
				$cust_tps = $this->input->post('cust_tp');
				
				// determine contract name prefix
				$cntrct_nm_prefix = substr($contract->get_cntrct_nm(), 0, 47);
				
				// go through each POST'd customer
				foreach ($this->input->post('cust_cd') as $cust_index => $cust_cd)
				{
					// override any existing contract id (same method as copy)
					$contract->clear_cntrct_id();
					
					// override existing customers assigned to contract
					$contract->set_customer_cds(array($cust_cd), array($cust_tps[$cust_index]));
					
					// truncate contract name to 47 characters and append customer name 
					$customers = $contract->get_customers();
					
					$contract->set_cntrct_nm($cntrct_nm_prefix.' - '.$customers[0]->get_cust_nm());
					
					$contract->save();
					
					// update audit log
					$this->audit_model->log_activity(
						'CT',
						$contract->get_cntrct_id(), 
						'C',
						$this->session->userdata('usr_id')
					);
					
					// since we've created multiple contracts, return to datatables page
					redirect('contract');
				}
			}
			else 
			{
				$contract->save();
				
				// log element modification			
				if (!$this->input->post('cntrct_id'))
				{
					$usr_actn = 'C'; // creating new contract
				}
				else
				{
					$usr_actn = 'U'; // updating existing contract
				}
				
				$this->audit_model->log_activity(
					'CT',
					$contract->get_cntrct_id(), 
					$usr_actn,
					$this->session->userdata('usr_id')
				);
				
				if ($action == "Save & View Contract")
				{
					redirect('contract/dsp/'.$contract->get_cntrct_id());
				}
				else {
					redirect('contract');
				}
			}			
		}
	}
	
	/**
	 * Confirms removal request and removes contract
	 */
	function remove()
	{
		$this->load->model('contract_model');
		
		// if not confirmed and uid provided in uri . . .
		if ($this->input->post('confirm') === FALSE && $this->uri->segment(3) !== FALSE)
		{	
			$remove_view['contract'] = $this->contract_model->get($this->uri->segment(3));
				
			$this->load->helper('form');

			// check for ajax request
			if (is_ajax())
			{
				// just send confirmation view
				$this->load->view('contract/remove_view',$remove_view);
			}
			else // build view normally
			{
				$this->_add_view('sec_cont_open_view');
				$this->_add_view('contract/remove_view', $remove_view);
				$this->_add_view('sec_cont_close_view');
					
				$this->_build();
			}
		}
		else
		{
			// get user's response
			$confirm = $this->input->post('confirm');
				
			$removed = FALSE;
			
			// user confirmed removal
			if ($confirm == "Continue")
			{
				// get user object
				$contract = $this->contract_model->get($this->input->post('contract_id'));

				// remove user
				$contract->remove();
				
				// log activity
				$this->audit_model->log_activity(
					'CT',
					$contract->get_cntrct_id(), 
					'D',
					$this->session->userdata('usr_id')
				);
				
				$removed = TRUE;
			}
				
			if (is_ajax())
			{
				echo json_encode($removed);
			}
			else
			{
				// send back to users list
				redirect('contract');
			}
		}
	}
	
	/**
	 * Makes a new copy of a contract
	 */
	function copy()
	{
		$this->load->model('contract_model');
		
		if ($this->uri->segment(3) !== FALSE)
		{
			// Get element we want to copy
			$contract = $this->contract_model->get($this->uri->segment(3));
			
			$contract->load_elements();
			
			// Set contract id to NULL so it will create new element
			$contract->clear_cntrct_id();
			
			// Change name so we know which one is the new copy
			$contract->set_cntrct_nm($contract->get_cntrct_nm()." (Copy)");
			
			// save element
			$contract->save();
		}	

		redirect('contract/setup_info/'.$contract->get_cntrct_id());
	}
	
	/**
	 * Ajax only function to return funding row to be inserted
	 * into funding elements table on the setup_funding_view
	 */
	function j_get_setup_fnd_row()
	{
		if (is_ajax())
		{
			$this->load->model('element_model');
			
			$element = $this->element_model->get($this->input->post('add_elem_id'));
			
			if ($element->is_loaded())
			{
				$element->set_strt_dts(array(0 => $this->input->post('strt_dt')));
				$element->set_end_dts(array(0 => $this->input->post('end_dt')));
				
				$setup_fnd_row_view['element'] = $element;
				$setup_fnd_row_view['app'] = $this->session->userdata('app');
				
				$this->load->view('contract/setup_fnd_row_view', $setup_fnd_row_view);
			}
			else
			{
				echo "";
			}
		}
	}
	
	function j_get_notes()
	{
		if (is_ajax())
		{		
			$this->load->model('contract_model');
			
			$contract = $this->contract_model->get($this->uri->segment(3));
			
			$contract->load_notes();
			
			$notes = array();
			
			foreach ($contract->get_notes() as $note)
			{
				$notes[$note->get_note_id()] = array(
					'body' => $note->get_body(),
					'lst_updt_tm' => $note->dsp_lst_updt_tm(),
					'file_nm' => $note->dsp_file_nm()
				);
			}
			
			echo json_encode($notes);
		}
	}
	
	function viewers()
	{
		$this->load->model('contract_model');
		
		$contract = $this->contract_model->get($this->uri->segment(3));
		
		$this->load->library('location', $contract->get_sls_ctr_cds()); 
		
		$viewers_view['roles'] = $this->role_model->get_by_cntrct($this->uri->segment(3));
		
		$this->load->view('contract/viewers_view', $viewers_view);
	}
	
	function search()
	{
		if (is_ajax())
		{
			$this->load->model('contract_model');
			
			$search = $this->input->post('search');
			
			$filters = array();
			
			foreach (array('app','sls_ctr_cd','yr') as $filter)
			{
				if (($value = $this->input->post($filter)) !== FALSE)
				{
					$filters[$filter] = $value;
				}
			}
			
			$filters['role_id'] = $this->session->userdata('role_id');
			
			$select_opts = array();
			
			foreach (Contract_model::search_by_name($search, $filters) as $row)
			{
				$select_opts[$row->CNTRCT_ID] = "#".$row->CNTRCT_ID." - ".$row->CNTRCT_NM;
			}
			
			echo json_encode($select_opts);
		}
	}
}
/* End of file contract.php */
/* Location: ./contract.php */