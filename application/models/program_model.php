<?php
/**
 * Program_model class
 * 
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage models
 */
class Program_model extends CI_Model {
	
	private $pgm_id = NULL;
	private $app = '';
	private $pgm_nm = '';
	
	private $elements = array();
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Sets program's id
	 * 
	 * @param integer $pgm_id
	 */
	function set_pgm_id($pgm_id)
	{
		$this->pgm_id = $pgm_id;
	}
	
	/**
	 * Sets pgm_id to NULL in preparation for
	 * copying 
	 */
	function clear_pgm_id()
	{
		$this->pgm_id = NULL;
	}
	
	/**
	 * Returns program's id
	 * 
	 * @return integer
	 */
	function get_pgm_id()
	{
		return $this->pgm_id;
	}
	
	/**
	 * Sets program's application
	 * 
	 * @param string $app
	 */
	function set_app($app)
	{
		$this->app = $app;
	}
	
	/**
	 * Returns progam's application
	 * 
	 * @return string
	 */
	function get_app()
	{
		return $this->app;
	}
	
	/**
	 * Sets program's name.  Limits to 40 characters
	 * 
	 * @param string $pgm_nm
	 */
	function set_pgm_nm($pgm_nm)
	{
		$this->pgm_nm = substr($pgm_nm, 0, 80);
	}
	
	/**
	 * Returns program's name
	 * 
	 * @return string
	 */
	function get_pgm_nm()
	{
		return $this->pgm_nm;
	}
	
	/**
	 * Returns program name escaped for db2 query
	 * 
	 * @return string
	 */
	function esc_pgm_nm()
	{
		return str_replace("'","''",$this->pgm_nm);
	}
	
	/**
	 * Sets program's elements
	 * 
	 * @param array $elements
	 */
	function set_elements($elements)
	{
		$this->elements = $elements;
	}
	
	/**
	 * Returns program's assigned elements
	 * 
	 * @return array
	 */
	function get_elements()
	{
		return $this->elements;
	}
	
	/**
	 * Returns array of element ids from elements assigned to program
	 * 
	 * @return array:
	 */
	function get_elem_ids()
	{
		$elem_ids = array();
		
		foreach ($this->elements as $element)
		{
			$elem_ids[] = $element->get_elem_id();
		}
		
		return $elem_ids;
	}
	
	/**
	 * If provided legit id, returns loaded program object
	 * Otherwise, returns empty object
	 * 
	 * @param integer $program_id
	 * @return object
	 */
	function &get($pgm_id = NULL)
	{
		// create new object
		$program = new Program_model();

		// if element_id provided, load values from db
		if ($pgm_id !== NULL && $pgm_id !== FALSE)
		{
			$program->set_pgm_id($pgm_id);
			$program->_load();
		}
		
		// return element_model object
		return $program;
	}
	
	/**
	 * Loads object with values from db
	 */
	function _load()
	{
		if ($this->pgm_id !== NULL)
		{
			$sql = "SELECT *
				FROM ".APP_SCHEMA.".PGM
				WHERE PGM_ID = ".$this->pgm_id;
				
			$rows = $this->db2->simple_query($sql)->fetch_assoc();
			
			if (count($rows) > 0)
			{
				$row = $rows[0];
				
				$this->set_pgm_id($row['PGM_ID']);
				$this->set_pgm_nm($row['PGM_NM']);
				$this->set_app($row['APP']);
				
				$this->load_elements();
			}
		}
	}
	
	/**
	 * Loads related elements from database
	 */
	function load_elements()
	{
		$CI =& get_instance();
		
		$CI->load->model('element_model');
		
		$this->set_elements(Element_model::get_elements_by_pgm_id($this->pgm_id));
	}
	
	/**
	 * Saves program to database
	 */
	function save()
	{
		$this->db2->begin_transaction();
		
		try {
			if ($this->exists())
			{
				// create update sql statement
				$update = "UPDATE ".APP_SCHEMA.".PGM
					SET PGM_NM = ?,
						APP = ?
					WHERE PGM_ID = ?";
				
				$update_parms = array(
					$this->get_pgm_nm(),
					$this->app,
					$this->pgm_id
				);
				
				$this->db2->query($update, $update_parms);
				
				$this->remove_elements();
			}
			else
			{
				// create insert statement
				$insert = "SELECT * FROM FINAL TABLE ( 
					INSERT INTO ".APP_SCHEMA.".PGM (PGM_ID,PGM_NM,APP)
					VALUES (NEXT VALUE FOR ".APP_SCHEMA.".SQ_PGM_PK, ?, ?) 
				)";
				
				$insert_parms = array(
					$this->get_pgm_nm(),
					$this->get_app()
				);
				
				$result = $this->db2->query($insert, $insert_parms)->fetch_object();
				
				$this->set_pgm_id($result[0]->PGM_ID);
			}
			
			// add relationships back
			$this->add_elements();
			
		} catch (Exception $e) {
			$this->db2->rollback();
			
			$error =& load_class('Exceptions', 'core');
			echo $error->show_error("Error saving Program", $e->getMessage(), 'error_general');
			exit;
		}		
		
		$this->db2->commit();
	}
	
	/**
	 * Return all programs for locations and app
	 * 
	 * @param array $locations - List of outlet locations to restrict to
	 * @param string $app      - App code to restrict to
	 * 
	 * @return array
	 */
	function get_programs($app = '')
	{
		$sql = new Query;
		
		$sql->selects[] = "DISTINCT P.PGM_ID";
		$sql->from = APP_SCHEMA.".PGM AS P";
		$sql->joins[] = "JOIN ".APP_SCHEMA.".PGM_ELEM AS P_E ON P_E.PGM_ID = P.PGM_ID";
		
		if ($app != '') $sql->wheres[] = "P.APP = '".$app."'";
				
		$programs = array();
		
		foreach ($this->db2->simple_query($sql->build())->fetch_object() as $row)
		{
			// grab a program object for each returned row
			$programs[] = $this->get($row->PGM_ID);
		}
		
		return $programs;
	}
	
	/**
	 * Checks db table for record with program id
	 * 
	 * @return bool
	 */
	function exists()
	{
		if ($this->pgm_id !== NULL)
		{
			$sql = "SELECT * 
				FROM ".APP_SCHEMA.".PGM
				WHERE PGM_ID = ".$this->pgm_id;
				
			if (count($this->db2->simple_query($sql)->fetch_assoc()) > 0)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * removes related elements from program_element table
	 */
	function remove_elements()
	{
		if ($this->pgm_id !== NULL)
		{
			$sql = "DELETE FROM ".APP_SCHEMA.".PGM_ELEM
				WHERE PGM_ID = ".$this->pgm_id;
			
			$this->db2->simple_query($sql);
		}
	}
	
	/**
	 * stores program's elements into program_element table
	 */
	function add_elements()
	{
		if ($this->pgm_id !== NULL && count($this->elements) > 0)
		{
			$insert = "INSERT INTO ".APP_SCHEMA.".PGM_ELEM (PGM_ID,ELEM_ID) VALUES (?, ?)";

			$this->db2->set_sql($insert)->prepare();
			
			foreach ($this->elements as $element)
			{
				$this->db2->set_params(array($this->pgm_id, $element->get_elem_id()))->execute();
			}
		}
	}
	
	/**
	 * Removes program from database.  
	 */
	function remove()
	{
		if ($this->pgm_id !== NULL)
		{
			$this->remove_elements();
			
			$sql = "DELETE FROM ".APP_SCHEMA.".PGM
				WHERE PGM_ID = ".$this->pgm_id;
			
			$this->db2->simple_query($sql);
		}
	}
	
	/**
	 * Searches programs by name
	 * 
	 * @param string $search String fragment to search for
	 * @param array $locations Outlet Locations to restrict search to
	 * @param string $app Application to restrict search to
	 * @param bool $no_obj Set to TRUE, returns db result instead of program_model objects
	 * 
	 * return array|object
	 */
	function search($search = '', $locations = array(), $app = '', $no_obj = FALSE)
	{
		$sql = new Query();
		
		$sql->selects[] = "DISTINCT P.*";
		
		$sql->from = APP_SCHEMA.".PGM AS P";
		
		$sql->wheres[] = "P.APP = '".$app."'";
		$sql->wheres[] = "LOWER(P.PGM_NM) LIKE ?";
		
		// join program's elements, and elements' locations if locations provided
		if (count($locations) > 0)
		{
			$sql->joins[] = "JOIN ".APP_SCHEMA.".PGM_ELEM AS P_E ON P_E.PGM_ID = P.PGM_ID";
			$sql->joins[] = "JOIN ".APP_SCHEMA.".ELEM_LOC AS E_L ON E_L.ELEM_ID = P_E.ELEM_ID";
						
			$sql->wheres[] = "E_L.SLS_CTR_CD IN ('".implode("','", $locations)."')";
		}
		
		// send back raw table data as array
		if ($no_obj)
		{
			return $this->db2->query($sql->build(), array("%".strtolower($search)."%"))->result_object();
		}
		else // return objects
		{
			$programs = array();
			
			foreach ($this->db2->query($sql->build(), array("%".strtolower($search)."%"))->result_object() as $row)
			{
				// create a new element object and add to array
				$programs[] = $this->get($row->PGM_ID);
			}
			
			return $programs;
		}	
	}
	
	/**
	 * Returns programs objects tied to contracts
	 * 
	 * @param int $contract_id
	 * 
	 * @return array
	 */
	function get_by_contract($contract_id = NULL)
	{
		if ($contract_id !== NULL)
		{
			$sql = "SELECT P.*
				FROM ".APP_SCHEMA.".CNTRCT_PGM AS CP
					JOIN ".APP_SCHEMA.".PGM AS P ON P.PGM_ID = CP.PGM_ID
				WHERE CP.CNTRCT_ID = ".$contract_id;
			
			$result = $this->db2->simple_query($sql)->fetch_object();
			
			if (count($result) > 0)
			{
				return $this->get($result[0]->PGM_ID);
			}
			else
			{
				return NULL;
			}
		}
		else
		{
			return NULL;
		}
	}
	
	/**
	 * Assigns program objects to $assignee
	 */
	function assign(&$assignee)
	{
		$programs = array();
		foreach ($assignee->get_pgm_ids() as $pgm_id)
		{
			
			$programs[$pgm_id] = $this->get($pgm_id);
		}
		
		$assignee->set_programs($programs);
	}
	
	/**
	 * Assigns object properties from post data
	 */
	function process_post()
	{
		$CI =& get_instance();
		
		if ($CI->input->post('pgm_nm') !== FALSE)
			$this->set_pgm_nm($CI->input->post('pgm_nm'));
			
		$this->set_app($CI->session->userdata('app'));
			
		$CI->load->model('element_model');
		
		if ($CI->input->post('elem_id') !== FALSE)
		{				
			$elements = array();
			foreach ($CI->input->post('elem_id') as $elem_id)
			{
				$element = new Element_model();
				
				$element->set_elem_id($elem_id);
				
				$elements[] = $element;
			}
					
			$this->set_elements($elements);	
		}	
	}
}