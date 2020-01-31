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
		if ($id=='new') $contact = array("ID"=>"", "Name"=>"New contact name");
		else $contact = $db->query("SELECT * FROM Contacts WHERE ID='{$id}'")->fetchArray();
		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$contact['ID'].'</td>';
		$content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" value="'.$contact['Name'].'"/></td></tr>';
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
					$db->query("INSERT INTO Contacts (Name) VALUES ('".$_POST['Name']."')");
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
