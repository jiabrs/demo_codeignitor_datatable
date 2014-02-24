<?php
/**
 * Report_model class
 * 
 *   
 * @package ctm_cma
 * @subpackage models
 */
class Report_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Runs query that supplies detailed accrual report
	 * @param array $sls_ctr_cds
	 * @param array $inv
	 * @param integer $accr_yr
	 * @param string $accr_tp
	 * @param array $cntrct_ids
	 * 
	 * @return array
	 */
	function run_detailed_accrual($sls_ctr_cds = NULL, $inv = NULL, $accr_yr = NULL, $accr_tp = NULL, $cntrct_ids = NULL, $role_id = NULL)
	{
		$params = array();
		
		$cl_wheres = array();
		
		// role id and sales center list are mandatory to run query
		if ($role_id !== NULL && $sls_ctr_cds !== NULL && is_array($sls_ctr_cds) && count($sls_ctr_cds) > 0)
		{
			$params = array_merge(array($role_id), $sls_ctr_cds);
			$cl_wheres[] = "CL.SLS_CTR_CD IN (".rtrim(str_repeat('?,', count($sls_ctr_cds)), ',').")";
		}
		else {
			return array();
		}
		
		if ($cntrct_ids !== NULL && is_array($cntrct_ids) && count($cntrct_ids) > 0)
		{
			$params = array_merge($params, $cntrct_ids);
			$cl_wheres[] = "CL.CNTRCT_ID IN (".rtrim(str_repeat('?,', count($cntrct_ids)), ',').")";
		}
		
		$cl_where = '';
		if (count($cl_wheres) > 0) $cl_where = " AND ".implode(" AND ", $cl_wheres);
		
		// default to post invoice only if not specified
		if ($inv === NULL) $inv = array('N');
		
		// default to current year if accrual year not specified
		if ($accr_yr === NULL) $accr_yr = date('Y');
		
		// default to projected amounts unless specified		
		$accr_field = 'TOT_ACCR_AMT';
		//$vol_field = 'TTL';
		
		if ($accr_tp == 'ytd')
		{
			$accr_field = 'YTD_ACCR_AMT';
			//$vol_field = 'YTD';
		}
		
		// Finish combining query parameters
		$params = array_merge(
			$params,
			array(
				$accr_yr,
				$this->session->userdata('app')
			),
			$inv
		);		
		
		$select = "WITH CL AS (
				SELECT DISTINCT CL.CNTRCT_ID, CL.SLS_CTR_CD
				FROM ".APP_SCHEMA.".CNTRCT_LOC CL
					JOIN ".APP_SCHEMA.".CNTRCT C ON C.CNTRCT_ID = CL.CNTRCT_ID
					JOIN ".APP_SCHEMA.".ROLE_APP RA ON RA.APP = C.APP
					JOIN ".APP_SCHEMA.".ROLE_LOC RL ON RL.SLS_CTR_CD = CL.SLS_CTR_CD
					LEFT JOIN ".APP_SCHEMA.".ROLE_CNTRCT RC ON RC.CNTRCT_ID = CL.CNTRCT_ID
						AND RC.ROLE_ID = RA.ROLE_ID
					LEFT JOIN ".APP_SCHEMA.".ROLE_CNTRCT RC2 ON RC2.ROLE_ID = RA.ROLE_ID
				WHERE RA.ROLE_ID = ?".$cl_where."
					AND (
						(RC2.ROLE_ID IS NOT NULL AND RC.CNTRCT_ID IS NOT NULL)
						OR
						(RC2.ROLE_ID IS NULL AND RC.CNTRCT_ID IS NULL)
					)
			),
			DIVS (ROWNUM, CNTRCT_ID, DIV_CD) AS (
				SELECT ROW_NUMBER() OVER(PARTITION BY CNTRCT_ID ORDER BY CNTRCT_ID, DIV_CD) AS ROWNUM, 
					CNTRCT_ID, DIV_CD
				FROM (
					SELECT DISTINCT CNTRCT_ID, DIV_CD
					FROM CL
						JOIN DW.SLS_CTR SC ON SC.SLS_CTR_CD = CL.SLS_CTR_CD
				) AS DIV
			),
			AGGR_DIV (ROWNUM, CNTRCT_ID, DIVS) AS (
				SELECT ROWNUM, CNTRCT_ID, CAST(DIV_CD AS VARCHAR(100))
				FROM DIVS
				WHERE ROWNUM = 1
				UNION ALL
				SELECT AGGR_DIV.ROWNUM + 1 AS ROWNUM, AGGR_DIV.CNTRCT_ID, 
					AGGR_DIV.DIVS || ', ' || DIVS.DIV_CD AS DIVS
				FROM AGGR_DIV
					JOIN DIVS ON DIVS.CNTRCT_ID = AGGR_DIV.CNTRCT_ID
						AND DIVS.ROWNUM = AGGR_DIV.ROWNUM + 1
			)
			SELECT A.CNTRCT_ID, C.CNTRCT_NM, COALESCE(N.NOTE_CNT, 0) AS NOTE_CNT, L.DIVS,
				E.ELEM_NM, E.ELEM_TP, E.ON_INV_FLG, E.ELEM_RT, E.SHR, 
				A.PRJ, A.YTD, A.TTL, A.LY,
				CASE WHEN A.LY = 0 THEN 0
					ELSE DECIMAL(ROUND(DECIMAL(A.TTL - A.LY, 13, 4) / A.LY, 4), 6, 4)
				END AS CHG, 
				DECIMAL(ROUND(DECIMAL(A.FIX_UNTS, 13, 2) / DECIMAL(E.UNT_DIV, 13, 2), 2), 13, 2) AS FIX_UNTS,
				A.".$accr_field." AS ACR
			FROM (
				SELECT A.CNTRCT_ID, A.ELEM_ID, SUM(CSE_VOL) AS TTL, SUM(LY_VOL) AS LY,
					SUM(FIX_UNTS) AS FIX_UNTS, SUM(ACCR_AMT) AS TOT_ACCR_AMT,
					SUM(CASE WHEN END_DT <= CURRENT_DATE - DAYOFMONTH(CURRENT_DATE) DAYS THEN ACCR_AMT ELSE 0 END) AS YTD_ACCR_AMT,
					SUM(CASE WHEN STRT_DT > CURRENT_DATE - DAYOFMONTH(CURRENT_DATE) DAYS THEN CSE_VOL ELSE 0 END) AS PRJ,
					SUM(CASE WHEN END_DT <= CURRENT_DATE - DAYOFMONTH(CURRENT_DATE) DAYS THEN CSE_VOL ELSE 0 END) AS YTD
				FROM ".APP_SCHEMA.".ACCR A
					JOIN CL ON CL.SLS_CTR_CD  = A.SLS_CTR_CD
						AND CL.CNTRCT_ID = A.CNTRCT_ID
					JOIN ".APP_SCHEMA.".ELEM E ON E.ELEM_ID = A.ELEM_ID
				WHERE YR = ?
					AND A.APP = ?
					AND E.ON_INV_FLG IN (".rtrim(str_repeat('?,', count($inv)), ',').")
				GROUP BY A.CNTRCT_ID, A.ELEM_ID
			) AS A
				JOIN ".APP_SCHEMA.".CNTRCT C ON C.CNTRCT_ID = A.CNTRCT_ID
				JOIN ".APP_SCHEMA.".ELEM E ON E.ELEM_ID = A.ELEM_ID
				LEFT JOIN (
					SELECT CNTRCT_ID, COUNT(*) AS NOTE_CNT
					FROM ".APP_SCHEMA.".NOTE
					GROUP BY CNTRCT_ID
				) AS N ON N.CNTRCT_ID = A.CNTRCT_ID
				LEFT JOIN (
					SELECT CNTRCT_ID, MAX(DIVS) AS DIVS
					FROM AGGR_DIV
					GROUP BY CNTRCT_ID
				) AS L ON L.CNTRCT_ID = A.CNTRCT_ID
			ORDER BY UPPER(C.CNTRCT_NM), UPPER(E.ELEM_NM)";
		
		return $this->db2->query($select, $params)->fetch_object();
	}
}