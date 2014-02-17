<?php
/**
 * View_builder Class
 *
 * Loads views and outputs to browser
 *
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage Custom Libraries
 *
 */
class View_builder
{
	// super object
	var $CI = NULL;

	// header view related vars
	private $views = array();
	private $css_files = array(
		'dataTable.css',
		'jqueryui/jquery-ui-1.8.custom.css'
	);

	private $js_files = array(
		'jquery-1.4.2.min.js',
		'jquery-ui-1.8.custom.min.js',
		'jquery.dataTables.min.js',
		'ctm_cma.js'
	);

	// Text that appears in browser tab, or browser title bar
	public $tab_title = '';

	// Text that appears in App title variable
	public $app_title = '';

	// currently logged in user
	public $logn_nm = '';
	
	public $fullname = '';

	// user logged in status
	public $logged_in = FALSE;

	/**
	 * @access private
	 */
	function __construct()
	{
		$this->CI =& get_instance();
	}

	/**
	 * Combines header, queued, and footer views for output
	 *
	 * @access public
	 *
	 */
	function build()
	{
		// load header variables
		if ($this->tab_title == '')
		{
			$header_view['tab_title'] = "CTM/CMA/Scanbacks";
		}
		else
		{
			$header_view['tab_title'] = $this->tab_title;
		}

		if ($this->app_title == '')
		{
			$header_view['app_title'] = 'CTM/CMA/Scanbacks';
		}
		else
		{
			$header_view['app_title'] = $this->app_title;
		}

		$header_view['css_files'] = $this->css_files;
		$header_view['js_files'] = $this->js_files;
		$header_view['app_nav_list'] = $this->CI->config->item('app_nav_list');
		$header_view['logn_nm'] = $this->logn_nm;
		$header_view['fullname'] = $this->fullname;
		$header_view['logged_in'] = $this->logged_in;
		$header_view['code'] = $this->CI->config->item('code');

		// load header view
		$this->CI->load->view('header_view', $header_view);

		// load any app views
		foreach ($this->views as $view)
		{
			$this->CI->load->view($view['name'], $view['vars']);
		}

		// load footer view
		$this->CI->load->view('footer_view');
	}

	/**
	 * Queues up view to be outputed by build function
	 *
	 * @param string $view_name
	 * @param mixed $view_vars
	 *
	 */
	function add_view($view_name = NULL, $view_vars = NULL)
	{
		// initialize view container
		$view = array(
		'name' => '',
		'vars' => array()
		);

		// did we get a view name?
		if ($view_name !== NULL)
		{
			$view['name'] = $view_name;
		}

		// did we get view variables?
		if ($view_vars !== NULL)
		{
			$view['vars'] = $view_vars;
		}

		// if view is not blank, queue up to load view
		if ($view['name'] != '')
		{
			$this->views[] = $view;
		}
	}

	/**
	 * Adds js file to included resources in header view
	 *
	 * @param string $js_name
	 */
	function add_js($js_name)
	{
		if (is_array($js_name))
		{
			$this->js_files += $js_name;
		}
		else
		{
			$this->js_files[] = $js_name;
		}
	}

	function add_jquery()
	{
		/*
		 $this->add_js('jquery-1.4.2.min.js');
		 $this->add_js('jquery-ui-1.8.custom.min.js');
		 $this->add_css('redmond/jquery-ui-1.8.custom.css');
		 */
	}

	/**
	 * Adds css file to included resources in header view
	 *
	 * @param string $css_name
	 */
	function add_css($css_name)
	{
		if (is_array($css_name))
		{
			$this->css_files += $css_name;
		}
		else
		{
			$this->css_files[] = $css_name;
		}
	}
}