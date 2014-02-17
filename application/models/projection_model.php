<?php 
/**
 * Projection_model class
 * 
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage models
 */
class Projection_Model extends CI_Model {
	
	public static function get_by_cntrct_elem($cntrct_id = NULL, $elem_id = NULL, $sls_ctr_cds = NULL)
	{
		$CI = &get_instance();
		
		$projections = array();
		
		if ($cntrct_id !== NULL && $elem_id !== NULL && $sls_ctr_cds !== NULL)
		{
			$select = "WITH ED AS (
					SELECT CED.CNTRCT_ID, ELEM_ID, CL.SLS_CTR_CD, YR, MTH, MIN(DT) AS EFF_FRM_DT, MAX(DT) AS EFF_TO_DT
					FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT CED
						JOIN DW.DT DT ON DT.DT BETWEEN CED.STRT_DT AND CED.END_DT
						JOIN ".APP_SCHEMA.".CNTRCT_LOC CL ON CL.CNTRCT_ID = CED.CNTRCT_ID
					WHERE CED.CNTRCT_ID = ?
						AND CED.ELEM_ID = ?
						AND CL.SLS_CTR_CD IN (".rtrim(str_repeat('?,', count($sls_ctr_cds)), ',').")
					GROUP BY CED.CNTRCT_ID, ELEM_ID, CL.SLS_CTR_CD, YR, MTH	
					ORDER BY CED.CNTRCT_ID, ELEM_ID, CL.SLS_CTR_CD, YR, MTH
				)
				SELECT YR, MTH, SUM(LAST) AS LAST, SUM(THIS) AS THIS
				FROM (
					SELECT ED.YR, ED.MTH, COALESCE(S.CSE_VOL, 0) AS LAST, 0 AS THIS
					FROM ".APP_SCHEMA.".CNTRCT_SLS S
						RIGHT JOIN ED ON ED.CNTRCT_ID = S.CNTRCT_ID 
							AND ED.ELEM_ID = S.ELEM_ID
							AND ED.SLS_CTR_CD = S.SLS_CTR_CD
							AND S.STLMNT_DT BETWEEN ED.EFF_FRM_DT - 1 YEARS AND ED.EFF_TO_DT - 1 YEARS
					UNION ALL
					SELECT ED.YR, ED.MTH, 0 AS LAST, COALESCE(CSE_VOL, 0) AS THIS
					FROM ".APP_SCHEMA.".CNTRCT_SLS S
						RIGHT JOIN ED ON ED.CNTRCT_ID = S.CNTRCT_ID 
							AND ED.ELEM_ID = S.ELEM_ID
							AND ED.SLS_CTR_CD = S.SLS_CTR_CD
							AND S.STLMNT_DT BETWEEN ED.EFF_FRM_DT AND ED.EFF_TO_DT
					--WHERE S.STLMNT_DT BETWEEN CURRENT_DATE - (DAYOFYEAR(CURRENT_DATE) - 1) DAYS 
					--	AND  CURRENT_DATE - DAY(CURRENT_DATE) DAYS
					UNION ALL
					SELECT ED.YR, ED.MTH, 0 AS LAST, COALESCE(PRJTD_CSE_VOL, 0) AS THIS
					FROM ".APP_SCHEMA.".CNTRCT_PRJTN P
						RIGHT JOIN ED ON ED.CNTRCT_ID = P.CNTRCT_ID
							AND ED.ELEM_ID = P.ELEM_ID
							AND ED.SLS_CTR_CD = P.SLS_CTR_CD
							AND P.STLMNT_DT BETWEEN ED.EFF_FRM_DT AND ED.EFF_TO_DT
					WHERE P.STLMNT_DT BETWEEN CURRENT_DATE - (DAY(CURRENT_DATE) - 1) DAYS 
						AND ED.EFF_TO_DT
				) AS PROJ_TABLE
				GROUP BY YR, MTH
				ORDER BY YR, MTH";
			
			// echo $select;
			$params = array_merge(
				array($cntrct_id, $elem_id),
				$sls_ctr_cds
			);
			
			// print_r($params);
			foreach ($CI->db2->query($select, $params)->fetch_object() as $row)
			{
				$projections[$row->YR][$row->MTH] = array(
					'this' => number_format(round($row->THIS, 0), 0),
					'last' => number_format(round($row->LAST, 0), 0)
				);
			}
			
		}		
		
		return $projections;
	}
	
	public function spread($cse_vol, $yr, $mth, $cntrct_id, $elem_id, $sls_ctrs, $usr_id)
	{
		$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_PRJTN
			WHERE CNTRCT_ID = ?
				AND ELEM_ID = ?
				AND MONTH(STLMNT_DT) = ?
				AND YEAR(STLMNT_DT) = ?
				AND SLS_CTR_CD IN (".rtrim(str_repeat('?,', count($sls_ctrs)), ',').")";
		
		$params = array($cntrct_id, $elem_id, $mth, $yr);
		
		$params = array_merge($params, $sls_ctrs);
		
		$this->db2->query($delete, $params);
		
		$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_PRJTN (
				STLMNT_DT, SLS_CTR_CD, CNTRCT_ID, ELEM_ID, PRJTD_CSE_VOL, LST_UPDT_TM, USR_ID
			)
			WITH LASTVOL AS (
				SELECT CED.CNTRCT_ID, CED.ELEM_ID, CL.SLS_CTR_CD, DT.YR, DT.MTH, SUM(COALESCE(S.CSE_VOL, 0)) AS CSE_VOL, MIN(DT) AS REC_DT
				FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT CED
					JOIN ".APP_SCHEMA.".CNTRCT_LOC CL ON CL.CNTRCT_ID = CED.CNTRCT_ID
					JOIN DW.DT DT ON DT.DT BETWEEN CED.STRT_DT AND CED.END_DT
					LEFT JOIN ".APP_SCHEMA.".CNTRCT_SLS S ON S.CNTRCT_ID = CED.CNTRCT_ID
						AND S.ELEM_ID = CED.ELEM_ID
						AND S.SLS_CTR_CD = CL.SLS_CTR_CD
						AND S.STLMNT_DT = DT.DT - 1 YEARS
				WHERE CED.CNTRCT_ID = ?
					AND CED.ELEM_ID = ?
					AND DT.MTH = ?
					AND DT.YR = ?	
					AND CL.SLS_CTR_CD IN (".rtrim(str_repeat('?,', count($sls_ctrs)), ',').")			
				GROUP BY CED.CNTRCT_ID, CED.ELEM_ID, CL.SLS_CTR_CD, DT.YR, DT.MTH
			)
			SELECT REC_DT AS STLMNT_DT, SLS_CTR_CD, CNTRCT_ID, ELEM_ID,  
				CASE TTL_VOL.TOT_CSE_VOL
					WHEN 0 THEN 
						DECIMAL(ROUND(DECIMAL(".$cse_vol.", 13, 4) / TTL_VOL.TOT_CNT, 4), 13, 4)
					ELSE 
						DECIMAL(ROUND(DECIMAL(LASTVOL.CSE_VOL, 13, 4) / DECIMAL(TTL_VOL.TOT_CSE_VOL, 13, 4) * DECIMAL(".$cse_vol.", 13, 4), 4), 13, 4) 
				END AS PRJTD_CSE_VOL,
				CURRENT_TIMESTAMP AS LST_UPDT_TM, ".$usr_id." AS USR_ID
			FROM LASTVOL
				JOIN (
					SELECT YR, MTH, SUM(CSE_VOL) AS TOT_CSE_VOL, COUNT(*) AS TOT_CNT
					FROM LASTVOL
					GROUP BY YR, MTH
				) AS TTL_VOL ON TTL_VOL.YR = LASTVOL.YR
					AND TTL_VOL.MTH = LASTVOL.MTH
			ORDER BY SLS_CTR_CD, REC_DT";
		
		$this->db2->query($insert, $params);
	}
	
	/**
	 * Returns list of users who have projected the contract and the most recent 
	 * projection time
	 * 
	 * @param unknown_type $cntrct_id
	 * @return multitype:multitype:NULL  
	 */
	public function get_usr_existing_proj($cntrct_id)
	{
		$exist_proj = array();
		
		$select = "SELECT U.FST_NM || ' ' || U.LST_NM AS USR_NM, 
				P.LST_UPDT_TM AS LST_UPDT_TM, 
				DT.MTH || '/' || DT.YR AS MAX_PROJ_FP
			FROM (
				SELECT USR_ID, VARCHAR_FORMAT(MAX(LST_UPDT_TM), 'MM/DD/YYYY HH24:MI') AS LST_UPDT_TM,
					MAX(STLMNT_DT) AS MAX_PROJ_DT
				FROM ".APP_SCHEMA.".CNTRCT_PRJTN 
				WHERE CNTRCT_ID = ?
				GROUP BY USR_ID
			) AS P
				JOIN ".APP_SCHEMA.".USR U ON U.USR_ID = P.USR_ID
				JOIN DW.DT DT ON DT.DT = P.MAX_PROJ_DT";
		
		foreach ($this->db2->query($select, array($cntrct_id))->fetch_object() as $row)
		{
			$exist_proj[] = array(
				'name' => $row->USR_NM,
				'lst_updt_tm' => $row->LST_UPDT_TM,
				'max_proj_fp' => $row->MAX_PROJ_FP
			);
		}
		
		return $exist_proj;
	}
	
	public function spread_cntrct_chg($chg = 0, $cntrct_id = NULL, $elem_ids = NULL, $sls_ctrs = NULL, $usr_id = NULL)
	{
		$chg = $chg / 100;
		
		$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_PRJTN P
			WHERE CNTRCT_ID = ?
				AND ELEM_ID IN (".rtrim(str_repeat('?,', count($elem_ids)), ',').")
				AND SLS_CTR_CD IN (".rtrim(str_repeat('?,', count($sls_ctrs)), ',').")
				AND STLMNT_DT IN (
					SELECT DISTINCT DT
					FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT CED
						JOIN DW.DT DT ON DT.DT BETWEEN CED.STRT_DT AND CED.END_DT
					WHERE CED.CNTRCT_ID = P.CNTRCT_ID
						AND CED.ELEM_ID = P.ELEM_ID
						AND DT.DT >= CURRENT_DATE - (DAY(CURRENT_DATE) - 1) DAYS
				)";
		
		$params = array_merge(array($cntrct_id), $elem_ids, $sls_ctrs);
		
		$this->db2->query($delete, $params);
		
		$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_PRJTN (
				STLMNT_DT, SLS_CTR_CD, CNTRCT_ID, ELEM_ID, PRJTD_CSE_VOL, LST_UPDT_TM, USR_ID
			)
			WITH LASTVOL AS (
				SELECT S.CNTRCT_ID, S.ELEM_ID, S.SLS_CTR_CD, DT.YR, DT.MTH, SUM(CSE_VOL) AS CSE_VOL, MIN(DT) AS REC_DT
				FROM ".APP_SCHEMA.".CNTRCT_SLS S
					JOIN DW.DT DT ON DT.DT = S.STLMNT_DT
					JOIN ".APP_SCHEMA.".CNTRCT_ELEM_DT CED ON CED.CNTRCT_ID = S.CNTRCT_ID 
						AND CED.ELEM_ID = S.ELEM_ID
						AND S.STLMNT_DT BETWEEN CED.STRT_DT - 1 YEARS AND CED.END_DT - 1 YEARS
				WHERE S.CNTRCT_ID = ?
					AND S.ELEM_ID IN (".rtrim(str_repeat('?,', count($elem_ids)), ',').")
					AND S.SLS_CTR_CD IN (".rtrim(str_repeat('?,', count($sls_ctrs)), ',').")
					AND DT.DT >= CURRENT_DATE - (DAY(CURRENT_DATE) - 1) DAYS - 1 YEARS	
				GROUP BY S.CNTRCT_ID, S.ELEM_ID, S.SLS_CTR_CD, DT.YR, DT.MTH
				ORDER BY S.CNTRCT_ID, S.ELEM_ID, S.SLS_CTR_CD, DT.YR, DT.MTH
			)
			SELECT REC_DT + 1 YEARS AS STLMNT_DT, SLS_CTR_CD, CNTRCT_ID, ELEM_ID,  
				DECIMAL(ROUND(DECIMAL(LASTVOL.CSE_VOL, 13, 4) * DECIMAL(".$chg.", 13, 4), 4), 13, 4) + LASTVOL.CSE_VOL AS PRJTD_CSE_VOL,
				CURRENT_TIMESTAMP AS LST_UPDT_TM, ".$usr_id." AS USR_ID
			FROM LASTVOL";
		
		$this->db2->query($insert, $params);
	}
}
?>