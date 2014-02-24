<?php
/**
 * DB2 Database access class
 * 
 * Provides basic query functionality
 * from i5 databases
 * 
 *  cbrogan
 *
 */
class Db2 {
	
	private $usr = '';
	private $pwd = '';
	private $db = '';
	private $opts = array();
	
	protected $conn = NULL;
	protected $result = NULL;
	protected $stmt = NULL;
	protected $sql = '';
	protected $params = array();
	
	public function __construct($params = NULL)
	{
		// CI is going to pass array of params. Set appropriate object props
		foreach (array('db', 'usr', 'pwd', 'opts') as $key)
		{
			if (isset($params[$key]))
			{
				$this->{"set_".$key}($params[$key]);
			}
		}
		
		$this->connect();
	}
	
	public function set_db($db)
	{
		$this->db = $db;
		return $this;
	}
	
	public function set_usr($usr)
	{
		$this->usr = $usr;
		return $this;
	}
	
	public function set_pwd($pwd)
	{
		$this->pwd = $pwd;
		return $this;
	}
	
	public function set_opts($opts)
	{
		$this->opts = $opts;
		return $this;
	}
	
	/**
	 * Connects to database server
	 * @throws Exception
	 */
	public function connect()
	{
		try {
			if ($this->conn === NULL)
			{
				if (!$this->conn = db2_connect($this->db, $this->usr, $this->pwd, $this->opts))
//					throw new Exception(db2_conn_errormsg($this->conn), db2_conn_error($this->conn));
					throw new Exception(db2_conn_errormsg(), db2_conn_error());
			}
		} catch (Exception $e) {
			echo "DB2 Connection Error: ".$e->getMessage()."\n";
			exit;
		}
	}
	
	public function cycle_connection()
	{
		db2_close($this->conn);
		
		$this->conn = NULL;
		
		$this->connect();
	}
	
	/**
	 * Directly Executes query using db2_exec()
	 * 
	 * @param string $sql
	 * @throws Exception
	 * 
	 * @return self
	 */
	public function simple_query($sql = NULL)
	{
		// reset attribute case to UPPER
		if (!db2_set_option($this->conn, array('db2_attr_case' => DB2_CASE_UPPER), 1))
		{
			// log_message('error', 'Failed to set db2_attr_case to UPPER');
		}
		
		// reset cached vars
		$this->result = NULL;
		$this->sql = '';
		$this->params = array();
		
		if ($sql !== NULL && $this->conn)
		{
			$this->sql = $sql;
			
			if (!$this->stmt = db2_exec($this->conn, $this->sql))
			{
				// log_message('error', $this->get_error());				
				throw new Exception($this->get_error());
			}
			else
			{
				// // log_message('debug','SQL: '.$this->get_error());
			}
		}	

		return $this;
	}
	
	/**
	 * Prepares sql statement and executes using db2_execute()
	 * 
	 * @param string $sql
	 * @param array $param
	 * @throws Exception
	 * 
	 * @return self
	 */
	public function query($sql = NULL, $param = NULL)
	{
		// reset attribute case to UPPER
		if (!db2_set_option($this->conn, array('db2_attr_case' => DB2_CASE_UPPER), 1))
		{
			// log_message('error', 'Failed to set db2_attr_case to UPPER');
		}
		
		// reset cached vars
		$this->result = NULL;
		$this->sql = '';
		$this->params = array();
		
		if ($sql !== NULL && $this->conn)
		{
			// we didn't get parameters so run simple query
			if ($param === NULL)
			{	
				$this->simple_query($sql);
				return $this;
			}
				
			$this->sql = $sql;
			
			$this->params = $param;
			
			$this->prepare()->execute();
		}	

		return $this;
	}
	
	/**
	 * Prepares Query to be executed
	 * 
	 * @param string $sql
	 * @return resource
	 */
	public function prepare()
	{
		if (FALSE === ($this->stmt = db2_prepare($this->conn, $this->sql)))
		{
			// log_message('error', $this->get_error());
			throw new Exception($this->get_error());
		}
		
		return $this;
	}
	
	/**
	 * Executes prepared statement
	 * 
	 * @param resource $stmt
	 * @param array $param
	 * @return boolean
	 */
	public function execute()
	{
		if (!db2_execute($this->stmt, $this->params))
		{
			// log_message('error', $this->get_error());
			throw new Exception($this->get_error());
		}	
		else {
			// // log_message('debug','SQL: '.$this->get_error());
		}
		
		return $this;
	}
	
	/**
	 * Returns array of row objects
	 * 
	 * @return array
	 */
	public function fetch_object()
	{
		$rows = array();
		
		if ($this->stmt !== NULL && $this->stmt !== FALSE) // && db2_stmt_error($this->stmt) == '00000')
		{	
			$row = db2_fetch_object($this->stmt);
			
			if ($row !== FALSE)
			{
				$rows[] = $row;
				while ($row = db2_fetch_object($this->stmt))
				{
					$rows[] = $row;
				}
			}
			else
			{
				// log_message('Error', $this->get_error());
			}
			
		}
		// cache the result in case we need to use it later for other functions
		$this->result = $rows;
		
		return $rows;
	}
	
	/**
	 * Returns array of rows as associative arrays
	 * 
	 * @return array
	 */
	public function fetch_assoc()
	{
		$rows = array();
		
		if ($this->stmt !== NULL && $this->stmt !== FALSE)
		{				
			while ($row = db2_fetch_assoc($this->stmt))
			{
				$rows[] = $row;
			}
		}
		
		// cache the result in case we need to use it later for other functions
		$this->result = $rows;
		
		return $rows;
	}
	
	function __destruct()
	{
		db2_close($this->conn);
	}
	
	/**
	 * Turn's off autocommit to start transaction
	 */
	public function begin_transaction()
	{
		db2_autocommit($this->conn, DB2_AUTOCOMMIT_OFF);
	}
	
	/**
	 * Commits transaction to the database
	 */
	public function commit()
	{
		db2_commit($this->conn);
		db2_autocommit($this->conn, DB2_AUTOCOMMIT_ON);
	}
	
	public function rollback()
	{
		db2_rollback($this->conn);
	}
	
	public function get_error()
	{
		$conn_err = '';
		
		if ($this->stmt !== FALSE)
		{
			$stmt_errormsg = db2_stmt_errormsg($this->stmt);
			$stmt_error = db2_stmt_error($this->stmt);
			
			if (substr(db2_stmt_error($this->stmt), 0, 4) == '4250')
				$conn_err = "Connection info: ".db2_conn_errormsg($this->conn)." (".db2_conn_error($this->conn).")\n";
		}
		else
		{
			$stmt_errormsg = db2_stmt_errormsg();
			$stmt_error = db2_stmt_error();
		}
		
		$error = $conn_err.$stmt_errormsg."(".$stmt_error.")";
			
		if ($this->sql != '' && $this->sql !== NULL)
			$error .= ": \nSQL:\t".$this->sql;
			
		if (is_array($this->params) && count($this->params) > 0)
			$error .= "\nPARAMETERS:\t".print_r($this->params, TRUE);
		
		return $error;
	}
	
	public function set_sql($sql)
	{
		$this->sql = $sql;
		
		return $this;
	}
	
	public function set_params($params)
	{
		$this->params = $params;
		
		return $this;
	}	
}