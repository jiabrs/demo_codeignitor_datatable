<?php
/**
 * Location class
 * 
 *   
 * @package ctm_cma
 * @subpackage models
 */
class Location {
	
	private $locations = array();
	
	function __construct($sls_ctrs = NULL)
	{
		$CI = &get_instance();
		
		$where = "";
		
		if ($sls_ctrs !== NULL) $where = " WHERE SLS_CTR_CD IN ('".implode("','", $sls_ctrs)."')";
		
		$select = "SELECT R.REGN_CD, REGN_NM, 
			CASE R.REGN_CD
				WHEN 'G' THEN 'GF'
				ELSE D.DIV_CD
			END AS DIV_CD, 
			CASE R.REGN_CD
				WHEN 'G' THEN 'GULF'
				ELSE D.DIV_NM
			END AS DIV_NM, 
			SC.SLS_CTR_CD, SLS_CTR_NM
			FROM DW.SLS_CTR SC
				JOIN DW.DIV D ON D.DIV_CD = SC.DIV_CD
				JOIN DW.REGN R ON R.REGN_CD = D.REGN_CD".$where."
			ORDER BY REGN_NM, DIV_NM, SLS_CTR_NM";
		
		foreach ($CI->db2->simple_query($select)->fetch_object() as $row)
		{
			$this->locations[$row->REGN_CD]['divs'][$row->DIV_CD]['sls_ctrs'][$row->SLS_CTR_CD] = array(
				'name' => $row->SLS_CTR_NM,
				'short_name' => $this->convert_short_name($row->SLS_CTR_NM)
			);
			$this->locations[$row->REGN_CD]['name'] = $row->REGN_NM;
			$this->locations[$row->REGN_CD]['divs'][$row->DIV_CD]['name'] = $row->DIV_NM;
		}
	}
	
	function get_by_sls_ctr()
	{
		$sls_ctrs = array();
		
		foreach ($this->locations as $regn_cd => $data)
		{
			foreach ($data['divs'] as $div_cd => $data2)
			{
				foreach ($data2['sls_ctrs'] as $sls_ctr_cd => $data3)
				{
					$sls_ctrs[$sls_ctr_cd] = $data3;
				}
				
			}
		}
		
		return $sls_ctrs;
	}
	
	function get_by_div()
	{
		$divs = array();
		
		foreach ($this->locations as $regn_cd => $data)
		{
			$divs = array_merge($divs, $data['divs']);
		}
		
		return $divs;
	}
	
	function get_by_regn()
	{
		return $this->locations;
	}
	
	private function convert_short_name($name)
	{
		if ($name == 'WEST ALABAMA')
		{
			return 'WAL';
		} else {
			return substr(str_replace('LAUREL MOUNTAIN ', 'LM', $name), 0, 3);
		}		
	}
	
	public function get_divs()
	{
		$divs = array();
		
		foreach ($this->locations as $regn_cd => $data)
		{
			foreach ($data['divs'] as $div_cd => $data2)
			{
				$divs[] = $div_cd;
			}
		}
		
		return $divs;
	}
	
	public function get_sls_ctr_drop_dwn()
	{
		$sls_ctrs = array();
		
		foreach ($this->locations as $regn_cd => $data)
		{
			foreach ($data['divs'] as $div_cd => $data2)
			{
				foreach ($data2['sls_ctrs'] as $sls_ctr_cd => $data3)
				{
					$sls_ctrs[$sls_ctr_cd] = $data3['name'];
				}
				
			}
		}
		
		return $sls_ctrs;
	}
}
?>