<?php
	if (isset($_POST['cmd'])) updateContact();
	elseif ($view) viewContact($view);
	else viewContactList();
	
	function viewContactList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Contacts ORDER BY Name";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>'.__('name').'</th><th>'.__('type').'</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/contacts/new\';"/></td></tr>';
		while($item = $list->fetchArray()) {
			$isUser = $db->querySingle("SELECT COUNT(*) as count FROM Users WHERE ContactID='{$item['ID']}'");
//			$user = $db->query("SELECT * FROM Users WHERE ContactID='{$item['ID']}'")->fetchArray();
			$content.= '<tr class="data"><td>'.$item['ID'].'</td><td>'.$item['Name'].'</td><td>'.($isUser?__('user'):'').'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/contacts/'.$item['ID'].'\';"/></td></tr>';
		}
		$content.= '</table>';
	}
	
	function viewContact($id) {
		global $db, $content, $url, $lang;
		if ($id=='new') $contact = array("ID"=>"", "Name"=>"", "Address"=>"", "Zipcode"=>"", "City"=>"","Country"=>"","Phone"=>"","Email"=>"","Member"=>"no");
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
		
		//default werkt nog niet:  werkt nog niet //<?php if($contact['Member']=="no") echo 'checked="checked"'		
		$content.= '<tr><th>'.__('membership').'</th><td>';
		$content.= '<input type="radio" name="Member" value="no" /> '.__('no').' ';
		$content.= '<input type="radio" name="Member" value="yes"/> '.__('yes').' </td></tr>';
		$content.= '<tr><th>'.$contact['Lid'].'</th></tr></table>';

		//TODO: lijst met accounts en een + om een nieuwe aan te maken, werkt nu via andere view, maar beter aanpassen om gewoon via javascript te werken en in contacts op te slaan
		
		//Payment End Points
		$content.= '<table><tr><tr><th>Rekeningen</th><td><input type="button" value="+" onclick="window.location.href=\''.$url.$lang.'/payment/new\';"/></td></tr></tr>';
		$payment_accounts=$db->query("SELECT * FROM PaymentEndpoint WHERE ContactID='{$id}'");
		while($pay = $payment_accounts->fetchArray()){
			$content.= '<tr><td>'.$pay['Account'].'</td><td><input type="button" value="-" onclick="window.location.href=\''.$url.$lang.'/payment/'.$pay['ID'].'\';"/></td></tr>';
		}
		$content.= '</table>';

		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/contacts\';"/>';
		$content.= '</form>';
	}
	
	function updateContact() {
		global $db, $content, $url, $lang;
		switch ($_POST['cmd']) {
			case 'update':
				if ($_POST['ID']=='new') {
					$db->query("INSERT INTO Contacts (Name,Address,Zipcode,City,Country,Phone,Email,Member) VALUES ('".$_POST['Name']."','".$_POST['Address']."','".$_POST['Zipcode']."','".$_POST['City']."','".$_POST['Country']."','".$_POST['Phone']."','".$_POST['Email']."','".$_POST['Member']."')");					
					$id = $db->lastInsertRowID();
				}
				else {
					$db->query("UPDATE Contacts SET Name='".$_POST['Name']."' WHERE ID='".$_POST['ID']."'");
					$id = $_POST['ID'];
				}
				break;
			case 'remove':
				$contact = $db->query("SELECT * FROM Contacts WHERE ID='".$_POST['ID']."'")->fetchArray();
				$db->query("DELETE FROM Contacts WHERE ID='".$_POST['ID']."'");
				break;
		}
		viewContactList();
	}
