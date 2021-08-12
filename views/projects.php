<?php
	if (isset($_POST['cmd'])) updateProject();
	elseif ($view) viewProject($view);
	else viewProjectList();
	
	function viewProjectList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Projects ORDER BY Name";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>'.__('name').'</th><th>'.__('account').'ID</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/projects/new\';"/></td></tr>';
		while($item = $list->fetchArray()) $content.= '<tr class="data"><td>'.$item['ID'].'</td><td>'.$item['Name'].'</td><td>'.$item['AccountID'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/projects/'.$item['ID'].'\';"/></td></tr>';
		$content.= '</table>';
	}
	
	function viewProject($id) {
		global $db, $content, $url, $lang;
		if ($id=='new') $project = array("ID"=>"", "Name"=>__('new project name'));
		else $project = $db->query("SELECT * FROM Projects WHERE ID='{$id}'")->fetchArray();
		$protected = false;
		//$active = true;			////
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$project['ID'].'</td>';
		if ($id=='new') $content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" placeholder="'.$project['Name'].'"/></td></tr>';
		else $content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" value="'.$project['Name'].'"/></td></tr>';

		$content.= '<tr><th>'.__('users').'</th><td>';
		$users = $db->query("SELECT * FROM Users ORDER BY Email");
		$userIDs = isset($project['UserIDs']) ? explode(',', $project['UserIDs']) : array();
		while($item = $users->fetchArray()) {
			$content.= '<input type="checkbox" name="UserIDs[]" value="'.$item['ID'].'"'.(in_array($item['ID'],$userIDs)?' checked':'').'> '.$item['Email'].'<br/>';
		}
		$content.= '</td></tr>';
		$content.= '</table>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/projects\';"/>';
		$content.= '</form>';
	}
	
	function updateProject() {
		global $db, $content, $url, $lang;
		$userIDs = isset($_POST['UserIDs']) ? implode(',', $_POST['UserIDs']) : '';
		switch ($_POST['cmd']) {
			case 'update':
				if ($_POST['ID']=='new') {
					$db->query("INSERT INTO Accounts (PID, Name) VALUES ('8', '".$_POST['Name']."')");
					$accountID = $db->lastInsertRowID();
					$db->query("INSERT INTO Projects (Name, AccountID, UserIDs) VALUES ('".$_POST['Name']."', '".$accountID."', '".$userIDs."')");
					$id = $db->lastInsertRowID();
				}
				else {
					$db->query("UPDATE Projects SET Name='".$_POST['Name']."', UserIDs='".$userIDs."' WHERE ID='".$_POST['ID']."'");
					$id = $_POST['ID'];
				}
				break;
			case 'remove':
				$project = $db->query("SELECT * FROM Projects WHERE ID='".$_POST['ID']."'")->fetchArray();
				//if ($db->query("SELECT * FROM Projects WHERE ID='".$_POST['ID']."'")->fetchArray()==null){////
				$db->query("DELETE FROM Accounts WHERE ID='".$project['AccountID']."'");
				$db->query("DELETE FROM Projects WHERE ID='".$_POST['ID']."'");
				//}///
				//else $active = false;////
				break;
		}
		viewProjectList();
	}
