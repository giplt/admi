<?php
	if ($cmd) updateSale();
	elseif ($view) viewSale($view);
	else viewSaleList();

	function viewSaleList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Sales ORDER BY ID";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr>';
		$content.= '<th>ID</th>';
		$content.= '<th>'.__('date').'</th>';
		$content.= '<th>'.__('reference').'</th>';
		$content.= '<th>'.__('amount').'</th>';
		$content.= '<th>'.__('project').'</th>';
		$content.= '<th>'.__('contact').'</th>';
		$content.= '<td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/sales/new\';"/></td>';
		$content.= '</tr>';
		while($sales = $list->fetchArray()) {

			//collect info
			$entry=$db->query("SELECT * FROM Entries WHERE ID='".$sales['EntryID']."'")->fetchArray();

			//get the info for multiple transactions with multiple mutations
			$show_PID=12;
			$transactions=array();
			$mutations=array();
			$amount_sum=0;
			$transaction_results=$db->query("SELECT * FROM Transactions WHERE EntryID='".$entry['ID']."'");
	
			while ($transaction=$transaction_results->fetchArray()){
				array_push($transactions,$transaction);
				$mutation_results=$db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE Mutations.TransactionID='".$transaction['ID']."' AND Accounts.PID='".$show_PID."'");

				while ($mutation=$mutation_results->fetchArray()){
					array_push($mutations,$mutation['Amount']);
				}
			}
			$amount_sum=array_sum($mutations);

			$project = $db->query("SELECT * FROM Projects WHERE ID='".$sales['ProjectID']."'")->fetchArray();
			$contact = $db->query("SELECT * FROM Contacts WHERE ID='".$sales['ContactID']."'")->fetchArray();
			
			//put in the table			
			$content.= '<tr class="data">';
			$content.= '<td>'.$sales['ID'].'</td>';
			$content.= '<td>'.$entry['TransactionDate'].'</td>';
			$content.= '<td>'.$sales['Reference'].'</td>';
			$content.= '<td>'.$amount_sum.'</td>';
			$content.= '<td>'.$project['Name'].'</td>';
			$content.= '<td>'.$contact['Name'].'</td>';
			$content.= '<td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/sales/'.$sales['ID'].'\';"/></td>';
			$content.= '</tr>';
		}
		$content.= '</table>';
	}

	
	function viewSale($id) {
		//checks voor verplichte velden
		//required er in zetten
		//2 = validatie, btw optelling klopt niet

		global $db, $content, $url, $lang;

		//get content
		if ($id=='new'){
			//create empty arrays
			$purchase = array("ID"=>"", "EntryID" => "", "Status" => "", "Reference" => "", "ContactID" => "", "ProjectID" => "", "Nett" => "", "VAT" => "", "Gross" => "", "VAT_Type" => "");
			$entry = array("ID"=>"", "TransactionDate" => "", "AccountingDate" => "", "URL" => "", "Log" => "");
			$transactions=array(array("ID"=>"","entryID"=>"","MergeID"=>""));
			$mutations=array(array("ID"=>"","TransactionID"=>"","AccountID"=>"","Amount"=>""));
		}		
		else {
			//load arrays from the database
			$purchase = $db->query("SELECT * FROM Sales WHERE ID='".$id."'")->fetchArray();
			$entry = $db->query("SELECT * FROM Entries WHERE ID='".$purchase['EntryID']."'")->fetchArray();

			$transactions=array();
			$mutations=array();
			$transaction_results = $db->query("SELECT * FROM Transactions WHERE EntryID='".$entry['ID']."'");

			while ($transaction=$transaction_results->fetchArray()){
				array_push($transactions,$transaction);
				$mutation_results=$db->query("SELECT * FROM Mutations WHERE Mutations.TransactionID='".$transaction['ID']."'");

				while ($mutation=$mutation_results->fetchArray()){
					array_push($mutations,$mutation);
				}
			}
		}			

		$protected = false;
		
		//TODO: base html nog maken
		$content.= '<div class="salesInputFrame"><div class="salesInputForm"><form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<fieldset><legend>'.__('recipient').'</legend><table>';		
		$content.= '<tr><th>ID</th><td>'.$purchase['ID'].'</td>';

                // Contactinformatie - nog meer info nodig? Ja bankrekening
		$options = '<option value="" disabled="disabled"'.($purchase['ContactID']?'':' selected').'>'.__('pick-contact').'</option>';
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) $options.= '<option value="'.$contact['ID'].'"'.($purchase['ContactID']==$contact['ID']?' selected':'').'>'.$contact['Name'].'</option>';
		$content.= '<tr><th>'.__('contact').'</th><td><select id="ContID" name="ContactID">'.$options.'</select> <input type="button" value="'.__('new').'" onclick="window.location.href=\''.$url.$lang.'/contacts/new\';"/></td></tr>';		
		$content.='</table></fieldset>';

		//TODO: WISHLIST multiple accounts for Plan B

		//Datum en locatie van het boekstuk
		$today=date('Y-m-d');
		$content.='<fieldset><legend>'.__('invoice').'</legend><table>';
		$content.= '<tr><th>'.__('date').'</th><td><input type="date" name="TransactionDate" value="'.$entry['TransactionDate'].'"/></td></tr>';
		$content.= '<input type="hidden" name="AccountingDate" value='.$today.'>';
		
		//ProjectID
		$options = '<option value="" disabled="disabled"'.($purchase['ProjectID']?'':' selected').'>'.__('pick-project').'</option>';
		$projects = $db->query("SELECT * FROM Projects ORDER BY Name");
		while($project = $projects->fetchArray()) $options.= '<option value="'.$project['ID'].'"'.($purchase['ProjectID']==$project['ID']?' selected':'').'>'.$project['Name'].'</option>';
		$content.= '<tr><th>'.__('project').'</th><td><select name="ProjectID">'.$options.'</select></td></tr>';
		
		//Reference
		$content.= '<tr><th>'.__('reference').'</th><td><input type="text" name="Reference" value="'.$purchase['Reference'].'"/></td></tr>';
		$content.= '</table></fieldset>';		
		
		$content.= '<fieldset><legend>'.__('invoice items').'</legend><table id="salesTable" class="salesInputTable">';

		//Geef alle opties voor sales, hier later nog even over nadenken, wil je kosten van de omzet appart hebben (voorraad, kosten derder etc)?
		$sales_options=array(array("def","kies type"),array("1","Uren"),array("2","Materialen"),array("3","Reiskosten"));
		$sales_options_safe=json_encode($sales_options);

		//Geef alle opties voor btw-type
		$vat_options=array(array("def","kies type"),array("0","btw-vrij"),array("9","9%"),array("21","21%"));
		$vat_options_safe=json_encode($vat_options);

		//Geef alle opties voor btw verlegging TODO: nog koppelen aan rekeningen
		$shift_options=array(array("nee","nee"),array("NL","NL"),array("EU","EU"),array("Ex","Ex"));
		$shift_options_safe=json_encode($shift_options);

		// Rijen met transacties
		$content.= '<tr class="salesInputRow"><th class="salesInputCol">'.__('sales type').'</th>';
		$content.='<th class="salesInputCol">'.__('description').'</th>'; 
		$content.='<th class="salesInputCol">'.__('number').'</th>';
		$content.='<th class="salesInputCol">'.__('price').'</th>';
		$content.='<th class="salesInputCol">'.__('nett').'</th>';
		$content.='<th class="salesInputCol">'.__('vat type').'</th>';
		$content.='<td class="salesInputColLast"><input type="button" id="addRowButton" value="+"/></td></tr>';
		
		//Laatste rij met het totaal
		$content.= '<table class="salesInputTotTable"><tr class="salesInputRow"><th class="salesInputCol">'.__('total').'</th>';
		$content.= '<td class="salesInputCol"><input type="number" step="0.01" class="salesInputField" id="grossTot"></td>';
		$content.= '<td class="salesInputCol"><input type="number" step="0.01" class="salesInputField" id="nettTot"></td>';
		$content.= '<td class="salesInputCol"><input type="number" step="0.01" class="salesInputField" id="vatTot"></td>';
		$content.= '<td class="salesInputCol"><select id="vatTypeTot"></td>';
		$content.= '<td class="salesInputCol"><select id="vatShift"></td>';
		$content.= '<td class="salesInputColLast"></td>';
		
		//Submit buttons
		$content.= '</table></fieldset>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/sales\';"/>';
		$content.= '</form></div>';
		$content.= '<div class="salesInputVis">Bonnetje</div></div>';
		
		//Laad javascript
		$content.= '<script type="text/javascript" src="../../js/sales.js"></script>';

		//Laad alle transacties uit de database en maak een nieuwe rij aan per transactie en reconstrueer de inhoud
		foreach ($transactions as $trans){
			if ($trans['ID']!=""){
				$sel_options=revMutations($db,$trans,$mutations);	
				$sel_options_safe=json_encode($sel_options);
			}		
			$content.= '<script>addSalesRow('.$sales_options_safe.','.$vat_options_safe.','.$sel_options_safe.')</script>';
		}
		// on click laad een nieuwe regel
		$content.= '<script>addOnClick('.$sales_options_safe.','.$vat_options_safe.')</script>';
		
		//laad de opties voor het totaal
		$content.= '<script>addOptionsPHP("vatTypeTot",'.$vat_options_safe.')</script>';
		$content.= '<script>addOptionsPHP("vatShift",'.$shift_options_safe.')</script>';
	}
	
	function updateSale() {
		global $db, $content, $url, $lang;
		$userIDs = isset($_POST['UserIDs']) ? implode(',', $_POST['UserIDs']) : '';
		switch ($_POST['cmd']) {
			case 'update':
				// validate
				if ($_POST['ID']=='new') {
					// if file uploaded
					// - save in data
					// - get URL

					$db->query("INSERT INTO Entries (TransactionDate, AccountingDate, URL) VALUES ('".$_POST['TransactionDate']."', '".$_POST['AccountingDate']."', '".$_POST['Location']."')");
					// get the entryID from the database $id = $db->lastInsertRowID();
					$last_entry=$db->querySingle("SELECT MAX(ID) FROM Entries LIMIT 1");
					$entryID=intval($last_entry);

					$db->query("INSERT INTO Sales (EntryID, Status, Reference, ContactID, ProjectID) VALUES ('".$entryID."','review','".$_POST['Reference']."', '".$_POST['ContactID']."', '".$_POST['ProjectID']."')");

					foreach ($_POST as $key => $value){
						$checkstr="SalesType";
						if (strpos($key,$checkstr)!==False){
							$db->query("INSERT INTO Transactions (EntryID) VALUES ('".$entryID."')");
							// get the entryID from the database
							$last_trans=$db->querySingle("SELECT MAX(ID) FROM Transactions LIMIT 1");
							$transID=intval($last_trans);
							$trans_num=substr($key, strlen($checkstr),strlen($key));
							$gross_key="gross".$trans_num;
							$nett_key="nett".$trans_num;
							$vat_key="vat".$trans_num;
							$vat_type_key="vatType".$trans_num;
							makeMutations($db,$transID,$value,$_POST[$gross_key],$_POST[$nett_key],$_POST[$vat_key],$_POST[$vat_type_key]);					
		
						}
					}

				}
				else {
					//how to approach this? remove previous mutations or changhe existing ones
					$db->query("UPDATE Accounts SET URL='".$_POST['URL']."', Date='".$_POST['Date']."', ContactID='".$_POST['ContactID']."', ProjectID='".$_POST['ProjectID']."', Reference='".$_POST['Reference']."' WHERE ID='".$_POST['ID']."'");
					$id = $_POST['ID'];
					
					$db->query("UPDATE Mutations SET AccountID='".$_POST['NettAccountID']."', Amount='".$_POST['Nett']."' WHERE ID='".$_POST['NettID']."'");
					
					$db->query("UPDATE Mutations SET AccountID='".$_POST['VATAccountID']."', Amount='".$_POST['VAT']."' WHERE ID='".$_POST['VATID']."'");
					
					$db->query("UPDATE Mutations SET AccountID='".$_POST['GrossAccountID']."', Amount='".$_POST['Gross']."' WHERE ID='".$_POST['GrossID']."'");
				}
				break;
			case 'remove':
				$db->query("DELETE FROM Accounts WHERE ID='".$_POST['ID']."'");
				break;
		}
		viewSaleList();
	}

	function makeMutations($db,$transID,$salesType,$gross,$nett,$vat,$vat_type) {

		//hier de regels voor het inboeken van inkoop transacties, nu nog even simpel
		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '".$salesType."', '".$nett."')");
		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '5', '".$nett."')");	
		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '19', '".$vat."')");
	}
	
	//boekhoudregels voor purchase in reverse, voelt onhandig, misschien gewoon $gross, $vat, $nett, $vat_type etc opslaan in de transaction?

	function revMutations($db, $trans,$mutations){

		$res_list=$db->query("SELECT * FROM Accounts WHERE PID='12'");
		foreach($mutations as $mut){
			if ($mut['TransactionID']==$trans['ID']){
				//result acounts		
			
				while($res=$res_list->fetchArray()){
					if (in_array($mut['AccountID'],$res)){
						$salesType=$mut['AccountID'];
						$nett=$mut['Amount'];
					} 
				}

				//vat accounts
				if ($mut['AccountID']==19){
					$vat=$mut['Amount'];
				}

				$gross=$nett+$vat;
				$vat_type="";
			}
		}

		return array($salesType,$gross,$nett,$vat,$vat_type);
	}

	

