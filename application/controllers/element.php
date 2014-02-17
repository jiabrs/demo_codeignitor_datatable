<?php
/**
 * Element Class
 *
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage controllers
 *
 */
class Element extends MY_Controller {

	function __construct()
	{
		parent::__construct();

		$this->authr->authorize('MC');
	}

	function index()
	{
		$this->load->model('element_model');
		$this->load->model('sls_crit_model');
		
		$index_view['elements'] = $this->element_model->get_elements($this->session->userdata('app'));
		
		$this->tab_title .= " > Elements";
		$this->_add_view('sec_cont_open_view');
		$this->_add_view('element/index_view',$index_view);
		$this->_add_view('sec_cont_close_view');
			
		$this->_add_css('std_form.css');
		$this->_add_js('element/index.js');
			
		$this->_build();
	}
	
	/**
	 * Handles server-side processing for datatable
	 */
	function get_datatable()
	{
		if (is_ajax())
		{
			$this->load->model('element_model');
			
			$sort_cols = array(
				'TIME_ORDER',
				'ELEM_ID',
				'ELEM_NM',
				'ELEM_DESC',
				'ELEM_TP_DESC',
				'ELEM_RT',
				'ON_INV_FLG_DESC'
			);
			
			$iDisplayStart = $this->input->post('iDisplayStart');
			$iDisplayLength = $this->input->post('iDisplayLength');
			$iColumns = $this->input->post('iColumns');
			$sSearch = $this->input->post('sSearch');
			$bRegex = $this->input->post('bRegex');
			$iSortingCols = $this->input->post('iSortingCols');
			$sEcho = intval($this->input->post('sEcho'));
			$iSortCol_0 = $this->input->post('iSortCol_0');
			$sSortDir_0 = strtoupper($this->input->post('sSortDir_0'));
			
			$aaData = array();
			
			$rec_cnt = 0;
			foreach ($this->element_model->get_datatable($sSearch, $this->session->userdata('usr_id'), $this->session->userdata('app'), $sort_cols[$iSortCol_0], $sSortDir_0) as $row)
			{
				if ($rec_cnt >= $iDisplayStart && $rec_cnt <= ($iDisplayStart + $iDisplayLength - 1))
				{
					$aaData[] = array(
						'<button type="button" title="Edit" class="edit" value="'.site_url('element/setup_info/'.$row->ELEM_ID).'"><span class="ui-icon ui-icon-wrench"></span></button>'.
						'<button type="button" title="Copy" class="copy" value="'.site_url('element/copy/'.$row->ELEM_ID).'"><span class="ui-icon ui-icon-copy"></span></button>'.
						'<button type="button" title="Remove" class="remove modal" value="'.site_url('element/remove/'.$row->ELEM_ID).'"><span class="ui-icon ui-icon-trash"></span></button>',
						$row->ELEM_ID,
						$row->ELEM_NM,
						$row->ELEM_DESC,
						$row->ELEM_TP_DESC,
						"$&nbsp;".Element_model::format_elem_rt(trim($row->ELEM_RT)),
						$row->ON_INV_FLG_DESC,
						'DT_RowClass' => 'expand_row'
					);
				}
				
				$rec_cnt++;
			}		

			echo json_encode(array(
				'iTotalRecords' => $this->element_model->get_elem_cnt($this->session->userdata('app')),
				'iTotalDisplayRecords' => $rec_cnt,
				'sEcho' => $sEcho,
				'aaData' => $aaData
			));
		}
	}
	
	function get_datatable_more_info()
	{
		if (is_ajax())
		{
			$this->load->model('element_model');
			
			$element = $this->element_model->get($this->uri->segment(3));
			
			$this->load->view('element/datatable_more_info_view', array('element' => $element));
		}
	}
	
	/**
	 * Starts element setup wizard process
	 */
	function setup_info()
	{
		// load validation library
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->model('element_model');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');
		
		$codes = $this->config->item('code');
			
		// did we validate form?
		if ($this->form_validation->run() == FALSE)
		{
			// get an element object.  If element_id was passed, we should
			// get back a real element
			$element = $this->element_model->get($this->uri->segment(3));
			
			// if this is a new element, set the app
			if ($this->uri->segment(3) === FALSE)
			{
				$element->set_app($this->session->userdata('app'));
			}
			
			$setup_info_view['form_error'] = "";
				
			$setup_info_view['element'] = $element;
			$setup_info_view['apps'] = $codes['app'];
				
			$this->tab_title .= " > Elements > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('element/setup_prog_view',array('progress'=>'info'));
			$this->_add_view('element/setup_info_view', $setup_info_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('element/setup_info.js');
				
			$this->_build();
		}
		else // load next wizard step
		{
			$element = $this->element_model->get($this->input->post('elem_id'));
			
			// submitted vars get stored in hidden inputs
			// to be carried through to the end of the wizard
			$element->process_post();
				
			$setup_rules_view['element'] = $element;
				
			$setup_rules_view['form_error'] = "";
				
			$this->tab_title .= " > Elements > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('element/setup_prog_view',array('progress'=>'rules'));
			$this->_add_view('element/setup_rules_view', $setup_rules_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('element/setup_rules.js');
				
			$this->_build();
		}
	}

	/**
	 * Reloads setup_rules_view upon validation failure. On
	 * success, loads setup_criteria_view
	 *
	 */
	function setup_rules()
	{
		// load validation library
		$this->load->library('form_validation');
		$this->load->helper(array('form','ctm_cma'));
		$codes = $this->config->item('code');
		$this->load->model('element_model');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');		
			
		// did we validate form?
		if ($this->form_validation->run() == FALSE)
		{
			$element = $this->element_model->get($this->input->post('elem_id'));
			
			// submitted vars get stored in hidden inputs
			// to be carried through to the end of the wizard
			$element->process_post();
				
			$setup_rules_view['element'] = $element;
				
			$setup_rules_view['form_error'] = "";

			$this->tab_title .= " > Elements > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('element/setup_prog_view',array('progress'=>'rules'));
			$this->_add_view('element/setup_rules_view', $setup_rules_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('element/setup_rules.js');
				
			$this->_build();
		}
		else // load next wizard step
		{
			$this->load->model('sls_crit_model');
			$this->load->model('code_model');
			
			$element = $this->element_model->get($this->input->post('elem_id'));
			
			// submitted vars get stored in hidden inputs
			// to be carried through to the end of the wizard
			$element->process_post();
				
			$element->load_sls_crits();
			
			$setup_criteria_view['element'] = $element;
			$setup_criteria_view['sls_crit_model'] = $this->sls_crit_model;
			$setup_criteria_view['code_model'] = $this->code_model;
			$setup_criteria_view['form_error'] = "";			
				
			$this->tab_title .= " > Elements > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('element/setup_prog_view',array('progress'=>'criteria'));
			$this->_add_view('element/setup_criteria_view', $setup_criteria_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('element/setup_criteria.js');
				
			$this->_build();
		}
	}

	/**
	 * Saves new element
	 */
	function setup_criteria()
	{
		// load validation library
		$this->load->library('form_validation');
		$this->load->helper(array('form','ctm_cma'));
		$codes = $this->config->item('code');
		$this->load->model('element_model');
		
		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');		
			
		// did we validate form?
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->model('sls_crit_model');
			$this->load->model('code_model');
			
			$element = $this->element_model->get($this->input->post('elem_id'));
			
			$element->load_sls_crits();
			
			// submitted vars get stored in hidden inputs
			// to be carried through to the end of the wizard
			$element->process_post();			
			
			$setup_criteria_view['code_model'] = $this->code_model;
			$setup_criteria_view['sls_crit_model'] = $this->sls_crit_model;
			$setup_criteria_view['element'] = $element;				
			$setup_criteria_view['form_error'] = "";
				
			$this->tab_title .= " > Elements > Setup";
			$this->_add_view('sec_cont_open_view');
			$this->_add_view('element/setup_prog_view',array('progress'=>'criteria'));
			$this->_add_view('element/setup_criteria_view', $setup_criteria_view);
			$this->_add_view('sec_cont_close_view');
				
			$this->_add_css('std_form.css');
			$this->_add_js('element/setup_criteria.js');
				
			$this->_build();
		}
		else
		{		
			$element = $this->element_model->get($this->input->post('elem_id'));
			
			// submitted vars get stored in hidden inputs
			// to be carried through to the end of the wizard
			$element->process_post();
				
			$element->save();
			
			// log element modification			
			if (!$this->input->post('elem_id'))
			{
				$usr_actn = 'C'; // updating existing element
			}
			else
			{
				$usr_actn = 'U'; // creating new element
			}
			
			$this->audit_model->log_activity(
				'EL',
				$element->get_elem_id(), 
				$usr_actn,
				$this->session->userdata('usr_id')
			);
			
			// redirect back to list of elements
			redirect('element');
		}
	}
	
	/**
	 * Used for ajax autocomplete in setup_criteria_view
	 * Returns XI codes and code descriptions matching
	 * provided search string and field
	 *
	 */
	function get_crit_vals()
	{
		if (is_ajax())
		{
			$this->load->model('code_model');
				
			$field = $this->input->post('field');
				
			echo json_encode($this->code_model->get_codes($field));
		}
	}
	
	/**
	 * Used by ajax call to retrieve available funding templates
	 * from config/settings file.  Returns id and desc in json 
	 * format for autocomplete
	 */
	function get_fund_tmplt()
	{
		// only respond if ajax request
		if (is_ajax())
		{
			$this->load->model('element_model');
			$templates = $this->element_model->funding_templates;
			$search = $this->input->post('search');
			$app = $this->input->post('app');
				
			$tmplts = array();
				
			foreach ($templates as $id => $info)
			{
				if (stristr($info['desc'], $search) != FALSE && in_array($app, $info['apps']))
				{
					$tmplts[] = array(
						'value' => $id,
						'label' => $info['desc']
					);
				}
			}
				
			// return search results as json
			echo json_encode($tmplts);
		}
		else
		{
			redirect();
		}
	}
	
	/**
	 * Ajax only.  Returns options for requested funding
	 * template.
	 */
	function get_fund_temp_options()
	{
		// only respond if ajax request
		if (is_ajax())
		{
			$this->load->model('element_model');
			$templates = $this->element_model->funding_templates;
			$template = $this->input->post('template');
				
			$options = array();
				
			if (array_key_exists($template, $templates))
			{
				$options = $templates[$template]['options'];
			}
			
			// return search results as json
			echo json_encode($options);
		}
		else
		{
			redirect();
		}
	}
	
	/**
	 * Confirms removal of element
	 */
	function remove()
	{
		$this->load->model('element_model');
		
		// if not confirmed and uid provided in uri . . .
		if ($this->input->post('confirm') === FALSE && $this->uri->segment(3) !== FALSE)
		{	
			$remove_view['element'] = $this->element_model->get($this->uri->segment(3));
				
			$this->load->helper('form');

			// check for ajax request
			if (is_ajax())
			{
				// just send confirmation view
				$this->load->view('element/remove_view',$remove_view);
			}
			else // build view normally
			{
				$this->_add_view('sec_cont_open_view');
				$this->_add_view('element/remove_view', $remove_view);
				$this->_add_view('sec_cont_close_view');
					
				$this->_build();
			}
		}
		else
		{
			// get user's response
			$confirm = $this->input->post('confirm');
				
			$removed = FALSE;
			
			// user confirmed removal
			if ($confirm == "Continue")
			{
				// get user object
				$element = $this->element_model->get($this->input->post('elem_id'));

				// remove user
				$element->remove();
				
				// log activity
				$this->audit_model->log_activity(
					'EL',
					$element->get_elem_id(), 
					'D',
					$this->session->userdata('usr_id')
				);
				
				$removed = TRUE;
			}
				
			if (is_ajax())
			{
				echo json_encode($removed);
			}
			else
			{
				// send back to users list
				redirect('element');
			}
		}
	}
	
	/**
	 * Takes existing element and creates new copy
	 */
	function copy()
	{
		$this->load->model('element_model');
		
		if ($this->uri->segment(3) !== FALSE)
		{
			// Get element we want to copy
			$element = $this->element_model->get($this->uri->segment(3));
			
			// Set element id to NULL so it will create new element
			$element->clear_elem_id();
			
			// Change name so we know which one is the new copy
			$element->set_elem_nm($element->get_elem_nm().' (2)');
			$element->set_elem_desc("Copy of ".$element->get_elem_desc());
			
			// save element
			$element->save();
			
			redirect('element/setup_info/'.$element->get_elem_id());
		}	
		else 
		{
			redirect('element');
		}
	}
	
	/**
	 * Ajax only function for adding elements to contract via
	 * autocomplete field
	 *
	function elem_lookup()
	{
		if (is_ajax())
		{
			$this->load->model('element_model');
			$this->load->model('cprogram_model');
			
			$search = $this->input->post('search');
			$source = $this->input->post('elem_src');
			$locations = $this->input->post('outloc');
			$start_date = $this->input->post('start_date');
			$end_date = $this->input->post('end_date');
			
			$elements = array();
			
			if ($source == "element")
			{
				$results = $this->element_model->search_elements($search, $locations, $this->session->userdata('app'), TRUE);
								
				foreach ($results as $row)
				{
					$elements[] = array(
						'label' => $row->NAME,
						'value' => $row->NAME,
						'start_date' => conv_date($start_date, 'Y-m-d', 'm/d/Y'),
						'end_date' => conv_date($end_date, 'Y-m-d', 'm/d/Y'),
						'elements' => array(
							$row->ELEMENT_ID = array(
								'name' => $row->NAME,
								'rate' => $row->RATE								
							)							
						)
					);
				}
			}
			else
			{
				$programs = $this->cprogram_model->search($search, $locations, $this->session->userdata('app'), FALSE);
				
				foreach ($programs as $program)
				{
					$elements[] = array(
						'label' => $program->name,
						'value' => $program->name,
						'elements' => $program->get_element_data(),
						'start_date' => conv_date($start_date, 'Y-m-d', 'm/d/Y'),
						'end_date' => conv_date($end_date, 'Y-m-d', 'm/d/Y')
					);
				}
			}
			
			echo json_encode($elements);
		}
	}
	*/
	
	function elem_lookup()
	{
		// Ajax only
		if(is_ajax())
		{
			$this->load->model('element_model');
			
			$locs = $this->input->post('sls_ctr_cd');
			
			// if ($locs === FALSE) $locs = $this->authr->get_locations();
			
			$app = $this->session->userdata('app');
			$search = $this->input->post('search');
			
			if ($search === FALSE) $search = NULL;
			
			$elem_lookup_view['elements'] = $this->element_model->get_elements($app, FALSE, 'Y', $search);
			
			$this->load->view('element/elem_lookup_view', $elem_lookup_view);
		}
	}
	
	/**
	 * Returns element json_encoded for jquery
	 * 
	 */
	function j_get_info()
	{
		if (is_ajax())
		{
			$this->load->model('element_model');
			
			$element = $this->element_model->get($this->uri->segment(3));
			
			echo json_encode(array(
				'elem_id' => $element->get_elem_id(),
				'elem_nm' => $element->get_elem_nm(),
				'elem_desc' => $element->get_elem_desc(),
				'elem_rt' => $element->dsp_elem_rt()
			));
		}		
	}
	
	/**
	 * Returns list of contracts using element
	 */
	function get_affected_cntrcts()
	{
		if (is_ajax())
		{
			$this->load->model('element_model');
			
			$element = $this->element_model->get($this->input->post('elem_id'));
			
			$contracts = $element->get_cntrcts();
			
			if (count($contracts) > 0)
			{
				$this->load->view('element/affected_cntrct_view.php', array('contracts' => $contracts));
			}
			else
			{
				echo '';
			}
		}
	}
}

/* End of file element.php */
/* Location: /ctm_cma/controllers/element.php */