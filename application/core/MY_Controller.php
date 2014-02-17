<?php

class MY_Controller extends CI_Controller 
{
	// header view related vars
	private $views = array();
	private $css_files = array(
		'dataTable.css',
		'jqueryui/jquery-ui-1.8.custom.css'
	);

	private $js_files = array(
		'jquery-1.6.2.min.js',
		'jquery-ui-1.8.custom.min.js',
		'jquery.dataTables.min.js',
		'jquery.dataTables.plugins.js',
		'ctm_cma.js'
	);
	
	// Text that appears in browser tab, or browser title bar
	public $tab_title = '';

	// Text that appears in App title variable
	public $app_title = '';

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Combines header, queued, and footer views for output
	 *
	 * @access private
	 *
	 */
	function _build()
	{
		$codes = $this->config->item('code');
		
		if ($this->tab_title == '') // tab title wasn't provided
		{
			if ($this->session->userdata('app') !== FALSE) // are we logged in?
			{
				// Set the base tab title to be the current app
				$tab_title = $codes['app'][$this->session->userdata('app')];
				
				// Append any controllers and methods that the user has selected
				for ($i=1;$i<3;$i++)
				{
					if ($this->uri->segment($i) !== FALSE)
						$tab_title .= '&nbsp;&gt;&nbsp;'.ucfirst($this->uri->segment($i));
				}
			}
			else { // we're not logged in, so set the tab title to Post Invoice Funding
				$tab_title = "Post Invoice Funding";
			}
		}
		else // User overrode the tab title
		{
			$tab_title = $this->tab_title;
		}		
		
		// set the rest of the header vars
		$header_view['tab_title'] = $tab_title;
		$header_view['app_title'] = 'Post Invoice Funding';
		$header_view['css_files'] = $this->css_files;
		$header_view['js_files'] = $this->js_files;
		$header_view['app_nav_list'] = $this->config->item('app_nav_list');
		$header_view['logn_nm'] = $this->authr->get_user_info('logn_nm');
		$header_view['fullname'] = $this->authr->get_user_info('full_nm');
		$header_view['logged_in'] = $this->authr->is_logged_in();
		$header_view['env_set'] = $this->session->userdata('role_id') !== FALSE && $this->session->userdata('app') !== FALSE;
		$header_view['code'] = $codes;

		// load header view
		$this->load->view('header_view', $header_view);

		// load any app views
		foreach ($this->views as $view)
		{
			$this->load->view($view['name'], $view['vars']);
		}

		// load footer view
		$this->load->view('footer_view');
	}
	
	/**
	 * Queues up view to be outputed by build function
	 *
	 * @param string $view_name
	 * @param mixed $view_vars
	 *
	 */
	function _add_view($view_name = NULL, $view_vars = NULL)
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
	function _add_js($js_name)
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
	
	/**
	 * Adds css file to included resources in header view
	 *
	 * @param string $css_name
	 */
	function _add_css($css_name)
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