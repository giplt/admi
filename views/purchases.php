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
				move_uploaded_file($_FILES["invoice"]["tmp_name"], 'files/'.$filename);
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
				imagejpeg($dst, 'files/'.$filename);
				imagedestroy($dst);
				echo $filename;
				break;
		}
		
		exit();
	}
	
	if ($cmd) updatePurchase();
	elseif ($view) viewPurchase($view);
	else viewPurchaseList();

	function viewPurchaseList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Purchases ORDER BY ID";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr>';
		$content.= '<th>ID</th>';
		$content.= '<th>'.__('date').'</th>';
		$content.= '<th>'.__('purchase-input-type').'</th>';
		$content.= '<th>'.__('reference').'</th>';
		$content.= '<th>'.__('amount').'</th>';
		$content.= '<th>'.__('project').'</th>';
		$content.= '<th>'.__('contact').'</th>';
		$content.= '<td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/purchases/new\';"/></td>';
		$content.= '</tr>';
		if($list){
			while($expense = $list->fetchArray()) {

				//collect info
				$entry=$db->query("SELECT * FROM Entries WHERE ID='".$expense['EntryID']."'")->fetchArray();

				//load data from the JSON file
				$json_path='files/purchases/'.$expense['ID'].".json";
				$json_array=json_decode(file_get_contents($json_path),true);

				//get project names from database
				$project = $db->query("SELECT * FROM Projects WHERE ID='".$expense['ProjectID']."'")->fetchArray();
				$contact = $db->query("SELECT * FROM Contacts WHERE ID='".$expense['ContactID']."'")->fetchArray();
			
				//put in the table			
				$content.= '<tr class="data">';
				$content.= '<td>'.$expense['ID'].'</td>';
				$content.= '<td>'.$entry['TransactionDate'].'</td>';
				$content.= '<td>'.$json_array['Meta']['purchaseInputType'].'</td>';
				$content.= '<td>'.$expense['Reference'].'</td>';
				$content.= '<td>'.$json_array['Meta']['totalNett'].'</td>';
				$content.= '<td>'.$project['Name'].'</td>';
				$content.= '<td>'.$contact['Name'].'</td>';
				$content.= '<td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/purchases/'.$expense['ID'].'\';"/></td>';
				$content.= '</tr>';
			}
		}
		$content.= '</table>';
	}

	
	function viewPurchase($id) {
		//checks voor verplichte velden
		//required er in zetten
		//2 = validatie, btw optelling klopt niet

		global $db, $content, $url, $lang;

		//get content
		if ($id=='new'){

			//create empty arrays
			$purchase = array(
				"ID"=>"", 
				"EntryID" => "", 
				"Status" => "", 
				"Reference" => "", 
				"ContactID" => "", 
				"SupplierID" => "",
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

			$json=null;
		}		
		else {
			//load arrays from the database
			$purchase = $db->query("SELECT * FROM Purchases WHERE ID='".$id."'")->fetchArray();
			$entry = $db->query("SELECT * FROM Entries WHERE ID='".$purchase['EntryID']."'")->fetchArray();

			//get the data from the json file/field
			$json_path='./files/purchases/'.$purchase['ID'].'.json';
			if(file_exists($json_path)){
				$json=file_get_contents($json_path);
			}
			else{
				$json=null;
			}

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
		$content.= '<div class="expenseInputFrame">';
		$content.= '<div class="expenseInputForm">';
		$content.= '<form id="expenseForm" method="post">';
			
		//radion button selection if it is a declaration or a invoice
		$content.= '<fieldset id="radioFieldSet">';
		$content.= '<input type="radio" name="purchaseInputType" id="invoiceType" value="invoice"'.($purchase['ID']==''?'checked':'').'/><label for="invoiceType">'.__('invoice').'</label>';	
		$content.= '<input type="radio" name="purchaseInputType" id="declarationType" value="declaration"/><label for="declarationType">'.__('declaration').'</label>';	
		$content.= '</fieldset>';		

		//Meta fieldset
		$content.= '<fieldset id="contactFieldSet" '.(($purchase['Status']=='readonly'?'disabled':'')).'><legend>'.__('contact').'</legend>';
		$content.= '<input type="hidden" id="idField" name="ID" value="'.$id.'"/>';
		$content.= '<input type="hidden" id="entryIdField" name="entryID" value="'.$purchase['EntryID'].'"/>';
		$content.= '<input type="hidden" readonly id="statusField" name="Status" value="'.$purchase['Status'].'"/>';
		$content.= '<table>';
		$content.= '<tr><th>ID</th><td>'.$purchase['ID'].'</td>';

       		 // Contactinformatie inladen
		$contact_options = '<option value="" disabled="disabled"'.($purchase['ContactID']?'':' selected').'>'.__('pick-contact').'</option>';
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) $contact_options.= '<option value="'.$contact['ID'].'"'.($purchase['ContactID']==$contact['ID']?' selected':'').'>'.$contact['Name'].'</option>';
		$content.= '<tr><th>'.__('contact').'</th>';
		$content.= '<td><select id="contactId" name="ContactID" onchange="readOnlySelect(\'contactId\',\'contactIdHidden\');">'.$contact_options.'</select>';
		$content.= '<select id="contactIdHidden" name="contactIDhidden" hidden="true">'.$contact_options.'</select></td>'; 
		$content.= '<td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/contacts/new\';"/></td></tr>';

		//get payment endpoints from database
		$payment_options=array(array("def","choose account"));
		$paymentEndPoints = $db->query("SELECT * FROM PaymentEndpoint");
		while($paymentEndPoint = $paymentEndPoints->fetchArray()) array_push($payment_options,array($paymentEndPoint['ID'],$paymentEndPoint['Account'],$paymentEndPoint['ContactID']));
		$payment_options_safe=json_encode($payment_options);

		//choose account
		$content.= '<tr><th>'.__('account').'</th><td><select id="PaymentID" name="PaymentID"></select></td></tr>';	

		//Declaration / Invoice Fieldset
		$content.= '</table></fieldset>';

		//Datum en locatie van het boekstuk
		$content.='<fieldset id="metaFieldSet" '.(($purchase['Status']=='readonly'?'disabled':'')).'>';
		$content.='<legend>Bonnetje = boekstuk</legend><table>';
		$content.='<tr id="supplierIdRow" hidden><th>'.__('contact').'</th>';
		$content.= '<td><select id="supplierId" name="SupplierID" onchange="readOnlySelect(\'supplierId\',\'supplierIdHidden\');">'.$contact_options.'</select>';
		$content.= '<select id="supplierIdHidden" name="SupplierIDHidden" hidden="true"'.($purchase['SupplierID']?'':' selected').'>'.$contact_options.'</select></td>'; 
		$content.='<td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/contacts/new\';"/></td></tr>';			

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

		
		//Invoice/Declaration upload
		$content.= '<tr><th>'.__('location').'</th><td><input type="text" id="location" name="Location" value="'.$entry['URL'].'"/></td>' ;
		$content.= '<td><input type="file" value="'.__('upload').'" name=myFile accept="image/*,.pdf" onchange="upload(\'invoice\', this);"></td></tr>';

		//ProjectID
		$options = '<option value="" disabled="disabled"'.($purchase['ProjectID']?'':' selected').'>'.__('pick-project').'</option>';
		$projects = $db->query("SELECT * FROM Projects ORDER BY Name");
		while($project = $projects->fetchArray()) $options.= '<option value="'.$project['ID'].'"'.($purchase['ProjectID']==$project['ID']?' selected':'').'>'.$project['Name'].'</option>';
		$content.= '<tr><th>'.__('project').'</th>';
		$content.= '<td><select id="projectId" name="ProjectID" onchange="readOnlySelect(\'projectId\',\'projectIdHidden\');">'.$options.'</select>';
		$content.= '<select id="projectIdHidden" name="ProjectIDhidden" hidden="true">'.$options.'</select></td></tr>';
		
		//Reference
		$content.= '<tr><th>'.__('reference').'</th><td><input type="text" name="Reference" value="'.$purchase['Reference'].'"/></td></tr>';
		$charge_options=array(array("0",__('no')),array("1",__('yes')),array("2",__('done')));
		$content.= '<tr><th>'.__('charge').'</th><td><select id="chargeSelect" name="ChargeSelect"></select></td></tr>';
		$content.= '</table></fieldset>';		

		//Haal alle opties voor kostensoort uit de database
	//	$exp_options = array(array("def","pick-expense"));
		$exp_options = array(array("def",__('pick-expense')));
		$expenses = $db->query("SELECT * FROM Accounts WHERE PID='12' ORDER BY Name");
		while($expense = $expenses->fetchArray()) array_push($exp_options,array($expense['ID'],$expense['Name']));
		$exp_options_safe=json_encode($exp_options);

		//Geef alle opties voor btw-type
		$vat_options=array(
			array("def","kies type"),
			array("0","btw-vrij"),
			array("9","9%"),
			array("21","21%")
		);
		$vat_options_safe=json_encode($vat_options);

		//Geef alle opties voor btw verlegging TODO: nog koppelen aan rekeningen
		$shift_options=array(
			array("no","nee"),
			array("NL","NL"),
			array("EU","EU"),
			array("Ex","Ex")
		);
		$shift_options_safe=json_encode($shift_options);

		// Rijen met transacties
		$content.='<fieldset id="expenseFieldSet"'.(($purchase['Status']=='readonly'?'disabled':'')).'>';
		$content.='<legend>Accounting lines</legend><table id="expenseTable" class="expenseInputTable">';
		$content.='<tr class="expenseInputRow"><th class="expenseInputCol">'.__('expense').'</th>'; 
		$content.='<th class="expenseInputCol">'.__('gross').makeInfoButton(__('help-button-gross')).'</th>';
		$content.='<th class="expenseInputCol">'.__('nett').makeInfoButton(__('help-button-nett')).'</th>';
		$content.='<th class="expenseInputCol">'.__('vat').makeInfoButton(__('help-button-vat')).'</th>';
		$content.='<th class="expenseInputCol">'.__('vat type').'</th>';
		$content.='<th class="expenseInputCol">'.__('vat shift').makeInfoButton(__('help-button-shift')).'</th>';
		$content.='<td class="expenseInputColLast"><input type="button" id="addExpenseRowButton" value="'.__('add row').'"></tr>';
		
		//Laatste rij met het totaal
		$content.= '<table class="expenseInputTotTable"><tr class="expenseInputRow"><th class="expenseInputCol">'.__('total').'</th>';
		$content.= '<td class="expenseInputCol"><input type="number" step="0.01" class="expenseInputField" name="grossTot" id="'.__('grossTot').'" readonly></td>';
		$content.= '<td class="expenseInputCol"><input type="number" step="0.01" class="expenseInputField" name="nettTot" id="'.__('nettTot').'" readonly></td>';
		$content.= '<td class="expenseInputCol"><input type="number" step="0.01" class="expenseInputField" name="vatTot" id="'.__('vatTot').'" readonly></td>';
		$content.= '<td class="expenseInputCol"></td>';
		$content.= '<td class="expenseInputCol"><select id="vatShift" name="VatShift"></td>';
		$content.= '<td class="expenseInputColLast"></td>';

		$content.= '</table></fieldset>';

		//Submit Buttons
		switch($purchase['Status']){
			case "" :
				$content.='<button type="submit" name="cmd" value="back">'.__('back').'</button>';
				$content.='<span id="updateSpan" title="Input data first">';
				$content.='<button type="submit" id="updateButton" name="cmd" value="update" disabled="disabled">'.__('submit').'</button></span>';
				if($purchase['ID']){	//if not new entry
					$content.='<span id="sendSpan" title="Input data first">';
					$content.='<button type="submit" id="sendButton" name="cmd" value="sendtoreview">'.__('send').' '.__('for').' '.__('review').'</button></span>';		
				}				
				break;
			case "review" :
				$content.='<button type="submit" name="cmd" value="back">'.__('back').'</button>';	
				$content.='<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
				$content.='<button type="submit" name="cmd" value="review">'.__('review').'</button>';
				break;
			case "final" :
				$content.='<button type="submit" name="cmd" value="back">'.__('back').'</button>';
				$content.='<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';	
				$content.='<button type="submit" name="cmd" value="save">'.__('save').'</button>';
				break;
			case "readonly" :
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
			array("vatShift",$shift_options),
			array("periodSelect",$period_options),
			array("periodSelectHidden",$period_options),
			array("yearSelect",$year_options),
			array("yearSelectHidden",$year_options),
			array("chargeSelect",$charge_options),
		);
		$all_options_safe=json_encode($all_options);

		$all_fieldsets=array("contactFieldSet","metaFieldSet","expenseFieldSet","radioFieldSet");
		$all_fieldsets_safe=json_encode($all_fieldsets);

		//Loading javascript
		$content.= '<script type="text/javascript" src="../../js/purchases.js"></script>';

		//Load specific javacript functions
		$content.= '<script>addOptionsPHP('.$all_options_safe.')</script>';
		$content.= '<script>setGlobalOptions('.$exp_options_safe.','.$vat_options_safe.')</script>';
		$content.= '<script>addOnClick()</script>';
		$content.= '<script>addOptionsPHP_onclick("PaymentID",'.$payment_options_safe.',"contactId")</script>';
		$content.= '<script>onChangeFieldSet('.$all_fieldsets_safe.')</script>';

		if ($json!=null){
			$content.= '<script>readJson('.$json.')</script>';
		}
		else{
			//add just one row
			$content.= '<script>addExpenseRow()</script>';
		}
	}
	
	function updatePurchase() {
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

					//Use the correct contact in case of a declaration
					($_POST['purchaseInputType']=="invoice" ? $sup=$_POST['ContactID']:$sup=$_POST['SupplierID']);

					//insert a new Purchase
					$db->query("INSERT INTO Purchases (EntryID, Status, Reference, ContactID, SupplierID, ProjectID,Charge) VALUES ('".$entryID."','','".$_POST['Reference']."', '".$_POST['ContactID']."', '".$sup."','".$_POST['ProjectID']."','".$_POST['ChargeSelect']."')");
					$last_entry=$db->querySingle("SELECT MAX(ID) FROM Purchases LIMIT 1");
					$purchaseID=intval($last_entry);

					//save form data in a json file in files/purchases
					createJSON($_POST,$purchaseID);

				}
				else {
					//update the entry
					$db->query("UPDATE Entries SET 
						TransactionDate='".$_POST['TransactionDate']."', 
						AccountingDate='".$_POST['AccountingDate']."', 
						PeriodFrom='".$_POST['PeriodFrom']."', 
						PeriodTo='".$_POST['PeriodTo']."', 
						URL='".$_POST['Location']."' 
						WHERE ID='".$_POST['entryID']."'
					");
					
					//Use the correct contact in case of a declaration
					($_POST['purchaseInputType']=="invoice" ? $sup=$_POST['ContactID']:$sup=$_POST['SupplierID']);

					//insert a new Purchase
					$db->query("Update Purchases SET 
						Status='', 
						Reference='".$_POST['Reference']."',
						ContactID='".$_POST['ContactID']."',
						SupplierID='".$sup."',
						ProjectID='".$_POST['ProjectID']."',
						Charge='".$_POST['ChargeSelect']."'
						WHERE ID='".$_POST['ID']."'
					");
					
					//replace the JSON
					createJSON($_POST,$_POST['ID']);
				}
				break;

			case 'sendtoreview':

					$db->query("UPDATE Purchases SET Status='review'");

				break;

			case 'remove':

				//delete entry and expense from the database
				$db->query("DELETE FROM Entries WHERE ID='".$_POST['EntryID']."'");
				$db->query("DELETE FROM Purchases WHERE ID='".$_POST['ID']."'");
				unlink('files/purchases/'.$_POST['ID'].'.json');

				//check if an invoice/declaration is uploaded
				if($_POST["Location"]!="" and file_exists('files/purchases/'.$_POST["Location"])){

					//delete the invoice/declaration
					unlink('files/purchases/'.$_POST["Location"]);
				} 

				break;

			case 'back':				

				//if a .pdf is created, but not saved then remove it
				if ($_POST['ID']=='new'){
					if($_POST["Location"]!="" and file_exists('files/purchases/'.$_POST["Location"])){

						//delete the invoice file
						unlink('files/purchases/'.$_POST["Location"]);
					} 
				}
				break;	

			case 'review':
				echo "reviewing".$_POST['ID'];
				$db->query("UPDATE Purchases SET Status='final' WHERE ID='".$_POST['ID']."'");

				break;	

			case 'save':
				
				//create the transactions and mutations
				foreach ($_POST as $key => $type){
					$checkstr="expenseType";
					$vatshift=$_POST["VatShift"];

					if (strpos($key,$checkstr)!==False){
						$db->query("INSERT INTO Transactions (EntryID) VALUES ('".$_POST['entryID']."')");
						$transID=intval($db->querySingle("SELECT MAX(ID) FROM Transactions LIMIT 1"));

						//get data for the mutation
						$trans_num=substr($key, strlen($checkstr),strlen($key));
						$gross_key="expenseGross".$trans_num;
						$nett_key="expenseNett".$trans_num;
						$vat_key="expenseVat".$trans_num;
						$vat_type_key="expenseVatType".$trans_num;

						makeMutations($db,$transID,$type,$_POST[$gross_key],$_POST[$nett_key],$_POST[$vat_key],$_POST[$vat_type_key]);					
	
					}
				}

				$db->query("UPDATE Purchases SET Status='readonly' WHERE ID='".$_POST['ID']."'");				
		
				break;

		}
		viewPurchaseList();
	}
	function createJSON($data,$purchaseID){
		$json_path='files/purchases/'.$purchaseID.".json";
		$meta_dict=array(
			'recipient'=>$data['ContactID'], //double also in database
			'invoiceDate'=>$data['TransactionDate'], //double also in database
			'reference'=>$data['Reference'], //double also in database
			'project'=>$data['ProjectID'], //double also in database
			'purchaseInputType'=>$data['purchaseInputType'],
			'periodSelect'=>$data['periodSelect'],
			'yearSelect'=>$data['yearSelect'],
			'chargeSelect'=>$data['ChargeSelect'], //double also in database
			'periodFrom'=>$data['PeriodFrom'], //double also in database
			'periodTo'=>$data['PeriodTo'], //double also in database
			'totalNett'=>$data['nettTot']
		);
						
		$json_dict=array('Meta'=>$meta_dict);

		foreach ($data as $key => $value){
			
			//add the expense rows to the json file
			if (strpos($key,"expenseTypeHidden")!==False){
				$sl=array();
				$sl_num=substr($key, strlen("expenseTypeHidden"),strlen($key));

				$sl['expenseType']=$data["expenseTypeHidden".$sl_num];
				$sl['expenseNett']=$data["expenseNett".$sl_num];
				$sl['expenseVatType']=$data["expenseVatType".$sl_num];
				$sl['expenseVat']=$data["expenseVat".$sl_num];
				$sl['expenseGross']=$data["expenseGross".$sl_num];

				$json_dict["expenseLine_".$sl_num]=$sl;
				
			}
				
		}

		$json_file=fopen($json_path, 'w');
		fwrite($json_file, json_encode($json_dict));
		fclose($json_file);
	}


	function makeMutations($db,$transID,$expenseType,$gross,$nett,$vat,$vat_type) {

		//hier de regels voor het inboeken van inkoop transacties, nu nog even simpel
		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '".$expenseType."', '".$nett."')");
		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '5', '".$nett."')");	
		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '19', '".$vat."')");
	}
	
	//boekhoudregels voor purchase in reverse, voelt onhandig, misschien gewoon $gross, $vat, $nett, $vat_type etc opslaan in de transaction?

