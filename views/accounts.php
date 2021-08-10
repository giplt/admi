<?php
	if (isset($_POST['cmd'])) updateAccount();
//	elseif (isset($_POST['edit'])) 
	elseif ($view) {
		if ($view2=='edit') editAccount($view);
		elseif ($view2=='view') viewAccount();
	}
	else viewAccountList();
	
	function viewAccount() {

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
		$content.='<tr>';

		//get all account options
		$options = '<option value="def"'.($acc_show=="def" ? 'selected' :'').'>'.__('all').'</option>';
		while($acc = $acc_list->fetchArray()){
			$pid=$db->query("SELECT * FROM Accounts WHERE ID=".$acc['PID']);
			$cnt=0;
			while($pid){
				$PID=$pid->fetchArray();
				if($PID['PID']){
					$pid=$db->query("SELECT * FROM Accounts WHERE ID=".$PID['PID']); 
					($pid ? $cnt+=1 : $pid=false);
				}
				else{
					$pid=false;
				}
				
			}			
			$options.= '<option value="'.$acc['ID'].'"'.($acc['ID']==$acc_show ?' selected':'').'>'.substr("------",0,$cnt).$acc['Name'].'</option>';
		}
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

		// TODO start date and end date selection, or fiscal year
		
		$content.='</table></form>';

		// If it is a parentID then just list accounts below it + any mutations directly on this account
		$pid_res = $db->query("SELECT * FROM Accounts WHERE PID=".$acc_show);
	
		$content.='<table>';
		$pid_sum=0;

		//create header
		if($pid_res->fetchArray()){
			//create a table and header
			$content.='<tr><tr>Subaccounts</tr>';
			$content.='<tr><th>ID</th><th>'.__('account').'</th><th>'.__('amount').'</th></tr>';
		}
		
		//populate with data
		while ($pids=$pid_res->fetchArray()){
			$mut_list=mutationSummary($pids['ID']);
			$acc_sum=0;

			foreach($mut_list as $mut){
				if (($pro_show=="def" or $pro_show==$mut['ProjectID']) and ($con_show=="def" or $con_show==$mut['ContactID'])){
					$acc_sum+=$mut['Amount'];
				}
			}

			//create content
			$content.='<tr><td>'.$pids['ID'].'</td><td>'.$pids['Name'].'</td><td>'.$acc_sum.'</td></tr>';
			$pid_sum+=$acc_sum;
		}
		
		// sum if there is content
		if ($pid_res->fetchArray()){
			$content.='<tr><td></td><td></td><td>--------</td></tr>';
			$content.='<tr><td></td><td></td><td>'.$pid_sum.'</td></tr>';
		}

		$content.='</table>';

		$content.='<table>';

		$mut_list=mutationSummary($acc_show);
		$acc_sum=0;
		if ($mut_list){
			//create header
			$content.='<table><tr>Mutations</tr>';
			$content.='<tr><th>ID</th><th>'.__('transaction').'</th><th>'.__('entry').'</th><th>'.__('date').'</th><th>'.__('project').'</th><th>'.__('contact').'</th><th>'.__('amount').'</th></tr>';
			
			//populate fields
			foreach($mut_list as $mut){
				if (($pro_show=="def" or $pro_show==$mut['ProjectID']) and ($con_show=="def" or $con_show==$mut['ContactID'])) {

					$content.='<tr>';
					$content.='<td>'.$mut['ID'].'</td>';
					$content.='<td>'.$mut['EntryID'].'</td>';
					$content.='<td>'.$mut['TransactionID'].'</td>';
					$content.='<td>'.$mut['TransactionDate'].'</td>';
					$content.='<td>'.$mut['ProjectName'].'</td>';
					$content.='<td>'.$mut['ContactName'].'</td>';
					$content.='<td>'.$mut['Amount'].'</td>';
					$content.='</tr>';
					
					$acc_sum+=$mut['Amount'];
	
				}
			}
			//sum 
			$content.='<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>-----------</td></tr>';
			$content.='<tr><td></td><td></td><td></td><td></td><td></td><td></td>';
			$content.='<td>'.$acc_sum.'</td></tr>';
			$content.='</table>';
		}
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/accounts\';"/>';
	}
	
	function viewAccountList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Accounts ORDER BY PID, ID";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr><th>ID</th><th>PID</th><th>'.__('name').'</th><th>'.__('rgs').'</th><td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/accounts/new\';"/></td></tr>';
		while($acc = $list->fetchArray()){
			$content.= '<tr><td>'.$acc['ID'].'</td><td>'.$acc['PID'].'</td><td>'.$acc['Name'].'</td><td>'.$acc['RGS'].'</td><td><input type="button" value="'.__('view').'" onclick="window.location.href=\''.$url.$lang.'/accounts/'.$acc['ID'].'/view\';"/><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/accounts/'.$acc['ID'].'/edit\';"/></td></tr>';
		}
		$content.= '</table>';
	}
	
	function editAccount($id) {
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
		$content.= '<tr><th>'.__('rgs').'</th><td><input type="text" name="Name" value="'.$account['RGS'].'"/></td></tr>';
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
					$db->query("INSERT INTO Accounts (Name) VALUES ('".$_POST['Name']."', '".$_POST['PID']."', '".$_POST['RGS']."')");
					$id = $db->lastInsertRowID();
				}
				else {
					$db->query("UPDATE Accounts SET Name='".$_POST['Name']."' PID='".$_POST['PID']."' RGS='".$_POST['RGS']."' WHERE ID='".$_POST['ID']."'");
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
	
	function mutationSummary($acc_ID){
		//define variables
		global $db;
		$tables=array("Purchases","Sales");

		//query for all mutations with $acc_ID
		$mut_query="SELECT * FROM Mutations WHERE AccountID=".$acc_ID." ORDER BY ID";
		$mut_list=$db->query($mut_query);

		$mut_sum=array();

		//loop through all mutations and get additional info
		if ($mut_list){
			while($mut=$mut_list->fetchArray()){
				//empty array				
				$mut_array=array();
				
				//get transaction
				$trans=$db->query("SELECT * FROM Transactions WHERE ID=".$mut['TransactionID'])->fetchArray();
				$entry=$db->query("SELECT * FROM Entries WHERE ID=".$trans['EntryID'])->fetchArray();

				// go through the tables
				foreach($tables as $table){
					$table_query="SELECT * FROM ".$table." WHERE EntryID=".$entry["ID"];					
					$res=$db->query($table_query);
	
					if ($res){
						$table_result=$res->fetchArray();
						if (isset($table_result['ID'])){
						
						//get contact and project info
						$proj_res=$db->query("SELECT * FROM Projects WHERE ID=".$table_result['ProjectID']);
						($proj_res ? $proj=$proj_res->fetchArray() : "");
						$cont_res=$db->query("SELECT * FROM Contacts WHERE ID=".$table_result['ContactID']);
						($cont_res ? $cont=$cont_res->fetchArray() : "");
						}
					}
				}
				
				// add relevant data to the arrayTable
				$mut_array['ID']=$mut['ID'];
				$mut_array['TransactionID']=$trans['ID'];
				$mut_array['EntryID']=$entry['ID'];
				$mut_array['TransactionDate']=$entry['TransactionDate'];
				$mut_array['AccountingDate']=$entry['AccountingDate'];
				$mut_array['Reference']=$entry['Reference'];
				$mut_array['ProjectID']=$proj['ID'];
				$mut_array['ProjectName']=$proj['Name'];
				$mut_array['ContactID']=$cont['ID'];
				$mut_array['ContactName']=$cont['Name'];
				$mut_array['Amount']=$mut['Amount'];
				
				array_push($mut_sum, $mut_array);
			}

			return $mut_sum;
		}
		else {
			return False;
		}
		 
	}
