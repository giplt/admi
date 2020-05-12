<?php
	if (!is_dir('files')) mkdir('files');
	
	if(isset($_FILES["bankCSV"])) {
		$error = false;
		
		$maxsize = ini_get("upload_max_filesize");
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $maxsize); // Remove the non-unit characters from the size.
		$maxsize = preg_replace('/[^0-9\.]/', '', $maxsize); // Remove the non-numeric characters from the size.
		$maxsize = $unit ? round($maxsize * pow(1024, stripos('bkmgtpezy', $unit[0]))) : round($maxsize);
		if ($_FILES["bankCSV"]["size"] > $maxsize) $error.= 'Sorry, your file is too large.<br/>';
		
		$filetype = strtolower(pathinfo($_FILES["bankCSV"]["name"],PATHINFO_EXTENSION));
		if($filetype!="csv") $error.= 'Wrong file type<br/>';
		
		if ($error) {
			$error.= 'Only CSV files are allowed. Maximum size: '.ini_get("upload_max_filesize").'<br/>';
			echo $error;
			exit();
		}
		
		$csv = file_get_contents($_FILES["bankCSV"]['tmp_name']);
		echo $csv;
		exit;
	}
	
	if ($cmd) updateBank();
	elseif ($view) viewBank($view);
	else viewBankList();

	function viewBankList() {
		global $db, $content, $url, $lang;
		
		//Laad javascript
		$content = '<script type="text/javascript" src="../js/bank.js"></script>';
		$content.= '<form id="bankForm" action="bank" method="post">';
		
		$query = "SELECT * FROM Accounts WHERE PID='3' ORDER BY Name";
		$banks = $db->query($query);
		$selected = isset($_GET['banks']) ? explode(',', $_GET['banks']) : array();
		while($bank = $banks->fetchArray()) {
//			$checked = in_array($bank['ID'], $selected) ? 'on' : 'off';
			$content.= '<input type="checkbox" name="bank_'.$bank['ID'].'"'.(in_array($bank['ID'], $selected)?' checked':'').' onchange="selectBanks(this);"/>'.$bank['Name'].'&nbsp;';
		}
		$content.= ' | Import CSV <input id="importButton" type="file" value="'.__('upload').'" name=myFile accept=".csv" onchange="upload(\'bankCSV\', this);"'.(count($selected)==1?'':' disabled').'>';
		$content.= '<div id="csvView"></div>';
/*		
		$query = "SELECT * FROM Bank ORDER BY ID";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr>';
		$content.= '<th>ID</th>';
		$content.= '<th>'.__('date').'</th>';
		$content.= '<th>'.__('amount').'</th>';
		$content.= '<th>'.__('project').'</th>';
		$content.= '<th>'.__('expense').'</th>';
		$content.= '<th>'.__('name').'</th>';
		$content.= '<td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/bank/new\';"/></td>';
		$content.= '</tr>';
		while($item = $list->fetchArray()) {

			//collect info
			$entry=$db->query("SELECT * FROM Entries WHERE ID='".$item['EntryID']."'")->fetchArray();

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

			$project = $db->query("SELECT * FROM Projects WHERE ID='".$item['ProjectID']."'")->fetchArray();
			$contact = $db->query("SELECT * FROM Contacts WHERE ID='".$item['ContactID']."'")->fetchArray();
			
			//put in the table			
			$content.= '<tr class="data">';
			$content.= '<td>'.$item['ID'].'</td>';
			$content.= '<td>'.$entry['TransactionDate'].'</td>';
			$content.= '<td>'.$item['Reference'].'</td>';
			$content.= '<td>'.$amount_sum.'</td>';
			$content.= '<td>'.$project['Name'].'</td>';
			$content.= '<td>'.$contact['Name'].'</td>';
			$content.= '<td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/bank/'.$item['ID'].'\';"/></td>';
			$content.= '</tr>';
		}
		$content.= '</table>';
*/
		$content.= '</form>';
	}
	
	function viewBank($id) {
		//checks voor verplichte velden
		//required er in zetten
		//2 = validatie, btw optelling klopt niet

		global $db, $content, $url, $lang;

		//get content
		if ($id=='new') {
			//create empty arrays
			$bank = array("ID"=>"", "EntryID" => "", "Status" => "", "Reference" => "", "ContactID" => "", "ProjectID" => "", "Nett" => "", "VAT" => "", "Gross" => "", "VAT_Type" => "");
			$entry = array("ID"=>"", "TransactionDate" => "", "AccountingDate" => "", "URL" => "", "Log" => "");
			$transactions=array(array("ID"=>"","entryID"=>"","MergeID"=>""));
			$mutations=array(array("ID"=>"","TransactionID"=>"","AccountID"=>"","Amount"=>""));
		}		
		else {
			//load arrays from the database
			$bank = $db->query("SELECT * FROM Bank WHERE ID='".$id."'")->fetchArray();
			$entry = $db->query("SELECT * FROM Entries WHERE ID='".$bank['EntryID']."'")->fetchArray();

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
		$content.= '<div class="expenseInputFrame">';
		$content.= '<div class="expenseInputForm">';
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<fieldset><legend>Declaratie door = contact</legend>';
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$bank['ID'].'</td>';

        // Contactinformatie - nog meer info nodig? Ja bankrekening
		$options = '<option value="" disabled="disabled"'.($bank['ContactID']?'':' selected').'>'.__('pick-contact').'</option>';
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) $options.= '<option value="'.$contact['ID'].'"'.($bank['ContactID']==$contact['ID']?' selected':'').'>'.$contact['Name'].'</option>';
		$content.= '<tr><th>'.__('contact').'</th><td><select id="ContID" name="ContactID">'.$options.'</select> <input type="button" value="'.__('new').'" onclick="window.location.href=\''.$url.$lang.'/contacts/new\';"/></td></tr>';		
		
		//get payment endpoints from database
		//TODO: ergens opslaan van de rekeninggegegevens, waar?
		$payment_options=array(array("def","choose account"));
		$paymentEndPoints = $db->query("SELECT * FROM PaymentEndpoint");
		while($paymentEndPoint = $paymentEndPoints->fetchArray()) array_push($payment_options,array($paymentEndPoint['ID'],$paymentEndPoint['Account'],$paymentEndPoint['ContactID']));
		$payment_options_safe=json_encode($payment_options);
			
		//choose account
		$content.= '<tr><th>'.__('account').'</th><td><select id="PaymentID" name="PaymentID"></select></td></tr>';		
		$content.='</table></fieldset>';

		//Datum en locatie van het boekstuk
		$today=date('Y-m-d');
		$content.='<fieldset><legend>Bonnetje = boekstuk</legend><table>';
		$content.= '<tr><th>'.__('date').'</th><td><input type="date" name="TransactionDate" value="'.$entry['TransactionDate'].'"/></td></tr>';
		$content.= '<input type="hidden" name="AccountingDate" value='.$today.'>';
		
		// upload bonnetje naar een specifieke plek op de server
		$content.= '<tr><th>'.__('location').'</th><td><input type="text" id="url" name="Location" value="'.$entry['URL'].'"/>' ;
		$content.= '<input type="file" value="'.__('upload').'" name=myFile accept="image/*,.pdf" onchange="upload(\'invoice\', this);"></td></tr>';

		//ProjectID
		$options = '<option value="" disabled="disabled"'.($bank['ProjectID']?'':' selected').'>'.__('pick-project').'</option>';
		$projects = $db->query("SELECT * FROM Projects ORDER BY Name");
		while($project = $projects->fetchArray()) $options.= '<option value="'.$project['ID'].'"'.($bank['ProjectID']==$project['ID']?' selected':'').'>'.$project['Name'].'</option>';
		$content.= '<tr><th>'.__('project').'</th><td><select name="ProjectID">'.$options.'</select></td></tr>';
		
		//Reference
		$content.= '<tr><th>'.__('reference').'</th><td><input type="text" name="Reference" value="'.$bank['Reference'].'"/></td></tr>';
		$content.= '</table></fieldset>';		
		
		// TODO: voeg afdracht toe bij kostensoort uren
		$content.= '<fieldset><legend>Item op bonnetje = transactie</legend><table id="expenseTable" class="expenseInputTable">';

		//Haal alle opties voor kostensoort uit de database
		$exp_options = array(array("def","pick-expense"));
		$expenses = $db->query("SELECT * FROM Accounts WHERE PID='12' ORDER BY Name");
		while($expense = $expenses->fetchArray()) array_push($exp_options,array($expense['ID'],$expense['Name']));
		$exp_options_safe=json_encode($exp_options);

		//Geef alle opties voor btw-type
		$vat_options=array(array("def","kies type"),array("0","btw-vrij"),array("9","9%"),array("21","21%"));
		$vat_options_safe=json_encode($vat_options);

		//Geef alle opties voor btw verlegging TODO: nog koppelen aan rekeningen
		$shift_options=array(array("nee","nee"),array("NL","NL"),array("EU","EU"),array("Ex","Ex"));
		$shift_options_safe=json_encode($shift_options);

		// Rijen met transacties
		$content.= '<tr class="expenseInputRow"><th class="expenseInputCol">'.__('expense').'</th>'; 
		$content.='<th class="expenseInputCol">'.__('gross').'</th>';
		$content.='<th class="expenseInputCol">'.__('nett').'</th>';
		$content.='<th class="expenseInputCol">'.__('vat').'</th>';
		$content.='<th class="expenseInputCol">vat_type</th>';
		$content.='<th class="expenseInputCol">verlegd</th>';
		$content.='<td class="expenseInputColLast"><input type="button" id="addRowButton" value="+"/></td></tr>';
		
		//Laatste rij met het totaal
		$content.= '<table class="expenseInputTotTable"><tr class="expenseInputRow"><th class="expenseInputCol">'.__('total').'</th>';
		$content.= '<td class="expenseInputCol"><input type="number" step="0.01" class="expenseInputField" id="grossTot"></td>';
		$content.= '<td class="expenseInputCol"><input type="number" step="0.01" class="expenseInputField" id="nettTot"></td>';
		$content.= '<td class="expenseInputCol"><input type="number" step="0.01" class="expenseInputField" id="vatTot"></td>';
		$content.= '<td class="expenseInputCol"><select id="vatTypeTot"></td>';
		$content.= '<td class="expenseInputCol"><select id="vatShift"></td>';
		$content.= '<td class="expenseInputColLast"></td>';
		
		//Submit buttons
		$content.= '</table></fieldset>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/bank\';"/>';
		$content.= '</form>';
		$content.= '</div>';
		$content.= '<div id="invoiceView" class="expenseInputVis">';
		switch(pathinfo($entry['URL'], PATHINFO_EXTENSION)) {
			case 'pdf': $content.= '<embed src="files/'.$entry['URL'].'" width="400px" height="600px" />'; break;
			case 'jpg': $content.= '<img src="files/'.$entry['URL'].'" width="400px" />'; break;
			default: $content.= 'Bonnetje'; break;
		}
		$content.= '</div>';
		$content.= '</div>';
		
		//Laad javascript
		$content.= '<script type="text/javascript" src="../../js/bank.js"></script>';

		//Laad alle transacties uit de database en maak een nieuwe rij aan per transactie en reconstrueer de inhoud
		foreach ($transactions as $trans){
			$sel_options=revMutations($db,$trans,$mutations);	
			$sel_options_safe=json_encode($sel_options);		
			$content.= '<script>addExpenseRow('.$exp_options_safe.','.$vat_options_safe.','.$sel_options_safe.')</script>';
		}
		// on click laad een nieuwe regel
		$content.= '<script>addOnClick('.$exp_options_safe.','.$vat_options_safe.')</script>';
		
		//laad de opties voor het totaal
		$content.= '<script>addOptionsPHP("vatTypeTot",'.$vat_options_safe.')</script>';
		$content.= '<script>addOptionsPHP("vatShift",'.$shift_options_safe.')</script>';
		$content.= '<script>addOptionsPHP_onclick("PaymentID",'.$payment_options_safe.',"ContID")</script>';
	}
	
	function updateBank() {
		global $db, $content, $url, $lang;
		$userIDs = isset($_POST['UserIDs']) ? implode(',', $_POST['UserIDs']) : '';
		switch ($_POST['cmd']) {
			case 'import':
				
				break;
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

					$db->query("INSERT INTO Bank (EntryID, Status, Reference, ContactID, ProjectID) VALUES ('".$entryID."','review','".$_POST['Reference']."', '".$_POST['ContactID']."', '".$_POST['ProjectID']."')");

					foreach ($_POST as $key => $value){
						$checkstr="ExpenseType";
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
		viewBankList();
	}
