<?php
require_once('Db2.php');

/**
 * Codeigniter DB2 class. Extends DB2 class
 * for codeigniter compatibility
 *  cbrogan
 *
 */
class Db2_ci extends Db2 {
	
	private $wheres = array();
	
	function __construct($params = NULL)
	{
		if ($params === NULL)
		{
			$CI =& get_instance();
			$params = $CI->config->item('db');
		}
		$this->log_message('debug', 'Db2_ci class constructor called');
		
		parent::__construct($params);
	}
	
	/**
	 * Redirect messages to CI logging
	 * 
	 * @param string $level
	 * @param string $message
	 */
	private function log_message($level, $message)
	{
		log_message($level, $message);
	}
	
	/**
	 * Builds where clause
	 * @param string $column
	 * @param $value
	 */
	public function where($column = NULL, $value = NULL)
	{
		if ($column !== NULL && $value !== NULL)
		{
			$this->wheres[$column] = $value;
		}
		elseif ($column !== NULL && $value === NULL)
		{
			$this->wheres[] = $column;
		}
	}
	
	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	function _has_operator($str)
	{
		$str = trim($str);
		if ( ! preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str))
		{
			return FALSE;
		}

		return TRUE;
	}
	
	/**
	 * Takes key => value array and builds sql conditions (wheres, sets)
	 * 
	 * @param cond $cond	Conditions as array of key => value pairs
	 * @param bool $param	Parameterize conditions
	 * @param string $sep 	condition seperator
	 * @return string
	 */
	private function _build_conditions($cond = NULL, $param = TRUE, $sep = 'AND')
	{
		if (is_array($cond))
		{
			$conds = array();
				
			foreach ($cond as $col => $val)
			{
				// if column is integer, than where provided
				// as string instead of key/value
				if (is_int($col))
				{
					$conds[] = $val;
				}
				else 
				{
					// make operator "=" if not provided
					$op = ' = ';
					if ($this->_has_operator($col))
						$op = ' ';
						
					if ($param) // parameterizing so make val ?
					{
						$val = "?";
					}
					elseif (is_string($val)) // escape value
					{
						$val = "'".db2_escape_string($val)."'";
					}
					
					$conds[] = $col.$op.$val;		
				}
			}
			
			return implode(' '.$sep.' ', $conds);
		}
		else
		{
			return '';
		}
	}
	
	/**
	 * Executes query against table using previously supplied
	 * predicates
	 * 
	 * @param string $table
	 * @return Db2
	 */
	public function get($table = NULL)
	{
		if ($table !== NULL)
		{
			$sql = "SELECT * FROM ".APP_SCHEMA.".".$table;
			
			$where = $this->_build_conditions($this->wheres);
			
			if ($where != '')
				$sql .= " WHERE ".$where;
			
			$this->query($sql, array_values($this->wheres));
			
			$this->wheres = array();
			
			if (!db2_set_option($this->stmt, array('db2_attr_case' => DB2_CASE_LOWER), 2))
			{
				log_message('debug', 'Failed to set db2_attr_case option');
			}
		}
		
		return $this;
	}
	
	/**
	 * Performs UPDATE 
	 * 
	 * @param string $table
	 * @param array $sets
	 * @return Db2
	 */
	public function update($table = NULL, $sets = NULL)
	{
		if ($table !== NULL && $sets !== NULL && is_array($sets))
		{
			$update = "UPDATE ".APP_SCHEMA.".".$table
				." SET ".$this->_build_conditions($sets, TRUE, ',');
			
			$where = $this->_build_conditions($this->wheres);
			
			if ($where != '')
				$update .= " WHERE ".$where;
			
			$this->query($update, array_merge(array_values($sets),array_values($this->wheres)));
			
			$this->wheres = array();
		}
		
		return $this;
	}
	
	/**
	 * Returns count of rows
	 * @return number
	 */
	public function num_rows()
	{
		// Pull result set if we haven't already
		if ($this->result === NULL)
			$this->fetch_object();
			
		return count($this->result);		
	}
	
	/**
	 * Returns first row in result set
	 * 
	 * @return object|NULL
	 */
	public function row()
	{
		// only use db2_fetch_object if we haven't pulled a result set yet
		if ($this->stmt !== NULL && $this->stmt !== FALSE)
		{	
			if ($this->result === NULL)
			{
				return db2_fetch_object($this->stmt);
			}	
			elseif (is_array($this->result))
			{
				return $this->result[0];
			}
			else {
				return NULL;
			}
		}
		else {
			return NULL;
		}
	}
	
	/**
	 * Builds insert string
	 * 
	 * @param string $table
	 * @param array $values
	 */
	public function insert_string($table = NULL, $values = NULL)
	{
		if ($table !== NULL && $values !== NULL)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".".$table." (".implode(',', array_keys($values)).") VALUES (";
			
			// build values string
			$vals = array();
			foreach ($values as $col => $val)
			{
				if (is_string($val))
				{
					$vals[] = "'".db2_escape_string($val)."'";
				}
				else 
				{
					$vals[] = $val;
				}
			}
			
			return $insert.implode(',', $vals).")";
		}
		else {
			return '';
		}
	}
	
	/**
	 * Builds sql for Update
	 * 
	 * @param string $table
	 * @param array $sets
	 * @param array $wheres
	 * 
	 * @return string
	 */
	public function update_string($table = NULL, $sets = NULL, $wheres = NULL)
	{
		if ($table !== NULL && is_array($sets) && is_array($wheres))
		{
			$update = "UPDATE ".APP_SCHEMA.".".$table." SET ";
			
			$update .= $this->_build_conditions($sets, FALSE, ',');
			
			$where = $this->_build_conditions($wheres, FALSE);
			
			if ($where != '')
				$update .= " WHERE ".$where;
			
			return $update;
		}
		else
		{
			return '';
		}
	}
	
	/**
	 * Deletes from table
	 * 
	 * @param string $table
	 * @return Db2
	 */
	public function delete($table = NULL)
	{
		if ($table !== NULL)
		{
			$delete = "DELETE FROM ".APP_SCHEMA.".".$table;
			
			$where = $this->_build_conditions($this->wheres, FALSE);
			
			if ($where != '')
				$delete .= " WHERE ".$where;
			
			$this->query($delete, array_values($this->wheres));
			
			$this->wheres = array();
		}
		
		return $this;
	}
}