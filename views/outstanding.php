<?php
	if (isset($_POST['TransactionIDs'])) {
		echo 'IDs: '.$_POST['TransactionIDs'].'<br/>';
		$mergeDate = date("Y-m-d");
		$db->query("INSERT INTO Merge (MergeDate) VALUES ('".$mergeDate."')");
		$mergeID = $db->lastInsertRowID();
		$list = explode(',', $_POST['TransactionIDs']);
		
		foreach($list as $id) $db->query("UPDATE Transactions SET MergeID='".$mergeID."' WHERE ID='".$id."'");
	}
	if (!$view) $view = 'topay';
	if (!$view2) $view2 = 'amount';
	
	$content = '<script type="text/javascript" src="../js/outstanding.js"></script>';
	
	//~ if (isset($_POST['cmd'])) updateContact();
	viewOutstandingList();
	
	function viewOutstandingList() {
		global $db, $content, $url, $lang, $view, $view2;

		$query = "SELECT * FROM Mutations LEFT JOIN Transactions ON Mutations.TransactionID = Transactions.ID LEFT JOIN Entries ON Transactions.EntryID = Entries.ID LEFT JOIN Accounts ON Mutations.AccountID = Accounts.ID";
		switch($view) {
			case 'payed':
				$legend_left = __('payed');
				$legend_right = __('to-pay').' &amp; '.__('received');
				$WHERE_LEFT = " WHERE AccountID = '23'";
				$WHERE_RIGHT = " WHERE (AccountID = '22' OR AccountID = '5')";
				break;
			case 'received':
				$legend_left = __('received');
				$legend_right = __('to-receive').' &amp; '.__('payed');
				$WHERE_LEFT = " WHERE AccountID = '22'";
				$WHERE_RIGHT = " WHERE (AccountID = '23' OR AccountID = '4')";
				break;
			case 'topay':
				$legend_left = __('to-pay');
				$legend_right = __('payed').' &amp; '.__('to-receive');
				$WHERE_LEFT = " WHERE AccountID = '5'";
				$WHERE_RIGHT = " WHERE (AccountID = '4' OR AccountID = '23')";
				break;
			case 'toreceive':
				$legend_left = __('to-receive');
				$legend_right = __('reveiced').' &amp; '.__('to-pay');
				$WHERE_LEFT = " WHERE AccountID = '4'";
				$WHERE_RIGHT = " WHERE (AccountID = '5' OR AccountID = '22')";
				break;
		}
		// ONLY entries that do not have a mergeID!!
		$WHERE_LEFT.= " AND MergeID IS NULL";
		$WHERE_RIGHT.= " AND MergeID IS NULL";
		switch($view2) {
			case 'date':
				$ORDER = " ORDER BY TransactionDate ASC";
				break;
			case 'amount':
				$ORDER = " ORDER BY Amount ASC";
				break;
		}

		$content.= __('filter').': ';
		$content.= '<select name="account">';
		$content.= '<option value="payed" onclick="window.location.href=\''.$url.$lang.'/outstanding/payed\';"'.($view=='payed'?' selected="selected"':'').'>'.__('amounts-payed').'</option>';
		$content.= '<option value="received" onclick="window.location.href=\''.$url.$lang.'/outstanding/received\';"'.($view=='received'?' selected="selected"':'').'>'.__('amounts-received').'</option>';
		$content.= '<option value="topay" onclick="window.location.href=\''.$url.$lang.'/outstanding/topay\';"'.($view=='topay'?' selected="selected"':'').'>'.__('amounts-to-pay').'</option>';
		$content.= '<option value="toreceive" onclick="window.location.href=\''.$url.$lang.'/outstanding/toreceive\';"'.($view=='toreceive'?' selected="selected"':'').'>'.__('amounts-to-receive').'</option>';
		$content.= '</select>';
		$content.= ' '.__('sort-by');
		$content.= '<input type="radio" name="sort" value="amount" onclick="window.location.href=\''.$url.$lang.'/outstanding/'.$view.'/amount\';"'.($view2=='amount'?' checked="checked"':'').'/>'.__('amount');
		$content.= '<input type="radio" name="sort" value="date" onclick="window.location.href=\''.$url.$lang.'/outstanding/'.$view.'/date\';"'.($view2=='date'?' checked="checked"':'').'/>'.__('date');
		$content.= ' '.__('check').' <div id="balance" style="display:inline-block;"></div>';
		$content.= ' <input type="button" id="btnMerge" value="'.__('merge').'" onclick="merge();" disabled="disabled"/>';
		$content.= '<p/>';
		
		$content.= '<fieldset class="leftpane">';
		$content.= '<legend>'.$legend_left.'</legend>';
		$list = $db->query($query.$WHERE_LEFT.$ORDER);
		$content.= '<table>';
		$content.= '<tr><th style="text-align:right;">ID</th><th>'.__('date').'</th><th style="text-align:right;">'.__('amount').'</th><th>'.__('description').'</th></tr>';
		$description = '';
		while($item = $list->fetchArray()) {
			switch($item['AccountID']) {
				case 4:
				case 5:
					$description = 'Description';
					break;
				case 22:
				case 23:
					$description = 'Reference';
					break;
			}
			$content.= '<tr class="data" id="l'.$item['TransactionID'].'" onclick="select(this, '.$item['Amount'].');"><td style="text-align:right;">'.$item['TransactionID'].'</td><td>'.$item['TransactionDate'].'</td><td style="text-align:right;">€'.number_format($item['Amount'], 2, ',', '').'</td><td>'.$description.'</td></tr>';
		}
		$content.= '</table>';
		$content.= '</fieldset>';
		
		$content.= '<fieldset class="rightpane">';
		$content.= '<legend>'.$legend_right.'</legend>';
		$list = $db->query($query.$WHERE_RIGHT.$ORDER);
		$content.= '<table>';
		$content.= '<tr><th style="text-align:right;">ID</th><th>'.__('date').'</th><th style="text-align:right;">'.__('amount').'</th><th>'.__('description').'</th></tr>';
		while($item = $list->fetchArray()) {
			$content.= '<tr class="data" id="r'.$item['TransactionID'].'" onclick="select(this, '.$item['Amount'].');"><td style="text-align:right;">'.$item['TransactionID'].'</td><td>'.$item['TransactionDate'].'</td><td style="text-align:right;">€'.number_format($item['Amount'], 2, ',', '').'</td><td>'.$description.'</td></tr>';
		}
		$content.= '</table>';
		$content.= '</fieldset>';
	}
