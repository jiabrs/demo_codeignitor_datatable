<?php
/**
 * Class Customer
 * 
 *   
 * @package ctm_cma
 * @subpackage controllers
 *
 */
class Customer extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		
	}
	
	/**
	 * Ajax only search utility for adding customers
	 * to contract
	 */
	function advanced_search()
	{
		if (is_ajax())
		{
			$this->load->model('customer_model');
				
			// get search criteria
			$customer = $this->input->post('customer');
			$search_for = $this->input->post('search_for');
			$bustype = $this->input->post('bustype');
			$cust_return = $this->input->post('cust_return');
			$locations = $this->input->post('sls_ctr_cd');
			
			// run search
			$customers = $this->customer_model->search($customer, $search_for, $locations, $cust_return, $bustype);
			
			$cust_list = array();
			
			// format for input select
			foreach ($customers as $row)
			{
				$cust_list[$row->CODE] = $row->NAME.' ('.$cust_return.': '.$row->CODE.')';
			}
			
			echo json_encode($cust_list);
		}
	}
	
	/*
	 * Ajax only "quick" customer add for contract customer setup
	 */
	function cust_quick_add()
	{
		if (is_ajax())
		{
			$this->load->model('customer_model');
			
			$quick_add = $this->input->post('quick_add');
			
			$cust_lst = explode(',', $quick_add);
			
			$customers = array();
			
			foreach ($cust_lst as $cust_cd)
			{
				switch (strlen(trim($cust_cd)))
				{
					case 4:
						$cust_tp = 'TG';
						break;
					case 5:
						$cust_tp = 'KA';
						break;
					case 9:
						$cust_tp = 'OT';
						break;
				}
				
				$customer = $this->customer_model->get(trim($cust_cd), $cust_tp);
				
				if ($customer->get_cust_nm() != '')
				{
					$customers[] = array(
						'cust_cd' => $customer->get_cust_cd(),
						'cust_nm' => $customer->get_cust_nm(),
						'cust_tp' => $customer->get_cust_tp()
					);
				}
			}
			
			echo json_encode($customers);
		}
	}
}

/* End of file customer.php */
/* Location: /ctm_cma/controllers/customer.php */