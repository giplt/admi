<?php
	
	//VAT tarifs, in case a new VAT tarrif is implemented insert an end-date for the old one
	// and give a start-date to the new one, without an end-date
 
	$VAT_tarifs=array(
		"Nul" => 
			array(array(
				"begin" => "01-01-1900",
				"end" => "",
				"level" => 0.00,
				"name" => "0",
			)), //nul
		"Laag" => 
			array(array(
				"begin" => "01-01-2019",
				"end" => "",
				"level" => 0.09,
				"name" => "9",
			)), //laag
		"Hoog" => 
			array(array(
				"begin" => "01-10-2012",
				"end" =>"",
				"level" => 0.21,
				"name" => "21",
			)), //hoog

		"Vrijgesteld" =>
			array(array(
				"begin" => "01-01-1990",
				"end" => "",
				"level" => 0.00,
				"name" => "0 (Vrijgesteld)"
			))
		) //array

	$VAT_shift_options=array("Nee","NL","EU","Ex")

	//BTW codes //combinatie van wat de belastingdienst wil horen en uit een tabel van AFAS (oud), 
	//Geen universele codes kunnen vinden
	//AFAS tabel: https://help.afas.nl/help/NL/SE/Fin_Config_VatIct_VatCde.htm
	//Belastingdienst: https://download.belastingdienst.nl/belastingdienst/docs/toelichting_bij_dig_aangifte_omzetbelasting_ob0731t11fd.pdf


	$VAT_codes=array(
		//standaard 
		array("1", "BTW te betalen (hoog)", "Hoog","Nee","verkoop"),
		array("2", "BTW te betalen (laag)", "laag","Nee","verkoop"),
		array("3", "BTW te betalen (nul)", "Nul","Nee","verkoop"),
		array("4", "BTW te vorderen (hoog)", "Hoog","Nee","inkoop"),
		array("5", "BTW te vorderen (laag)", "Laag","Nee","inkoop"),
		array("6", "BTW te vorderen (nul)", "Nul","Nee","inkoop"),
		
		//Verlegd binnen Nederland
		array("31", "BTW te betalen verlegd", "Nul","NL", "verkoop"),
		array("VR21", "te BTW vorderen verlegd","Hoog","NL","inkoop"),
		array("VR6", "te BTW vorderen verlegd","Hoog","NL","inkoop"),

		//Verlegd binnen Europa
		array("34", "IC levering Goederen","Nul","EU","sales"),
		array("35", "IC levering Diensten","Nul","EU","sales"),
		array("V02", "Export buiten de EU ","Uitgesloten van BTW","Ex","sales"),
		array("V03", "Export buiten de EU Diensten","Uitgesloten van BTW","Ex","sales"),
		
		//array("36", "Import buiten EU","Hoog","purchases"),
		array("37", "IC verwerving NUL","Nul","EU","purchases"),
		array(42, "IC Verwerving Laag","Laag","EU","purchases"),
		array(43, "IC Verwerving Hoog","Hoog","EU","purchases"),

		array("40", "BTW vrijgesteld","Nul","Nee","sales"),
	)

	function saleMutations($entry, $transID){

		echo $entry['PeriodFrom'];

		// wat moeten we hier allemaal kunnen

		// splitsen van een transactie? als het over meer dan een jaar loopt 

		// een boeking doen op resultaat

		// een boeking doen op de BTW

		// een boeking doen op debiteuren


	} //function saleMutations

	function saleVat($entry, $vat, $vat_type, $shift){

		if ($vat_type==0){
			$vat_tarif="Nul";
		}
		else if ($vat_type==9){
			$vat_tarif="Laag";
		}
		else if ($vat_type==21){
			$vat_tarif="Hoog";
		}
		
	}




