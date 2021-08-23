<?php
	if (isset($_POST['cmd'])) updateRUle();
	elseif ($view) viewRule($view);
	else viewRuleList();
	
	function viewRuleList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Rules ORDER BY Name";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>'.__('name').'</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/rules/new\';"/></td></tr>';
		while($item = $list->fetchArray()) $content.= '<tr class="data"><td>'.$item['ID'].'</td><td>'.$item['Name'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/rules/'.$item['ID'].'\';"/></td></tr>';
		$content.= '</table>';
	}
	
	function viewRule($id) {
		global $db, $content, $url, $lang;
		if ($id=='new') $rule = array("ID"=>"", "Name"=>__('new rule name'));
		else $rule = $db->query("SELECT * FROM Rules WHERE ID='{$id}'")->fetchArray();
		$protected = false;
		$content.= '<form method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<table>';
		if ($id!='new') $content.= '<tr><th>ID</th><td>'.$rule['ID'].'</td></tr>';
		if ($id=='new') $content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" placeholder="'.$rule['Name'].'"/></td></tr>';
		else $content.= '<tr><th>'.__('name').'</th><td><input type="text" name="Name" value="'.$rule['Name'].'"/></td></tr>';
		$content.= '<tr><th>'.__('json').'</th><td><textarea name="Json">';
		if ($id=='new') $content.= file_get_contents('ruletemplate.json');
		else $content.= $rule['Json'];
		$content.= '</textarea></td></tr>';
		$content.= '</table>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/rules\';"/>';
		$content.= '</form>';
	}
	
	function updateRule() {
		global $db, $content, $url, $lang;
		switch ($_POST['cmd']) {
			case 'update':
				if ($_POST['ID']=='new') {
					$db->query("INSERT INTO Rules (Name, Json) VALUES ('".$_POST['Name']."', '".$_POST['Json']."')");
					$id = $db->lastInsertRowID();
				}
				else {
					$db->query("UPDATE Rules SET Name='".$_POST['Name']."', Json='".$_POST['Json']."' WHERE ID='".$_POST['ID']."'");
					$id = $_POST['ID'];
				}
				break;
			case 'remove':
				$db->query("DELETE FROM Rules WHERE ID='".$_POST['ID']."'");
				break;
		}
		viewRulesList();
	}
