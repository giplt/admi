<?php
	if (!is_dir('files')) mkdir('files');
	
	if(isset($_FILES["bankCSV"])) {
		$error = false;
		
		$maxsize = ini_get("upload_max_filesize");
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $maxsize); // Remove the non-unit characters from the size.
		$maxsize = preg_replace('/[^0-9\.]/', '', $maxsize); // Remove the non-numeric characters from the size.
		$maxsize = $unit ? round($maxsize * pow(1024, stripos('bkmgtpezy', $unit[0]))) : round($maxsize);
		if ($_FILES["bankCSV"]["size"] > $maxsize) $error.= 'Sorry, your file is too large.<br/>';
		
		$filetype = strtolower(pathinfo($_FILES["bankCSV"]["name"],PATHINFO_EXTENSION));
		if($filetype!="csv") $error.= 'Wrong file type<br/>';
		
		if ($error) {
			$error.= 'Only CSV files are allowed. Maximum size: '.ini_get("upload_max_filesize").'<br/>';
			echo $error;
			exit();
		}

		$bankAccountID = $_POST['bankID'];
		$paymentProvider = $db->query("SELECT * FROM PaymentProviders WHERE AccountID='".$bankAccountID."'")->fetchArray();
		$api = json_decode($paymentProvider['API']);
		
		$file = file_get_contents($_FILES["bankCSV"]['tmp_name']);
		// remove byte order mark (used in PayPal CSV) and leading or trailing whitespace
		$file = trim($file, "\xEF\xBB\xBF \t\n\r\0\x0B");
		// add header row if available from api
		if ($api->addHeaderRow) $file = $api->addHeaderRow.PHP_EOL.$file;
		// convert lines into array
		$array = preg_split("/\r\n|\n|\r/", $file);
		
		$rows = array_map('str_getcsv', $array);
		$header = array_shift($rows);
		$csv = [];
		foreach($rows as $row) $csv[] = array_combine($header, $row);
		
		$imports = 0;
		$doubles = 0;
		echo '<table>';
		echo '<tr><th>datum</th><th>bedrag</th><th>wederpartij</th><th>omschrijving</th></tr>';
		foreach($csv as $item) {
			$date = date_parse($item[$api->dateField]);
			
			$amount = $item[$api->amountField];
			if ($api->amountFormat) $amount = str_replace(',', '.', str_replace('.', '', $amount));
			$amount = floatval($amount);
			if (isset($item[$api->signField]) && $item[$api->signField]==$api->signFieldMinusValue) $amount = -$amount;
//			TODO: implement proper number parser, e.g.
//			$amount = numfmt_parse(numfmt_create('de_DE', NumberFormatter::DECIMAL), $item[$api->amountField])."\n";
//			Needs non-default php.ini configuration turn on "intl" function: sudo apt-get install php7.0-intl
			$account = $api->accountField ? $item[$api->accountField] : "";
			$description = $item[$api->descriptionField];
			$paymentEndPoint = $api->paymentEndPointField ? $item[$api->paymentEndPointField]: "";
			$transactionFee = $api->transactionFeeField ? $item[$api->transactionFeeField] : false;
			// get unique ID for transaction, to be used for protection against double booking of the same transaction on re-import
			$transactionID = $item[$api->transactionID] ? $item[$api->transactionID] : md5(serialize($item));
			
			// check if bank entry exists
			// TODO: add payment provider name to transaction id to prevent (improbable) collisions of IDs
			$newRecord = $db->querySingle("SELECT COUNT(*) as count FROM Bank WHERE TransactionID='".$transactionID."'") == 0;

			// create payment entry
			if ($newRecord) {
				$transactionDate = sprintf("%04d-%02d-%02d", $date['year'], $date['month'], $date['day']);
				$accountingDate = date("Y-m-d");
				$db->query("INSERT INTO Entries (TransactionDate, AccountingDate, URL) VALUES ('".$transactionDate."', '".$accountingDate."', '".$_FILES["bankCSV"]["name"]."')");
				// get the entryID from the database $id = $db->lastInsertRowID();
				$last_entry = $db->querySingle("SELECT MAX(ID) FROM Entries LIMIT 1");
				$entryID = intval($last_entry);
				$db->query("INSERT INTO Bank (EntryID, TransactionID, Description, FromPaymentEndpointID, ToPaymentEndpointID) VALUES ('".$entryID."','".$transactionID."','".$description."', '', '')");
				$imports++;

				// Create transaction
				$db->query("INSERT INTO Transactions (EntryID) VALUES ('".$entryID."')");
				$last_entry = $db->querySingle("SELECT MAX(ID) FROM Transactions LIMIT 1");
				$transactionID = intval($last_entry);
				// boek transactie op bankrekening
				$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transactionID."','".$bankAccountID."','".$amount."')");
				// en op een tussenrekening voor ontvangen (ID 22) of betaalde (ID 23) bedragen
				if ($amount>=0.0) {
					$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transactionID."','22','".$amount."')");
				}
				else {
					$db->query("INSERT INTO Mutations (TransactionID, AccountID, Amount) VALUES ('".$transactionID."','23','".(-$amount)."')");
				}

				// TODO: Create purchase transaction for transaction fee
				if ($transactionFee) {
				}

				// TODO: Use $account and $description to search for merge proposals

				echo '<tr><td>'.$date['day'].'-'.$date['month'].'-'.$date['year'].'</td><td>'.$amount.'</td><td>'.$account.'</td><td>'.$description.'</td></tr>';
			}
			else $doubles++;
		}
		echo '</table>';
		echo $imports.' new records imported, '.$doubles.' already existing records omitted';
		exit;
	}
	
	$sort = isset($_GET['sort']) ? $_GET['sort'] : 'Description';
	if ($sort[0]=='-') {
		$order = 'DESC';
		$sort = substr($sort, 1);
	}
	else {
		$order = 'ASC';
	}
	
	//Laad javascript
	$content = '<script type="text/javascript" src="../js/bank.js"></script>';
	
	if ($cmd) updateBank();
	elseif ($view) viewBank($view);
	else viewBankList();

	function sortColumn($id) {
		global $sort, $order;
		$html = '';
		if (!($sort==$id && $order=='ASC')) $html.= '<span style="cursor:pointer;" onclick="sort(\''.$id.'\')">▲</span>';
		if (!($sort==$id && $order=='DESC')) $html.= '<span style="cursor:pointer;" onclick="sort(\'-'.$id.'\')">▼</span>';
		return $html;
	}

	function viewBankList() {
		global $db, $content, $url, $lang, $sort, $order;
		
		$content.= '<form id="bankForm" action="bank" method="post">';
		$content.= 'Filter: ';
		
		$query = "SELECT * FROM Accounts WHERE PID='3' ORDER BY Name";
		$banks = $db->query($query);
		$selected = isset($_GET['filter']) ? explode(',', $_GET['filter']) : array();
		while($bank = $banks->fetchArray()) {
//			$checked = in_array($bank['ID'], $selected) ? 'on' : 'off';
			$content.= '<input type="checkbox" name="bank_'.$bank['ID'].'"'.(in_array($bank['ID'], $selected)?' checked':'').' onchange="filter(this);"/>'.$bank['Name'].'&nbsp;';
		}
		$content.= '<input type="button" value="'.__('import').'" onclick="window.location.href=\''.$url.$lang.'/bank/import\';"/>';
		$content.= '<hr/>';

		$content.= '<table>';
		$content.= '<tr>';
		$content.= '<th>ID'.sortColumn('ID').'</th>';
		$content.= '<th>'.__('date').sortColumn('TransactionDate').'</th>';
		$content.= '<th>'.__('amount').sortColumn('Amount').'</th>';
		$content.= '<th>'.__('description').sortColumn('Description').'</th>';
//		$content.= '<td><input type="button" value="'.__('add').'" onclick="window.location.href=\''.$url.$lang.'/bank/new\';"/></td>';
		$content.= '</tr>';
		
		$query = "SELECT * FROM Bank LEFT JOIN Entries ON Bank.EntryID = Entries.ID ORDER BY ".$sort." ".$order;
//		$query = "SELECT * FROM Bank LEFT JOIN Entries ON Bank.EntryID = Entries.ID LEFT JOIN Transactions ON Bank.EntryID='".$entry['ID']."'" ORDER BY ".$sort." ".$order;
		$list = $db->query($query);
		while($item = $list->fetchArray()) {
			// collect info
			$entry = $db->query("SELECT * FROM Entries WHERE ID='".$item['EntryID']."'")->fetchArray();
			$transaction = $db->query("SELECT * FROM Transactions WHERE EntryID='".$entry['ID']."'")->fetchArray();
			$mutation = $db->query("SELECT * FROM Mutations LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID WHERE TransactionID='".$transaction['ID']."' AND PID='3'")->fetchArray();
			
			// put in the table
			if (!$selected || in_array($mutation['AccountID'], $selected)) {
				$content.= '<tr class="data">';
				$content.= '<td>'.$item['ID'].'</td>';
				$content.= '<td>'.$entry['TransactionDate'].'</td>';
				$content.= '<td style="text-align:right;">€'.number_format($mutation['Amount'], 2, ',', '').'</td>';
				$content.= '<td>'.$item['Description'].'</td>';
	//			$content.= '<td><input type="button" value="'.__('edit').'" onclick="window.location.href=\''.$url.$lang.'/bank/'.$item['ID'].'\';"/></td>';
				$content.= '</tr>';
			}
		}
		$content.= '</table>';
		$content.= '</form>';
	}
	
	function viewBank($id) {
		global $db, $content, $url, $lang;
		
		//get content
		if ($id=='import') {
			$query = "SELECT * FROM Accounts WHERE PID='3' ORDER BY Name";
			$banks = $db->query($query);
			$content.= '<h3>Import CSV</h3>';
			$content.= 'Select bank:<br/>';
			while($bank = $banks->fetchArray()) {
				$content.= '<input type="radio" name="bankID" value="'.$bank['ID'].'" onclick="validateBankImportButton();"/>'.$bank['Name'].'<br/>';
			}
			$content.= 'Select file:<input id="importFile" type="file" value="'.__('upload').'" name=myFile accept=".csv" onchange="validateBankImportButton();"><br/>';
			$content.= '<button id="importButton" onclick="upload();" disabled="disabled">Import</button>';
			$content.= '<div id="csvView"></div>';
			return;
		}
	}
	
	function updateBank() {
		global $db, $content, $url, $lang;
		$userIDs = isset($_POST['UserIDs']) ? implode(',', $_POST['UserIDs']) : '';
		switch ($_POST['cmd']) {
			case 'import':
				
				break;
		}
		viewBankList();
	}
