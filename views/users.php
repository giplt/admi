<?php
	if (isset($_POST['cmd'])) updateUser();
	elseif ($view) viewUser($view);
	else viewUserList();
	
	function viewUserList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Users ORDER BY Email";
		$list = $db->query($query);
		
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>'.__('Contact').'</th><th>'.__('email').'</th><th>'.__('status').'</th><td></tr>';
		while($item = $list->fetchArray()) {
			$contact = $db->query("SELECT * FROM Contacts WHERE ID='{$item['ContactID']}'")->fetchArray();
			$content.= '<tr class="data"><td>'.$item['ID'].'</td><td>'.$contact['Name'].'</td><td>'.$item['Email'].'</td><td>'.$item['Status'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/users/'.$item['ID'].'\';"/></td></tr>';
		}
		$content.= '</table>';
	}
	
	function viewUser($id) {
		global $db, $content, $url, $lang;
		$user = $db->query("SELECT * FROM Users WHERE ID='{$id}'")->fetchArray();
		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$user['ID'].'</td>';
		$content.= '<tr><th>'.__('email').'</th><td><input type="text" name="Email" value="'.$user['Email'].'"/></td></tr>';
		
		$options = '<option value="" disabled="disabled"'.($user['ContactID']?'':' selected').'>'.__('pick-contact').'</option>';
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) $options.= '<option value="'.$contact['ID'].'"'.($user['ContactID']==$contact['ID']?' selected':'').'>'.$contact['Name'].'</option>';
		$content.= '<tr><th>'.__('contact').'</th><td><select name="ContactID">'.$options.'</select></td></tr>';
		
		$content.= '</table>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/users\';"/>';
		$content.= '</form>';
	}
	
	function updateUser() {
		global $db, $content, $url, $lang;
		switch ($_POST['cmd']) {
			case 'update':
				$db->query("UPDATE Users SET Email='".$_POST['Email']."', ContactID='".$_POST['ContactID']."' WHERE ID='".$_POST['ID']."'");
				break;
		}
		viewUserList();
	}
