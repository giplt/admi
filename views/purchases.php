<?php
	if ($cmd) updatePurchase();
	elseif ($view) viewPurchase($view);
	else viewPurchaseList();
	
	function viewPurchaseList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Purchases ORDER BY ID";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr>';
		$content.= '<th>ID</th>';
		$content.= '<th>'.__('date').'</th>';
		$content.= '<th>'.__('amount').'</th>';
		$content.= '<th>'.__('project').'</th>';
		$content.= '<th>'.__('expense').'</th>';
		$content.= '<th>'.__('name').'</th>';
		$content.= '<td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/purchases/new\';"/></td>';
		$content.= '</tr>';
		while($item = $list->fetchArray()) {

			//collect info
			$entry=$db->query("SELECT * FROM Entries WHERE ID='".$item['EntrytID']."'")->fetchArray();
			$mutation = $db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE Mutations.TransactionID='".$item['ID']."' AND Accounts.PID='12'")->fetchArray();
			$project = $db->query("SELECT * FROM Projects WHERE ID='".$item['ProjectID']."'")->fetchArray();
			$contact = $db->query("SELECT * FROM Projects WHERE ID='".$item['ContactID']."'")->fetchArray();
			
			//put in the table			
			$content.= '<tr class="data">';
			$content.= '<td>'.$item['ID'].'</td>';
			$content.= '<td>'.$entry['TransactionDate'].'</td>';
			$content.= '<td>'.$mutation['Amount'].'</td>';
			$content.= '<td>'.$project['Name'].'</td>';
			$content.= '<td>'.$contact['Name'].'</td>';
			$content.= '<td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/purchases/'.$item['ID'].'\';"/></td>';
			$content.= '</tr>';
		}
		$content.= '</table>';
	}
	
	function viewPurchase($id) {
		//checks voor verplichte velden
		//required er in zetten
		//2 = validatie, btw optelling klopt niet

		global $db, $content, $url, $lang;
		//get content
		if ($id=='new'){
			$purchase = array("ID"=>"", "EntryID" => "", "Status" => "", "Reference" => "", "ContactID" => "", "ProjectID" => "", "Nett" => "", "VAT" => "", "Gross" => "", "VAT_Type" => "");
			$entry = array("ID"=>"", "TransactionDate" => "", "AccountingDate" => "", "URL" => "", "Log" => "");
		}		
		else {
			$purchase = $db->query("SELECT * FROM Purchases WHERE ID='".$id."'")->fetchArray();
			$entry = $db->query("SELECT * FROM Entries WHERE ID=''".$purchase['EntryID']."'")->fetchArray();
			$transactions = $db->query("SELECT * FROM Transactions WHERE EntryID=''".$entry['ID']."'")->fetchArray();
		}			

		$protected = false;
		
		//TODO: base html nog maken
		$content.= '<script type="text/javascript" src="../../js/purchases.js"></script>'; 
		$content.= '</script>';
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<fieldset><legend>Declaratie door = contact</legend><table>';		
		$content.= '<tr><th>ID</th><td>'.$purchase['ID'].'</td>';

                // Contactinformatie - nog meer info nodig? Ja bankrekening
		$options = '<option value="" disabled="disabled"'.($purchase['ContactID']?'':' selected').'>'.__('pick-contact').'</option>';
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) $options.= '<option value="'.$contact['ID'].'"'.($purchase['ContactID']==$contact['ID']?' selected':'').'>'.$contact['Name'].'</option>';
		$content.= '<tr><th>'.__('contact').'</th><td><select name="ContactID">'.$options.'</select> <input type="button" value="'.__('new').'" onclick="window.location.href=\''.$url.$lang.'/contacts/new\';"/></td></tr>';		
		$content.='</table></fieldset>';

		//Datum en locatie van het boekstuk
		$content.='<fieldset><legend>Bonnetje = boekstuk</legend><table>';
		$content.= '<tr><th>'.__('date').'</th><td><input type="date" name="factuurdatum" value="'.$entry['TransactionDate'].'"/></td></tr>';
		
		// TODO: upload bonnetje naar een specifieke plek op de server
		$content.= '<tr><th>'.__('location').'</th><td><input type="text" name="Location" value="'.$entry['URL'].'"/> <input type="file" value="'.__('upload').'" name=myFile accept="image/*,.pdf"></td></tr>';

		//ProjectID
		$options = '<option value="" disabled="disabled"'.($purchase['ProjectID']?'':' selected').'>'.__('pick-project').'</option>';
		$projects = $db->query("SELECT * FROM Projects ORDER BY Name");
		while($project = $projects->fetchArray()) $options.= '<option value="'.$project['ID'].'"'.($purchase['ProjectID']==$project['ID']?' selected':'').'>'.$project['Name'].'</option>';
		$content.= '<tr><th>'.__('project').'</th><td><select name="ProjectID">'.$options.'</select></td></tr>';
		
		//Reference
		$content.= '<tr><th>'.__('reference').'</th><td><input type="text" name="Reference" value="'.$purchase['Reference'].'"/></td></tr>';
		$content.= '</table></fieldset>';		
		
		// TODO: voeg afdracht toe bij kostensoort uren
		$content.= '<fieldset><legend>Item op bonnetje = transactie</legend><table id="expenseTable">';

		$options = '<option value="" disabled="disabled"'.($purchase['ID']?'':' selected').'>'.__('pick-expense').'</option>';
		$expenses = $db->query("SELECT * FROM Accounts WHERE PID='12' ORDER BY Name");
		while($expense = $expenses->fetchArray()) $options.= '<option value="'.$expense['ID'].'"'.($purchase['ExpenseID']==$expense['ID']?' selected':'').'>'.$expense['Name'].'</option>';
		
		$content.= '<tr><th>'.__('expense').'</th><th>'.__('gross').'</th><th>'.__('nett').'</th><th>'.__('vat').'</th><td><input type="button" onclick="addExpenseRow(\''.htmlentities($options).'\');" value="+" /></td></tr>';
		
		
		// TODO: omgaan met array van transacties
/*		$mutation = $db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE Mutations.TransactionID='".$transactions['ID']."' AND Accounts.PID='12'")->fetchArray();
		$content.= '<tr><th>'.__('amount').'</th><td><input type="text" name="gross" placeholder="'.__('gross').'" value="'.$mutation['Amount'].'"/>';
		$mutation = $db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE Mutations.TransactionID='".$transactions['ID']."' AND Accounts.PID='6'")->fetchArray();
		$content.= '<input type="text" name="nett" placeholder="'.__('nett').'" value="'.$mutation['Amount'].'"/>';
		$mutation = $db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE Mutations.TransactionID='".$transactions['ID']."' AND Accounts.PID='3'")->fetchArray();
		$content.= '<input type="text" name="vat" placeholder="'.__('vat').'" value="'.$mutation['Amount'].'"/></tr>';
*/		
		// BTW type TODO: BTW type inladen? (of niet nodig?) BTW type in het talen-bestand zetten, VAT_Type als database_item
		// $content.= '<tr><th>BTW-type</th>';
		// $options = '<option value="" disabled="disabled"'.($purchase['VAT_Type']?'':' selected').'>Choose VAT type</option>';
		// $options.= '<option value="0">0% (vrijgesteld)</option>';
		// $options.= '<option value="9">9%</option>';
		// $options.= '<option value="9VNL">9% verlegd NL</option>';
		// $options.= '<option value="9VEU">9% verlegd EU</option>';
		// $options.= '<option value="21">21%</option>';
		// $options.= '<option value="21VNL">21% verlegd NL</option>';
		// $options.= '<option value="21VEU">21% verlegd EU</option>';
		// $options.= '<option value="21">21%</option>';
		// $options.= '<option value="21% verlegd">21% velegd EU</option>';
		// $content.= '<td><select name="VAT_Type">'.$options.'</select></td></tr>';
		
		//Submit buttons
		$content.= '</table></fieldset>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/purchases\';"/>';
		$content.= '</form>';
	}
	
	function updatePurchase() {
		global $db, $content, $url, $lang;
		$userIDs = isset($_POST['UserIDs']) ? implode(',', $_POST['UserIDs']) : '';
		switch ($_POST['cmd']) {
			case 'update':
				// validate
				if ($_POST['ID']=='new') {
					// if file uploaded
					// - save in data
					// - get URL
					$db->query("INSERT INTO Transactions (URL, Type, Date, ContactID, ProjectID, Reference) VALUES ('".$_POST['URL']."', '2', '".$_POST['Date']."', '".$_POST['ContactID']."', '".$_POST['ProjectID']."', '".$_POST['Reference']."')");
					$id = $db->lastInsertRowID();
					
					$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$id."', '".$_POST['ExpenseID']."', '".$_POST['Nett']."')");
					$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$id."', '19', '".$_POST['VAT']."')");
					
					// check if creditor (PID=5) exists for contactID, create if absent
					$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$id."', '".$_POST['AccountID']."', '".$_POST['Gross']."')");
				}
				else {
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
		viewPurchaseList();
	}
