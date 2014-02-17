<?php
require_once('/www/tcpdf/config/lang/eng.php');
require_once('/www/tcpdf/tcpdf.php');

$locs = array();
foreach ($this->location->get_by_sls_ctr() as $sls_ctr_cd => $sc_data) { 
	$locs[] = $sc_data['short_name']; 
}

$inv_opts = array();
foreach ($inv as $type) 
{
	if ($type == 'Y') $inv_opts[] = 'On Invoice';
	if ($type == 'N') $inv_opts[] = 'Post Invoice';
}	

$w = array(
	'element' => 155,
	'oi' => 15,
	'vol' => 38,
	'curr' => 65,
	'ind' => 5
);

$h = 18;

$pdf = new pdf_report('P', 'pt', 'LETTER');

$pdf->set_report_author($this->session->userdata('fullname'));
$pdf->set_report_name('Detailed Accrual Report');
$pdf->set_report_app($code['app'][$this->session->userdata('app')]);
$pdf->setup_pdf();

$pdf->AddPage();

draw_head($pdf, $w, $h, $locs, $inv_opts, $accrual_yr, $accr_tps, $accr_tp);

$accr_pst = $accr_oi = $cntrct_accr = 0; 
$cntrct_id = 0;
$elem_rows = array();
$row = NULL;
$cntrct_row_cnt = 0;

for ($i=0; $i<count($data); $i++)
{
	if ($data[$i]->ON_INV_FLG == 'Y') 
	{
		$accr_oi += $data[$i]->ACR;
	}
	else
	{
		$accr_pst += $data[$i]->ACR;
	}
	
	$cntrct_accr += $data[$i]->ACR;
	
	$elem_rows[] = $data[$i];
	
	$cntrct_row_cnt++;
	
	if ($i+1 == count($data) || $data[$i]->CNTRCT_ID <> $data[$i+1]->CNTRCT_ID)
	{	
		if ($pdf->check_page_break($h*($cntrct_row_cnt+1)))
		{
			draw_head($pdf, $w, $h, $locs, $inv_opts, $accrual_yr, $accr_tps, $accr_tp);
		}	
		
		$pdf->SetTextColor(0);
		$pdf->SetFillColor(211, 221, 229);
		$pdf->SetFont('helvetica', 'B');
		$pdf->Cell($w['element']+$w['oi'], $h, $data[$i]->CNTRCT_NM, 1, 0, 'L', 1, site_url('contract/dsp/'.$data[$i]->CNTRCT_ID));
		$pdf->Cell(($w['vol']*7) + $w['curr'], $h, $data[$i]->DIVS, 1, 0, 'L', 1);
		$pdf->Cell($w['curr'], $h, '$ '.number_format($cntrct_accr, 2), 1, 0, 'R', 1);
		$pdf->Ln();
		
		$pdf->SetFont('helvetica', '');
		
		foreach ($elem_rows as $index => $elem_row)
		{
			if ($index%2 == 0)
			{
				$pdf->SetFillColor(255);
			}
			else
			{
				$pdf->SetFillColor(243, 251, 239);
			}
			
			$pdf->Cell($w['ind'], $h, '', 1, 0, 'R', 1);
			$pdf->MultiCell($w['element'] - $w['ind'], $h, $elem_row->ELEM_NM, 0, 'L', TRUE, 0, '', '', TRUE, 0, FALSE, TRUE, $h, 'T', TRUE);
			if ($elem_row->ON_INV_FLG == 'Y')
			{
				$pdf->Cell($w['oi'], $h, '', 1, 0, 'C', 1);
				$pdf->Image(FCPATH.'resource/images/ui-icon-flag.png', $pdf->GetX()-$w['oi'], $pdf->GetY(), $w['oi'], '');
			}
			else
			{
				$pdf->Cell($w['oi'], $h, '', 1, 0, 'C', 1);
			}
			$fix_unts = '';
			if ($elem_row->ELEM_TP == 'TU') $fix_unts = number_format($elem_row->FIX_UNTS, 2);
			
			$pdf->Cell($w['vol'], $h, number_format($elem_row->LY, 0), 1, 0, 'R', 1);
			$pdf->Cell($w['vol'], $h, number_format($elem_row->YTD, 0), 1, 0, 'R', 1);
			$pdf->Cell($w['vol'], $h, number_format($elem_row->PRJ, 0), 1, 0, 'R', 1);
			$pdf->Cell($w['vol'], $h, number_format($elem_row->TTL, 0), 1, 0, 'R', 1);
			$pdf->Cell($w['vol'], $h, number_format($elem_row->CHG, 2).'%', 1, 0, 'R', 1);
			$pdf->Cell($w['vol'], $h, $fix_unts, 1, 0, 'R', 1);
			$pdf->Cell($w['curr'], $h, '$ '.number_format($elem_row->ELEM_RT, 5), 1, 0, 'R', 1);
			$pdf->Cell($w['vol'], $h, number_format($elem_row->SHR, 2).'%', 1, 0, 'R', 1);
			$pdf->Cell($w['curr'], $h, '$ '.number_format($elem_row->ACR, 2), 1, 0, 'R', 1);
			$pdf->Ln();
		}
		
		$elem_rows = array();
		$cntrct_accr = 0;
		$cntrct_row_cnt = 0;
	}
}

$pdf->SetFillColor(102, 102, 102);
$pdf->SetTextColor(255);
$pdf->SetFont('helvetica', 'B');

if (in_array('Y', $inv))
{
	$pdf->Cell($w['element'] + ($w['vol']*7) + $w['curr'] + $w['oi'], $h, 'On Invoice Total', 1, 0, 'L', 1);
	$pdf->Cell($w['curr'], $h, '$ '.number_format($accr_oi, 2), 1, 0, 'R', 1);
	$pdf->Ln();
}

if (in_array('N', $inv))
{
	$pdf->Cell($w['element'] + ($w['vol']*7) + $w['curr'] + $w['oi'], $h, 'Post Invoice Total', 1, 0, 'L', 1);
	$pdf->Cell($w['curr'], $h, '$ '.number_format($accr_pst, 2), 1, 0, 'R', 1);
	$pdf->Ln();
}

$pdf->Cell($w['element'] + ($w['vol']*7) + $w['curr'] + $w['oi'], $h, 'Report Total', 1, 0, 'L', 1);
$pdf->Cell($w['curr'], $h, '$ '.number_format($accr_oi + $accr_pst, 2), 1, 0, 'R', 1);
$pdf->Ln();

$pdf->Output('Detailed Accrual Report-'.mt_rand(1, 9999).'.pdf', 'D');

function draw_head (&$pdf, $w, $h, $locs, $inv_opts, $accrual_yr, $accr_tps, $accr_tp) {
	$pdf->SetFontSize(8);
	
	$pdf->Cell(70, 0, 'Locations:');
	$pdf->Cell(470, 0, implode(', ', $locs));
	$pdf->Ln();
	
	$pdf->Cell(70, 0, 'Accrual Year:');
	$pdf->Cell(470, 0, $accrual_yr);
	$pdf->Ln();

	$pdf->Cell(70, 0, 'Showing:');
	$pdf->Cell(470, 0, implode(', ', $inv_opts));
	$pdf->Ln();

	$pdf->Cell(70, 0, 'Accrual Amount:');
	$pdf->Cell(470, 0, $accr_tps[$accr_tp]);
	$pdf->Ln();
	
	$pdf->SetFillColor(102, 102, 102);
	$pdf->SetTextColor(255);
	$pdf->SetDrawColor(255);
	$pdf->SetLineWidth(0.3);
	$pdf->SetFont('helvetica', 'B');
	
	$pdf->Cell($w['element'], $h, 'Element', 1, 0, 'C', 1);
	$pdf->Cell($w['oi'], $h, 'OI', 1, 0, 'C', 1);
	$pdf->Cell($w['vol'], $h, 'LY', 1, 0, 'C', 1);
	$pdf->Cell($w['vol'], $h, 'YTD', 1, 0, 'C', 1);
	$pdf->Cell($w['vol'], $h, 'PRJ', 1, 0, 'C', 1);
	$pdf->Cell($w['vol'], $h, 'TTL', 1, 0, 'C', 1);
	$pdf->Cell($w['vol'], $h, 'CHG', 1, 0, 'C', 1);
	$pdf->Cell($w['vol'], $h, 'Units', 1, 0, 'C', 1);
	$pdf->Cell($w['curr'], $h, 'Rate', 1, 0, 'C', 1);
	$pdf->Cell($w['vol'], $h, 'Share', 1, 0, 'C', 1);
	$pdf->Cell($w['curr'], $h, 'Accrued', 1, 0, 'C', 1);
	$pdf->Ln();
}