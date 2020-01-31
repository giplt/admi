<?php
	if (isset($_POST['cmd'])) updateAccount();
	elseif ($view) viewAccount($view);
	else viewAccountList();
	
	function viewAccountList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Accounts ORDER BY PID, ID";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>PID</th><th>'.__('name').'</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/accounts/new\';"/></td></tr>';
		while($item = $list->fetchArray()) $content.= '<tr><td>'.$item['ID'].'</td><td>'.$item['PID'].'</td><td>'.$item['Name'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/accounts/'.$item['ID'].'\';"/></td></tr>';
		$content.= '</table>';
	}
	
	function viewAccount($id) {
		global $db, $content, $url, $lang;
		if ($id=='new') $account = array("ID"=>"", "PID"=>"", "Name"=>"New account name");
		else $account = $db->query("SELECT * FROM Accounts WHERE ID='{$id}'")->fetchArray();
		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$account['ID'].'</td>';
		$content.= '<tr><th>PID</th><td><input type="text" name="PID" value="'.$account['PID'].'"/></td></tr>';
		$content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" value="'.$account['Name'].'"/></td></tr>';
		$content.= '</table>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/accounts\';"/>';
		$content.= '</form>';
	}
	
	function updateAccount() {
		global $db, $content, $url, $lang;
		switch ($_POST['cmd']) {
			case 'update':
				if ($_POST['ID']=='new') {
					$db->query("INSERT INTO Accounts (Name) VALUES ('".$_POST['Name']."', '".$_POST['PID']."')");
					$id = $db->lastInsertRowID();
				}
				else {
					$db->query("UPDATE Accounts SET Name='".$_POST['Name']."' PID='".$_POST['PID']."' WHERE ID='".$_POST['ID']."'");
					$id = $_POST['ID'];
				}
				break;
			case 'remove':
				$account = $db->query("SELECT * FROM Accounts WHERE ID='".$_POST['ID']."'")->fetchArray();
				$db->query("DELETE FROM Accounts WHERE ID='".$_POST['ID']."'");
				break;
		}
		viewAccountList();
	}
