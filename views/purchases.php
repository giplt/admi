<?php
	if ($cmd) updatePurchase();
	elseif ($view) viewPurchase($view);
	else viewPurchaseList();
	
	function viewPurchaseList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Transactions WHERE Type = 2 ORDER BY Date";
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
			$content.= '<tr class="data">';
			$content.= '<td>'.$item['ID'].'</td>';
			$content.= '<td>'.$item['Date'].'</td>';
			$mutation = $db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE Mutations.TransactionID='".$item['ID']."' AND Accounts.PID='12'")->fetchArray();
			$content.= '<td>'.$mutation['Amount'].'</td>';
			$project = $db->query("SELECT * FROM Projects WHERE ID='".$item['ProjectID']."'")->fetchArray();
			$content.= '<td>'.$project['Name'].'</td>';
			$content.= '<td>'.$mutation['Name'].'</td>';
			$contact = $db->query("SELECT * FROM Projects WHERE ID='".$item['ContactID']."'")->fetchArray();
			$content.= '<td>'.$contact['Name'].'</td>';
			$content.= '<td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/purchases/'.$item['ID'].'\';"/></td>';
			$content.= '</tr>';
		}
		$content.= '</table>';
	}
	
	function viewPurchase($id) {
		global $db, $content, $url, $lang;
		if ($id=='new') $purchase = array("ID"=>"", "Type"=>"2", "Date" => "", "Location" => "", "Reference" => "", "ContactID" => "", "ProjectID" => "", "Nett" => "", "VAT" => "", "Gross" => "");
		else $purchase = $db->query("SELECT * FROM Transactions WHERE ID='{$id}'")->fetchArray();
		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$purchase['ID'].'</td>';
		$content.= '<tr><th>'.__('date').'</th><td><input type="text" name="Date" value="'.$purchase['Date'].'"/></td></tr>';
		
		// TODO: upload bonnetje
		$content.= '<tr><th>'.__('location').'</th><td><input type="text" name="Location" value="'.$purchase['URL'].'"/></td></tr>';
		
		$content.= '<tr><th>'.__('reference').'</th><td><input type="text" name="Reference" value="'.$purchase['Reference'].'"/></td></tr>';
		
		$options = '<option value="" disabled="disabled"'.($purchase['ProjectID']?'':' selected').'>'.__('pick-project').'</option>';
		$projects = $db->query("SELECT * FROM Projects ORDER BY Name");
		while($project = $projects->fetchArray()) $options.= '<option value="'.$project['ID'].'"'.($purchase['ProjectID']==$project['ID']?' selected':'').'>'.$project['Name'].'</option>';
		$content.= '<tr><th>'.__('project').'</th><td><select name="ProjectID">'.$options.'</select></td></tr>';
		
		$options = '<option value="" disabled="disabled"'.($purchase['ContactID']?'':' selected').'>'.__('pick-contact').'</option>';
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) $options.= '<option value="'.$contact['ID'].'"'.($purchase['ContactID']==$contact['ID']?' selected':'').'>'.$contact['Name'].'</option>';
		$content.= '<tr><th>'.__('contact').'</th><td><select name="ContactID">'.$options.'</select></td></tr>';
		
		// TODO: voeg afdracht toe bij kostensoort uren
		$options = '<option value="" disabled="disabled"'.($purchase['ExpenseID']?'':' selected').'>'.__('pick-expense').'</option>';
		$expenses = $db->query("SELECT * FROM Accounts WHERE PID='12' ORDER BY Name");
		while($expense = $expenses->fetchArray()) $options.= '<option value="'.$expense['ID'].'"'.($purchase['ExpenseID']==$expense['ID']?' selected':'').'>'.$expense['Name'].'</option>';
		$content.= '<tr><th>'.__('expense').'</th><td><select name="ExpenseID">'.$options.'</select></td></tr>';
		
		// TODO: gebruik btw percentages in calculator
		$mutation = $db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE Mutations.TransactionID='".$purchase['ID']."' AND Accounts.PID='12'")->fetchArray();
		$content.= '<tr><th>'.__('nett').'</th><td><input type="text" name="Nett" value="'.$mutation['Amount'].'"/></td></tr>';
		$mutation = $db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE Mutations.TransactionID='".$purchase['ID']."' AND Accounts.PID='6'")->fetchArray();
		$content.= '<tr><th>'.__('vat').'</th><td><input type="text" name="VAT" value="'.$mutation['Amount'].'"/></td></tr>';
		$mutation = $db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE Mutations.TransactionID='".$purchase['ID']."' AND Accounts.PID='3'")->fetchArray();
		$content.= '<tr><th>'.__('gross').'</th><td><input type="text" name="Gross" value="'.$mutation['Amount'].'"/></td></tr>';
		
		$content.= '</table>';
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
