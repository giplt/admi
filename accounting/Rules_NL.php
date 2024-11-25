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

	
	$VAT_shift_options=array("Nee","NL","EU")


	function expenseMutations($db, $entry, $line, $transid){

		//check if the expensetype exists in purchases view itself (cannot be def)
		
		//the rules that need to be checked on order to aprove the mutation
		$check=array(
			"Type" => "inkoop";
			"VAT_Type" => $line['VAT_Type'];
			"VAT_Shift" => $line['VAT_Shift'];
		);

		//walk through each line in the rulebook and add each mutation to the mutation list
		$mutation_list=array();

		foreach($rules_dict as $rule){

			$mutate=false;

			//walk through the columns of the rulebook
			foreach($rule as $key=> $value){

				//only look at those variables that are in the checklist
				if(array_key_exists($key,$check){

					//if the rule applies to this entry or to all (*) 
					if($rule[$key]==$check[$key] or $rule[$key]=="*"){
						$mutate=true;

					} //if

					else{
						$mutate=false;
					} //else
					

				} //if

			} //foreach

			//add mutation to the mutation_list,
			// add the item that is in the rulebook
			if($mutate){

				$amount=$line[$rule['Item']];

				if($rule['AccountVar']==""){
					$account=$rule['AccountID'];
				}
				else{
					$account=$line[$rule['AccountVar']];
				}

				array_push($mutation_list, array(
					"TransactionID" => $transid,					
					"AccountID"=> $account,
					"Amount"=>$amount
					));

			} //if
		
			 
		} //foreach

		//TODO: add together double bookings to Deb if the VAT date and Period correspond to eachother
		//Keeping it seperated is only usefull when there are invoices that go over years, you want to book 
		//expenses in the last year, but book the vat in this year and you need to split the booking to credit
	
		//Get all the values for Account in the mutation list
		array
		foreach($mutation_list as $m){
	
		}
		
		$mutation_list_new=$mutation_list;

		foreach($mutation_list_new){
			
	
		}
		


		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '".$expenseType."', '".$nett."')");

		getVatCode($line, "expense");
		
		$vatpayed=
		
		//book credit
		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '".$expenseType."', '".($nett+$vatpayed)."')");

	}

	


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




