<?php
/**
 * Code_model Class
 *
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage models
 *
 */
class Code_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Retrieves all valid options for provided
	 * field code from XI database
	 *
	 * @param string $field Basis field code (ex "OM1112")
	 * @return bool|array
	 */
	function get_codes($table = NULL)
	{
		if ($table !== NULL)
		{
			$select = "SELECT ".strtoupper($table)."_CD AS CODE, ".strtoupper($table)."_NM AS NAME
				FROM DW.".strtoupper($table)."
				ORDER BY ".strtoupper($table)."_NM ASC";			
				
			$code_rows = $this->db2->simple_query($select)->fetch_assoc();
				
			$codes = array();
				
			foreach ($code_rows as $code_row)
			{
				$codes[$code_row['CODE']] = $code_row['NAME'];
			}
				
			return $codes;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Searches through provided field code's value
	 * descriptions for match with provide search value.
	 * Returns found values in jquery autocomplete freindly
	 * format.
	 *
	 * @param string $field Data warehouse code table (ex "BUS_TP")
	 * @param string $search Search string
	 *
	 * @return array
	 */
	function search($field = NULL, $search = '')
	{
		if ($field !== NULL)
		{
			$select = "SELECT *
				FROM DW.".$field."
				WHERE LCASE(".$field."_NM) LIKE ?
				FETCH FIRST 15 ROWS ONLY";
				
			$select_parms = array("%".strtolower($search)."%");
				
			$code_rows = $this->db2->query($select, $select_parms)->fetch_assoc();
				
			$codes = array();
				
			foreach ($code_rows as $code_row)
			{
				$codes[$code_row[$field."_CD"]] = $code_row[$field."_NM"];
			}
				
			return $codes;
		}
		else
		{
			return array();
		}
	}
}