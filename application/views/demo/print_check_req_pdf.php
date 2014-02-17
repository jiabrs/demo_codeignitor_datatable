<?php
require_once('/www/tcpdf/config/lang/eng.php');
require_once('/www/tcpdf/tcpdf.php');

$pdf = new pdf_report('P', 'pt', 'LETTER');

$pdf->set_report_author($this->authr->user->get_full_nm());
$pdf->set_report_name('CMA Check Request');
$pdf->setup_pdf();

$n = new NumberFormatter("en-US", NumberFormatter::ORDINAL); 
$codes = $this->config->item('code');
$curr_check_req_id = NULL;
$elem_rows = array();

$label_w = 80;

foreach ($check_reqs as $check_req)
{
	// Add new page
	$pdf->AddPage();
	
	$pdf->Ln(20);
	
	// Add Header, invoice and date
	$pdf->SetFontSize(20);
	$pdf->Cell(0, '40', 'CMA Check Request', 'TB');
	$pdf->SetFontSize(10);
	$pdf->Ln(0);
	$pdf->Cell(0, '20', 'Invoice #: '.$check_req->get_check_ref(), '', 1, 'R');
	$pdf->Cell(0, '20', date('F d, Y', $check_req->get_check_req_dt()), '', 0, 'R');
	
	$pdf->Ln(30);
	
	// Write out Contract Info
	// row 1
	$pdf->Cell($label_w, '', 'Contract:');
	$pdf->Cell(250, '', $check_req->get_cntrct_nm());
	$pdf->Cell(20);
	$pdf->Cell($label_w, '', 'Term:');
	$pdf->Cell(150, '', $check_req->get_term());
	
	$pdf->Ln(20);
	
	// row 2
	$s = '';
	if (count($check_req->get_customers()) > 1)
		$s = 's';
		
	$pdf->Cell($label_w, '', 'Account'.$s.':');	
	$cnt = 1;	
	foreach ($check_req->get_customers() as $cust_cd => $cust_obj)
	{
		if ($cnt > 1) // indent if account on new line
			$pdf->Cell($label_w);
		
		$pdf->Cell(250, '', $cust_obj->get_cust_nm().' ('.$cust_obj->get_cust_cd().')');
		
		if ($cnt == 1) // slip in the manager on the same row
		{
			$pdf->Cell(20);
			$pdf->Cell($label_w, '', 'Manager:');
			$pdf->Cell(150, '', $check_req->get_manager());
		}
		
		$pdf->Ln(20);
		
		$cnt++;
	}		
	
	$pdf->Ln(20);	
	
	// Build A/P section
	$pdf->Cell(0, 20, 'A/P', 'TB', 1);
		
	$pdf->Ln(20);
	
	// A/P info
	// row 1
	$pdf->Cell($label_w, '', 'Check Text:');	
	$pdf->Cell(250,'', $check_req->get_check_text());	
	$pdf->Cell(20);
	$pdf->Cell($label_w, '', 'Check Ref:');
	$pdf->Cell(150, '', $check_req->get_check_ref());
	$pdf->Ln(20);
	
	// row 2
	$s = '';
	if (count($check_req->get_profit_ctrs()) > 1)
		$s = 's';
	$pdf->Cell($label_w,'', 'A/P Account'.$s.':');
	
	$cnt = 1;
	foreach ($check_req->get_profit_ctrs() as $prf_ctr => $info)
	{	
		if ($cnt > 1)
			$pdf->Cell($label_w);
				
		$pdf->Cell(90, '', $info['sls_ctr_nm']);
		$pdf->Cell(10);
		$pdf->Cell(70, '', $codes['gl']['CM'].' '.$prf_ctr);
		$pdf->Cell(10);
		$pdf->Cell(70, '', '$'.number_format($info['amt'], 2), '', '', 'R');
		
		if ($cnt == 1) // slip in check text
		{
			$pdf->Cell(20);
			$pdf->Cell($label_w, '', 'Vendor #:');
			$pdf->Cell(150, '', $check_req->get_vend_no());
		}
		
		$pdf->Ln(20);
		
		$cnt++;
	}	
	
	$pdf->Ln(20);
	
	$pdf->Cell(0, 20, 'Elements', 'TB', 1);

	// start element table
	$pdf->SetFont('', 'U', 10);
	$pdf->Cell(350, 30, 'Description', 'T');
	$pdf->Cell(60, 30, 'Rate', 'T', 0, 'R', FALSE);
	$pdf->Cell(60, 30, 'Cases', 'T', 0, 'R', FALSE);
	$pdf->Cell(0, 30, 'Amount', 'T', 0, 'R', FALSE);
	$pdf->SetFont('', 'R');
	$pdf->Ln(40);		
		
	$tot_amt = 0;	
	
	foreach ($check_req->get_elements() as $elem_row)
	{
		$pdf->Cell(350,'',$elem_row['elem_nm']);
		$pdf->Cell(60,'',$elem_row['elem_rt'], '', 0, 'R');
		$pdf->Cell(60,'',number_format($elem_row['cse_vol'], 0), '', 0, 'R');
		$pdf->Cell(0,'','$'.number_format($elem_row['accr_amt'], 2), '', 0, 'R');
		
		$pdf->Ln(30);	
		
		$tot_amt += $elem_row['accr_amt'];
	}
	
	// wrap up element table
	$pdf->Cell('470',20, 'Pay Total:', 'TB', '', 'R');
	$pdf->Cell(0, 20, '$'.number_format($tot_amt, 2), 'TB', 1, 'R');
	$pdf->Ln(30);
	$pdf->Ln(40);
	
	$pdf->SetFontSize('12');		
		
	// Add approvers
	foreach ($check_req->get_approvers() as $approver)
	{
		$pdf->Cell(150, '', $approver);
		$pdf->Cell(300, '', "", 'B', 1);
		$pdf->Ln();
	}
	
	// Add return to
	$pdf->Cell('','', 'Please return check to '.$check_req->get_return_to());
	$pdf->Ln();	
}	

$pdf->Output('CMA Check Request.pdf', 'D');