<?php
/**
 * Demo class
 *
 * Used to demo non-working interfaces
 *
 *  cbrogan
 * @package ctm_cma
 * @subpackage Controllers
 *
 */
class Demo extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Loads user list for user management
	 */
	function check_req()
	{
		$select = "WITH T AS (
				SELECT ROW_NUMBER() OVER(ORDER BY CNTRCT_ID, PYMT_FREQ, STRT_DT) AS CHECK_REQ_ID, 
					CNTRCT_ID, PYMT_FREQ, STRT_DT, SUM(ACCR_AMT) AS TOT_AMT, COUNT(DISTINCT SLS_CTR_CD) AS SLS_CTR_CDS
				FROM PICHAD.CHECK_ACCR
				WHERE QUARTER(STRT_DT) = 1
				GROUP BY CNTRCT_ID, PYMT_FREQ, STRT_DT	
			)
			SELECT T.*, C.CNTRCT_NM, E.ELEM_NM, E.ELEM_RT, A.ELEM_ID, A.CSE_VOL, 
				A.ACCR_AMT, CHAR(A.STRT_DT, USA) AS E_STRT_DT, CHAR(A.END_DT, USA) AS E_END_DT, A.SLS_CTR_CD, SC.SLS_CTR_NM,
				CASE T.PYMT_FREQ
					WHEN '03' THEN QUARTER(T.STRT_DT)
					WHEN '12' THEN MONTHNAME(T.STRT_DT)
					WHEN '1E' THEN 'YEAR END'
					WHEN '1S' THEN 'YEAR START'
					ELSE NULL
				END AS CHECK_TXT
			FROM T
				JOIN PICHAD.CHECK_ACCR A ON A.CNTRCT_ID = T.CNTRCT_ID
					AND A.PYMT_FREQ = T.PYMT_FREQ
					AND A.STRT_DT = T.STRT_DT
				JOIN PICHAD.ELEM E ON E.ELEM_ID = A.ELEM_ID
				JOIN PICHAD.CNTRCT C ON C.CNTRCT_ID = T.CNTRCT_ID
				JOIN DW.SLS_CTR SC ON SC.SLS_CTR_CD = A.SLS_CTR_CD
			WHERE T.TOT_AMT <> 0
				AND A.ACCR_AMT <> 0
			ORDER BY C.VEND_NO, T.CNTRCT_ID, T.PYMT_FREQ, T.STRT_DT, E.ELEM_ID";
		
		$check_req_view['rows'] = $this->db2->simple_query($select)->fetch_object();
		$check_req_view['status'] = $this->uri->segment(3, 'ready');
		$check_req_view['approvers'] = array(
			'User 1',
			'User 2',
			'User 3'
		);
		
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('demo/check_req_view', $check_req_view);
		$this->_add_view('sec_cont_close_view');
			
		$this->_build();
	}

	function print_check_req()
	{
		$this->load->model('customer_model');
		$this->load->model('check_req_model');
		
		$select = "WITH T AS (
				SELECT ROW_NUMBER() OVER(ORDER BY CNTRCT_ID, PYMT_FREQ, STRT_DT) AS CHECK_REQ_ID, 
					CNTRCT_ID, PYMT_FREQ, STRT_DT, SUM(ACCR_AMT) AS TOT_AMT, 
					COUNT(DISTINCT SLS_CTR_CD) AS SLS_CTR_CDS, MAX(END_DT) AS END_DT
				FROM PICHAD.CHECK_ACCR
				WHERE QUARTER(STRT_DT) = 1
				GROUP BY CNTRCT_ID, PYMT_FREQ, STRT_DT	
			)
			SELECT T.*, C.CNTRCT_NM, E.ELEM_NM, E.ELEM_RT, A.ELEM_ID, A.CSE_VOL, 
				CHAR(T.STRT_DT, USA) AS STRT_DT_USA, CHAR(T.END_DT, USA) AS END_DT_USA,
				A.ACCR_AMT, CHAR(A.STRT_DT, USA) AS E_STRT_DT, CHAR(A.END_DT, USA) AS E_END_DT, A.SLS_CTR_CD, SC.SLS_CTR_NM,
				CASE T.PYMT_FREQ
					WHEN '03' THEN QUARTER(T.STRT_DT)
					WHEN '12' THEN MONTHNAME(T.STRT_DT)
					WHEN '1E' THEN 'YEAR END'
					WHEN '1S' THEN 'YEAR START'
					ELSE NULL
				END AS CHECK_TXT,
				T.CNTRCT_ID || '-' || T.CHECK_REQ_ID AS CHECK_REQ_REF,
				P.PRF_CTR_CD, C.VEND_NO
			FROM T
				JOIN PICHAD.CHECK_ACCR A ON A.CNTRCT_ID = T.CNTRCT_ID
					AND A.PYMT_FREQ = T.PYMT_FREQ
					AND A.STRT_DT = T.STRT_DT
				JOIN PICHAD.ELEM E ON E.ELEM_ID = A.ELEM_ID
				JOIN PICHAD.CNTRCT C ON C.CNTRCT_ID = T.CNTRCT_ID
				JOIN DW.SLS_CTR SC ON SC.SLS_CTR_CD = A.SLS_CTR_CD
				JOIN PICHAD.LOC_PRF_CTR P ON P.SLS_CTR_CD = A.SLS_CTR_CD
			WHERE T.TOT_AMT <> 0
				AND A.ACCR_AMT <> 0
			ORDER BY C.VEND_NO, T.CNTRCT_ID, T.PYMT_FREQ, T.STRT_DT, E.ELEM_ID";
		
		$approvers = array(
			'User 1',
			'User 2',
			'User 3'
		);
		
		$return_to = 'User 4';
		
		$check_reqs = array();
		$curr_check_req_id = $check_req = NULL;
		
		foreach ($this->db2->simple_query($select)->fetch_object() as $row)
		{
			// start a new object
			if ($curr_check_req_id != $row->CHECK_REQ_ID)
			{
				if ($curr_check_req_id !== NULL)
				{
					$check_req->set_elements($elements);
					$check_req->set_profit_ctrs($profit_ctrs);
					$check_reqs[] = $check_req;
				}
				$check_req = new Check_req_model();
				
				$check_req->set_check_req_id($row->CHECK_REQ_ID);
				$check_req->set_check_req_sts('R');
				$check_req->set_check_req_dt(mktime());
				$check_req->set_approvers($approvers);
				$check_req->set_return_to($return_to);
				$check_req->generate_check_txt($row->PYMT_FREQ, $row->CHECK_TXT);
				$check_req->set_check_ref($row->CHECK_REQ_REF);
				$check_req->set_term($row->STRT_DT_USA.' - '.$row->END_DT_USA);
				$check_req->set_cntrct_nm($row->CNTRCT_NM);
				$check_req->set_customers($this->customer_model->get_by_contract($row->CNTRCT_ID));
				$check_req->set_vend_no($row->VEND_NO);
				
				$elements = $profit_ctrs = array();
			}
			
			$elements[] = array(
				'elem_nm' => $row->ELEM_NM,
				'elem_rt' => $row->ELEM_RT,
				'cse_vol' => $row->CSE_VOL,
				'accr_amt' => $row->ACCR_AMT
			);
			
			// track total amounts by profit center
			if (array_key_exists($row->PRF_CTR_CD, $profit_ctrs))
			{
				$profit_ctrs[$row->PRF_CTR_CD]['amt'] += $row->ACCR_AMT;
			}
			else {
				$profit_ctrs[$row->PRF_CTR_CD]['amt'] = $row->ACCR_AMT;
				$profit_ctrs[$row->PRF_CTR_CD]['sls_ctr_nm'] = $row->SLS_CTR_NM;
			}
			
			$curr_check_req_id = $row->CHECK_REQ_ID;
		}
		
		$check_req->set_elements($elements);
		$check_req->set_profit_ctrs($profit_ctrs);
		$check_reqs[] = $check_req;
		
		$print_check_req_view['check_reqs'] = $check_reqs;
		
		$this->load->library('pdf_report');
		
		$this->load->view('demo/print_check_req_pdf', $print_check_req_view);
	}
	
	function check_stub()
	{
		$select = "WITH T AS (
				SELECT ROW_NUMBER() OVER(ORDER BY CNTRCT_ID, PYMT_FREQ, STRT_DT) AS CHECK_REQ_ID, 
					CNTRCT_ID, PYMT_FREQ, STRT_DT, SUM(ACCR_AMT) AS TOT_AMT
				FROM PICHAD.CHECK_ACCR
				WHERE QUARTER(STRT_DT) = 1
				GROUP BY CNTRCT_ID, PYMT_FREQ, STRT_DT	
			)
			SELECT C.VEND_NO, T.TOT_AMT, T.PYMT_FREQ,
				'1' || DIGITS(DECIMAL(T.CHECK_REQ_ID, 7)) || DIGITS(DECIMAL(T.CNTRCT_ID, 8)) AS CHECK_REQ_REF,
				CASE T.PYMT_FREQ
					WHEN '03' THEN QUARTER(T.STRT_DT)
					WHEN '12' THEN MONTHNAME(T.STRT_DT)
					WHEN '1E' THEN 'YEAR END'
					WHEN '1S' THEN 'YEAR START'
					ELSE NULL
				END AS CHECK_TXT
			FROM T
				JOIN PICHAD.CNTRCT C ON C.CNTRCT_ID = T.CNTRCT_ID
			WHERE T.TOT_AMT <> 0
			ORDER BY C.VEND_NO, T.CNTRCT_ID, T.PYMT_FREQ, T.STRT_DT";
		
		$check_stub_view['rows'] = $this->db2->simple_query($select)->fetch_object();
		
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('demo/check_stub_view', $check_stub_view);
		$this->_add_view('sec_cont_close_view');
			
		$this->_build();
	}
}
/* End of file demo.php */
/* Location: ./demo.php */