<?php
	if (isset($_POST['cmd'])) updatePaymentProvider();
	elseif ($view) viewPaymentProvider($view);
	else viewPaymentProviderList();
	
	function viewPaymentProviderList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM PaymentProviders ORDER BY Name";
		$list = $db->query($query);
		
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>'.__('name').'</th><th>'.__('account').'</th><th>'.__('API').'</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/paymentproviders/new\';"/></td></tr>';
		while($item = $list->fetchArray()) {
			$content.= '<tr class="data"><td>'.$item['ID'].'</td><td>'.$item['Name'].'</td><td>'.$item['Account'].'</td><td>'.$item['API'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/paymentproviders/'.$item['ID'].'\';"/></td></tr>';
		}
		$content.= '</table>';
	}
	
	function viewPaymentProvider($id) {
		global $db, $content, $url, $lang;
		if ($id=='new') $provider = array("ID"=>"", "Name"=>"", "Account"=>"", "API"=>"");
		else $provider = $db->query("SELECT * FROM PaymentProviders WHERE ID='{$id}'")->fetchArray();
		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$provider['ID'].'</td>';
		$content.= '<tr><th>AccountID</th><td>'.$provider['AccountID'].'</td>';
		$content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" value="'.$provider['Name'].'"/></td></tr>';
		$content.= '<tr><th>'.__('account').'</th><td><input type="text" name="Account" value="'.$provider['Account'].'"/></td></tr>';
		$content.= '<tr><th>'.__('API').'</th><td><textarea name="API">'.$provider['API'].'</textarea></td></tr>';
		$content.= '</table>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/paymentproviders\';"/>';
		$content.= '</form>';
	}
	
	function updatePaymentProvider() {
		global $db, $content, $url, $lang;
		switch ($_POST['cmd']) {
			case 'update':
				if ($_POST['ID']=='new') {
					$db->query("INSERT INTO Accounts (Name,PID) VALUES ('".$_POST['Name']."', '3')");
					$accountID = $db->lastInsertRowID();
					
					$db->query("INSERT INTO PaymentProviders (AccountID,Name,Account,API) VALUES ('".$accountID."','".$_POST['Name']."','".$_POST['Account']."','".$_POST['API']."')");
					$id = $db->lastInsertRowID();
				}
				else {
					$db->query("UPDATE PaymentProviders SET Name='".$_POST['Name']."', Account='".$_POST['Account']."', API='".$_POST['API']."' WHERE ID='".$_POST['ID']."'");
				}
				break;
		}
		viewPaymentProviderList();
	}
