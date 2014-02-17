<?php
/*
 * Sales interface for CTM/CMA
 * 
 */
echo "CTM/CMA Pull Sales Log\nStart Time: ".date('Y-m-d G:i:s T')."\n\n";

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
			'cntrct_id|c=i' => 'Contract Identifier',
			'elem_id|e=i' => 'Element Identifier',
			'verbose|v' => 'Verbose Logging',
			'help|h' => 'Display Help'
		)
	);
	
	$opts->setHelp(
		array(
			'c' => "Limit to Contract Identifier",
			'e' => "Limit to Element Identifier",
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

/*
 * Parse command line
 */
$cntrct_id = $opts->c;
$elem_id = $opts->e;

// setup Db2 connection
try {
	$db = new Db2($config['db']);
}
catch (Exception $e)
{
	echo "Database Connection Error:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

// Get list of contracts and elements
try {
	$where = '';
	$params = $preds = array();
	
	if ($cntrct_id !== NULL)
	{
		$params[] = $cntrct_id;
		$preds[] = "C.CNTRCT_ID = ?";
	}
	
	if ($cntrct_id !== NULL && $elem_id !== NULL)
	{
		$params[] = $elem_id;
		$preds[] = "CE.ELEM_ID = ?";
	}
	
	if (count($params) > 0)
		$where .= implode(' AND ', $preds);
	
	if ($where != '')
		$where = 'WHERE '.$where;
		
	/*
	 * We're selecting contracts whose details have changes sinced their
	 * sales were pulled and accruals were calculated. We're also pulling 
	 * contracts that are missing sales or whose sales are not current
	 * to last month.
	 */
	$select = "SELECT T.CNTRCT_ID, T.ELEM_ID, 
			CAST(CASE 
				WHEN S.LST_UPDT IS NULL THEN 'R'
				WHEN T.LST_UPDT > S.LST_UPDT THEN 'R'
				ELSE 'U' -- NEED TO UPDATE SALES
			END AS CHAR(1) CCSID 37) AS ACTION,
			(12 * YEAR(CURRENT_DATE) + MONTH(CURRENT_DATE)) -	(12 * YEAR(S.LST_UPDT) + MONTH(S.LST_UPDT)) AS UPDT_MTHS
		FROM (
			-- REVERSE PIVOT EACH LAST CHANGE DATE AND GET MOST RECENT 
			-- CHANGE TO COMPARE WITH SALES
			SELECT T.CNTRCT_ID, T.ELEM_ID, MAX(T2.LST_UPDT) AS LST_UPDT
			FROM (
				-- PULL ALL CONTRACT PIECES THAT MAY AFFECT SALES
				-- AND GET THE MAXIMUM LAST CHANGE DATE
				SELECT C.CNTRCT_ID, CE.ELEM_ID, 
					MAX(C.LST_UPDT) AS CNTRCT_LST_UPDT,
					MAX(CE.LST_UPDT) AS CNTRCT_ELEM_LST_UPDT,
					MAX(CED.LST_UPDT) AS CNTRCT_ELEM_DT_LST_UPDT,
					MAX(CC.LST_UPDT) AS CNTRCT_CUST_LST_UPDT,
					MAX(CL.LST_UPDT) AS CNTRCT_LOC_LST_UPDT,
					MAX(E.LST_UPDT) AS ELEM_LST_UPDT,
					MAX(SC.LST_UPDT) AS SLS_CRIT_LST_UPDT
				FROM ".APP_SCHEMA.".CNTRCT C
					JOIN ".APP_SCHEMA.".CNTRCT_ELEM CE ON CE.CNTRCT_ID = C.CNTRCT_ID
					JOIN ".APP_SCHEMA.".CNTRCT_ELEM_DT CED ON CED.CNTRCT_ID = C.CNTRCT_ID
						AND CED.ELEM_ID = CE.ELEM_ID
					JOIN ".APP_SCHEMA.".CNTRCT_CUST CC ON CC.CNTRCT_ID = C.CNTRCT_ID
					JOIN ".APP_SCHEMA.".CNTRCT_LOC CL ON CL.CNTRCT_ID = C.CNTRCT_ID
					JOIN ".APP_SCHEMA.".ELEM E ON E.ELEM_ID = CE.ELEM_ID
					JOIN ".APP_SCHEMA.".SLS_CRIT SC ON SC.ELEM_ID = CE.ELEM_ID ".$where."				
				GROUP BY C.CNTRCT_ID, CE.ELEM_ID
				ORDER BY C.CNTRCT_ID, CE.ELEM_ID
			) AS T, 
				TABLE(
					VALUES 
						('CNTRCT', T.CNTRCT_LST_UPDT), 
						('CNTRCT_ELEM', T.CNTRCT_ELEM_LST_UPDT),
						('CNTRCT_ELEM_DT', T.CNTRCT_ELEM_DT_LST_UPDT),
						('CNTRCT_CUST', T.CNTRCT_CUST_LST_UPDT),
						('CNTRCT_LOC', T.CNTRCT_LOC_LST_UPDT),
						('ELEM', T.ELEM_LST_UPDT),
						('SLS_CRIT', T.SLS_CRIT_LST_UPDT)
				) AS T2(TBL, LST_UPDT)
			GROUP BY T.CNTRCT_ID, T.ELEM_ID
		) AS T
			-- GET THE OLDEST LAST UPDATE DATE BY CNTRCT_ID
			-- AND ELEM_ID TO COMPARE WITH CHANGE DATES IN
			-- CONTRACT
			LEFT JOIN (
				-- GET THE OLDEST DATE
				SELECT CNTRCT_ID, ELEM_ID, MIN(LST_UPDT_TM) AS LST_UPDT
				FROM ".APP_SCHEMA.".CNTRCT_SLS
				GROUP BY CNTRCT_ID, ELEM_ID
				ORDER BY CNTRCT_ID, ELEM_ID
			) AS S ON S.CNTRCT_ID = T.CNTRCT_ID
				AND S.ELEM_ID = T.ELEM_ID
		WHERE T.LST_UPDT > S.LST_UPDT
			OR S.CNTRCT_ID IS NULL
			OR S.LST_UPDT < CURRENT_TIMESTAMP - (DAY(CURRENT_TIMESTAMP) - 1) DAYS - MIDNIGHT_SECONDS(CURRENT_TIMESTAMP) SECONDS
		ORDER BY T.CNTRCT_ID, T.ELEM_ID";
	
	$contracts = $db->query($select, $params)->fetch_object();
}
catch (Exception $e)
{
	echo "Error pulling contract/element list:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

$curr_cntrct_id = 0;
$btch_job_id = 0;

foreach ($contracts as $row)
{
	// commit on each contract
	if ($curr_cntrct_id != $row->CNTRCT_ID)
	{
		if ($curr_cntrct_id != 0)
		{			
			$db->commit();
			
			// update batch job entry
			$update = "UPDATE ".APP_SCHEMA.".CNTRCT_BTCH_JOB
				SET JOB_STS = '1',
					JOB_MSG = 'Sales pull complete'
				WHERE JOB_ID = ?";
			
			$db->query($update, array($btch_job_id));
		}
		
		echo "Pulling sales for contract\t#".$row->CNTRCT_ID."\n";
		
		$curr_cntrct_id = $row->CNTRCT_ID;
		
		// put a job entry into the batch job table		
		$insert = "SELECT * FROM FINAL TABLE (
			INSERT INTO ".APP_SCHEMA.".CNTRCT_BTCH_JOB (CNTRCT_ID, JOB_STS, JOB_NM, JOB_MSG)
			VALUES (?, ?, ?, ?))";
		
		$result = $db->query($insert, array($row->CNTRCT_ID, '0', 'Pull Sales', 'Sales Pull Started'))->fetch_object();
		
		$btch_job_id = $result[0]->JOB_ID;
		
		$db->begin_transaction();		
		
	}
	
	echo "\tElement\t#".$row->ELEM_ID."\n";
	
	// rebuild criteria list for contract/element
	if ($row->ACTION == 'R')
	{
		echo "\t\tRebuilding criteria lists . . . ";

		$cmd = '/usr/bin/php-cli -q '.dirname(dirname(dirname($argv[0]))).'/application/cli/build_crit_lists.php';
		
		$cmd .= ' -c '.$row->CNTRCT_ID;
			
		$cmd .= ' -e '.$row->ELEM_ID;
		
		$output = array();
		$pgm_state = 0;
		
		exec($cmd, $output, $pgm_state);
		
		if ($pgm_state == 0)
		{
			echo "success\n";
		}
		else 
		{
			echo "error:\n".implode("\n", $output)."\n\n";
			exit(1);
		}	
	}	
	
	echo "\t\tRemoving existing sales . . . ";
	
	// remove existing sales for contract/element/fiscal period combination
	try {
		$delete = "DELETE FROM ".APP_SCHEMA.".CNTRCT_SLS
			WHERE CNTRCT_ID = ?
				AND ELEM_ID = ?";
		
		if ($row->ACTION == 'U') // just update previous months sales
		{
			$delete .= " AND (
					STLMNT_DT BETWEEN 
						CURRENT_DATE - (DAY(CURRENT_DATE) - 1) DAYS - ".$row->UPDT_MTHS." MONTHS - 1 YEARS 
						AND 
						CURRENT_DATE - DAY(CURRENT_DATE) DAYS - 1 YEARS 
					OR 
					STLMNT_DT BETWEEN 
						CURRENT_DATE - (DAY(CURRENT_DATE) - 1) DAYS - ".$row->UPDT_MTHS." MONTHS
						AND
						CURRENT_DATE - DAY(CURRENT_DATE) DAYS
						
				)";
		}
		
		$params = array(
			$row->CNTRCT_ID,
			$row->ELEM_ID
		);
		
		$db->query($delete, $params);
	}
	catch (Exception $e)
	{
		$db->rollback();
		
		// update batch job entry
		$update = "UPDATE ".APP_SCHEMA.".CNTRCT_BTCH_JOB
			SET JOB_STS = '2',
				JOB_MSG = 'Error removing sales'
			WHERE JOB_ID = ?";
		
		$db->query($update, array($btch_job_id));
		
		echo "Error removing existing sales:\n\t".$e->getMessage()."\n\n";
		exit(1);
	}
	
	echo "success\n\t\tInserting new sales . . . ";
	
	if ($row->ACTION == 'R')
	{
		$where = "WHERE STLMNT_DT <= CURRENT_DATE - DAY(CURRENT_DATE) DAYS";
	}
	else {
		$where = "WHERE STLMNT_DT BETWEEN 
				CURRENT_DATE - (DAY(CURRENT_DATE) - 1) DAYS - ".$row->UPDT_MTHS." MONTHS - 1 YEARS 
				AND 
				CURRENT_DATE - DAY(CURRENT_DATE) DAYS - 1 YEARS 
			OR 
			STLMNT_DT BETWEEN 
				CURRENT_DATE - (DAY(CURRENT_DATE) - 1) DAYS - ".$row->UPDT_MTHS." MONTHS
				AND
				CURRENT_DATE - DAY(CURRENT_DATE) DAYS";
	}
	
	// insert new sales
	try {
		$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_SLS (
				CNTRCT_ID, ELEM_ID, SLS_CTR_CD, STLMNT_DT, CSE_VOL, NET_SELL_AMT, LST_UPDT_TM, MAN_SLS_FLG
			)
			WITH C AS (
				SELECT C.CNTRCT_ID, CED.ELEM_ID, C.CSE_TP, MIN(CED.STRT_DT - 1 YEARS) AS MIN_DT, MAX(CED.END_DT) AS MAX_DT
				FROM ".APP_SCHEMA.".CNTRCT C
					JOIN ".APP_SCHEMA.".CNTRCT_ELEM_DT CED ON CED.CNTRCT_ID = C.CNTRCT_ID
				WHERE C.CNTRCT_ID = ?
					AND CED.ELEM_ID = ?
				GROUP BY C.CNTRCT_ID, CED.ELEM_ID, C.CSE_TP
			)
			SELECT C.CNTRCT_ID, C.ELEM_ID, CM.OUT_LOC AS SLS_CTR_CD, S.STLMNT_DT, 
				SUM(
					CASE C.CSE_TP
						WHEN 'CV' THEN DECIMAL(ROUND(DECIMAL(CSE_VOL, 15, 6) * DECIMAL(PM.EQUIV_CNVRT_FCTR, 15, 6), 4), 13, 4)
						WHEN 'PH' THEN CSE_VOL
						ELSE 0
					END 
				) AS CSE_VOL,
				SUM(WHSLE_AMT + UPCHRG_AMT + TX_LBLTY_AMT - CUST_COMSN_AMT + DISC_AMT + ON_INV_CTM_FDG_AMT + ON_INV_CMA_FDG_AMT) AS NET_SELL_AMT, 
				CURRENT_TIMESTAMP,
				'N'
			FROM C
				JOIN DW.ON_INV_SLS S ON S.STLMNT_DT BETWEEN C.MIN_DT AND C.MAX_DT
				JOIN ".APP_SCHEMA.".CNTRCT_OUT CO ON CO.CNTRCT_ID = C.CNTRCT_ID
					AND CO.ELEM_ID = C.ELEM_ID
					AND CO.OUT_ID = S.OUT_ID
				JOIN ".APP_SCHEMA.".CNTRCT_ART CA ON CA.CNTRCT_ID = C.CNTRCT_ID
					AND CA.ELEM_ID = C.ELEM_ID
					AND CA.ART_ID = S.ART_ID
				JOIN DW.BASIS_PM PM ON PM.ART_ID = S.ART_ID 
					AND S.STLMNT_DT BETWEEN PM.EFF_FRM_DT AND PM.EFF_TO_DT
				JOIN DW.BASIS_CM CM ON CM.OUT_ID = S.OUT_ID ".$where."
			GROUP BY C.CNTRCT_ID, C.ELEM_ID, CM.OUT_LOC, S.STLMNT_DT
			ORDER BY C.CNTRCT_ID, C.ELEM_ID, CM.OUT_LOC, S.STLMNT_DT";
		
		$params = array(
			$row->CNTRCT_ID,
			$row->ELEM_ID
		);
		
		$db->query($insert, $params);
	}
	catch (Exception $e)
	{
		$db->rollback();
		
		// update batch job entry
		$update = "UPDATE ".APP_SCHEMA.".CNTRCT_BTCH_JOB
			SET JOB_STS = '2',
				JOB_MSG = 'Error inserting new sales'
			WHERE JOB_ID = ?";
		
		$db->query($update, array($btch_job_id));
		
		echo "Error inserting new sales:\n\t".$e->getMessage()."\n\n";
		exit(1);
	}
	
	/*
	 * Some contract elements will not catch any sales. To prevent sales pull
	 * from continually processing these elements, insert a dummy sales record
	 */
	try {
		$insert = "INSERT INTO ".APP_SCHEMA.".CNTRCT_SLS (
				CNTRCT_ID, ELEM_ID, SLS_CTR_CD, STLMNT_DT, CSE_VOL, NET_SELL_AMT, LST_UPDT_TM, MAN_SLS_FLG
			)
			SELECT CED.CNTRCT_ID, CED.ELEM_ID, CL.SLS_CTR_CD, END_DT, 0 AS CSE_VOL, 0 AS NET_SELL_AMT, CURRENT_TIMESTAMP, 'N'
			FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT CED
				JOIN ".APP_SCHEMA.".CNTRCT_LOC CL ON CL.CNTRCT_ID = CED.CNTRCT_ID
				LEFT JOIN ".APP_SCHEMA.".CNTRCT_SLS S ON S.CNTRCT_ID = CED.CNTRCT_ID
					AND S.ELEM_ID = CED.ELEM_ID
			WHERE CED.CNTRCT_ID = ?
				AND CED.ELEM_ID= ?
				AND S.CNTRCT_ID IS NULL";
		
		$params = array(
			$row->CNTRCT_ID,
			$row->ELEM_ID
		);
		
		$db->query($insert, $params);
	}
	catch (Exception $e)
	{
		$db->rollback();
		
		// update batch job entry
		$update = "UPDATE ".APP_SCHEMA.".CNTRCT_BTCH_JOB
			SET JOB_STS = '2',
				JOB_MSG = 'Error inserting dummy sales records'
			WHERE JOB_ID = ?";
		
		$db->query($update, array($btch_job_id));
		
		echo "Error inserting dummy sales record:\n\t".$e->getMessage()."\n\n";
		exit(1);
	}
	
	echo "success\n";
}

// wrap up last contract
$db->commit();
			
// update batch job entry
$update = "UPDATE ".APP_SCHEMA.".CNTRCT_BTCH_JOB
	SET JOB_STS = '1',
		JOB_MSG = 'Sales pull complete'
	WHERE JOB_ID = ?";

$db->query($update, array($btch_job_id));
			
echo "\nRe-calculating accrual amounts\n";

$cmd = '/usr/bin/php-cli -q '.dirname(dirname(dirname($argv[0]))).'/application/cli/calc_accr.php';

// add options if provided
if ($cntrct_id !== NULL)
	$cmd .= " -c ".$cntrct_id;
	
if ($elem_id !== NULL)
	$cmd .= " -e ".$elem_id;
	
$output = array();
$pgm_state = 0;

exec($cmd, $output, $pgm_state);

if ($pgm_state == 0)
{
	echo "\nAccrual calculation successfull";
}
else 
{
	echo "\n\nError calculating accruals:\n".implode("\n", $output)."\n\n";
}	

echo "\nFinished\nEnd Time: ".date('Y-m-d G:i:s T')."\n\n";
exit(0);