<?php
	define('IMAGE_PATH',"php_common/images/");
	define('FPDF_FONTPATH',"php_common/fonts/");

	require('tfpdf.php');
	
	function createPDF($data) {
		$pdf = new tFPDF('P', 'mm', 'A4');
		$pdf->AddFont('DidactGothic','','DidactGothic.ttf', true);
		$pdf->AddFont('DidactGothic','B','DidactGothicB.ttf', true);
		$pdf->SetAutoPagebreak(False);
		$pdf->SetMargins(0,0,0);
		
		$pdf->AddPage();
		
		// logo
		$pdf->Image(IMAGE_PATH."/planb.png", 20, 20, 40);
		
		// adres coop
		$pdf->SetXY(90, 20);
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->MultiCell(100, 6, "Geldersestraat 6\n3812 PP Amersfoort NL\n+31(0)334481622\ninfo@planb.coop", 0, 'R');
		
		// factuur
		$pdf->SetTextColor(128);
		$pdf->SetFont("DidactGothic", "B", 24);
		$pdf->SetXY(90, 50);
		$pdf->Cell(100, 6, "FACTUUR/INVOICE", 0, 0, 'R');
		
		// datum
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY(90, 60);
		$pdf->Cell(70, 6, "Datum/date:", 0, 0, 'R');
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY(160, 60);
		$pdf->Cell(30, 6, $data['date'], 0, 0, 'R');
		
		// factuurnr
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY(90, 66);
		$pdf->Cell(70, 6, "Factuur/invoice №:", 0, 0, 'R');
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY(160, 66);
		$pdf->Cell(30, 6, $data['id'], 0, 0, 'R');
		
		// balk
		$pdf->SetFillColor(192);
		$pdf->Rect(20, 75, 170, 2, "F");
		
		// ontvanger
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY(20, 80);
		$pdf->Cell(20, 6, "Aan/to:", 0, 0, 'R');
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY(40, 80);
		$pdf->MultiCell(100, 6, $data['to'], 0, 'L');
		
		// omschrijving
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY(20, 100);
		$pdf->MultiCell(170, 6, "Online betaling voor /\npayment concerning", 0, 'R');
		$pdf->SetFont("DidactGothic", "", 16);
		$pdf->SetXY(20, 115);
		$pdf->MultiCell(170, 6, $data['description'], 0, 'R');
		
		// bedrag
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY(120, 160);
		$pdf->MultiCell(40, 6, "Bedrag/amount\nBTW/VAT ".(100.0*$data['vat'])."%\nTotaal/total", 0, 'R');
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY(160, 160);
		
		//$gross = number_format((float)$data['amount'], 2, '.', '');
		//$nett = number_format((float)($gross/(1.0+$data['vat'])), 2, '.', '');
		//$vat = number_format((float)($gross*(1.0 - 1.0/(1.0+$data['vat']))), 2, '.', '');
		
		$pdf->MultiCell(30, 6, "€ ".$nett."\n€ ".$vat."\n€ ".$gross, 0, 'R');
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY(20, 190);
		$pdf->MultiCell(170, 6, "betaling voldaan op / payment settled on ".$data['paymentDate'], 0, 'R');
		
		// balk
		$pdf->SetFillColor(192);
		$pdf->Rect(20, 220, 170, 5, "F");
		
		// referenties
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY(20, 230);
		$pdf->Cell(170, 6, "KvK №: 56719213 – BTW/VAT №: NL 8522.83.842 B01 – IBAN: NL68 TRIO 0390 4273 06", 0, 0, 'R');
		
		// return pdf
		return $pdf->Output($data['id'].'.pdf', 'S');
	}
?>
