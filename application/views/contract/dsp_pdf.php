<?php 
require_once('/www/tcpdf/config/lang/eng.php');
require_once('/www/tcpdf/tcpdf.php');

class contract_detail_pdf extends pdf_report {
	
	public $cd_font_size = 10;
	public $cd_font = 'helvetica';
	
	function cd_report_header($cntrct_nm = '')
	{
		$this->SetTextColor(198, 0, 0);
		$this->SetLineStyle(array('width'=>1, 'color'=>array(198,0,0)));
		$this->SetFontSize(20);
		$this->Cell(0, 24, $cntrct_nm, 'B');
		
		$this->Ln();
		$this->Ln();
		
		$this->SetTextColor(0);
		$this->SetFontSize(10);
		$this->SetLineStyle(array('width'=>1, 'color'=>array(0,0,0)));
	}
	
	function cd_section_header($title = '')
	{
		$this->Ln(10);
		$font_size = $this->getFontSize();
		$this->SetFontSize(14);
		$this->setlineStyle(array('width'=>1, 'color'=>array(0,0,0)));
		$this->Cell(0, '', $title, 'B', 1);
		$this->setfont($this->cd_font, '', $this->cd_font_size);
		$this->Ln(10);
	}
	
	function cd_info_label($label = '', $w = 0)
	{
		$this->SetFont($this->cd_font, 'B', $this->cd_font_size);
		$this->Cell($w, 0, $label, 0);
		$this->SetFont($this->cd_font, '');
	}
	
	function cd_elem_section($element, $contract)
	{
		// prepare element date ranges for display
		$dt_ranges = array();	
		foreach ($element->get_dt_range() as $index => $dts)
		{
			$dt_ranges[] = $dts['strt_dt']." - ".$dts['end_dt'];
		}
		
		// prepare element info for display
		$elem_info_rows = array(
			array(
				'label' => 'Rate:',
				'data' => "$".$element->dsp_elem_rt()
			),
			array(
				'label' => 'Projected Cost:',
				'data' => "$".number_format($contract->get_elem_acr($element->get_elem_id()), 2)
			),
			array(
				'label' => 'On Invoice:',
				'data' => $element->dsp_on_inv_flg()
			),
			array(
				'label' => 'Date Range:',
				'data' => $dt_ranges
			)
		);
		
		if ($element->get_shr() < 100)
			$elem_info_rows[] = array(
				'label' => 'United Share:',
				'data' => number_format($element->get_shr(), 0)."%"
			);
			
		if (trim($element->get_elem_desc()) <> '')
			$elem_info_rows[] = array(
				'label' => 'Description:',
				'data' => $element->get_elem_desc()
			);
			
		// prepare element sales criteria for display
		$sls_crits = array();
		
		foreach ($element->get_sls_crits() as $sls_crit)
		{
			$sls_crits[$sls_crit->get_accr_flg()][$sls_crit->dsp_crit_fld()][] = $sls_crit->dsp_crit_cd();
		}
		
		$includes = array();
		$excludes = array();
		$include_title = $exclude_title = '';
		foreach ($sls_crits as $accr_flg => $fields)
		{
			foreach ($fields as $field => $codes)
			{
				if ($accr_flg == 'Y')
				{
					$includes[] = "  ".$field.": ".implode(', ', $codes);
					$include_title = "Accrues Against:\n";
				}	
				if ($accr_flg == 'N')
				{
					$excludes[] = "  ".$field.": ".implode(', ', $codes);
					$exclude_title = "Excludes:\n";
				}
			}
		}
		
		$sls_crit_dsp = array(
			$include_title.implode("\n", $includes),
			$exclude_title.implode("\n", $excludes)
		);
		
		// pull element sales statistics
		$sls_stats = $contract->get_elem_sls_stats($element->get_elem_id());
		
		/*
		 * build out Element section
		 */
		$this->setfont($this->cd_font, 'B', 12);
		$this->Cell(0, 0, $element->get_elem_nm(), 0, 1);
		$this->setY($this->getY()+5);
		
		$sls_crit_y = $this->GetY();
		
		foreach ($elem_info_rows as $elem_info_row)
		{
			$this->cd_info_label($elem_info_row['label'], 90);
			if (is_array($elem_info_row['data']))
			{
				$this->MultiCell(120, 0, implode("\n", $elem_info_row['data']), 0, 'R', 0, 1);
			} elseif (strlen($elem_info_row['data']) > 24) {
				$this->MultiCell(120, 0, $elem_info_row['data'], 0, 'R', 0, 1);	
			} else {
				$this->Cell(120, 0, $elem_info_row['data'], 0, 1, 'R');
			}
			
			$this->Ln(5);		
		}
		
		$sls_y = $this->GetY();
		
		$this->SetY($sls_crit_y);
		$this->SetX(250);
		
		$this->MultiCell(0, 0, implode("\n\n", $sls_crit_dsp), 1, 'L');
		
		if ($this->GetY() < $sls_y) $this->setY($sls_y);
		
		$this->setY($this->getY()+10);
		
		$sls_w = array(100, 40, 55, 60, 60, 60, 60, 60, 0);
		
		foreach (array('DIV','LOC','UNITS','LY','YTD','PROJ','TTL','% CHG','ACCR') AS $i => $th)
		{
			if ($th <> 'UNITS' || ($th == 'UNITS' && $element->get_elem_tp() == 'TU'))
				$this->Cell($sls_w[$i], 0, $th, 1, 0, 'C');
		}
		
		$this->Ln();
		
		$fix_unts = $ly = $ytd = $prj = $ttl = $acr = 0;
		
		foreach ($sls_stats as $row)
		{
			$fix_unts += $row->FIX_UNTS;
			$ly += $row->LY;
			$ytd += $row->YTD;
			$prj += $row->PRJ;
			$ttl += $row->TTL;
			$acr += $row->ACR;
			
			$this->Cell($sls_w[0], 0, $row->DIV_NM, 1, 0, 'L');
			$this->Cell($sls_w[1], 0, $row->SLS_CTR_NM, 1, 0, 'L');
			if ($element->get_elem_tp() == 'TU')
				$this->Cell($sls_w[2], 0, number_format($row->FIX_UNTS, 2), 1, 0, 'R');
			$this->Cell($sls_w[3], 0, number_format($row->LY, 0), 1, 0, 'R');
			$this->Cell($sls_w[4], 0, number_format($row->YTD, 0), 1, 0, 'R');
			$this->Cell($sls_w[5], 0, number_format($row->PRJ, 0), 1, 0, 'R');
			$this->Cell($sls_w[6], 0, number_format($row->TTL, 0), 1, 0, 'R');
			$this->Cell($sls_w[7], 0, number_format($row->CHG*100, 2), 1, 0, 'R');
			$this->Cell($sls_w[8], 0, "$".number_format($row->ACR, 0), 1, 1, 'R');
			
		}
		
		$this->setFont($this->cd_font, 'B');
		
		$this->Cell($sls_w[0]+$sls_w[1], 0, 'Total', 1, 0, 'L');
		if ($element->get_elem_tp() == 'TU')
			$this->Cell($sls_w[2], 0, number_format($fix_unts, 2), 1, 0, 'R');
		$this->Cell($sls_w[3], 0, number_format($ly, 0), 1, 0, 'R');
		$this->Cell($sls_w[4], 0, number_format($ytd, 0), 1, 0, 'R');
		$this->Cell($sls_w[5], 0, number_format($prj, 0), 1, 0, 'R');
		$this->Cell($sls_w[6], 0, number_format($ttl, 0), 1, 0, 'R');
		$this->Cell($sls_w[7], 0, number_format(($ly == 0) ? 0 : ($ttl - $ly) / $ly * 100, 2), 1, 0, 'R');
		$this->Cell($sls_w[8], 0, "$".number_format($acr, 0), 1, 0, 'R');
		
		$this->Ln(30);
	}
}

$pdf = new contract_detail_pdf('P', 'pt', 'LETTER');

$pdf->set_report_author($this->session->userdata('fullname'));
$pdf->set_report_name($report_title);
$pdf->set_report_app($code['app'][$this->session->userdata('app')]);
$pdf->setup_pdf();

$pdf->AddPage();
$pdf->SetFont($pdf->cd_font);

$pdf->cd_report_header($contract->get_cntrct_nm());

$info_strt_y = $pdf->GetY();

$info_rows = array(
	array(
		'label' => 'Date Range:',
		'data' => $contract->dsp_strt_dt('m/d/Y').' - '.$contract->dsp_end_dt('m/d/Y')
	),
	array(
		'label' => 'Fiscal Year:',
		'data' => $contract->get_accrual_yr()
	),
	array(
		'label' => 'Case Type:',
		'data' => $contract->dsp_cse_tp()
	)
);

foreach ($info_rows as $row)
{
	$pdf->cd_info_label($row['label'], 80);
	$pdf->Cell(120, 0, $row['data'], 0, 1, 'R');
	$pdf->Ln(5);
}

if ($this->session->userdata('app') == 'CM')
{
	$pdf->cd_info_label('Check Approvers:', 110);
	
	foreach ($appr_lst as $i => $appr_nm)
	{
		if ($i > 1)
			$pdf->Cell(110);
			
		$pdf->Cell(90, 0, $appr_nm, 0, 1, 'R');
	}
	$pdf->Ln(5);
	
	$pdf->cd_info_label('Return to:', 100);
	$pdf->Cell(100, 0, $retrn_to, 0, 1, 'R');
	$pdf->Ln(5);
}

$prj_cost = $contract->get_proj_cost();

$pdf->cd_info_label('Projected Cost:', 80);
$pdf->Cell(80, 0, "$ ".number_format($prj_cost['oi'], 2), 0, 0, 'R');
$pdf->Cell(40, 0, 'On Inv.', 0, 1, 'L');
$pdf->Cell(160, 0, "$ ".number_format($prj_cost['pst'], 2), 0, 0, 'R');
$pdf->Cell(40, 0, 'Post', 0, 1, 'L');
$pdf->Cell(160, 0, "$ ".number_format($prj_cost['pst'] + $prj_cost['oi'], 2), 0, 0, 'R');
$pdf->Cell(40, 0, 'Total', 0, 1, 'L');

$info_end_y = $pdf->getY();

$pdf->Sety($info_strt_y);

$pdf->SetXY(250, $info_strt_y);

$pdf->cd_info_label('Customers:', 70);

$customer_list = array();
$i=1;
$customer_list_extra = '';

foreach ($contract->get_customers() as $customer)
{
	if ($i < 6)
	{
		$customer_list[] = $customer->get_cust_nm().' ('.$customer->get_cust_tp().': '.$customer->get_cust_cd().')';
	} else {
		$customer_list_extra = "\n".($i-5)." customers not shown";
	}
	
	$i++;
}

$pdf->MultiCell(0, 0, implode("\n", $customer_list).$customer_list_extra, 0, 'L', FALSE, 1);

$pdf->SetXY(250, $pdf->GetY()+5);

$pdf->cd_info_label('Divisions:', 70);

$locations_list = array();

foreach ($this->location->get_by_div() as $div_cd => $div_data)
{
	$sls_ctrs = array();
	foreach ($div_data['sls_ctrs'] as $sls_ctr_cd => $sls_ctr_data)
	{
		$sls_ctrs[] = $sls_ctr_data['short_name'];
	}
	
	$locations_list[] = trim($div_data['name']).": (".implode(', ', $sls_ctrs).")";
}

$pdf->MultiCell(0, 0, implode("\n", $locations_list), 0, 'L');

/* 
 * If customers and Divisions didn't push us past left side info column
 * then use bottom of left column as start point for next section
 */ 
if ($pdf->getY() < $info_end_y) $pdf->setY($info_end_y);

$notes = $contract->get_notes();

if (count($notes) > 0)
{
	$pdf->cd_section_header('Notes');
	
	foreach ($notes as $note)
	{
		$pdf->cd_info_label($note->dsp_lst_updt_tm().":", 100);
		$pdf->MultiCell(0, 0, $note->get_body(), 0, 'L', 0, 1, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
		$pdf->Ln(5);
	}
}

$pdf->cd_section_header('Elements');

foreach ($contract->get_elements() as $element)
{	
	$curr_page = $pdf->getPage();
	$hist_pdf = clone $pdf;
	
	$pdf->cd_elem_section($element, $contract);
	
	if ($pdf->getPage() > $curr_page)
	{
		$pdf = &$hist_pdf;
		$pdf->AddPage();
		$pdf->cd_report_header($contract->get_cntrct_nm());
		$pdf->cd_elem_section($element, $contract);
	}
	
	unset($hist_pdf);
}


$pdf->Output('Contract Detail Report-'.$contract->get_cntrct_id().'.pdf', 'D');

