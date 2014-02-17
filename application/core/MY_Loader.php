<?php
class MY_Loader extends CI_Loader {
	
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Database Loader
	 *
	 * Don't care to mess with CI's database library, so we reference $CI->db to 
	 * $CI->db2
	 * 
	 * @access	public
	 * @return	object
	 */
	public function database()
	{
		// Grab the super object
		$CI =& get_instance();

		// All we want to do is reference the existing DB2 object
		$CI->db =& $CI->db2_ci;
		$CI->db2 =& $CI->db2_ci;
	}
}