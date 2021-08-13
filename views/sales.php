<?php
	include_once('php_common/invoice.php');

	if (!is_dir('files/sales/')) mkdir('files/sales/');
	
	//handler for deleting an invoice 
	if(isset($_POST["invoice_del"])) {

		$invoice_path='files/sales/'.$_POST["invoice_del"];

		unlink($invoice_path);
		
		echo "";

		exit();
	}
	
	//handler for generating an invoice
	if(isset($_POST["invoice_gen"])) {
		if(isset($_POST["Location"])){
			$invoice_file = $_POST["Location"];
		}
		else{
			$unid=uniqid();
			$invoice_file = $unid.".pdf";
		}

		$invoice_path='files/sales/'.$invoice_file;

		//save a .pdf file in the files/sales
		$pdffile=fopen($invoice_path, 'w');
		$invoice_data=json_decode($_POST["invoice_gen"],true);
		
		//get data from the database
		$contact=$db->query("SELECT * FROM Contacts WHERE ID='".$invoice_data['Meta']['recipient']."'")->fetchArray();
		$invoice_data['Meta']['recipient']=$contact;
		$project=$db->query("SELECT * FROM Projects WHERE ID='".$invoice_data['Meta']['project']."'")->fetchArray();
		$invoice_data['Meta']['project']=$project;
		
		fwrite($pdffile,createPDF($invoice_data));
		fclose($pdffile);
		
		echo $invoice_file;

		exit();
	}
	
	//handler for uploading an invoice
	if(isset($_FILES["invoice_up"])) {
		$error = false;
		
		$maxsize = ini_get("upload_max_filesize");
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $maxsize); // Remove the non-unit characters from the size.
		$maxsize = preg_replace('/[^0-9\.]/', '', $maxsize); // Remove the non-numeric characters from the size.
		$maxsize = $unit ? round($maxsize * pow(1024, stripos('bkmgtpezy', $unit[0]))) : round($maxsize);
		if ($_FILES["invoice_up"]["size"] > $maxsize) $error.= 'Sorry, your file is too large.<br/>';
		
		$filetype = strtolower(pathinfo($_FILES["invoice_up"]["name"],PATHINFO_EXTENSION));
		if($filetype!="pdf" && $filetype!="jpg" && $filetype!="png" && $filetype!="jpeg" && $filetype!="gif" ) $error.= 'Wrong file type<br/>';
		
		if ($error) {
			$error.= 'Only PDF, JPG, JPEG, PNG & GIF files are allowed. Maximum size: '.ini_get("upload_max_filesize").'<br/>';
			echo $error;
			exit();
		}
		
		switch($filetype) {
			case "pdf":
				$filename = uniqid().".pdf";
				move_uploaded_file($_FILES["invoice_up"]["tmp_name"], 'files/sales/'.$filename);
				echo $filename;
				break;
			default:
				$filename = uniqid().".jpg";
				$max = 1024;
				$src = imagecreatefromstring(file_get_contents($_FILES["invoice_up"]['tmp_name']));
				list($src_w, $src_h, $type, $attr) = getimagesize($_FILES["invoice_up"]['tmp_name']);

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
				imagejpeg($dst, 'files/sales/'.$filename);
				imagedestroy($dst);

				echo $filename;
				break;
		}
		
		exit();
	}
	
	if ($cmd) updateSale();
	elseif ($view) viewSale($view);
	else viewSaleList();

	//VIEW - overview of sales
	function viewSaleList() {
		global $db, $content, $url, $lang;
		$query = "SELECT * FROM Sales ORDER BY ID";
		$list = $db->query($query);
		$content.= '<table>';
		$content.= '<tr>';
		$content.= '<th>ID</th>';
		$content.= '<th>'.__('date').'</th>';
		$content.= '<th>'.__('reference').'</th>';
		$content.= '<th>'.__('location').'</th>';
		$content.= '<th>'.__('amount').'</th>';
		$content.= '<th>'.__('project').'</th>';
		$content.= '<th>'.__('contact').'</th>';
		$content.= '<td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/sales/new\';"/></td>';
		$content.= '</tr>';

		if($list){
			while($sales = $list->fetchArray()) {

				//collect info
				$entry=$db->query("SELECT * FROM Entries WHERE ID='".$sales['EntryID']."'")->fetchArray();

				//get the nett total amount from JSON file
				$json_path='files/sales/'.$sales['ID'].".json";
				$json_array=json_decode(file_get_contents($json_path),true);
				$amount_sum=$json_array['Meta']['totalNett'];								

				//get project names from database
				$project = $db->query("SELECT * FROM Projects WHERE ID='".$sales['ProjectID']."'")->fetchArray();
				$contact = $db->query("SELECT * FROM Contacts WHERE ID='".$sales['ContactID']."'")->fetchArray();
			
				//put in the table			
				$content.= '<tr class="data">';
				$content.= '<td>'.$sales['ID'].'</td>';
				$content.= '<td>'.$entry['TransactionDate'].'</td>';
				$content.= '<td>'.$sales['Reference'].'</td>';
				$content.= '<td>'.$entry['URL'].'</td>';
				$content.= '<td>'.$amount_sum.'</td>';
				$content.= '<td>'.$project['Name'].'</td>';
				$content.= '<td>'.$contact['Name'].'</td>';
				$content.= '<td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/sales/'.$sales['ID'].'\';"/></td>';
				$content.= '</tr>';
			}
		}
		$content.= '</table>';
	}

	//VIEW - new or existing entry/sale
	function viewSale($id) {

		global $db, $content, $url, $lang;

		//get content
		if ($id=='new'){

			//create empty arrays
			$sale = array(
				"ID"=>"", 
				"EntryID" => "", 
				"Status" => "", 
				"Reference" => "", 
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

			$json=null;
		}		
		else {
			//load arrays from the database
			$sale = $db->query("SELECT * FROM Sales WHERE ID='".$id."'")->fetchArray();
			$entry = $db->query("SELECT * FROM Entries WHERE ID='".$sale['EntryID']."'")->fetchArray();

			//get the data from the json file/field
			$json_path='./files/sales/'.$sale['ID'].'.json';
			if(file_exists($json_path)){
				$json=file_get_contents($json_path);
			}
			else{
				$json=null;
			}

			//maybe usefull for a check later on
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
		$content.= '<div class="salesInputFrame">';
		$content.= '<div class="salesInputForm">';
		$content.= '<form id="salesForm" method="post">';
		$content.= '<fieldset id="metaFieldSet" '.(($sale['Status']=='readonly'?'disabled':'')).'>';
		$content.= '<legend>'.__('data').'</legend>';	
		$content.= '<input type="hidden" id="Id" name="ID" value="'.$id.'"/>';	
		$content.= '<input type="hidden" name="entryID" value="'.$sale['EntryID'].'"/>';	
		$content.= '<table><tr><th>ID</th><td>'.$sale['ID'].'</td>';

                // Contactinformatie
		$options = '<option value="" disabled="disabled"'.($sale['ContactID']?'':' selected').'>'.__('pick-contact').'</option>';
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) $options.= '<option value="'.$contact['ID'].'"'.($sale['ContactID']==$contact['ID']?' selected':'').'>'.$contact['Name'].'</option>';
		$content.= '<tr><th>'.__('contact').'</th>';
		$content.= '<td><select id="contactId" name="ContactID" onchange="readOnlySelect(\'contactId\',\'contactIdHidden\');">'.$options.'</select>';
		$content.= '<select id="contactIdHidden" name="contactIDhidden" hidden="true">'.$options.'</select></td>'; 
		$content.= '<td><input type="button" value="'.__('new').'" onclick="window.location.href=\''.$url.$lang.'/contacts/new\';"/></td></tr>';		

		//TODO: WISHLIST multiple accounts for Plan B

		//Invoice date
		$today=date('Y-m-d');
		$content.= '<tr><th>'.__('invoice-date').'</th><td><input type="date" id="transactionDate" name="TransactionDate" value="'.$entry['TransactionDate'].'"/></td></tr>';
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
		$period_options_safe=json_encode($period_options);
		$year_options=array(array("def","kies jaar"),array(date('Y')-1,date('Y')-1),array(date('Y'),date('Y')),array(date('Y')+1,date('Y')+1));
		$year_options_safe=json_encode($year_options);

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
		$content.= '<td id="periodFromLabel" hidden="true">'.__('from').'<input type="date" id="periodFrom" name="periodFrom" value="'.$entry['PeriodFrom'].'"></td>';
		$content.= '<td id="periodToLabel" hidden="true">'.__('to').'<input type="date" id="periodTo" name="periodTo" value="'.$entry['PeriodTo'].'"></td></tr>';
		
	
		//ProjectID select field
		$options = '<option value="" disabled="disabled"'.($sale['ProjectID']?'':' selected').'>'.__('pick-project').'</option>';
		//oude versie van bovenstaande, werkte niet goed, maar in de nieuwe regel gebeurt er niets met readonly, is dat nodig?:
		//$options = '<option value="" disabled="disabled"'.(($sale['Status']=='readonly'?'disabled':'')).'>'.__('pick-project').'</option>';
		$projects = $db->query("SELECT * FROM Projects ORDER BY Name");
		while($project = $projects->fetchArray()) if ($project['Status']=='active') $options.= '<option value="'.$project['ID'].'"'.($sale['ProjectID']==$project['ID']?' selected':'').'>'.$project['Name'].'</option>';
		$content.= '<tr><th>'.__('project').'</th><td>';
		$content.= '<select id="projectId" name="ProjectID" onchange="readOnlySelect(\'projectId\',\'projectIdHidden\');">'.$options.'</select>';
		$content.= '<select id="projectIdHidden" name="ProjectIDhidden" hidden="true">'.$options.'</select></td></tr>';
		
		//Invoice mode - uploading, paper or generate
		$form_options='<option value="upload">'.__('upload existing invoice').'</option>';
		$form_options.='<option value="generate">'.__('generate new invoice').'</option>';
		$form_options.='<option value="paper">'.__('paper invoice').'</option>';
		$content.= '<tr><th>'.__('input mode').'</th><td>';
		$content.= '<select id="invoiceMode" name="invoiceMode">'.$form_options.'</select>'; //no onchange already a listener
		$content.= '<select id="invoiceModeHidden" name="invoiceModeHidden" hidden="true">'.$form_options.'</select></td></tr>';

		//Invoice location
		
		$content.= '<tr><th>'.__('invoice').'</th><td><input type="text" id="location" name="Location" value="'.$entry['URL'].'"></td>';
		//$content.= '<tr><th>Invoice</th><td><input type="text" id="location" name="Location" value="'.$entry['URL'].'"></td>';
		$content.= '<td><input type="file" id="invoiceUpBut" value="'.__('upload').'" name=myFile accept="image/*,.pdf" onchange="uploadInvoice(\'invoice_up\', this);"></td></tr>';
		$content.= '<tr><th>'.__('reference').'</th><td><input type="text" id="reference" name="Reference" value="'.$sale['Reference'].'"/></td></tr>';
		$content.= '</table></fieldset>';		

		//Geef alle opties voor sales, hier later nog even over nadenken, wil je kosten van de omzet appart hebben (voorraad, kosten derder etc)?
		$sales_options=array(array("def","kies type"),array("1","Uren"),array("2","Materialen"),array("3","Reiskosten"));
		$sales_options_safe=json_encode($sales_options);

		//Geef alle opties voor invoice (sales+header)
		$invoice_options=array(array("def","kies type"),array("head","Header"),array("1","Uren"),array("2","Materialen"),array("3","Reiskosten"));
		$invoice_options_safe=json_encode($invoice_options);

		//Geef alle opties voor btw-type
		$vat_options=array(array("def","kies type"),array("0","btw-vrij"),array("9","9%"),array("21","21%"));
		$vat_options_safe=json_encode($vat_options);

		//Geef alle opties voor btw verlegging TODO: nog koppelen aan rekeningen
		$shift_options=array(array("no",__('no')),array("NL","NL"),array("EU","EU"),array("Ex","Ex"));
		$shift_options_safe=json_encode($shift_options);

		//Invoice table
		$content.= '<fieldset id="invoiceFieldSet" hidden="true" '.(($sale['Status']=='readonly'?'disabled':'')).'>';
		$content.= '<legend>'.__('invoice items').'</legend><table id="invoiceTable" class="salesInputTable">';
		$content.= '<tr class="salesInputRow"><th class="salesInputCol">'.__('sales type').'</th>';
		$content.='<th class="salesInputCol">'.__('description').'</th>'; 
		$content.='<th class="salesInputCol">'.__('number').'</th>';
		$content.='<th class="salesInputCol">'.__('price').'</th>';
		$content.='<th class="salesInputCol">'.__('nett').'</th>';
		$content.='<th class="salesInputCol">'.__('vat type').'</th>';
		$content.='<td class="salesInputColLast"><input type="button" id="addInvoiceRowButton" value="+"/></td></tr>';
		$content.= '</table>';
		$content.= '<input type="button" id="invoiceMake" value="'.('make-invoice').'"></input>';
		$content.='</fieldset>';


		// Rijen met transacties
		$content.= '<fieldset id="salesFieldSet"'.(($sale['Status']=='readonly'?'disabled':'')).'>';
	//	$content.= '<legend>Accounting lines</legend><table id="salesTable" class="salesInputTable">';
		$content.= '<legend>'.__('accounting lines').'</legend><table id="salesTable" class="salesInputTable">';
		$content.= '<tr class="salesInputRow"><th class="salesInputCol">'.__('sales type').'</th>';
		$content.='<th class="salesInputCol">'.__('nett').makeInfoButton(__('help-button-nett')).'</th>';
		$content.='<th class="salesInputCol">'.__('vat type').'</th>';
		$content.='<th class="salesInputCol">'.__('vat').makeInfoButton(__('help-button-vat')).'</th>';
		$content.='<th class="salesInputCol">'.__('gross').makeInfoButton(__('help-button-gross')).'</th>';
		$content.='<td class="salesInputColLast"><input type="button" id="addSalesRowButton" value="+"/></td></tr>';

		//Totalen van de transacties
		$content.= '<table class="salesInputTotTable" align="right"><tr class="salesInputRow"><tr>____________</tr>';
		$content.= '<tr><th class="salesInputCol">'.__('nett').'</th>'; 
		$content.= '<td class="salesInputCol"><input type="number" step="0.01" class="salesInputField" name="nettTot" id="nettTot" readonly></td></tr>';
		foreach($vat_options as $vat){
			if ($vat[0]!="def" and $vat[0]!=0){
				$content.= '<tr id="vatTotRow_'.$vat[0].'"><th class="salesInputCol">'.$vat[1].'</th><td class="salesInputCol"><input type="number" step="0.01" class="salesInputField" id="vatTot_'.$vat[0].'" readonly></td></tr>';
			}
		}
		//TODO: add vat shift functionality
		$content.='<tr><th class="salesInputCol">'.__('vat shift').'</th><td class="salesInputCol">';
		$content.='<select name="vatShift" id="vatShift" disabled="true"></select>';
		$content.='<select name="vatShiftHidden" id="vatShiftHidden" hidden="true"></select></td></tr>';
		$content.='<tr><th></th><td>-------------------</td></tr>';
		$content.='<tr><th class="salesInputCol">'.__('gross').'</th>';
		$content.='<td class="salesInputCol"><input type="number" step="0.01" class="salesInputField" id="grossTot" disabled></td></tr>';
		$content.='<tr><td class="salesInputColLast"></td></tr>';
		$content.='</table></fieldset>';
		
		//Submit Buttons
		switch($sale['Status']){
			case "" :
				$content.='<button type="submit" name="cmd" value="back">'.__('back').'</button>';
				$content.='<span id="update_span" title="Input data first">';
				$content.='<button type="submit" id="update" name="cmd" value="update" disabled="disabled">'.__('save').makeInfoButton(__('help-button-save-sales')).'</button></span>';	
				if($sale['ID']){	//if not new entry
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
				$content.='<button type="submit" name="cmd" value="save">'.'</button>';
				
	//	$content.='<th class="salesInputCol">'.__('nett').makeInfoButton(__('help-button-nett')).'</th>';
				break;
			case "readonly" :
				$content.='<button type="submit" name="cmd" value="back">'.__('back').'</button>';
		}
		$content.= '</form></div>';
		
		//div containing the uploaded or generated invoice
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
		);
		$all_options_safe=json_encode($all_options);

		//Loading javascript
		$content.= '<script type="text/javascript" src="../../js/sales.js"></script>';

		//Calling specific javasctipt functions
		$content.= '<script>addOptionsPHP('.$all_options_safe.')</script>';
		$content.= '<script>setGlobalOptions('.$invoice_options_safe.','.$sales_options_safe.','.$vat_options_safe.')</script>';
		$content.= '<script>addOnClick()</script>';
		$content.= '<script>onChangeFieldSet("metaFieldSet")</script>';
		$content.= '<script>onChangeFieldSet("salesFieldSet")</script>';
		$content.= '<script>onChangeFieldSet("invoiceFieldSet")</script>';
		$content.= '<script>onchangeInput("invoiceMode")</script>';
		$content.= '<script>onchangeMake("invoiceMake","invoice_gen")</script>';	

		if ($json!=null){
			$content.= '<script>readJson('.$json.')</script>';
		}
		else{
			//add just one row
			$content.= '<script>addInvoiceRow()</script>';
		}

	}
	
	function updateSale() {
		global $db, $content, $url, $lang;
		$userIDs = isset($_POST['UserIDs']) ? implode(',', $_POST['UserIDs']) : '';
		switch ($_POST['cmd']) {
			case 'update':
				
				if ($_POST['ID']=='new') {
					
					//Insert a new entry
					$db->query("INSERT INTO Entries (TransactionDate, AccountingDate, PeriodFrom, PeriodTo, URL) VALUES ('".$_POST['TransactionDate']."', '".$_POST['AccountingDate']."', '".$_POST['periodFrom']."', '".$_POST['periodTo']."','".$_POST['Location']."')");	

					// get the entryID from the database $id = $db->lastInsertRowID();
					$last_entry=$db->querySingle("SELECT MAX(ID) FROM Entries LIMIT 1");
					$entryID=intval($last_entry);

					//insert a new sale
					$db->query("INSERT INTO Sales (EntryID, Status, Reference, ContactID, ProjectID) VALUES ('".$entryID."','','".$_POST['Reference']."', '".$_POST['ContactID']."', '".$_POST['ProjectID']."')");
					$last_entry=$db->querySingle("SELECT MAX(ID) FROM Sales LIMIT 1");
					$salesID=intval($last_entry);

					//save form data in a json file in files/sales
					createJSON($POST,$salesID);
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
					
					//update the sale
					$db->query("UPDATE Sales SET 
						Reference='".$_POST['Reference']."', 
						ContactID='".$_POST['ContactID']."', 
						ProjectID='".$_POST['ProjectID']."' 
						WHERE ID='".$_POST['ID']."'
					");

					//replace the JSON
					createJSON($POST,$_POST['ID']);

				}
				break;

			case 'sendtoreview':

					$db->query("UPDATE Sales SET Status='review'");

				break;

			case 'remove':
				
				//delete entry and sales from the database
				$db->query("DELETE FROM Entries WHERE ID='".$_POST['EntryID']."'");
				$db->query("DELETE FROM Sales WHERE ID='".$_POST['ID']."'");
				unlink('files/sales/'.$_POST['ID'].'.json');

				if ($_POST['invoiceModeHidden']=='generate' or $_POST['invoiceModeHidden']=='upload'){

					//check if an invoice is there
					if($_POST["Location"]!=""){

						//delete the invoice file
						unlink('files/sales/'.$_POST["Location"]);
					}
				} 

				break;

			case 'back':
				//if a .pdf is created, but not saved then remove it
				if ($_POST['ID']=='new'){
					if ($_POST['invoiceModeHidden']=='generate' or $_POST['invoiceModeHidden']=='upload'){

						//check if an invoice is there
						if($_POST["Location"]!=""){

							//delete the invoice file
							unlink('files/sales/'.$_POST["Location"]);
						}
					} 
				}
				break;				
			
			case 'review':
				
				$db->query("UPDATE Sales SET Status='final' WHERE ID='".$_POST['ID']."'");

				break;

			case 'save':
				
				//create the transactions and mutations
				foreach ($_POST as $key => $value){
					$checkstr="salesType";
					$vatshift=$_POST["vatShiftHidden"];

					if (strpos($key,$checkstr)!==False){
						$db->query("INSERT INTO Transactions (EntryID) VALUES ('".$_POST['entryID']."')");
						$transID=intval($db->querySingle("SELECT MAX(ID) FROM Transactions LIMIT 1"));

						//get data for the mutation
						$trans_num=substr($key, strlen($checkstr),strlen($key));
						$gross_key="salesGross".$trans_num;
						$nett_key="salesNett".$trans_num;
						$vat_key="salesVat".$trans_num;
						$vat_type_key="salesVatType".$trans_num;

						makeMutations($db,"no",$transID,$value,$_POST[$nett_key],$_POST[$vat_type_key],$_POST[$vat_key],$_POST[$gross_key]);					
					}
				}

				$db->query("UPDATE Sales SET Status='readonly' WHERE ID='".$_POST['ID']."'");
				
		}
		viewSaleList();
	}
	
	function createJSON($POST,$salesID){
		$json_path='files/sales/'.$salesID.".json";
		$meta_dict=array(
			'recipient'=>$_POST['ContactID'],
			'invoiceDate'=>$_POST['TransactionDate'],
			'reference'=>$_POST['Reference'],
			'project'=>$_POST['ProjectID'],
			'filetype'=>$_POST['invoiceModeHidden'],
			'periodSelect'=>$_POST['periodSelectHidden'],
			'yearSelect'=>$_POST['yearSelectHidden'],
			'periodFrom'=>$_POST['periodFrom'],
			'periodTo'=>$_POST['periodTo'],
			'totalNett'=>$_POST['nettTot']
		);
						
		$json_dict=array('Meta'=>$meta_dict);

		foreach ($_POST as $key => $value){
			
			//add the sales rows to the json file
			if (strpos($key,"salesTypeHidden")!==False){
				$sl=array();
				$sl_num=substr($key, strlen("salesTypeHidden"),strlen($key));

				$sl['salesType']=$_POST["salesTypeHidden".$sl_num];
				$sl['salesNett']=$_POST["salesNett".$sl_num];
				$sl['salesVatType']=$_POST["salesVatType".$sl_num];
				$sl['salesVat']=$_POST["salesVat".$sl_num];
				$sl['salesGross']=$_POST["salesGross".$sl_num];

				$json_dict["salesLine_".$sl_num]=$sl;
				
			}
				
			//add the invoice rows to the json file
			if (strpos($key,"invoiceTypeHidden")!==False){
				$il=array();
				$in_num=substr($key, strlen("invoiceTypeHidden"),strlen($key));

				$il['invoiceType']=$_POST["invoiceTypeHidden".$in_num];
				$il['invoiceDesc']=$_POST["invoiceDesc".$in_num];

				if ($_POST["invoiceTypeHidden".$in_num]=="head"){
					$il['invoiceAmount']=0;
					$il['invoicePrice']=0;
					$il['invoiceNett']=0;
					$il['invoiceVatType']=0;
				}
				else{
					$il['invoiceAmount']=$_POST["invoiceAmount".$in_num];
					$il['invoicePrice']=$_POST["invoicePrice".$in_num];
					$il['invoiceNett']=$_POST["invoiceNett".$in_num];
					$il['invoiceVatType']=$_POST["invoiceVatType".$in_num];
				}
				
				$json_dict["invoiceLine_".$in_num]=$il;
			}
		}

		$json_file=fopen($json_path, 'w');
		fwrite($json_file, json_encode($json_dict));
		fclose($json_file);
	}


	function makeMutations($db,$shift,$transID,$salesType,$nett,$vat_type,$vat,$gross) {
		
		// TODO: verlegde btw toevoegen wanneer dit ook in de rekeningenstructuur zit 

		//result
		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '13', '".$nett."')");

		//debiteuren
		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '4', '".$gross."')");

		//vat
		switch ($vat_type){
			case 0:
				$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '16', '".$vat."')");
			case 9:
				$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '17', '".$vat."')");
			case 21:
				$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '18', '".$vat."')");
		}
	}

