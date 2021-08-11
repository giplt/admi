<?php
	if (!is_dir('files')) mkdir('files');
	
	if(isset($_FILES["invoice"])) {
		$error = false;
		
		$maxsize = ini_get("upload_max_filesize");
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $maxsize); // Remove the non-unit characters from the size.
		$maxsize = preg_replace('/[^0-9\.]/', '', $maxsize); // Remove the non-numeric characters from the size.
		$maxsize = $unit ? round($maxsize * pow(1024, stripos('bkmgtpezy', $unit[0]))) : round($maxsize);
		if ($_FILES["invoice"]["size"] > $maxsize) $error.= 'Sorry, your file is too large.<br/>';
		
		$filetype = strtolower(pathinfo($_FILES["invoice"]["name"],PATHINFO_EXTENSION));
		if($filetype!="pdf" && $filetype!="jpg" && $filetype!="png" && $filetype!="jpeg" && $filetype!="gif" ) $error.= 'Wrong file type<br/>';
		
		if ($error) {
			$error.= 'Only PDF, JPG, JPEG, PNG & GIF files are allowed. Maximum size: '.ini_get("upload_max_filesize").'<br/>';
			echo $error;
			exit();
		}
		
		switch($filetype) {
			case "pdf":
				$filename = uniqid().".pdf";
				move_uploaded_file($_FILES["invoice"]["tmp_name"], 'files/memorial'.$filename);
				echo $filename;
				break;
			default:
				$filename = uniqid().".jpg";
				$max = 1024;
				$src = imagecreatefromstring(file_get_contents($_FILES["invoice"]['tmp_name']));
				list($src_w, $src_h, $type, $attr) = getimagesize($_FILES["invoice"]['tmp_name']);
				if ($src_w>$max || $src_h>$max) {
					$dst_w = $src_w>$src_h ? $max : $max*$src_w/$src_h;
					$dst_h = $src_w>$src_h ? $max*$src_h/$src_w : $max;
				}
				else {
					$dst_w = $src_w;
					$dst_h = $src_h;
				}
				$dst = imagecreatetruecolor($dst_w, $dst_h);
				imagecopyresampled($dst, $src, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
				imagedestroy($src);
				imagejpeg($dst, 'files/memorial/'.$filename);
				imagedestroy($dst);
				echo $filename;
				break;
		}
		
		exit();
	}
	
	if ($cmd) updateMemorial();
	elseif ($view) viewMemorial($view);
	else viewMemorialList();

	function viewMemorialList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Memorial ORDER BY ID";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr>';
		$content.= '<th>ID</th>';
		$content.= '<th>'.__('date').'</th>';
		$content.= '<th>'.__('description').'</th>';
		$content.= '<th>'.__('project').'</th>';
		$content.= '<th>'.__('contact').'</th>';
		$content.= '<td>'.__('account').'</td>';
		$content.= '<td>'.__('amount').'</td>';
		$content.= '<td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/memorial/new\';"/></td>';
		$content.= '</tr>';
		if($list){
			while($memorial = $list->fetchArray()) {

				//collect info
				$entry=$db->query("SELECT * FROM Entries WHERE ID='".$memorial['EntryID']."'")->fetchArray();

				//get project names from database
				$project = $db->query("SELECT * FROM Projects WHERE ID='".$memorial['ProjectID']."'")->fetchArray();
				$contact = $db->query("SELECT * FROM Contacts WHERE ID='".$memorial['ContactID']."'")->fetchArray();
				$mutations = $db->query("SELECT * FROM Mutations WHERE TransactionID='".$memorial['TransactionID']."'")->fetchArray();
			
				//put in the table			
				$content.= '<tr class="data">';
				$content.= '<td>'.$memorial['ID'].'</td>';
				$content.= '<td>'.$entry['TransactionDate'].'</td>';
				$content.= '<td>'.$memorial['Description'].'</td>';
				$content.= '<td>'.$project['Name'].'</td>';
				$content.= '<td>'.$contact['Name'].'</td>';
				$content.= '<td></td>';
				$content.= '<td></td>';
				$content.= '<td><input type="button" value="'.__('inzien').'" onclick="window.location.href=\''.$url.$lang.'/memorial/'.$memorial['ID'].'\';"/></td>';
				$content.= '</tr>';
				
				foreach ($mutations as $mut){
					$content.= '<tr class="data">';
					$content.= '<td></td>';
					$content.= '<td></td>';
					$content.= '<td></td>';
					$content.= '<td></td>';
					$content.= '<td></td>';
					$content.= '<td>'.$mut['Account'].'</td>';
					$content.= '<td>'.$mut['Amount'].'</td>';
					$content.= '</tr>';
				}					
			}
		}
		$content.= '</table>';
	}

	
	function viewMemorial($id) {
		//checks voor verplichte velden
		//required er in zetten
		//2 = validatie, btw optelling klopt niet

		global $db, $content, $url, $lang;

		//get content
		if ($id=='new'){

			//create empty arrays
			$last_transaction = $db-> query("SELECT MAX(ID) FROM Transactions LIMIT 1)");

			$memorial = array(
				"ID"=>"", 
				"EntryID" => "", 
				"TransactionID" => $last_transaction+1,
				"Description" => "", 
				"ContactID" => "", 
				"ProjectID" => ""
			);

			$entry = array(
				"ID"=>"", 
				"TransactionDate" => "", 
				"AccountingDate" => "", 
				"PeriodFrom" => "",
				"PeriodTo" => "",
				"URL" => "", 
				"Log" => ""
			);

		}		
		else {
			//load arrays from the database
			$memorial = $db->query("SELECT * FROM Memorial WHERE ID='".$id."'")->fetchArray();
			$entry = $db->query("SELECT * FROM Entries WHERE ID='".$memorial['EntryID']."'")->fetchArray();


			//maybe usefull for later
			$transactions=array();
			$mutations=array();
			$transaction_results = $db->query("SELECT * FROM Transactions WHERE EntryID='".$entry['ID']."'");

			while ($transaction=$transaction_results->fetchArray()){
				array_push($transactions,$transaction);
				$mutation_results=$db->query("SELECT * FROM Mutations WHERE Mutations.TransactionID='".$transaction['ID']."'");

				while ($mutation=$mutation_results->fetchArray()){
					array_push($mutations,$mutation);
				}
			}
		}			

		$protected = false;
		
		//TODO: base html nog maken
		$content.= '<div class="memorialInputFrame">';
		$content.= '<div class="memorialInputForm">';
		$content.= '<form id="memorialForm" method="post">';

		//Datum en locatie van het boekstuk
		$content.='<fieldset id="metaFieldSet"'.(($memorial['ID']==''?'':'disabled')).'>';
		$content.='<legend>Bonnetje = boekstuk</legend><table>';
		$content.= '<input type="hidden" id="idField" name="ID" value="'.$id.'"/>';
		$content.= '<input type="hidden" id="entryIdField" name="entryID" value="'.$memorial['EntryID'].'"/>';

		//Hidden fields
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$memorial['ID'].'</td>';


		//Date fields
		$today=date('Y-m-d');
		$content.= '<tr><th>'.__('date').'</th><td><input type="date" name="TransactionDate" value="'.$entry['TransactionDate'].'"/></td></tr>';
		$content.= '<input type="hidden" name="AccountingDate" value='.$today.'>';

		//Period to which the entry applies
		$period_options=array(
			array("def","kies periode"),
			array("Y","Jaar"),
			array("Q1",__('quarter')." 1"),array("Q2",__('quarter')." 2"),array("Q3",__('quarter')." 3"),array("Q4",__('quarter')." 4"),
			array("1",__('january')),array("2",__('february')),array("3",__('march')),array("4",__('april')),array("5",__('may')),array("6",__('june')),
			array("7",__('july')),array("8",__('august')),array("9",__('september')),array("10",__('october')),array("11",__('november')),array("12",__('december')),
			array("Else","--Else")
		);
		$year_options=array(
			array("def","kies jaar"),
			array(date('Y')-1,date('Y')-1),
			array(date('Y'),date('Y')),
			array(date('Y')+1,date('Y')+1)
		);

		$content.= '<tr><th>'.__('period').'</th><td>';
		$content.= '<select id="periodSelect" name="periodSelect" onchange="
			readOnlySelect(\'periodSelect\',\'periodSelectHidden\'); 
			switchPeriodPresets(\'yearSelectHidden\',\'periodSelectHidden\',\'periodFrom\',\'periodTo\');
			"></select>';
		$content.= '<select id="yearSelect" name="yearSelect" onchange="
			readOnlySelect(\'yearSelect\',\'yearSelectHidden\'); 
			switchPeriodPresets(\'yearSelectHidden\',\'periodSelectHidden\',\'periodFrom\',\'periodTo\');
			"></select>';
		$content.= '<select id="periodSelectHidden" name="periodSelectHidden" hidden="true"></select>';
		$content.= '<select id="yearSelectHidden" name="yearSelectHidden" hidden="true"></select></td>';
		$content.= '<td id="periodFromLabel" hidden="true">'.__('from').'<input type="date" id="periodFrom" name="PeriodFrom" value="'.$entry['PeriodFrom'].'"></td>';
		$content.= '<td id="periodToLabel" hidden="true">'.__('to').'<input type="date" id="periodTo" name="PeriodTo" value="'.$entry['PeriodTo'].'"></td></tr>';

		//Entry upload
		$content.= '<tr><th>'.__('location').'</th><td><input type="text" id="location" name="Location" value="'.$entry['URL'].'"/></td>' ;
		$content.= '<td><input type="file" value="'.__('upload').'" name=myFile accept="image/*,.pdf" onchange="upload(\'invoice\', this);"></td></tr>';

       		//ContactID
		$contact_options = '<option value="" disabled="disabled"'.($memorial['ContactID']?'':' selected').'>'.__('pick-contact').'</option>';
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) $contact_options.= '<option value="'.$contact['ID'].'"'.($purchase['ContactID']==$contact['ID']?' selected':'').'>'.$contact['Name'].'</option>';
		$content.= '<tr><th>'.__('contact').'</th>';
		$content.= '<td><select id="contactId" name="ContactID" onchange="readOnlySelect(\'contactId\',\'contactIdHidden\');">'.$contact_options.'</select>';
		$content.= '<select id="contactIdHidden" name="contactIDhidden" hidden="true">'.$contact_options.'</select></td>'; 

		//ProjectID
		$options = '<option value="" disabled="disabled"'.($memorial['ProjectID']?'':' selected').'>'.__('pick-project').'</option>';
		$projects = $db->query("SELECT * FROM Projects ORDER BY Name");
		while($project = $projects->fetchArray()) $options.= '<option value="'.$project['ID'].'"'.($memorial['ProjectID']==$project['ID']?' selected':'').'>'.$project['Name'].'</option>';
		$content.= '<tr><th>'.__('project').'</th>';
		$content.= '<td><select id="projectId" name="ProjectID" onchange="readOnlySelect(\'projectId\',\'projectIdHidden\');">'.$options.'</select>';
		$content.= '<select id="projectIdHidden" name="ProjectIDhidden" hidden="true">'.$options.'</select></td></tr>';
		
		//Description //in css
		$content.= '<tr><th>'.__('description').'</th><td><textarea rows="3" name="Description" value="'.$memorial['Description'].'"/>'.__('description').'</textarea></td></tr>';
		$content.= '</table></fieldset>';
		
		//Haal alle opties voor accounts uit de database
		$account_options = array(array("def","pick-account"));
		$accounts = $db->query("SELECT * FROM Accounts ORDER BY Name");
		while($account = $accounts->fetchArray()) array_push($account_options,array($account['ID'],$account['Name']));
		$account_options_safe=json_encode($account_options);

		//Mutations
		$content.='<fieldset id="mutationFieldSet"'.(($memorial['ID']==''?'':'disabled')).'>';
		$content.='<legend>Mutations</legend>';
		$content.='<input id="transactionId" name="TransactionID" hidden="true" value="'.$memorial['TransactionID'].'">';
		$content.='<table id="mutationTable" class="mutantionInputTable">';
		$content.='<tr class="mutationInputRow">';
		$content.='<th class="mutationInputCol">'.__('account').'</th>'; 
		$content.='<th class="mutationInputCol">'.__('amount').'</th>';
		$content.='<td class="mutationInputColLast"><input type="button" id="addMutationRowButton" value="'.__('add row').'">'; 
		$content.='</tr></table>';
		
		//Laatste rij met het totaal //CSS nodig om gelijke kolombreedte te krijgen
		$content.= '<table class="mutationInputTotTable">';
		$content.= '<tr class="mutationInputRow"><th class="mutationInputCol">'.__('total').'</th>';
		$content.= '<td class="mutationInputCol"><input type="number" step="0.01" class="mutationInputField" name="mutationTot" id="mutationTot" readonly></td>';
		$content.= '</tr>';
		$content.= '</table></fieldset>';

		//Submit Buttons
		if($memorial['ID']==''){
			$content.='<button type="submit" name="cmd" value="back">'.__('back').'</button>';
			$content.='<span id="updateSpan" title="Input data first">';
			$content.='<button type="submit" id="updateButton" name="cmd" value="update" disabled="disabled">'.__('submit').'</button></span>';		
		}
		else{
			$content.='<button type="submit" name="cmd" value="back">'.__('back').'</button>';
		}

		$content.= '</form></div>';

		//div containing the uploaded invoice
		$content.= '<div id="invoiceView" class="expenseInputVis">';
		switch(pathinfo($entry['URL'], PATHINFO_EXTENSION)) {
			case 'pdf': $content.= '<embed src="files/'.$entry['URL'].'" width="400px" height="600px" />'; break;
			case 'jpg': $content.= '<img src="files/'.$entry['URL'].'" width="400px" />'; break;
			default: $content.= 'Bonnetje'; break;
		}
		$content.= '</div>';
		$content.= '</div>';
		
		//All select item options (double safe does not work)
		$all_options=array(
			array("periodSelect",$period_options),
			array("periodSelectHidden",$period_options),
			array("yearSelect",$year_options),
			array("yearSelectHidden",$year_options),
		);
		$all_options_safe=json_encode($all_options);

		$all_fieldsets=array("metaFieldSet","mutationFieldSet");
		$all_fieldsets_safe=json_encode($all_fieldsets);

		//Loading javascript
		$content.= '<script type="text/javascript" src="../../js/memorial.js"></script>';

		//Running specific javascript functions
		$content.= '<script>addOptionsPHP('.$all_options_safe.')</script>';		//adds all options for select fields that need to be loaded once
		$content.= '<script>setGlobalOptions('.$account_options_safe.')</script>';	//adds all options for select fields that need to be created & loaded dynamically
		$content.= '<script>addOnClick()</script>';					//connects an event listener to the addRow button
		$content.= '<script>onChangeFieldSet('.$all_fieldsets_safe.')</script>';	//runs sripts when Changes are made to the fieldsets

		//add just one row
		$content.= '<script>addMutationRow()</script>';
	}
	
	function updateMemorial() {
		global $db, $content, $url, $lang;
		$userIDs = isset($_POST['UserIDs']) ? implode(',', $_POST['UserIDs']) : '';
		switch ($_POST['cmd']) {
			case 'update':
				
				if ($_POST['ID']=='new') {

					//Insert a new entry
					$db->query("INSERT INTO Entries (TransactionDate, AccountingDate, URL) VALUES ('".$_POST['TransactionDate']."', '".$_POST['AccountingDate']."', '".$_POST['Location']."')");
					
					// get the entryID from the database $id = $db->lastInsertRowID();
					$last_entry=$db->querySingle("SELECT MAX(ID) FROM Entries LIMIT 1");
					$entryID=intval($last_entry);

					//insert a new Memorial
					$db->query("INSERT INTO Memorial (EntryID, TransactionID, Description, ContactID, ProjectID) VALUES ('".$entryID."','".$_POST['TransactionID']."','".$_POST['Description']."', '".$_POST['ContactID']."','".$_POST['ProjectID']."')");
										
					foreach ($_POST as $key => $value){
			
						//add the expense rows to the json file
						if (strpos($key,"mutationAccountFromHidden")!==False){
							$mut_num=substr($key, strlen("mutationAccountFromHidden"),strlen($key));
							$account=$_POST["mutationAccountFrom".$mut_num];
							$amount=$_POST["mutationAmount".$mut_num];		
							$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$_POST['TransactionID']."', '".$account."', '".$amount."')");
	
						} //if
					} //foreach
				
				} //if
				
				break;


			case 'back':				

				//if a .pdf is created, but not saved then remove it
				if ($_POST['ID']=='new'){
					if($_POST["Location"]!="" and file_exists('files/memorial/'.$_POST["Location"])){

						//delete the invoice file
						unlink('files/memorial/'.$_POST["Location"]);
					} 
				}
				break;	


		}
		viewMemorialList();
	}

