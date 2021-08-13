<?php
	session_start();
	$DEBUG = true;
	$menu = '';
	$error = '';
	$content = '';
	
	// Extract interface language, page and view from url
	$path = substr($_SERVER["PHP_SELF"], 0, strpos($_SERVER["PHP_SELF"], 'index.php'));
	$url = 'http://'.$_SERVER["SERVER_NAME"].$path;
	
	if (strpos($_SERVER["HTTP_HOST"],$_SERVER["SERVER_PORT"])){
		$url_port = substr($url,0,strlen($url)-1);
		$url = $url_port.":".$_SERVER["SERVER_PORT"]."/";
	}
	
	$query = explode('/', strtok(substr($_SERVER["REQUEST_URI"], strlen($path)), '?'));
	$lang = isset($query[0]) ? $query[0] : 'nl';
	$page = isset($query[1]) ? $query[1] : 'projects';
	$view = isset($query[2]) ? $query[2] : '';
	$view2 = isset($query[3]) ? $query[3] : '';
	$cmd = isset($_POST['cmd']) ? $_POST['cmd'] : '';
	
	// Load dictionary for interface language
	$dictionary = json_decode(file_get_contents('lang.json'), true);
	function __($msgid) {
		global $lang, $dictionary;
		return array_key_exists($msgid, $dictionary) ? $dictionary[$msgid][$lang] : $msgid;
	}
	if ($lang != 'en') $lang = 'nl';
	
	// Generate span with an onClick to toggle an info popup with the given message
	function makeInfoButton($msg) {
		return '<span onclick="toggleInfoPopup(this,\''.$msg.'\')" class="hyphaInfoButton"></span>';
	}
	
	// Load database or create new is absent
	if (!is_writable('.')) exit('Root folder not writable, cannot create database.');
	$db = new SQLite3('accounting.sqlite');
	if(filesize('accounting.sqlite')==0) $db->exec(file_get_contents('template.sql'));
	$init = $db->querySingle("SELECT COUNT(*) as count FROM Users") == 0;
	$admin_email = $db->querySingle("SELECT Email FROM Users WHERE Status='admin'");
	
	// Do login stuff TODO: differential access for users and not all views in menu
	include_once('login.php');
	if (isset($_SESSION['login'])) {
		// Define available pages
		$views = array(
			"users" => "users.php",
			"contacts" => "contacts.php",
			"payment" => "payment.php",
			"projects" => "projects.php",
			"bank" => "bank.php",
			"purchases" => "purchases.php",
			"sales" => "sales.php",
			"memorial" => "memorial.php",
//			"taxes" => "taxes.php",
//			"balance" => "balance.php",
//			"profit" => "profit.php",
//			"loans" => "loans.php",
			"accounts" => "accounts.php",
			"paymentproviders" => "paymentproviders.php",
			"expenses" => "expenses.php",
			"revenues" => "revenues.php",
			"outstanding" => "outstanding.php",
			"rules" => "rules.php"
		);

		// Build menu
		foreach($views as $key => $php) $menu.= '<div class="menuItem'.($page==$key?' selected':'').'" onclick="location.href=\''.$url.$lang.'/'.$key.'\';">'.__($key).'</div>';

		// Load page
		if (array_key_exists($page, $views) && file_exists('views/'.$views[$page])) include_once('views/'.$views[$page]);
		else $content = __('error');
	}
	
	// Output HTML
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Administratie Coöp Plan B</title>
		<link rel="stylesheet" type="text/css" href="style/admi.css"/>
		<script type="text/javascript" src="js/help.js"></script>
	</head>
	<body>
		<h1>Administratie Coöp Plan B</h1>
		<div id="menu"><?=$menu?></div>
		<div id="content"><?=$content?></div>
		<div id="error"><?=$error?></div>
	</body>
</html>
