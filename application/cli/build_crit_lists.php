<?php
/*
 * Builds outlet/article lists for contract/element combinations
 * 
 */
echo "CTM/CMA Build Criteria List Log\nStart Time: ".date('Y-m-d G:i:s T')."\n\n";

include_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/../libraries/Db2.php';

// Makes sure environment script can only be included, not directly accessed
define('SECURE',TRUE);

include_once dirname(dirname(dirname($argv[0]))).'/env.php';
include_once pathinfo(__FILE__, PATHINFO_DIRNAME).'/../config/'.ENVIRONMENT.'/settings.php';
include_once '/usr/local/zendsvr/share/ZendFramework/library/Zend/Console/Getopt.php';

if (! defined('APP_SCHEMA')) 
{
	echo "failed to get settings\n";
	exit(1);
}

// Setup cli options capture
try {
	$opts = new Zend_Console_Getopt(
		array(
			'app|a=s' => 'Application',
			'cntrct_id|c=i' => 'Contract Identifier',
			'elem_id|e=i' => 'Element Identifier',
			'verbose|v' => 'Verbose Logging',
			'help|h' => 'Display Help'
		)
	);
	
	$opts->setHelp(
		array(
			'a' => "Application: CTM > 'CT', CMA > 'CM', BILLBACKS > 'SC'",
			'c' => "Contract Identifier",
			'e' => "Element Identifier",
			'v' => "Verbose logging",
			'h' => "Displays this help screen"	
		)
	);
	
	// grab options
	$opts->parse();
	
} catch (Zend_Console_Getopt_Exception $e) {
	
	// found bad options, let user know.
	echo $e->getUsageMessage()."\n\n";
	
	exit(1);
}

if ($opts->h) {
	echo $opts->getUsageMessage()."\n\n";
	exit(0);
}


// setup db2 connection
try {
	$db = new Db2($config['db']);
}
catch (Exception $e)
{
	echo "Database Connection Error:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

// parse options
$cntrct_id = $opts->c;
$elem_id = $opts->e;
$app = $opts->a;

// Get list of contracts and elements
try {
	$params = array();	
	$preds = array();
	
	if ($cntrct_id !== NULL)
	{
		$params[] = $cntrct_id;
		$preds[] = "C.CNTRCT_ID = ?";
	}
	else // if contract not specifically provided, limit to active contracts
	{
		$params[] = date('Y');
		$preds[] = "? BETWEEN YEAR(STRT_DT) AND YEAR(END_DT)";
	}
	
	if ($cntrct_id !== NULL && $elem_id !== NULL)
	{
		$params[] = $elem_id;
		$preds[] = "ELEM_ID = ?";
	}
	
	if ($app !== NULL)
	{
		$params[] = $app;
		$preds[] = "C.APP = ?";
	}
	
	$select = "SELECT DISTINCT C.CNTRCT_ID, CE.ELEM_ID
		FROM ".APP_SCHEMA.".CNTRCT C
			JOIN ".APP_SCHEMA.".CNTRCT_ELEM CE ON CE.CNTRCT_ID = C.CNTRCT_ID
		WHERE ".implode(' AND ', $preds)."
		ORDER BY C.CNTRCT_ID, CE.ELEM_ID";
	
	$contracts = $db->query($select, $params)->fetch_object();
}
catch (Exception $e)
{
	echo "Error pulling contract/element list:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

echo "Building lists for . . . .\n";
$current_cntrct_id = 0;

foreach ($contracts as $row)
{
	// remove existing customer lists for contract/element combination
	try {
		$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_OUT
			WHERE CNTRCT_ID = ?
				AND ELEM_ID = ?";
		
		$db->query($delete, array($row->CNTRCT_ID, $row->ELEM_ID));
	} 
	catch (Exception $e)
	{
		echo "Error removing contract's customer list:\n\t".$e->getMessage()."\n\n";
		exit(1);
	}
	
	// remove existing product lists for contract/element combination
	try {
		$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_ART
			WHERE CNTRCT_ID = ?
				AND ELEM_ID = ?";
		
		$db->query($delete, array($row->CNTRCT_ID, $row->ELEM_ID));
	} 
	catch (Exception $e)
	{
		echo "Error removing contract's product list:\n\t".$e->getMessage()."\n\n";
		exit(1);
	}
	
	// get customer/product element criteria
	try {		
		$crit_select = "SELECT CASE TRIM(CRIT_FLD)
					WHEN 'BUS_TP' THEN CAST('CM' AS CHAR(2) CCSID 37)
					WHEN 'BUS_TP_EXT' THEN CAST('CM' AS CHAR(2) CCSID 37)
					ELSE CAST('PM' AS CHAR(2) CCSID 37)
				END AS MSTR,
				TRIM(CRIT_FLD) || CAST('_CD' AS CHAR(3) CCSID 37) AS CRIT_FLD, 
				TRIM(CRIT_CD) AS CRIT_CD, ACCR_FLG
			FROM ".APP_SCHEMA.".SLS_CRIT
			WHERE ELEM_ID = ?";
		
		$crit_result = $db->query($crit_select, array($row->ELEM_ID))->fetch_object();
	}
	catch (Exception $e)
	{
		echo "Error pulling contract/element criteria:\n\t".$e->getMessage()."\n\n";
		exit(1);
	}
	
	// now we need to build associative array from criteria results for processing
	$crit_assoc = array();
	
	foreach ($crit_result as $crit_row)
	{
		$crit_assoc[$crit_row->MSTR][$crit_row->CRIT_FLD][$crit_row->ACCR_FLG][] = $crit_row->CRIT_CD;
	}
	
	// build customer/product where strings with parameters
	$m_wheres['CM'] = array();
	$m_wheres['PM'] = array();
	
	$cm_params = array();
	$pm_params = array();
	
	foreach ($crit_assoc as $mstr => $fields)
	{
		foreach ($fields as $field => $accr_flgs)
		{
			foreach ($accr_flgs as $accr_flg => $crit_cds)
			{
				$crit_cnt = count($crit_cds);
				
				// set the operator (NOT IN, IN, <>, or =) for the statement and assign to $m_wheres array
				if ($accr_flg == 'Y' && $crit_cnt == 1)
				{
					$m_wheres[$mstr][] = $field." = ?";
				}
				elseif ($accr_flg == 'Y' && $crit_cnt > 1)
				{
					$m_wheres[$mstr][] = $field." IN (".substr(str_repeat('?,', $crit_cnt), 0, -1).")";
				}
				elseif ($accr_flg == 'N' && $crit_cnt == 1)
				{
					$m_wheres[$mstr][] = $field." <> ?";
				}
				elseif ($accr_flg == 'N' && $crit_cnt > 1)
				{
					$m_wheres[$mstr][] = $field." NOT IN (".substr(str_repeat('?,', $crit_cnt), 0, -1).")";
				}
				
				// add the code to the params array
				foreach ($crit_cds as $crit_cd)
				{
					if ($mstr == 'CM')
					{
						$cm_params[] = $crit_cd;
					}
					elseif ($mstr == 'PM')
					{
						$pm_params[] = $crit_cd;
					}
				}					
			}
		}
	}
	
	// get customer codes
	try {		
		$crit_select = "SELECT CAST(CASE CUST_TP
					WHEN 'OT' THEN 'OUT_ID'
					WHEN 'KA' THEN 'KEY_ACCT_CD'
					WHEN 'TG' THEN 'TRD_GRP_CD'
					ELSE ''
				END AS VARCHAR(12) CCSID 37) AS CUST_FLD,
				TRIM(CUST_CD) AS CUST_CD
			FROM ".APP_SCHEMA.".CNTRCT_CUST
			WHERE CNTRCT_ID = ?";
		
		$cust_result = $db->query($crit_select, array($row->CNTRCT_ID))->fetch_object();
	}
	catch (Exception $e)
	{
		echo "Error pulling contract customer codes:\n\t".$e->getMessage()."\n\n";
		exit(1);
	}
	
	$customers = array();
	$cust_params = array();
	foreach ($cust_result as $cust)
	{
		$customers[] = "CM.".$cust->CUST_FLD." = ?";
		$cust_params[] = $cust->CUST_CD;
	}
	
	// if no customers, move on to next contract/element
	if (count($customers) == 0)
	{
		continue;
	}
	
	// create customer list
	try {
		
		$where = '';
		if (count($m_wheres['CM']) > 0)
			$where = " AND ".implode(' AND ', $m_wheres['CM']);
			
		$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_OUT (CNTRCT_ID, ELEM_ID, OUT_ID)
			SELECT CL.CNTRCT_ID AS CNTRCT_ID, ".$row->ELEM_ID." AS ELEM_ID, OUT_ID
			FROM ".APP_SCHEMA.".CNTRCT_LOC CL
				JOIN DW.BASIS_CM CM ON CM.OUT_LOC = CL.SLS_CTR_CD
			WHERE CNTRCT_ID = ?
				AND (".implode(" OR ", $customers).")".$where;
		
		$db->query($insert, array_merge(array($row->CNTRCT_ID),$cust_params,$cm_params));
	} 
	catch (Exception $e)
	{
		echo "Error creating customer list for contract #".$row->CNTRCT_ID.", element #".$row->ELEM_ID.":\n\t".$e->getMessage()."\n\n";
		exit(1);
	}
		
	// create customer list
	try {
		$where = '';
		if (count($m_wheres['PM']) > 0)
			$where = "WHERE ".implode(' AND ', $m_wheres['PM']);
			
		$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_ART (CNTRCT_ID, ELEM_ID, ART_ID)
			SELECT DISTINCT ".$row->CNTRCT_ID." AS CNTRCT_ID, ".$row->ELEM_ID." AS ELEM_ID, ART_ID
			FROM DW.BASIS_PM ".$where;			
		
		$db->query($insert, $pm_params);
	} 
	catch (Exception $e)
	{
		echo "Error creating product list for contract #".$row->CNTRCT_ID.", element #".$row->ELEM_ID.":\n\t".$e->getMessage()."\n\n";
		exit(1);
	}
	
	if ($current_cntrct_id !== $row->CNTRCT_ID)
	{
		echo "Contract:\t#".$row->CNTRCT_ID."\n";
		$current_cntrct_id = $row->CNTRCT_ID;
	}
	
	echo "\tElement:\t#".$row->ELEM_ID."\n";	
}

echo "\nFinished\nStart Time: ".date('Y-m-d G:i:s T')."\n\n";
exit(0);