<?php
/*
 * Sales interface for CTM/CMA
 * 
 */
echo "CTM/CMA Accrual Calculation Log\nStart Time: ".date('Y-m-d G:i:s T')."\n\n";

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
			'app|a=s' => 'Application',
			'elem_id|e=i' => 'Element Identifier',
			'accrual year|y=i' => 'Accrual Year',
			'verbose|v' => 'Verbose Logging',
			'help|h' => 'Display Help'
		)
	);
	
	$opts->setHelp(
		array(
			'c' => "Contract Identifier",
			'a' => 'Application',
			'e' => "Element Identifier",
			'y' => "Accrual Year: YYYY",
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

// parse the command line
$cntrct_id = $opts->c;
$elem_id = $opts->e;
$accr_yr = $opts->y;
$app = $opts->a;

// setup db2 connection
try {
	$db = new Db2($config['db']);
}
catch (Exception $e)
{
	echo "Database Connection Error:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

// create temporary table for calculation
try {
	$declare = "DECLARE GLOBAL TEMPORARY TABLE ACCR (
			CNTRCT_ID INTEGER NOT NULL,
			ELEM_ID INTEGER NOT NULL,
			ACCR_AMT DECIMAL(13, 5) NOT NULL DEFAULT 0.00000,
			CSE_VOL DECIMAL(13, 4) NOT NULL DEFAULT 0.0000,
			FIX_UNTS INTEGER NOT NULL DEFAULT 0
		) WITH REPLACE";
	
	$db->simple_query($declare);
} catch (Exception $e) {
	echo "Error declaring temporary table:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

$params = $wheres = array();

// filter temporary table by cntrct_id and elem_id if provided
if ($cntrct_id !== NULL)
{
	$params[] = $cntrct_id;
	$wheres[] = "CE.CNTRCT_ID = ?";
}

if ($elem_id !== NULL)
{
	$params[] = $elem_id;
	$wheres[] = "CE.ELEM_ID = ?";
}

$where = '';
if (count($wheres) > 0)
	$where = ' WHERE '.implode(' AND ', $wheres);
	
// populate temporary table
try {
	$insert = "INSERT INTO QTEMP.ACCR (CNTRCT_ID, ELEM_ID, ACCR_AMT, CSE_VOL, FIX_UNTS)
		SELECT CE.CNTRCT_ID, CE.ELEM_ID, 
			DECIMAL(ROUND(
				CASE ELEM_TP
					WHEN 'PC' THEN 
						CASE WHEN E.ELEM_TRIGR = 'FC' 
								OR (E.ELEM_TRIGR = 'EL' AND PCT / 100.00 * LAST_VOL <= THIS_VOL) 
								OR (E.ELEM_TRIGR = 'GL' AND PCT / 100.00 * LAST_VOL < THIS_VOL) 
								OR (E.ELEM_TRIGR = 'CT' AND THIS_VOL > CSE_THLD)
							THEN 
								CASE ELEM_TRGT
									WHEN 'AC' THEN 
										DECIMAL(ELEM_RT, 13, 5) * DECIMAL(THIS_VOL, 13, 5)
									WHEN 'NC' THEN 
										DECIMAL(ELEM_RT, 13, 5) * DECIMAL((THIS_VOL - LAST_VOL), 13, 5)
									ELSE 0
								END
							ELSE 0
						END
					WHEN 'TL' THEN 
						CASE WHEN (ELEM_TRIGR = 'CT' AND THIS_VOL > CSE_THLD) OR ELEM_TRIGR <> 'CT' 
							THEN ELEM_RT
							ELSE 0
						END 
					WHEN 'TU' THEN DECIMAL(ELEM_RT, 13, 5) * DECIMAL(COALESCE(FIX_UNTS, 0), 13, 5) / DECIMAL(CASE WHEN UNT_DIV = 0 THEN 12 ELSE UNT_DIV END, 13, 5)
					ELSE 0
				END * (DECIMAL(SHR / 100.00, 13, 5))
			, 4), 13, 4) AS ACCR_AMT, 
			THIS_VOL, COALESCE(FIX_UNTS, 0) AS FIX_UNTS
		FROM ".APP_SCHEMA.".CNTRCT_ELEM CE
			JOIN ".APP_SCHEMA.".CNTRCT C ON C.CNTRCT_ID = CE.CNTRCT_ID
			JOIN ".APP_SCHEMA.".ELEM E ON E.ELEM_ID = CE.ELEM_ID
			JOIN (
				SELECT CNTRCT_ID, ELEM_ID, SUM(THIS_VOL) AS THIS_VOL, SUM(LAST_VOL) AS LAST_VOL
				FROM (
					SELECT CED.CNTRCT_ID, CED.ELEM_ID, COALESCE(CSE_VOL, 0) AS THIS_VOL, 0 AS LAST_VOL
					FROM ".APP_SCHEMA.".CNTRCT_SLS S
						RIGHT JOIN ".APP_SCHEMA.".CNTRCT_ELEM_DT CED ON CED.CNTRCT_ID = S.CNTRCT_ID
							AND CED.ELEM_ID = S.ELEM_ID
							AND S.STLMNT_DT BETWEEN CED.STRT_DT AND CED.END_DT
					UNION ALL
					SELECT CED.CNTRCT_ID, CED.ELEM_ID, COALESCE(PRJTD_CSE_VOL, 0) AS THIS_VOL, 0 AS LAST_VOL
					FROM ".APP_SCHEMA.".CNTRCT_PRJTN S
						RIGHT JOIN ".APP_SCHEMA.".CNTRCT_ELEM_DT CED ON CED.CNTRCT_ID = S.CNTRCT_ID
							AND CED.ELEM_ID = S.ELEM_ID
							AND S.STLMNT_DT >= CED.STRT_DT
							AND S.STLMNT_DT <= CED.END_DT
							AND S.STLMNT_DT >= CURRENT_DATE - (DAY(CURRENT_DATE) - 1) DAYS
					UNION ALL
					SELECT CED.CNTRCT_ID, CED.ELEM_ID, 0 AS THIS_VOL, COALESCE(CSE_VOL, 0) AS LAST_VOL
					FROM ".APP_SCHEMA.".CNTRCT_SLS S
						RIGHT JOIN ".APP_SCHEMA.".CNTRCT_ELEM_DT CED ON CED.CNTRCT_ID = S.CNTRCT_ID
							AND CED.ELEM_ID = S.ELEM_ID
							AND S.STLMNT_DT BETWEEN CED.STRT_DT - 1 YEARS AND CED.END_DT - 1 YEARS
				) AS S
				GROUP BY CNTRCT_ID, ELEM_ID
			) AS S ON S.CNTRCT_ID = CE.CNTRCT_ID 
				AND S.ELEM_ID = CE.ELEM_ID
			LEFT JOIN (
				SELECT CFU.CNTRCT_ID, CFU.ELEM_ID, SUM(FIX_UNTS) AS FIX_UNTS
				FROM ".APP_SCHEMA.".CNTRCT_FIX_UNT CFU
					JOIN (
						SELECT DISTINCT CNTRCT_ID, ELEM_ID, YR, MTH
						FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT CED
							JOIN DW.DT DT ON DT.DT BETWEEN CED.STRT_DT AND CED.END_DT
					) AS DTS ON DTS.CNTRCT_ID = CFU.CNTRCT_ID
						AND DTS.ELEM_ID = CFU.ELEM_ID
						AND DTS.YR = CFU.YR
						AND DTS.MTH = CFU.MTH
				GROUP BY CFU.CNTRCT_ID, CFU.ELEM_ID
			) AS FU ON FU.CNTRCT_ID = CE.CNTRCT_ID
				AND FU.ELEM_ID = CE.ELEM_ID".$where;
	
	$db->query($insert, $params);
	
} catch (Exception $e) {
	echo "Error calculating accruals:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

// sets the options for all four queries
$params = $wheres = $wheres2 = array();

if ($app !== NULL)
{
	$params[] = $app;
	
	$wheres[] = "C.APP = ?";
	$wheres2[] = "APP = ?";
}

if ($cntrct_id !== NULL) 
{
	$params[] = $cntrct_id;
	
	$wheres[] = "CED.CNTRCT_ID = ?";
	$wheres2[] = "CNTRCT_ID = ?";
}

if ($elem_id !== NULL) 
{
	$params[] = $elem_id;
	
	$wheres[] = "CED.ELEM_ID = ?";
	$wheres2[] = "ELEM_ID = ?";
}

if ($accr_yr !== NULL) 
{
	$params[] = $accr_yr;
	$wheres[] = "DT.YR = ?";
	$wheres2[] = "YR = ?";
}

try {
	$delete = "DELETE FROM ".APP_SCHEMA.".ACCR";
	
	if (count($wheres2) > 0)
		$delete .= " WHERE ".implode(' AND ', $wheres2);
	
	$db->query($delete, $params);
} catch (Exception $e) {
	echo "Error removing existing accrual amounts:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

echo "Break out accrual amounts by sales center and date\n";

$where = '';
if (count($wheres) > 0)
	$where = ' WHERE '.implode(' AND ', $wheres);
	
try {
	$insert = "INSERT INTO ".APP_SCHEMA.".ACCR (CNTRCT_ID, ELEM_ID, SLS_CTR_CD, APP, STRT_DT, END_DT, YR, ACCR_AMT, CSE_VOL, LY_VOL, FIX_UNTS)
		WITH C AS (
			SELECT CED.CNTRCT_ID, CED.ELEM_ID, CL.SLS_CTR_CD, DT.YR, COALESCE(FU.MTH, 0) AS FU_MTH, 
				CED.STRT_DT AS DT, MIN(DT) AS STRT_DT, MAX(DT) AS END_DT
			FROM ".APP_SCHEMA.".CNTRCT_ELEM_DT CED
				JOIN DW.DT DT ON DT.DT BETWEEN CED.STRT_DT AND CED.END_DT
				JOIN ".APP_SCHEMA.".CNTRCT C ON C.CNTRCT_ID = CED.CNTRCT_ID
				JOIN ".APP_SCHEMA.".CNTRCT_LOC CL ON CL.CNTRCT_ID = CED.CNTRCT_ID
				LEFT JOIN ".APP_SCHEMA.".CNTRCT_FIX_UNT FU ON FU.CNTRCT_ID = CED.CNTRCT_ID
					AND FU.ELEM_ID = CED.ELEM_ID
					AND FU.SLS_CTR_CD = CL.SLS_CTR_CD
					AND FU.YR = DT.YR
					AND FU.MTH = DT.MTH".$where."
			GROUP BY CED.CNTRCT_ID, CED.ELEM_ID, CL.SLS_CTR_CD, DT.YR, COALESCE(FU.MTH, 0), CED.STRT_DT,
				CASE WHEN DT.DT <= CURRENT_DATE - DAYOFMONTH(CURRENT_DATE) DAYS THEN 'A' ELSE 'P' END
		)
		SELECT C.CNTRCT_ID, C.ELEM_ID, C.SLS_CTR_CD, C2.APP, C.STRT_DT, C.END_DT, C.YR, 
			DECIMAL(ROUND(CASE 
				WHEN E.ELEM_TP = 'TU' AND A.FIX_UNTS > 0 THEN 
					DECIMAL(ROUND(DECIMAL(COALESCE(FU.FIX_UNTS, 0), 13, 5) / DECIMAL(A.FIX_UNTS, 13, 5), 5), 13, 5) * DECIMAL(A.ACCR_AMT, 13, 5)
				WHEN E.ELEM_TP <> 'TU' AND A.CSE_VOL > 0 THEN 
					DECIMAL(S.CSE_VOL, 13, 4) / DECIMAL(A.CSE_VOL, 13, 4) * DECIMAL(A.ACCR_AMT, 13, 5)
				ELSE 0
			END, 5), 13, 5) AS ACCR_AMT2, 
			S.CSE_VOL, S.LY_VOL, COALESCE(FU.FIX_UNTS, 0) AS FIX_UNTS
		FROM C
			JOIN ".APP_SCHEMA.".CNTRCT C2 ON C2.CNTRCT_ID = C.CNTRCT_ID
			JOIN ".APP_SCHEMA.".ELEM E ON E.ELEM_ID = C.ELEM_ID
			JOIN QTEMP.ACCR A ON A.CNTRCT_ID = C.CNTRCT_ID
				AND A.ELEM_ID = C.ELEM_ID
			JOIN (
				SELECT CNTRCT_ID, ELEM_ID, SLS_CTR_CD, STRT_DT, SUM(CSE_VOL) AS CSE_VOL, SUM(LY_VOL) AS LY_VOL
				FROM (
					SELECT C.CNTRCT_ID, C.ELEM_ID, C.SLS_CTR_CD, C.STRT_DT, COALESCE(CSE_VOL, 0) AS CSE_VOL, 0 AS LY_VOL
					FROM ".APP_SCHEMA.".CNTRCT_SLS S
						RIGHT JOIN C ON C.CNTRCT_ID = S.CNTRCT_ID
							AND C.ELEM_ID = S.ELEM_ID
							AND C.SLS_CTR_CD = S.SLS_CTR_CD
							AND S.STLMNT_DT BETWEEN C.STRT_DT AND C.END_DT
					UNION ALL
					SELECT C.CNTRCT_ID, C.ELEM_ID, C.SLS_CTR_CD, C.STRT_DT, COALESCE(PRJTD_CSE_VOL, 0) AS CSE_VOL, 0 AS LY_VOL
					FROM ".APP_SCHEMA.".CNTRCT_PRJTN S
						RIGHT JOIN C ON C.CNTRCT_ID = S.CNTRCT_ID
							AND C.ELEM_ID = S.ELEM_ID
							AND C.SLS_CTR_CD = S.SLS_CTR_CD
							AND S.STLMNT_DT >= C.STRT_DT
							AND S.STLMNT_DT <= C.END_DT
							AND S.STLMNT_DT >= CURRENT_DATE - (DAY(CURRENT_DATE) - 1) DAYS
					UNION ALL
					SELECT C.CNTRCT_ID, C.ELEM_ID, C.SLS_CTR_CD, C.STRT_DT, 0 AS CSE_VOL, COALESCE(CSE_VOL, 0) AS LY_VOL
					FROM ".APP_SCHEMA.".CNTRCT_SLS S
						RIGHT JOIN C ON C.CNTRCT_ID = S.CNTRCT_ID
							AND C.ELEM_ID = S.ELEM_ID
							AND C.SLS_CTR_CD = S.SLS_CTR_CD
							AND S.STLMNT_DT BETWEEN C.STRT_DT - 1 YEARS AND C.END_DT - 1 YEARS
				) AS S
				GROUP BY CNTRCT_ID, ELEM_ID, SLS_CTR_CD, STRT_DT
			) AS S ON S.CNTRCT_ID = C.CNTRCT_ID
				AND S.ELEM_ID = C.ELEM_ID
				AND S.SLS_CTR_CD = C.SLS_CTR_CD
				AND S.STRT_DT = C.STRT_DT
			LEFT JOIN ".APP_SCHEMA.".CNTRCT_FIX_UNT FU ON FU.CNTRCT_ID = C.CNTRCT_ID
				AND FU.ELEM_ID = C.ELEM_ID
				AND FU.SLS_CTR_CD = C.SLS_CTR_CD
				AND FU.YR = C.YR
				AND FU.MTH = C.FU_MTH
		ORDER BY C.CNTRCT_ID, C.ELEM_ID, C.SLS_CTR_CD, C.STRT_DT";
	
	$db->query($insert, $params);
}
catch (Exception $e)
{
	echo "Error inserting accrual amounts:\n\t".$e->getMessage()."\n\n";
	exit(1);
}

echo "\nFinished\nStart Time: ".date('Y-m-d G:i:s T')."\n\n";
exit(0);