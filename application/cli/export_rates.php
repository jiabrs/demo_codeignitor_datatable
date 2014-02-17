<?php
/*
 * Sales interface for CTM/CMA
 * 
 */
echo "CTM/CMA Export Post Rates Log\nStart Time: ".date('Y-m-d G:i:s T')."\n\n";

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
			'year|y=i' => 'Accrual Year',
			'app|a=s' => 'Application',
			'verbose|v' => 'Verbose Logging',
			'help|h' => 'Display Help'
		)
	);
	
	$opts->setHelp(
		array(
			'y' => "Accrual Year: YYYY",
			'a' => "Application",
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

$apps = array();

foreach (explode(',', $opts->a) as $app)
{
	if (in_array($app, array_keys($config['code']['app'])))
	{
		$apps[] = $app;
	}
}

if (count($apps) == 0)
{
	echo "Application required:\n\n".$opts->getUsageMessage()."\n\n";
	exit(1);
}

echo "Processing applications ";

foreach ($apps as $app)
{
	echo $config['code']['app'][$app].", ";
}

echo "\n\n";

// setup db2 connection
try {
	$db = new Db2($config['db']);;
	
}
catch (Exception $e)
{
	echo "Database Connection Error:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

// calculate date based on options
if ($opts->y === NULL)
{
	$year = date('Y');
}
else 
{
	$year = $opts->y;
}

echo "Processing ".$year."\n\n";

echo "Clearing temporary file\n\n";

/*
 * Clear out the temporary PST_INV_SLS file
 */
try {
	$delete = "DELETE FROM ".APP_SCHEMA.".PST_INV_SLS";
	
	$db->simple_query($delete);
}
catch (Exception $e)
{
	echo "Error clearing temp file:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

echo "Building rates\n\n";

// populate temporary table
try {
	$insert = "INSERT INTO ".APP_SCHEMA.".PST_INV_SLS (
			TXN_NO, TXN_NO_SUFX, ART_ID, ART_SLS_TP_ID, MACH_NO, OUT_ID, DT,
			APP, PST_INV_AMT, LST_UPDT
		)
		SELECT TXN_NO, TXN_NO_SUFX, S.ART_ID, ART_SLS_TP_ID, S.MACH_NO, S.OUT_ID, S.STLMNT_DT, C.APP,
			SUM(
				DECIMAL(
					ROUND(
						DECIMAL(
							ROUND(DECIMAL(S.CSE_VOL, 15, 6) * DECIMAL(CASE WHEN C.CSE_TP = 'CV' THEN PM.EQUIV_CNVRT_FCTR ELSE 1 END, 15, 6), 4)
						, 13, 4) 
						* DECIMAL(ROUND(R.ACCR_AMT / R.CSE_VOL, 4), 13, 4)
					, 4)
				, 13, 4)
			) AS PST_INV_CTM_FDG_AMT,
			CURRENT_TIMESTAMP
		FROM DW.ON_INV_SLS S
			JOIN DW.DT DT ON DT.DT = S.STLMNT_DT
			JOIN DW.BASIS_PM PM ON PM.ART_ID = S.ART_ID
				AND S.STLMNT_DT BETWEEN PM.EFF_FRM_DT AND PM.EFF_TO_DT
			JOIN DW.CM AS CM ON CM.OUT_ID = S.OUT_ID
			JOIN ".APP_SCHEMA.".CNTRCT_OUT O ON O.OUT_ID = S.OUT_ID
			JOIN ".APP_SCHEMA.".CNTRCT_ART A ON A.ART_ID = S.ART_ID
				AND A.CNTRCT_ID = O.CNTRCT_ID
				AND A.ELEM_ID = O.ELEM_ID
			JOIN ".APP_SCHEMA.".ACCR R ON S.STLMNT_DT BETWEEN R.STRT_DT AND R.END_DT
				AND R.CNTRCT_ID = O.CNTRCT_ID
				AND R.ELEM_ID = O.ELEM_ID
				AND R.SLS_CTR_CD = CM.OUT_LOC
			JOIN ".APP_SCHEMA.".CNTRCT C ON C.CNTRCT_ID = R.CNTRCT_ID
			JOIN ".APP_SCHEMA.".ELEM E ON E.ELEM_ID = R.ELEM_ID
		WHERE DT.YR = ?
			AND E.ON_INV_FLG = 'N'
			AND C.APP IN ('".implode("','", $apps)."')
			AND R.CSE_VOL <> 0
			AND R.ACCR_AMT <> 0
		GROUP BY TXN_NO, TXN_NO_SUFX, S.ART_ID, ART_SLS_TP_ID, S.MACH_NO, S.OUT_ID, S.STLMNT_DT, C.APP";
	
	$db->query($insert, array($year));
}
catch (Exception $e)
{
	echo "Error populating temporary table:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

echo "Setting orphaned records to zero\n\n";

$db->begin_transaction();

/*
 * Any records in PST_INV_SLS that do not exist
 * in the temporary table for the current year 
 * are set to $0.00 for all three fields
 */
try {
	$update = "UPDATE DW.PST_INV_SLS P
			SET PST_INV_AMT = 0
		WHERE P.APP IN ('".implode("','", $apps)."')
			AND NOT EXISTS (
				SELECT 1 FROM ".APP_SCHEMA.".PST_INV_SLS T
				WHERE T.TXN_NO = P.TXN_NO
					AND T.TXN_NO_SUFX = P.TXN_NO_SUFX
					AND T.ART_ID = P.ART_ID
					AND T.ART_SLS_TP_ID = P.ART_SLS_TP_ID
					AND T.MACH_NO = P.MACH_NO
					AND T.APP = P.APP
				)
			AND DT IN (SELECT DT FROM DW.DT WHERE YR = ?)";
	
	$db->query($update, array($year));
}
catch (Exception $e)
{
	$db->rollback();
	echo "Error zeroing out orphaned records:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

echo "Updating changed records\n\n";

/*
 * Update any records in PST_INV_SLS whose 
 * values do not match the corresponding values in 
 */
try {
	$update = "UPDATE DW.PST_INV_SLS P
			SET PST_INV_AMT = (SELECT PST_INV_AMT FROM ".APP_SCHEMA.".PST_INV_SLS T
					WHERE T.TXN_NO = P.TXN_NO
						AND T.TXN_NO_SUFX = P.TXN_NO_SUFX
						AND T.ART_ID = P.ART_ID
						AND T.ART_SLS_TP_ID = P.ART_SLS_TP_ID
						AND T.MACH_NO = P.MACH_NO
						AND T.APP = P.APP
					)
		WHERE P.APP IN ('".implode("','", $apps)."')
			AND PST_INV_AMT <> (
				SELECT PST_INV_AMT FROM ".APP_SCHEMA.".PST_INV_SLS T
				WHERE T.TXN_NO = P.TXN_NO
					AND T.TXN_NO_SUFX = P.TXN_NO_SUFX
					AND T.ART_ID = P.ART_ID
					AND T.ART_SLS_TP_ID = P.ART_SLS_TP_ID
					AND T.MACH_NO = P.MACH_NO
					AND T.APP = P.APP
				)";
	
	$db->simple_query($update);
}
catch (Exception $e)
{
	$db->rollback();
	echo "Error updating changed records:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

echo "Inserting new records\n\n";

/*
 * Insert any new records into PST_INV_SLS
 */
try {
	$insert = "INSERT INTO DW.PST_INV_SLS (
			TXN_NO, TXN_NO_SUFX, ART_ID, ART_SLS_TP_ID, MACH_NO, OUT_ID, DT, APP,
			PST_INV_AMT
		)
		SELECT TXN_NO, TXN_NO_SUFX, ART_ID, ART_SLS_TP_ID, MACH_NO, OUT_ID, DT, APP,
			PST_INV_AMT
		FROM ".APP_SCHEMA.".PST_INV_SLS T
		WHERE T.APP IN ('".implode("','", $apps)."')
			AND NOT EXISTS (
			SELECT 1
			FROM DW.PST_INV_SLS P
			WHERE P.TXN_NO = T.TXN_NO
				AND P.TXN_NO_SUFX = T.TXN_NO_SUFX
				AND P.ART_ID = T.ART_ID
				AND P.ART_SLS_TP_ID = T.ART_SLS_TP_ID
				AND P.MACH_NO = T.MACH_NO
				AND P.APP = T.APP
		)";
	
	$db->simple_query($insert);
}
catch (Exception $e)
{
	$db->rollback();
	echo "Error inserting new records:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

$db->commit();

echo "Clearing temporary file\n\n";

/*
 * Clear out the temporary PST_INV_SLS file
 */
try {
	$delete = "DELETE FROM ".APP_SCHEMA.".PST_INV_SLS";
	
	$db->simple_query($delete);
}
catch (Exception $e)
{
	echo "Error clearing temp file:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

echo "\nFinished\nStart Time: ".date('Y-m-d G:i:s T')."\n\n";
exit(0);