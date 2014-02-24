<?php
/**
 * Constructs queries
 * 
 *   
 * @package ctm_cma
 * @subpackage libraries
 *
 */
class Query {
	
	public $selects = array();
	public $from = '';
	public $joins = array();
	public $wheres = array();
	public $extra = '';
	
	/**
	 * Builds the query from the public properties 
	 * and returns as string
	 * 
	 * @return string
	 */
	function build()
	{
		$sql = "SELECT ".implode(', ', $this->selects);
		
		$sql .= " FROM ".$this->from;
		
		if (count($this->joins) > 0) $sql .= " ".implode(' ', $this->joins);
		
		if (count($this->wheres) > 0) $sql .= " WHERE ".implode(' AND ', $this->wheres);
		
		if ($this->extra != '') $sql .= " ".$this->extra;
		
		return $sql;
	}
}
/* End of file query.php */
/* Location: /ctm_cma/libraries/query.php */