<?php
	if (isset($_POST['cmd'])) updatePayment();
	elseif ($view) viewPayment($view);
	else viewPaymentList();
	
	function viewPaymentList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM PaymentEndpoint";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>'.__('user').'</th><th>Provider / Bank</th><th>'.__('account').'</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/payment/new\';"/></td></tr>';

		while($item = $list->fetchArray()) {
			$contact=$db->query("SELECT * FROM Contacts WHERE ID=".$item['ContactID'])->fetchArray();
			$provider=$db->query("SELECT * FROM PaymentProviders WHERE ID=".$item['PaymentProviderID'])->fetchArray();
			$content.= '<tr class="data"><td>'.$item['ID'].'</td><td>'.$contact['Name'].'</td><td>'.$provider['ProviderName'].'</td><td>'.$item['Account'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/payment/'.$item['ID'].'\';"/></td></tr>';
			
		}
		$content.= '</table>';
	}
	
	function viewPayment($id) {
		global $db, $content, $url, $lang;

		if ($id=='new'){
			//if refered from contact
			$past_query = explode('/', substr($_SERVER["HTTP_REFERER"], strlen($url)));
			$past_contact=(is_numeric($past_query[2])) ? $past_query[2] : "def";

			$provider = array("ID"=>"", "ProviderName"=>"", "Account"=>"","API"=>"");			
			$payment = array("ID"=>"", "ContactID"=>$past_contact, "PaymentProviderID"=>"def", "Account"=>"", "API"=>"");
		}
		else {
			$payment = $db->query("SELECT * FROM PaymentEndpoint WHERE ID='{$id}'")->fetchArray();
			$provider = $db->query("SELECT * FROM PaymentProviders WHERE ID='".$payment['PaymentProviderID']."'")->fetchArray();

		}

		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<input type="hidden" name="redirect" value="'.$_SERVER["HTTP_REFERER"].'"/>';

		//get providers from database
		$provider_options=array(array("def","choose account"),array("new","new account"));
		$providers = $db->query("SELECT * FROM PaymentProviders ORDER BY ProviderName");
		while($provider = $providers->fetchArray()) array_push($provider_options,array($provider['ID'],$provider['ProviderName']));
		$provider_options_safe=json_encode($provider_options);

		//get contacts from the database
		$contact_options=array(array("def","choose contact"));
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) array_push($contact_options,array($contact['ID'],$contact['Name']));
		$contact_options_safe=json_encode($contact_options);
		
		// kan dit nog handiger met een, voor elke key in array maak hetzelfde?
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$payment['ID'].'</td>';
		$content.= '<tr><th>ContactID</th><td><select id="ContID" name="ContactID"></td></tr>';
		$content.= '<tr><th>Payment Provider</th><td><select id="ProvID" name="ProviderID"></select></td>';

		//disabled fields, are enabled when new is selected and filled when another value is selected
		$content.= '<tr><th>Provider Name</th><td><input type="text" name="ProviderName" id="ProvName" disabled="true" value="'.$provider['ProviderName'].'"/></td></tr>';
		$content.= '<tr><th>Provider Account</th><td><input type="text" name="Account" id="ProvAccount" disabled="true" value="'.$provider['Account'].'"/></td></tr>';		
		$disabled=json_encode(array("ProvName","ProvAccount"));

		$content.= '<tr><th>Account</th><td><input type="text" name="Account" value="'.$payment['Account'].'"/></td></tr>';
		$content.= '</table>';

		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/contacts\';"/>';
		$content.= '</form>';

		//javascript
		$content.= '<script type="text/javascript" src="../../js/payment.js"></script>';
		$content.= '<script>on_prov_change('.$disabled.')</script>';
		$content.= '<script>addOptionsPHP("ProvID",'.$provider_options_safe.',"'.$payment['PaymentProviderID'].'")</script>';
		$content.= '<script>addOptionsPHP("ContID",'.$contact_options_safe.',"'.$payment['ContactID'].'")</script>';

	}
	
	//TODO: make it update all fields, for provider only if select is new
	function updatePayment() {
		global $db, $content, $url, $lang;
		switch ($_POST['cmd']) {
			case 'update':
				if ($_POST['ID']=='new') {
					if ($_POST['ProviderID']=="new"){
						$db->query("INSERT INTO PaymentProviders (ProviderName,Account) VALUES ('".$_POST['ProviderName']."','".$_POST['Account']."')");
						$prov_id = $db->lastInsertRowID();
						$db->query("INSERT INTO PaymentEndpoint (ContactID, PaymentProviderID,Account) VALUES ('".$_POST['ContactID']."','".$prov_id."','".$_POST['Account']."')");			
					}
					else{
						$db->query("INSERT INTO PaymentEndpoint (ContactID, PaymentProviderID,Account) VALUES ('".$_POST['ContactID']."','".$_POST['ProviderID']."','".$_POST['Account']."')");	
					}
				}
				else {
					if ($_POST['ProviderID']=="new"){
						$db->query("INSERT INTO PaymentProviders (ProviderName,Account) VALUES ('".$_POST['ProviderName']."','".$_POST['Account']."')");
						$prov_id = $db->lastInsertRowID();
						$db->query("UPDATE PaymentEndpoint SET ContactID='".$_POST['ContactID']."',PaymentProviderID='".$prov_id."',Account='".$_POST['Account']."' WHERE ID='".$_POST['ID']."'");		
					}
					else{
						$db->query("UPDATE PaymentEndpoint SET ContactID='".$_POST['ContactID']."',PaymentProviderID='".$_POST['ProviderID']."',Account='".$_POST['Account']."' WHERE ID='".$_POST['ID']."'");		
					}
				}
				break;
			case 'remove':
				$db->query("DELETE FROM PaymentEndpoint WHERE ID='".$_POST['ID']."'");
				break;
		}

		$past_query = explode('/', substr($_POST['redirect'], strlen($url)));	
		if (is_numeric($past_query[2])){
			header('Location: '.$_POST['redirect']);
		}
		elseif ($past_query[1]== 'contacts' and $past_query[2]=="new"){
			$cont_id = $db->query("SELECT * FROM Contacts ORDER BY id DESC LIMIT 1")->fetchArray();
			header('Location: '.$url.$lang.'/contacts/'.$cont_id['ID']);
		}
		else{
			viewPaymentList();
		}
	}
