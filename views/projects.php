<?php
	if (isset($_POST['cmd'])) updateProject();
	elseif ($view) viewProject($view);
	else viewProjectList();
	
	function viewProjectList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Projects ORDER BY Name";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>'.__('name').'</th><th>'.__('account').'ID</th><th>'.__('active').'status</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/projects/new\';"/></td></tr>';
		while($item = $list->fetchArray()) $content.= '<tr class="data"><td>'.$item['ID'].'</td><td>'.$item['Name'].'</td><td>'.$item['AccountID'].'</td><td>'.$item['Status'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/projects/'.$item['ID'].'\';"/></td></tr>';
		$content.= '</table>';
	}
	
	function viewProject($id) {
		global $db, $content, $url, $lang;
		if ($id=='new') $project = array("ID"=>"", "Name"=>__('new project name'), "Status"=>"Active");
		else $project = $db->query("SELECT * FROM Projects WHERE ID='{$id}'")->fetchArray();
		
		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$project['ID'].'</td></tr>';
		if ($id=='new') $content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" placeholder="'.$project['Name'].'"/></td></tr>';
		else $content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" value="'.$project['Name'].'"/></td></tr>';

		$content.= '<tr><th>'.__('users').'</th><td>';
		$users = $db->query("SELECT * FROM Users ORDER BY Email");
		$userIDs = isset($project['UserIDs']) ? explode(',', $project['UserIDs']) : array();
		while($item = $users->fetchArray()) {
			$content.= '<input type="checkbox" name="UserIDs" value="'.$item['ID'].'"'.(in_array($item['ID'],$userIDs)?' checked':'').'> '.$item['Email'].'<br/>';
		}
		$content.= '</td></tr>';
		$content.= '<tr>';
		$content.= '<th>Status</th>';
		$content.= '<td><select name="Status" id="status">
			<option value="active"'.($project['Status']=='active'?' selected':'').'>Active</option>
			<option value="deactivated"'.($project['Status']=='deactivated'?' selected':'').'>Deactivated</option>
		  </select></td>';


		$content.= '</tr>';
		$content.= '</table>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		//if (!$protected) 
		$content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/projects\';"/>';
		$content.= '</form>';
	}
	
	function updateProject() {
		//TODO: only deactivate when saldo = 0
		global $db, $content, $url, $lang;
		$userIDs = isset($_POST['UserIDs']) ? implode(',', $_POST['UserIDs']) : '';
		$status = $_POST['Status'];
		switch ($_POST['cmd']) {
			case 'update':
				if ($_POST['ID']=='new') {
					$db->query("INSERT INTO Accounts (PID, Name) VALUES ('8', '".$_POST['Name']."')");
					$accountID = $db->lastInsertRowID();
					$db->query("INSERT INTO Projects (Name, AccountID, UserIDs, Status) VALUES ('".$_POST['Name']."', '".$accountID."', '".$userIDs."', '".$status."')");
					$id = $db->lastInsertRowID();
				}
				else {
					$db->query("UPDATE Projects SET Name='".$_POST['Name']."', Status='".$_POST['Status']."', UserIDs='".$userIDs."' WHERE ID='".$_POST['ID']."'");
					$id = $_POST['ID'];
				}
				break;
			case 'remove':
				$projectExistsInPurchases = $db->querySingle("SELECT COUNT(*) as count FROM Purchases WHERE ProjectID='".$_POST['ID']."'");
				$projectExistsInSales = $db->querySingle("SELECT COUNT(*) as count FROM Sales WHERE ProjectID='".$_POST['ID']."'");
				$projectExistsInMemorial = $db->querySingle("SELECT COUNT(*) as count FROM Memorial WHERE ProjectID='".$_POST['ID']."'");

				if ($projectExistsInPurchases || $projectExistsInSales || $projectExistsInMemorial) {//als hij wel bestaat
					echo 'Project is used, it cannot be deleted';
				}
				else {
					
				
			//while($project = $projects->fetchArray()) if ($sale['ProjectID']==$project['ID']||$purchase['ProjectID']==$project['ID']){
				$project = $db->query("SELECT * FROM Projects WHERE ID='".$_POST['ID']."'")->fetchArray();
				$db->query("DELETE FROM Accounts WHERE ID='".$project['AccountID']."'");
				$db->query("DELETE FROM Projects WHERE ID='".$_POST['ID']."'");}
				break;
		}
		viewProjectList();
	}
