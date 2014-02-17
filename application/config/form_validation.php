<?php

$config = array(
	'design_std/form' => array(
		array(
			'field' => 'ex_name',
			'label' => 'Name Example',
			'rules' => 'required'
		),
		array(
			'field' => 'ex_desc',
			'label' => 'Description Example',
			'rules' => 'required'
		),
		array(
			'field' => 'ex_currency',
			'label' => 'Currency Example',
			'rules' => 'required'
		),
		array(
			'field' => 'ex_amount',
			'label' => 'Amount Example',
			'rules' => 'required'
		),
		array(
			'field' => 'ex_dropdown',
			'label' => 'Drop Down Example',
			'rules' => 'required'
		),
		array(
			'field' => 'ex_radio',
			'label' => 'Radio Example',
			'rules' => 'required'
		),
		array(
			'field' => 'ex_checkbx',
			'label' => 'Checkbox Example',
			'rules' => 'required'
		)
	),
	'user/login' => array(
		array(
			'field' => 'logn_nm',
			'label' => 'User Name',
			'rules' => 'required|callback__logn_nm_exists'
		),
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => 'required'
		)
	),
	'user/environment' => array(
		array(
			'field' => 'role',
			'label' => 'Role',
			'rules' => 'required'
		),
		array(
			'field' => 'app',
			'label' => 'Application',
			'rules' => 'required|callback_role_has_app'
		)
	),
	'user/setup' => array(
		array(
			'field' => 'usr_id',
			'label' => 'User ID',
			'rules' => ''
		),
		array(
			'field' => 'logn_nm',
			'label' => 'Login ID',
			'rules' => 'required'
		),
		array(
			'field' => 'fst_nm',
			'label' => 'First Name',
			'rules' => 'required'
		),
		array(
			'field' => 'lst_nm',
			'label' => 'Last Name',
			'rules' => 'required'
		),
		array(
			'field' => 'enbl',
			'label' => 'User Status',
			'rules' => 'required'
		),
		array(
			'field' => 'sls_ctr_cd',
			'label' => 'Location',
			'rules' => 'required'
		)
	),
	'role/setup' => array(
		array(
			'field' => 'role_nm',
			'label' => 'Name',
			'rules' => 'required'
		),
		array(
			'field' => 'role_desc',
			'label' => 'Description',
			'rules' => 'required'
		),
		array(
			'field' => 'app[]',
			'label' => 'Apps',
			'rules' => 'required'
		),
		array(
			'field' => 'perm[]',
			'label' => 'Permissions',
			'rules' => ''
		),
		array(
			'field' => 'sls_ctr_cd[]',
			'label' => 'Locations',
			'rules' => 'required'
		),
		array(
			'field' => 'cntrct_id[]',
			'label' => 'Contracts',
			'rules' => ''
		)
	),
	'element/setup_info' => array(
		array(
			'field' => 'elem_nm',
			'label' => 'Name',
			'rules' => 'required'
		),
		array(
			'field' => 'elem_desc',
			'label' => 'Description',
			'rules' => 'max_length[140]'
		),
		array(
			'field' => 'elem_rt',
			'label' => 'Rate',
			'rules' => 'required|is_currency'
		),
		array(
			'field' => 'app',
			'label' => 'Application',
			'rules' => 'required'
		),
		array(
			'field' => 'on_inv_flg',
			'label' => 'On Invoice',
			'rules' => 'required'
		)
	),
	'element/setup_rules' => array(
		array(
			'field' => 'elem_tp',
			'label' => 'Rate Type',
			'rules' => 'required'
		),
		array(
			'field' => 'unt_div',
			'label' => 'Unit Divider',
			'rules' => 'numeric'
		),
		array(
			'field' => 'elem_trgt',
			'label' => 'Rate Target',
			'rules' => 'required'
		),
		array(
			'field' => 'elem_trigr',
			'label' => 'Rate Trigger',
			'rules' => 'required'
		),
		array(
			'field' => 'pct',
			'label' => '% of Last Year',
			'rules' => 'numeric'
		),
		array(
			'field' => 'pymt_lmt',
			'label' => 'Pay Limit',
			'rules' => 'numeric'
		),
		array(
			'field' => 'shr',
			'label' => 'Share',
			'rules' => 'numeric'
		),
		array(
			'field' => 'cse_thld',
			'label' => 'Case Threshold',
			'rules' => 'numeric'
		)
	),
	'element/setup_criteria' => array(
		array(
			'field' => 'crit_fld',
			'label' => 'Sales Criteria Field',
			'rules' => 'required'
		),
		array(
			'field' => 'crit_cd',
			'label' => 'Sales Criteria Code',
			'rules' => ''
		),
		array(
			'field' => 'crit_accr_flg',
			'label' => 'Sales Criteria Accrual Flag',
			'rules' => ''
		)
	),
	'program/setup' => array(
		array(
			'field' => 'pgm_nm',
			'label' => 'Program Name',
			'rules' => 'required'
		),
		array(
			'field' => 'elem_id[]',
			'label' => 'Assigned Elements',
			'rules' => 'callback__assigned_elements'
		)
	),
	'contract/setup_info' => array(
		array(
			'field' => 'cntrct_nm',
			'label' => 'Contract Name',
			'rules' => 'required'
		),
		array(
			'field' => 'cse_tp',
			'label' => 'Case Type',
			'rules' => 'required'
		),
		array(
			'field' => 'strt_dt',
			'label' => 'Contract Start',
			'rules' => 'required'
		),
		array(
			'field' => 'end_dt',
			'label' => 'Contract End',
			'rules' => 'required'
		),
		array(
			'field' => 'appr_lst_id',
			'label' => 'Approval List',
			'rules' => ''
		),
		array(
			'field' => 'vend_no',
			'label' => 'Vendor #',
			'rules' => ''
		)
	),
	'contract/setup_locations' => array(
		array(
			'field' => 'sls_ctr_cd[]',
			'label' => 'Locations',
			'rules' => 'required'
		)
	),
	'contract/setup_customer' => array(
		array(
			'field' => 'cust_cd[]',
			'label' => 'Customers',
			'rules' => 'required'
		),
		array(
			'field' => 'cust_tp[]',
			'label' => 'Customer Type',
			'rules' => 'required'
		)
	),
	'contract/setup_funding' => array(
		array(
			'field' => 'element',
			'label' => 'Funding element',
			'rules' => 'required'
		)
	),
	'contract/setup_user' => array(
		array(
			'field' => 'usr_id[]',
			'label' => 'Users',
			'rules' => ''
		)
	),
	'collection/setup' => array(
		array(
			'field' => 'crit_clctn_nm',
			'label' => 'Collection Name',
			'rules' => 'required'
		),
		array(
			'field' => 'crit_fld[]',
			'label' => 'Criteria',
			'rules' => 'required'
		)
	),
	'note/add_edit' => array(
		array(
			'field' => 'body',
			'label' => 'Note Text',
			'rules' => 'required|max_length[512]'
		),
		array(
			'field' => 'file',
			'label' => 'File',
			'rules' => ''
		)
	),
    
    'approval/setup_approval' => array(
		array(
			'field' => 'usr_lst[]',
			'label' => 'Approval User List',
			'rules' => 'required'
		)
	)
    
    
    
    
    
    
    
);
/* End of file form_validation.php */
/* Location: /ctm_cma/config/form_validation.php */