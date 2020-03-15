<?php
	if (isset($_POST['cmd'])) updateAccount();
	elseif (isset($_POST['manage'])) viewAccountList();
	elseif (isset($_POST['show'])) viewAccountMutations();
	elseif ($view) viewAccount($view);
	else viewAccountMutations();

	function viewAccountMutations(){

		//check if show is set

		if(isset($_POST['show'])){
			$acc_show=$_POST['AccountID'];
			$pro_show=$_POST['ProjectID'];
			$con_show=$_POST['ContactID'];
		}
		else{
			//show default
			$acc_show=2;
			$pro_show="def";
			$con_show="def";
		}

		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Accounts ORDER BY PID, ID";
		$acc_list = $db->query($query);
		$content.= '<table><tr><th>'.__('account').'</th><th>'.__('project').'</th><th>'.__('contact').'</th>';
		$content.='<form method="post">';
		$content.='<th><button type="submit" name="show" value="show">'.__('show').'</button></th>';
		$content.='<th><button type="submit" name="manage" value="manage">'.__('manage').' '.__('accounts').'</button></th></tr>';
		$content.='<tr>';

		//get all account options
		while($acc = $acc_list->fetchArray()) $options.= '<option value="'.$acc['ID'].'"'.($acc['ID']==$acc_show ?' selected':'').'>'.$acc['Name'].'</option>';
		$content.= '<td><select id="AccID" name="AccountID">'.$options.'</select></td>';

		//get all projects
		$options = '<option value="def"'.($pro_show=="def" ? 'selected' :'').'>'.__('all').'</option>';
		$projects = $db->query("SELECT * FROM Projects ORDER BY Name");
		while($project = $projects->fetchArray()) $options.= '<option value="'.$project['ID'].'"'.($pro_show==$project['ID']?' selected':'').'>'.$project['Name'].'</option>';
		$content.= '<td><select name="ProjectID">'.$options.'</select></td>';

        	//get all contacts
		$options = '<option value="def"'.($con_show=="def" ? 'selected':'').'>'.__('all').'</option>';
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) $options.= '<option value="'.$contact['ID'].'"'.($con_show==$contact['ID']?' selected':'').'>'.$contact['Name'].'</option>';
		$content.= '<td><select id="ContID" name="ContactID">'.$options.'</select></td>';

		$content.='</table></form>';

		$content.='<table>';
		$content.='<tr><th>ID</th><th>'.__('date').'</th><th>'.__('entry').'</th><th>'.__('transaction').'</th><th>'.__('project').'</th><th>'.__('contact').'</th><th>'.__('amount').'</th></tr>';

		// list the mutations
		// TODO start date and end date selection, or fiscal year
		// TODO: filter on project and contact
		$tables=array("Purchases","Sales");
		$mut_query="SELECT * FROM Mutations WHERE AccountID=".$acc_show." ORDER BY ID";
		$mut_list=$db->query($mut_query);
		if ($mut_list){
			while($mut=$mut_list->fetchArray()){
				//get transaction
				$trans=$db->query("SELECT * FROM Transactions WHERE ID=".$mut['TransactionID'])->fetchArray();
				$entry=$db->query("SELECT * FROM Entries WHERE ID=".$trans['EntryID'])->fetchArray();

				// TODO: not wortking yet: find the table with entry
				foreach($tables as $table){
					$result=$db->query("SELECT * FROM ".$table." WHERE EntryID=".$entry["ID"]);	
					($result ? $table_result=$result->fetchArray() : "");
				
				}
				
				echo $table_result['Reference'];

				// get the contact and project information
				$result=$db->query("SELECT * FROM Contacts WHERE ID=".$table_result['ContactID']);
				($result ? $cont_result=$resul->fetchArray() : "");
				$result=$db->query("SELECT * FROM Projects WHERE ID=".$table_result['ProjectID']);
				($result ? $proj_result=$resul->fetchArray() : "");

				$content.='<tr>';
				$content.='<td>'.$mut['ID'].'</td>';
				$content.='<td>'.$entry['TransactionDate'].'</td>';
				$content.='<td>'.$entry['ID'].'</td>';
				$content.='<td>'.$trans['ID'].'</td>';
				$content.='<td>'.$proj_results['Name'].'</td>';
				$content.='<td>'.$cont_results['Name'].'</td>';
				$content.='<td>'.$mut['Amount'].'</td>';
				$content.='</tr>';
			}
			$content.='</table';
		}

		//TODO: show all Child accounts and their summed amounts (means only book on child accounts and not directly on parent accounts
		
	}
	
	function viewAccountList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Accounts ORDER BY PID, ID";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>PID</th><th>'.__('name').'</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/accounts/new\';"/></td></tr>';
		while($acc = $list->fetchArray()){
			$content.= '<tr><td>'.$acc['ID'].'</td><td>'.$acc['PID'].'</td><td>'.$acc['Name'].'</td><td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/accounts/'.$acc['ID'].'\';"/></td></tr>';
		}
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
