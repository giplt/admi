<?php
	include_once('php_common/invoice.php');

	if (!is_dir('files/sales')) mkdir('files/sales');

	if(isset($_POST["removeInvoice"])){
		$filename=$_POST["removeInvoice"];
		$filepath='files/sales/'.$filename;
		unlink($filepath);
	}
	
	if(isset($_POST["invoice"])) {
		if(isset($_POST["Location"])){
			$filename = $_POST["Location"];
			$invoicepdf = substr($_POST["Location"],0,-5)+".pdf";
		}
		else{
			$filename = uniqid().".json";
			$invoicepdf = uniqid().".pdf";
		}

		$filepath='files/sales/'.$filename;
		$invoicepath='files/sales/'.$invoicepdf;

		//save the json file in files/sales
		$infile=fopen($filepath, 'w');
		fwrite($infile, $_POST["invoice"]);
		fclose($infile);

		//save a .pdf file in the files/sales
		$pdffile=fopen($invoicepath, 'w');
		$invoice_data=json_decode($_POST["invoice"],true);
		fwrite($pdffile,createPDF($invoice_data));
		fclose($pdffile);
		
		exit();
	}
	
	if ($cmd) updateSale();
	elseif ($view) viewSale($view);
	else viewSaleList();

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

				//get the info for multiple transactions with multiple mutations
				$show_PID=12;
				$transactions=array();
				$mutations=array();
				$amount_sum=0;
				$transaction_results=$db->query("SELECT * FROM Transactions WHERE EntryID='".$entry['ID']."'");
	
				while ($transaction=$transaction_results->fetchArray()){
					array_push($transactions,$transaction);
					$mutation_results=$db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE Mutations.TransactionID='".$transaction['ID']."' AND Accounts.PID='".$show_PID."'");

					while ($mutation=$mutation_results->fetchArray()){
					array_push($mutations,$mutation['Amount']);
					}
				}
				$amount_sum=array_sum($mutations);

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

	
	function viewSale($id) {
		//checks voor verplichte velden
		//required er in zetten
		//2 = validatie, btw optelling klopt niet

		global $db, $content, $url, $lang;

		//get content
		if ($id=='new'){
			//create empty arrays
			$purchase = array("ID"=>"", "EntryID" => "", "Status" => "", "Reference" => "", "ContactID" => "", "ProjectID" => "", "Nett" => "", "VAT" => "", "Gross" => "", "VAT_Type" => "");
			$entry = array("ID"=>"", "TransactionDate" => "", "AccountingDate" => "", "URL" => "", "Log" => "");
			$transactions=array(array("ID"=>"","entryID"=>"","MergeID"=>""));
			$mutations=array(array("ID"=>"","TransactionID"=>"","AccountID"=>"","Amount"=>""));
			$invoice="";
		}		
		else {
			//load arrays from the database
			$purchase = $db->query("SELECT * FROM Sales WHERE ID='".$id."'")->fetchArray();
			$entry = $db->query("SELECT * FROM Entries WHERE ID='".$purchase['EntryID']."'")->fetchArray();

			//get the data from the invoice
			$invoice_path='./files/sales/'.$entry['URL'];

			if(file_exists($invoice_path) ){//and explode(".",$invoice_path)[1]=="json"){
				$invoice=file_get_contents($invoice_path);
			}
			else{
				$invoice=null;
			}

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
		$content.= '<div class="salesInputFrame"><div class="salesInputForm"><form id="salesForm" method="post">';
		$content.= '<input type="hidden" name="ID" value="'.$id.'"/>';
		$content.= '<fieldset><legend>'.__('recipient').'</legend><table>';		
		$content.= '<tr><th>ID</th><td>'.$purchase['ID'].'</td>';

                // Contactinformatie - nog meer info nodig? Ja bankrekening
		$options = '<option value="" disabled="disabled"'.($purchase['ContactID']?'':' selected').'>'.__('pick-contact').'</option>';
		$contacts = $db->query("SELECT * FROM Contacts ORDER BY Name");
		while($contact = $contacts->fetchArray()) $options.= '<option value="'.$contact['ID'].'"'.($purchase['ContactID']==$contact['ID']?' selected':'').'>'.$contact['Name'].'</option>';
		$content.= '<tr><th>'.__('contact').'</th><td><select id="contactId" name="ContactID">'.$options.'</select> <input type="button" value="'.__('new').'" onclick="window.location.href=\''.$url.$lang.'/contacts/new\';"/></td></tr>';		
		$content.='</table></fieldset>';

		//TODO: WISHLIST multiple accounts for Plan B

		//Datum en locatie van het boekstuk
		$today=date('Y-m-d');
		$content.='<fieldset><legend>'.__('invoice').'</legend><table>';
		$content.= '<tr><th>'.__('date').'</th><td><input type="date" id="transactionDate" name="TransactionDate" value="'.$entry['TransactionDate'].'"/></td></tr>';
		$content.= '<input type="hidden" name="AccountingDate" value='.$today.'>';
		
		//ProjectID
		$options = '<option value="" disabled="disabled"'.($purchase['ProjectID']?'':' selected').'>'.__('pick-project').'</option>';
		$projects = $db->query("SELECT * FROM Projects ORDER BY Name");
		while($project = $projects->fetchArray()) $options.= '<option value="'.$project['ID'].'"'.($purchase['ProjectID']==$project['ID']?' selected':'').'>'.$project['Name'].'</option>';
		$content.= '<tr><th>'.__('project').'</th><td><select id="projectId" name="ProjectID">'.$options.'</select></td></tr>';
		
		//Reference
		$content.= '<tr><th>Invoice</th><td><input type="text" id="location" name="Location" value="'.$entry['URL'].'"></td></tr>';
		$content.= '<tr><th>'.__('reference').'</th><td><input type="text" id="reference" name="Reference" value="'.$purchase['Reference'].'"/></td></tr>';
		$form_options='<option value="old">Enter existing invoice</option><option value="new">Create new invoice</option>';
		$content.= '<tr><th>'.__('input mode').'</th><td><select id="invoiceMode">'.$form_options.'</select></td></tr>';
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
		$content.= '<fieldset id="invoiceFieldSet" hidden="true"><legend>'.__('invoice items').'</legend><table id="invoiceTable" class="salesInputTable">';
		$content.= '<tr class="salesInputRow"><th class="salesInputCol">'.__('sales type').'</th>';
		$content.='<th class="salesInputCol">'.__('description').'</th>'; 
		$content.='<th class="salesInputCol">'.__('number').'</th>';
		$content.='<th class="salesInputCol">'.__('price').'</th>';
		$content.='<th class="salesInputCol">'.__('nett').'</th>';
		$content.='<th class="salesInputCol">'.__('vat type').'</th>';
		$content.='<td class="salesInputColLast"><input type="button" id="addInvoiceRowButton" value="+"/></td></tr>';
		$content.= '</table>';
		$content.= '<input type="button" id="invoiceMake" value="'.('make-invoice').'"></input>';
		$content.= '<input type="button" id="invoiceRemove" value="'.('remove-invoice').'"></input>';
		$content.='</fieldset>';

		//Totalen van de factuur


		// Rijen met transacties
		$content.= '<fieldset id="salesFieldSet"><legend>Accounting lines</legend><table id="salesTable" class="salesInputTable">';
		$content.= '<tr class="salesInputRow"><th class="salesInputCol">'.__('sales type').'</th>';
		$content.='<th class="salesInputCol">'.__('nett').'</th>';
		$content.='<th class="salesInputCol">'.__('vat type').'</th>';
		$content.='<th class="salesInputCol">'.__('vat').'</th>';
		$content.='<th class="salesInputCol">'.__('gross').'</th>';
		$content.='<td class="salesInputColLast"><input type="button" id="addSalesRowButton" value="+"/></td></tr>';

		//Totalen van de transacties
		$content.= '<table class="salesInputTotTable" align="right"><tr class="salesInputRow"><tr>____________</tr>';
		$content.= '<tr><th class="salesInputCol">'.__('nett').'</th><td class="salesInputCol"><input type="number" step="0.01" class="salesInputField" id="nettTot" disabled></td></tr>';
		foreach($vat_options as $vat){
			if ($vat[0]!="def" and $vat[0]!=0){
				$content.= '<tr id="vatTotRow_'.$vat[0].'"><th class="salesInputCol">'.$vat[1].'</th><td class="salesInputCol"><input type="number" step="0.01" class="salesInputField" id="vatTot_'.$vat[0].'" disabled></td></tr>';
			}
		}
		//TODO: add vat shift functionality
		$content.= '<tr><th class="salesInputCol">'.__('shift').'</th><td class="salesInputCol"><select name="vatShift" id="vatShift" disabled="true"></td></tr>';
		$content.= '<tr><th></th><td>-------------------</td></tr>';
		$content.= '<tr><th class="salesInputCol">'.__('gross').'</th><td class="salesInputCol"><input type="number" step="0.01" class="salesInputField" id="grossTot" disabled></td></tr>';
		$content.= '<tr><td class="salesInputColLast"></td></tr>';
		
		//Submit buttons
		$content.= '</table></fieldset>';
		$content.= '<button type="submit" name="cmd" value="update">'.__('submit').'</button>';
		if (!$protected) $content.= '<button type="submit" name="cmd" value="remove">'.__('remove').'</button>';
		$content.= '<input type="button" value="'.__('back').'" onclick="window.location.href=\''.$url.$lang.'/sales\';"/>';
		$content.= '</form></div>';
		$content.= '<div class="salesInputVis">Bonnetje</div></div>';
		
		//Laad javascript
		$content.= '<script type="text/javascript" src="../../js/sales.js"></script>';

		//Calling javasctipt functions
		$content.= '<script>addOptionsPHP("vatShift",'.$shift_options_safe.')</script>';
		$content.= '<script>setGlobalOptions('.$invoice_options_safe.','.$sales_options_safe.','.$vat_options_safe.')</script>';
		$content.= '<script>addOnClick()</script>';
		$content.= '<script>onChangeFieldSet("salesFieldSet")</script>';
		$content.= '<script>onChangeFieldSet("invoiceFieldSet")</script>';
		$content.= '<script>onchangeInput("invoiceMode")</script>';
		$content.= '<script>onchangeMake("invoiceMake","invoice")</script>';
		$content.= '<script>onchangeRemove("invoiceRemove","invoice")</script>';	

		//Load transactions from the database
		foreach ($transactions as $trans){
			if ($trans['ID']!=""){
				$sel_options=revMutations($db,$entry, $trans,$mutations);	
				$sel_options_safe=json_encode($sel_options);
			}		
			$content.= '<script>addSalesRow('.$sel_options_safe.')</script>';
		}

		if ($invoice!=null){
			$content.= '<script>readInvoice('.$invoice.')</script>';
		}
		else{
			//add just one row
			$content.= '<script>addInvoiceRow('.$sel_in_options_safe.')</script>';
		}

	}
	
	function updateSale() {
		global $db, $content, $url, $lang;
		$userIDs = isset($_POST['UserIDs']) ? implode(',', $_POST['UserIDs']) : '';
		switch ($_POST['cmd']) {
			case 'update':
				// validate
				if ($_POST['ID']=='new') {
					// if file uploaded
					// - save in data
					// - get URL

					$db->query("INSERT INTO Entries (TransactionDate, AccountingDate, URL) VALUES ('".$_POST['TransactionDate']."', '".$_POST['AccountingDate']."', '".$_POST['Location']."')");	

					// get the entryID from the database $id = $db->lastInsertRowID();
					$last_entry=$db->querySingle("SELECT MAX(ID) FROM Entries LIMIT 1");
					$entryID=intval($last_entry);

					$db->query("INSERT INTO Sales (EntryID, Status, Reference, ContactID, ProjectID) VALUES ('".$entryID."','review','".$_POST['Reference']."', '".$_POST['ContactID']."', '".$_POST['ProjectID']."')");

					foreach ($_POST as $key => $value){
						$checkstr="SalesType";
						if (strpos($key,$checkstr)!==False){
							$db->query("INSERT INTO Transactions (EntryID) VALUES ('".$entryID."')");
							$last_trans=$db->querySingle("SELECT MAX(ID) FROM Transactions LIMIT 1");
							$transID=intval($last_trans);

							$trans_num=substr($key, strlen($checkstr),strlen($key));
							$sales_type_key="SalesType".$trans_num;
							$nett_key="nett".$trans_num;
							$vat_type_key="vatType".$trans_num;
							$vat_key="vat".$trans_num;
							$gross_key="gross".$trans_num;

							makeMutations($db,$_POST['vatShift'],$transID,$value,$_POST[$sales_type_key], $_POST[$nett_key],$_POST[$vat_type_key],$_POST[$vat_key],$_POST[$gross_key]);					
						}
					}

				}
				else {
					//how to approach this? remove previous mutations or change existing ones?
					$db->query("UPDATE Accounts SET URL='".$_POST['URL']."', Date='".$_POST['Date']."', ContactID='".$_POST['ContactID']."', ProjectID='".$_POST['ProjectID']."', Reference='".$_POST['Reference']."' WHERE ID='".$_POST['ID']."'");
					$id = $_POST['ID'];
					
					$db->query("UPDATE Mutations SET AccountID='".$_POST['NettAccountID']."', Amount='".$_POST['Nett']."' WHERE ID='".$_POST['NettID']."'");
					
					$db->query("UPDATE Mutations SET AccountID='".$_POST['VATAccountID']."', Amount='".$_POST['VAT']."' WHERE ID='".$_POST['VATID']."'");
					
					$db->query("UPDATE Mutations SET AccountID='".$_POST['GrossAccountID']."', Amount='".$_POST['Gross']."' WHERE ID='".$_POST['GrossID']."'");
				}
				break;
			case 'remove':
				$db->query("DELETE FROM Accounts WHERE ID='".$_POST['ID']."'");
				break;

			case 'make':
				
		}
		viewSaleList();
	}

	function makeMutations($db,$shift,$transID,$salesType,$nett,$vat_type,$vat,$gross) {
		
		// TODO: verlegde btw toevoegen wanneer dit ook in de rekeningenstructuur zit 

		//result
		$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transID."', '8', '".$nett."')");

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
	
	//boekhoudregels voor purchase in reverse, voelt onhandig, misschien gewoon $gross, $vat, $nett, $vat_type etc opslaan in de transaction?

	function revMutations($db, $entry, $trans,$mutations){

		foreach($mutations as $mut){
			
			if ($mut['TransactionID']==$trans['ID']){
				//result acounts

				if (strpos($mut['AccountID'],"8") !==false){
					$salesType=$mut['AccountID'];
					templog("Account id =".$mut['AccountID']."\n");
					$nett=$mut['Amount'];
				} 
				
				//vat accounts
				switch ($mut['AccountID']){
					case 16:
						$vat=$mut['Amount'];
						$vat_type=0;
					case 17:
						$vat=$mut['Amount'];
						$vat_type=9;
					case 18:
						$vat=$mut['Amount'];
						$vat_type=21;
				}

				if(isset($nett) and isset($vat)){
					$gross=$nett+$vat;
				}
			}

			$invoice_data=revJson($entry['URL']);

			
		}

		templog("Nett = ".$nett."\n");
		templog("Vat = ".$vat."\n");
		templog("Vat_type = ".$vat_type."\n");
		templog("Gross = ".$gross."\n");

		return array($salesType,$gross,$nett,$vat,$vat_type,$invoice_data);
	}

	function revJson(){
		return array("Beschrijving",2,3);
	}

	function templog($log_str){
		$logfile=fopen('log.txt','a');
		fwrite($logfile,$log_str);
		fclose($logfile);

	}

