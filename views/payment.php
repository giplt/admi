<?php
	if (isset($_POST['cmd'])) updatePayment();
	elseif ($view) viewPayment($view);
	else viewPaymentList();
	
	function viewPaymentList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM PaymentEndpoint";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>'.__('name').'</th><th>'.__('type').'</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/payment/new\';"/></td></tr>';
		while($item = $list->fetchArray()) {
			$content.= '<tr class="data"><td>'.$item['ID'].'</td><td>'.$item['ContactID'].'</td><td>'.$item['Account'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/payment/'.$item['ID'].'\';"/></td></tr>';
		}
		$content.= '</table>';
	}
	
	function viewPayment($id) {
		global $db, $content, $url, $lang;
		if ($id=='new'){
			$provider = array("ID"=>"", "ProviderName"=>"", "Account"=>"","API"=>"");
			$payment = array("ID"=>"", "ContactID"=>"", "PaymentProviderID"=>"", "Account"=>"", "API"=>"");
		}
		else {
			$payment = $db->query("SELECT * FROM paymentEndPoint WHERE ID='{$id}'")->fetchArray();
			$provider = $db->query("SELECT * FROM paymentProviders WHERE ID='".$payment['PaymentProviderID']."'")->fetchArray();

		}
		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';

		//get providers from database
		$provider_options=array(array("def","choose account"),array("new","new account"));
		$providers = $db->query("SELECT * FROM PaymentProviders ORDER BY ProviderName");
		while($provider = $providers->fetchArray()) array_push($provider_options,array($providers['ID'],$providers['ProviderName']));
		$provider_options_safe=json_encode($provider_options);
		
		// kan dit nog handiger met een, voor elke key in array maak hetzelfde?
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$payment['ID'].'</td>';
		$content.= '<tr><th>Payment Provider</th><td><select id="ProvID" name="ProviderID"></select></td>';

		//disabled fields, are enabled when new is selected and filled when another value is selected
		$content.= '<tr><th>Provider Name</th><td><input type="text" name="ProviderName" id="ProvName" disabled="true" value="'.$provider['ProviderName'].'"/></td></tr>';
		$content.= '<tr><th>Provider Account</th><td><input type="text" name="Account" id="ProvAccount" disabled="true" value="'.$provider['Account'].'"/></td></tr>';		
		$disabled=json_encode(array("ProvName","ProvAccount"));

		$content.= '<tr><th>ContactID</th><td><input type="text" name="ContactID" value="'.$payment['ContactID'].'"/></td></tr>';
		$content.= '<tr><th>Account</th><td><input type="text" name="Account" value="'.$payment['Account'].'"/></td></tr>';
		$content.= '</table>';

		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/contacts\';"/>';
		$content.= '</form>';

		//javascript
		$content.= '<script type="text/javascript" src="../../js/payment.js"></script>';
		$content.= '<script>on_prov_change('.$disabled.')</script>';
		$content.= '<script>addOptionsPHP("ProvID",'.$provider_options_safe.')</script>';
		

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
						$db->query("INSERT INTO PaymentEndpoint (ContactID, PaymentProviderID,Account) VALUES ('".$_POST['ContactID']."','".$_POST['ProviderID']."','".$_POST['Account']."')");					
						$id = $db->lastInsertRowID();
					}
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
		viewPaymentList();
	}
