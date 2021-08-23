<?php
	if (isset($_POST['cmd'])) updateContact();
	elseif ($view) viewContact($view);
	else viewContactList();
	
	function viewContactList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Contacts ORDER BY Name";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>'.__('name').'</th><th>'.__('type').'</th><th>'.__('active').'status</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/contacts/new\';"/></td></tr>';
		while($item = $list->fetchArray()) {
			$isUser = $db->querySingle("SELECT COUNT(*) as count FROM Users WHERE ContactID='{$item['ID']}'");
//			$user = $db->query("SELECT * FROM Users WHERE ContactID='{$item['ID']}'")->fetchArray();
			$content.= '<tr class="data"><td>'.$item['ID'].'</td><td>'.$item['Name'].'</td><td>'.($isUser?__('user'):'').'</td><td>'.$item['Status'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/contacts/'.$item['ID'].'\';"/></td></tr>';
		}
		$content.= '</table>';
	}
	
	function viewContact($id) {
		global $db, $content, $url, $lang;
		if ($id=='new') $contact = array("ID"=>"", "Name"=>"", "Address"=>"", "Zipcode"=>"", "City"=>"","Country"=>"","Phone"=>"","Email"=>"","Member"=>"no", "Status"=>"Active");
		else $contact = $db->query("SELECT * FROM Contacts WHERE ID='{$id}'")->fetchArray();
		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		
		// kan dit nog handiger met een, voor elke key in array maak hetzelfde?
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$contact['ID'].'</td>';
		$content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" placeholder="Voor en Achternaam" value="'.$contact['Name'].'"/></td></tr>';
		$content.= '<tr><th>'.__('address').'</th><td><input type="text" name="Address" value="'.$contact['Address'].'"/></td></tr>';
		$content.= '<tr><th>'.__('zipcode').'</th><td><input type="text" name="Zipcode" value="'.$contact['Zipcode'].'"/></td></tr>';
		$content.= '<tr><th>'.__('city').'</th><td><input type="text" name="City" value="'.$contact['City'].'"/></td></tr>';
		$content.= '<tr><th>'.__('country').'</th><td><input type="text" name="Country" value="'.$contact['Country'].'"/></td></tr>';
		$content.= '<tr><th>'.__('phone').'</th><td><input type="text" name="Phone" value="'.$contact['Phone'].'"/></td></tr>';
		$content.= '<tr><th>'.__('email').'</th><td><input type="text" name="Email" value="'.$contact['Email'].'"/></td></tr>';
		
		//lid van Plan B		
		$content.= '<tr><th>'.__('membership').'</th><td>';
		$content.= '<input type="radio" name="Member" value="no"'.(($contact['Member']=="no") ? "checked" : "").'> '.__('no').' ';
		$content.= '<input type="radio" name="Member" value="yes"'.(($contact['Member']=="yes") ? "checked" : "").'> '.__('yes').' </td></tr>';

		//Payment End Points
		$content.= '<table><tr><tr><th>Rekeningen</th><td><button type = "submit" name="cmd" value="add_account">+</td></tr></tr>';
		$payment_accounts=$db->query("SELECT * FROM PaymentEndpoint WHERE ContactID='{$id}'");
		while($pay = $payment_accounts->fetchArray()){
			$edit='edit'.$pay['ID'];
			$content.= '<tr><td>'.$pay['Account'].'</td><td><button type="submit" name="cmd" value="'.$edit.'">edit</td></tr>';
		}
		//Status 
		$content.= '<tr>';
		$content.= '<th>Status</th>';
		$content.= '<td><select name="Status" id="status">
			<option value="active"'.($contact['Status']=='active'?' selected':'').'>Active</option>
			<option value="deactivated"'.($contact['Status']=='deactivated'?' selected':'').'>Deactivated</option>
		  </select></td>';


		$content.= '</tr>';
		$content.= '</table>';
	
		//submit buttons
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		//if (!$protected) 
		$content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/contacts\';"/>';
		$content.= '</form>';
	}
	
	function updateContact() {
		global $db, $content, $url, $lang;
		$status = $_POST['Status'];
		switch (true) {
			case ($_POST['cmd']=='update'):
				updateContact_fields($_POST);
				viewContactList();
				break;

			case ($_POST['cmd']=='remove'):
			//Check if contact has any accounts
			
				$contactExistsInPurchases = $db->querySingle("SELECT COUNT(*) as count FROM Purchases WHERE ContactID='".$_POST['ID']."'");
				$contactExistsInUsers = $db->querySingle("SELECT COUNT(*) as count FROM Users WHERE ContactID='".$_POST['ID']."'");
				$contactExistsInSales = $db->querySingle("SELECT COUNT(*) as count FROM Sales WHERE ContactID='".$_POST['ID']."'");
				$contactExistsInMemorial = $db->querySingle("SELECT COUNT(*) as count FROM Memorial WHERE ContactID='".$_POST['ID']."'");
				$contactExistsInPaymentEndpoint = $db->querySingle("SELECT COUNT(*) as count FROM PaymentEndpoint WHERE ContactID='".$_POST['ID']."'");

				if ($contactExistsInPurchases || $contactExistsInUsers || $contactExistsInSales || $contactExistsInMemorial||$contactExistsInPaymentEndpoint) {
					//Check if contact has been used
					echo 'Contact is used, it cannot be deleted';
				}
				else {
					//Delete the contact
				$contact = $db->query("SELECT * FROM Contacts WHERE ID='".$_POST['ID']."'")->fetchArray();
				$db->query("DELETE FROM Contacts WHERE ID='".$_POST['ID']."'");
				}
			
			/*
				//Delete all paymentendpoints belonging to this contact
				$payment_accounts=$db->query("SELECT * FROM PaymentEndpoint WHERE ContactID='".$POST['ID']."'");
				while($acc= $payment_accounts->fetchArray()){
					$db->query("DELETE FROM PaymentEndpoint WHERE ID='".$acc['ID']."'");
				}
				//Delete all (accounts) belonging to the contact
								
				//Delete the contact
				$contact = $db->query("SELECT * FROM Contacts WHERE ID='".$_POST['ID']."'")->fetchArray();
				$db->query("DELETE FROM Contacts WHERE ID='".$_POST['ID']."'");
			*/
				viewContactList();
				break;

			case  ($_POST['cmd']=='add_account'):
				updateContact_fields($_POST);
				$payurl=$url.$lang.'/payment/new';
				header('Location: '.$payurl);
				break;

			case (substr($_POST['cmd'],0,4)=='edit'):
				updateContact_fields($_POST);
				$payid=substr($_POST['cmd'],4);
				$payurl=$url.$lang.'/payment/'.$payid;
				header('Location: '.$payurl);
				break;
		}	
	}

	function updateContact_fields($DAT){
		global $db, $content, $url, $lang;
		//insert all fields
		if ($DAT['ID']=='new') {
			$db->query("INSERT INTO Contacts (Name,Address,Zipcode,City,Country,Phone,Email,Member, Status) VALUES ('".$DAT['Name']."','".$DAT['Address']."','".$DAT['Zipcode']."','".$DAT['City']."','".$DAT['Country']."','".$DAT['Phone']."','".$DAT['Email']."','".$DAT['Member']."','".$DAT['Status']."')");					
			$id = $db->lastInsertRowID();
		}
		//else update all fields
		else {
			$db->query("UPDATE Contacts SET Name='".$DAT['Name']."',Address='".$DAT['Address']."',Zipcode='".$DAT['Zipcode']."',City='".$DAT['City']."',Country='".$DAT['Country']."',Phone='".$DAT['Phone']."',Email='".$DAT['Email']."',Member='".$DAT['Member']."',Status='".$DAT['Status']."'  WHERE ID='".$DAT['ID']."'");
			$id = $_POST['ID'];
		}

	}
