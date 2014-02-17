<?php
/**
 * Design_std Class
 *
 * Testing and Designing template for application
 *
 * @author Chad Brogan <chadbrogan@ccbcu.com>
 * @package ctm_cma
 * @subpackage controllers
 *
 */
class Design_std extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->view_builder->tab_title .= " > Design Standards";
		$this->view_builder->add_view('design_std/action_view');
		$this->view_builder->add_view('sec_cont_open_view');
		$this->view_builder->add_view('sec_cont_close_view');

		$this->view_builder->build();
	}

	/**
	 * Tests form layout and controls
	 */
	function form()
	{
		// load validation library
		$this->load->library('form_validation');

		// change delimiters
		$this->form_validation->set_error_delimiters('<span class="valid_error">', '</span>');

		$this->load->helper('form');

		$form_view['ex_dropdowns'] = array(
		1 => 'Select 1',
		2 => 'Select 2',
		3 => 'Select 3',
		4 => 'Select 4',
		5 => 'Select 5'
		);

		$form_view['ex_radio_opts'] = array(
		1 => 'Option 1',
		2 => 'Option 2',
		3 => 'Option 4',
		4 => 'Option 5',
		5 => 'Option 6',
		6 => 'Option 7',
		7 => 'Option 8'
		);

		$form_view['ex_checkbx_opts'] = array(
			'A' => 'Option A',
			'B' => 'Option B',
			'C' => 'Option C',
			'D' => 'Option D',
			'E' => 'Option E'
			);

			$form_view['table_data'] = array(
			0 => array(
				'Column 1' => 'posuere at,',
				'Column 2' => '(702) 884-2226',
				'Column 3' => 'tortor'
				),
				1 => array(
				'Column 1' => 'mus. Proin',
				'Column 2' => '(330) 567-8403',
				'Column 3' => 'risus.'
				),
				2 => array(
				'Column 1' => 'vitae, sodales',
				'Column 2' => '(256) 253-0531',
				'Column 3' => 'orci.'
				),
				3 => array(
				'Column 1' => 'dui. Cum',
				'Column 2' => '(667) 108-6226',
				'Column 3' => 'Curae'
				),
				4 => array(
				'Column 1' => 'Integer sem',
				'Column 2' => '(454) 489-7044',
				'Column 3' => 'Praesent'
				),
				5 => array(
				'Column 1' => 'sed tortor.',
				'Column 2' => '(725) 136-7924',
				'Column 3' => 'pede'
				),
				6 => array(
				'Column 1' => 'arcu. Vestibulum',
				'Column 2' => '(365) 622-5689',
				'Column 3' => 'nisi'
				),
				7 => array(
				'Column 1' => 'montes, nascetur',
				'Column 2' => '(887) 328-2017',
				'Column 3' => 'lorem'
				),
				8 => array(
				'Column 1' => 'convallis est,',
				'Column 2' => '(484) 634-8613',
				'Column 3' => 'amet'
				),
				9 => array(
				'Column 1' => 'egestas, urna',
				'Column 2' => '(670) 461-0597',
				'Column 3' => 'eu'
				)
				);

				if ($this->form_validation->run() == FALSE)
				{
					$form_view['form_error'] = "Generic form error: Blah blah blah blah blah blah";
						
					$this->view_builder->tab_title .= " > Design Standards > Form";
					$this->view_builder->add_view('design_std/action_view', array());
					$this->view_builder->add_view('sec_cont_open_view');
					$this->view_builder->add_view('design_std/form_view', $form_view);
					$this->view_builder->add_view('sec_cont_close_view');
					$this->view_builder->add_css('std_form.css');
						
					$this->view_builder->build();
				}
				else
				{
					redirect('design_std/form');
				}
	}

	function result_table()
	{
		$result_table_view['table_data'] = array(
		0 => array(
				'Column 1' => 'posuere at,',
				'Column 2' => '(702) 884-2226',
				'Column 3' => 'tortor'
				),
				1 => array(
				'Column 1' => 'mus. Proin',
				'Column 2' => '(330) 567-8403',
				'Column 3' => 'risus.'
				),
				2 => array(
				'Column 1' => 'vitae, sodales',
				'Column 2' => '(256) 253-0531',
				'Column 3' => 'orci.'
				),
				3 => array(
				'Column 1' => 'dui. Cum',
				'Column 2' => '(667) 108-6226',
				'Column 3' => 'Curae'
				),
				4 => array(
				'Column 1' => 'Integer sem',
				'Column 2' => '(454) 489-7044',
				'Column 3' => 'Praesent'
				),
				5 => array(
				'Column 1' => 'sed tortor.',
				'Column 2' => '(725) 136-7924',
				'Column 3' => 'pede'
				),
				6 => array(
				'Column 1' => 'arcu. Vestibulum',
				'Column 2' => '(365) 622-5689',
				'Column 3' => 'nisi'
				),
				7 => array(
				'Column 1' => 'montes, nascetur',
				'Column 2' => '(887) 328-2017',
				'Column 3' => 'lorem'
				),
				8 => array(
				'Column 1' => 'convallis est,',
				'Column 2' => '(484) 634-8613',
				'Column 3' => 'amet'
				),
				9 => array(
				'Column 1' => 'egestas, urna',
				'Column 2' => '(670) 461-0597',
				'Column 3' => 'eu'
				)
				);

				$this->view_builder->tab_title .= " > Design Standards > Result Table";
				$this->view_builder->add_view('design_std/action_view', array());
				$this->view_builder->add_view('sec_cont_open_view');
				$this->view_builder->add_view('design_std/result_table_view', $result_table_view);
				$this->view_builder->add_view('sec_cont_close_view');

				$this->view_builder->build();
	}
}

/* End of file design_std.php */
/* Location: /ctm_cma/controllers/design_std.php */