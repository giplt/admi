<?php
	define('IMAGE_PATH',"php_common/images/");
	define('FPDF_FONTPATH',"php_common/fonts/");

	require('tfpdf.php');
	
	function createPDF($data) {
		
		$lm = 10; //margin for text on the left
		$rm= 200; //margin for text on the right
		$sp = 6; //space between lines
		$cw = 25; //column width
		$lc = 1; //line count	
		$sty = 130; // start of the invoice_lines
		
		$pdf = new tFPDF('P', 'mm', 'A4');
		$pdf->AddFont('DidactGothic','','DidactGothic.ttf', true);
		$pdf->AddFont('DidactGothic','B','DidactGothicB.ttf', true);
		$pdf->SetAutoPagebreak(False);
		$pdf->SetMargins(0,0,0);
		
		$pdf->AddPage();
		
		// logo
		$pdf->Image(IMAGE_PATH."/planb.png", 20, 20, 40);
		
		// adres coop
		$pdf->SetXY($rm-60, 20);
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->MultiCell(60, $sp, "Geldersestraat 6\n3812 PP Amersfoort NL\n+31(0)334481622\ninfo@planb.coop", 0, 'R');
		
		// factuur
		$pdf->SetTextColor(128);
		$pdf->SetFont("DidactGothic", "B", 24);
		$pdf->SetXY($rm-100, 50);
		$pdf->Cell(100, $sp, "FACTUUR/INVOICE", 0, 0, 'R');
		
		// datum
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY($rm-2*$cw, 60);
		$pdf->Cell($cw, $sp, "Datum/date:", 0, 0, 'R');
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY($rm-$cw, 60);
		$pdf->Cell($cw, $sp, $data['Meta']['invoiceDate'], 0, 0, 'R');
		
		// factuurnr
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY($rm-2*$cw, 66);
		$pdf->Cell($cw, $sp, "Factuur/invoice nr.:", 0, 0, 'R');
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY($rm-$cw, 66);
		$pdf->Cell($cw, $sp, $data['Meta']['reference'], 0, 0, 'R');
		
		// balk
		$pdf->SetFillColor(192);
		$pdf->Rect($lm, 75, $rm-$lm, 2, "F");
		
		// ontvanger
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY($lm, 80);
		$pdf->Cell(20, 6, "Aan/to:", 0, 0, 'L');
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY($lm+20, 80);
		$pdf->MultiCell(100, 6, $data['recipient'], 0, 'L');
		
		// Project
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY($lm, 100);
		$pdf->MultiCell(175, 6, "Factuur voor / Invoice for project: ".$data['Meta']['project'], 0, 'L');

		// Header voor factuur-regels
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY($lm, 120);
		$pdf->MultiCell(2*$cw, $sp, "Omschrijving /\n Description:", 'B', 'L');
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY($lm+2*$cw, 120);
		$pdf->MultiCell($cw, $sp, "Aantal /\n Amount:", 'B', 'L');
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY($lm+3*$cw, 120);
		$pdf->MultiCell($cw, $sp, "Prijs/\nPrice:", 'B', 'L');
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY($lm+4*$cw, 120);
		$pdf->MultiCell($cw, $sp,"Netto/\nNett:", 'B', 'L');
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY($lm+5*$cw, 120);
		$pdf->MultiCell($cw, $sp, "BTW/\nVat:", 'B', 'L');
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY($lm+6*$cw, 120);
		$pdf->MultiCell($cw, $sp, " Totaal/\nTotal:", 'B', 'L');


		// Factuur-regels
		foreach($data as $data_key=>$data_val){
			if(substr($data_key, 0,11)=="invoiceLine"){
				if($data_val['invoiceType']=='head'){
					if($lc!=1){					
						$lc=$lc+1;
					}
					$pdf->SetTextColor(0);
					$pdf->SetFont("DidactGothic", "B", 12);
					$pdf->SetXY($lm, $sty+$lc*$sp);
					$pdf->MultiCell(6*$cw, $sp, $data_val['desc'], 'B', 'L');

				}
				else{
					$pdf->SetTextColor(0);
					$pdf->SetFont("DidactGothic", "", 12);
					$pdf->SetXY($lm, $sty+$lc*$sp);
					$pdf->MultiCell($cw, $sp, $data_val['invoiceType'], 0, 'L');
					$pdf->SetTextColor(0);
					$pdf->SetFont("DidactGothic", "", 12);
					$pdf->SetXY($lm+1*$cw, $sty+$lc*$sp);
					$pdf->MultiCell($cw, $sp, $data_val['desc'], 0, 'L');
					$pdf->SetTextColor(0);
					$pdf->SetFont("DidactGothic", "", 12);
					$pdf->SetXY($lm+2*$cw, $sty+$lc*$sp);
					$pdf->MultiCell($cw, $sp, $data_val['amount'], 0, 'L');
					$pdf->SetTextColor(0);
					$pdf->SetFont("DidactGothic", "", 12);
					$pdf->SetXY($lm+3*$cw, $sty+$lc*$sp);
					$pdf->MultiCell($cw, $sp, $data_val['price'], 0, 'L');
					$pdf->SetTextColor(0);
					$pdf->SetFont("DidactGothic", "", 12);
					$pdf->SetXY($lm+4*$cw, $sty+$lc*$sp);
					$pdf->MultiCell($cw, $sp, $data_val['nett'], 0, 'L');
					$pdf->SetTextColor(0);
					$pdf->SetFont("DidactGothic", "", 12);
					$pdf->SetXY($lm+5*$cw, $sty+$lc*$sp);
					$pdf->MultiCell($cw, $sp, $data_val['vat_type']." %", 0, 'L');
				}
				$lc=$lc+1;
			}
		}
		
		// Totalen
		foreach($data as $data_key=>$data_val){
			if($data_key=="salesTot"){

				//nett
				$pdf->SetTextColor(0);
				$pdf->SetFont("DidactGothic", "B", 12);
				$pdf->SetXY($lm+4*$cw, $sty+($lc+1)*$sp);
				$pdf->MultiCell(2*$cw, $sp,"Netto/Nett", 0, 'R');
				$pdf->SetTextColor(0);
				$pdf->SetFont("DidactGothic", "B", 12);
				$pdf->SetXY($lm+6*$cw, $sty+($lc+1)*$sp);
				$pdf->MultiCell($cw, $sp,$data_val['nett'], 0, 'L');
				$lc=$lc+1;

				//if $data_val['shift']!=1)

				foreach($data_val as $tot_key=>$tot_val){
					if(substr($tot_key,0,4)=="vat_"){
						if($tot_val>0){

							// name
							$pdf->SetTextColor(0);
							$pdf->SetFont("DidactGothic", "B", 12);
							$pdf->SetXY($lm+4*$cw, $sty+($lc+1)*$sp);
							$pdf->MultiCell(2*$cw, $sp,substr($tot_key,4,strlen($tot_key))." % BTW/Vat", 0, 'R');

							//number
							$pdf->SetTextColor(0);
							$pdf->SetFont("DidactGothic", "B", 12);
							$pdf->SetXY($lm+6*$cw, $sty+($lc+1)*$sp);
							$pdf->MultiCell($cw, $sp, $tot_val, 0, 'L');
							$lc=$lc+1;
						}
					}

				}
				
				//gross
				$pdf->SetTextColor(0);
				$pdf->SetFont("DidactGothic", "B", 12);
				$pdf->SetXY($lm+4*$cw, $sty+($lc+1)*$sp);
				$pdf->MultiCell(2*$cw, $sp,"Bruto/Gross", 0, 'R');
				$pdf->SetTextColor(0);
				$pdf->SetFont("DidactGothic", "B", 12);
				$pdf->SetXY($lm+6*$cw, $sty+($lc+1)*$sp);
				$pdf->MultiCell($cw, $sp,$data_val['gross'], 'T', 'L');
				$lc=$lc+1;

			}
		}
		
		// bedrag
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "B", 12);
		$pdf->SetXY(120, 160);
		//$pdf->MultiCell(40, 6, "Bedrag/amount\nBTW/VAT ".(100.0*$data['vat'])."%\nTotaal/total", 0, 'R');
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY(160, 160);
		
		//$gross = number_format((float)$data['amount'], 2, '.', '');
		//$nett = number_format((float)($gross/(1.0+$data['vat'])), 2, '.', '');
		//$vat = number_format((float)($gross*(1.0 - 1.0/(1.0+$data['vat']))), 2, '.', '');
		
		//$pdf->MultiCell(30, 6, "€ ".$nett."\n€ ".$vat."\n€ ".$gross, 0, 'R');
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY(20, 190);
		//$pdf->MultiCell(170, 6, "betaling voldaan op / payment settled on ".$data['paymentDate'], 0, 'R');
		
		// balk
		$pdf->SetFillColor(192);
		$pdf->Rect(20, 220, 170, 5, "F");
		
		// referenties
		$pdf->SetTextColor(0);
		$pdf->SetFont("DidactGothic", "", 12);
		$pdf->SetXY(20, 230);
		$pdf->Cell(170, 6, "KvK №: 56719213 – BTW/VAT №: NL 8522.83.842 B01 – IBAN: NL68 TRIO 0390 4273 06", 0, 0, 'R');
		
		// return pdf
		return $pdf->Output('id'.'.pdf', 'S');
	}
?>
