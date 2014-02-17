<?php

$header = array(
	'Contract', 
	'Divisions', 
	'Element', 
	'On Invoice', 
	'Last Year', 
	'Year-to-date', 
	'Projected', 
	'Total', 
	'Change', 
	'Fixed Units', 
	'Rate', 
	'Share',
	'Accrued'
);

$data_rows = array();

$del = ",";

foreach ($data as $row)
{
	$data_rows[] = '"'.$row->CNTRCT_NM.'"'.$del.
		'"'.$row->DIVS.'"'.$del.
		'"'.$row->ELEM_NM.'"'.$del.
		$row->ON_INV_FLG.$del.
		$row->LY.$del.
		$row->YTD.$del.
		$row->PRJ.$del.
		$row->TTL.$del.
		$row->CHG.$del.
		$row->FIX_UNTS.$del.
		$row->ELEM_RT.$del.
		$row->SHR.$del.
		$row->ACR;
}
	
header('Content-Type: application/csv');
header('Content-Disposition: attachment;filename="DetailedAccrualReport-'.mt_rand(10000, 99999).'.csv"');
header('Cache-Control: max-age=0');

echo implode($del, $header)."\r\n".implode("\r\n", $data_rows);