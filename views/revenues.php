<?php
	if (isset($_POST['cmd'])) updateRevenue();
	elseif ($view) viewRevenue($view);
	else viewRevenueList();
	
	function viewRevenueList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Accounts WHERE PID = 13 ORDER BY Name";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>'.__('name').'</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/revenues/new\';"/></td></tr>';
		while($item = $list->fetchArray()) $content.= '<tr class="data"><td>'.$item['ID'].'</td><td>'.$item['Name'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/revenues/'.$item['ID'].'\';"/></td></tr>';
		$content.= '</table>';
	}
	
	function viewRevenue($id) {
		global $db, $content, $url, $lang;
		if ($id=='new') $revenue = array("ID"=>"", "Name"=>__('new revenue name'));
		else $revenue = $db->query("SELECT * FROM Accounts WHERE ID='{$id}'")->fetchArray();
		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$revenue['ID'].'</td>';
		if ($id=='new') $content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" placeholder="'.$revenue['Name'].'"/></td></tr>';
		else $content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" value="'.$revenue['Name'].'"/></td></tr>';
		$content.= '</table>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/revenues\';"/>';
		$content.= '</form>';
	}
	
	function updateRevenue() {
		global $db, $content, $url, $lang;
		$userIDs = isset($_POST['UserIDs']) ? implode(',', $_POST['UserIDs']) : '';
		switch ($_POST['cmd']) {
			case 'update':
				if ($_POST['ID']=='new') {
					$db->query("INSERT INTO Accounts (PID, Name) VALUES ('13', '".$_POST['Name']."')");
					$id = $db->lastInsertRowID();
				}
				else {
					$db->query("UPDATE Accounts SET Name='".$_POST['Name']."' WHERE ID='".$_POST['ID']."'");
					$id = $_POST['ID'];
				}
				break;
			case 'remove':
			$revenuetypeExistsInMutations = $db->querySingle("SELECT COUNT(*) as count FROM Mutations WHERE AccountID='".$_POST['ID']."'");
					//If contact has been used
				if ($revenuetypeExistsInMutations) {
					echo 'Revenuetype is used, it cannot be deleted';
				}
				else {
					//Delete the expensetype
				$db->query("DELETE FROM Accounts WHERE ID='".$_POST['ID']."'");
			}
				break;
		}
		viewRevenueList();
	}
