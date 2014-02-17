<?php

/* Application Codes */
$config['code'] = array(
	'app' => array(
		'CT' => 'CTM',
		'CM' => 'CMA',
		'SC' => 'Scanbacks',
		'CF' => 'CCF'
	),
	'gl' => array(
		'CT' => '214020',
		'CM' => '214010',
		'SC' => '',
		'CF' => '214025'
	)
);

/* End Application Codes */


$config['app_nav_list'] = array(
	'CT' => array(
		'contract' => 'Contracts',
		'report' => 'Reports',
		'user' => 'Users'
		),
	'CM' => array(
		'contract' => 'Contracts',
		'check' => 'Check Requests',
		'report' => 'Reports',
		'user' => 'Users'
		),
	'SC' => array(
		'contract' => 'Contracts',
		'report' => 'Reports',
		'user' => 'Users'
		),
	'CF' => array(
		'contract' => 'Contracts',
		'report' => 'Reports',
		'user' => 'Users'
		)
);

/*
 * Database app schema
 */
define('APP_SCHEMA', 'CTMCMA');

$config['db'] = array(
	'db' => 'SOLUTION',
	'usr' => 'dw99cma',
	'pwd' => 'cma@2011',
	'opts' => array('i5_lib' => 'CTMCMA','i5_libl' => 'CTMCMA')
);

$config['ldap'] = array(
	'host' => 'ccdcserv.centralnt.msft',
	'port' => 389,
	'dn' => 'dc=centralnt,dc=msft',
	'logn_nm' => 'samaccountname',
	'frst_nm' => 'givenname',
	'lst_nm' => 'sn',
	'base_filter' => array(
		'objectCategory' => 'CN=Person,CN=Schema,CN=Configuration,DC=centralnt,DC=msft',
		'objectClass' => 'user'
	),
	'bind_usr' => 'ldapint',
	'bind_pass' => 'Coke11*',
	'bind_pfx' => 'Centralnt\\'
);
/* End of file settings.php */
/* Location: ctm_cma/config/settings.php */