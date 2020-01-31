<?php
	$showRegistrationForm = $init;
	$cmd = isset($_POST['cmd']) ? $_POST['cmd'] : (isset($_GET['cmd']) ? $_GET['cmd'] : false);
	$email = isset($_POST['email']) ? $_POST['email'] : false;
	$name = isset($_POST['name']) ? $_POST['name'] : false;
	$password = isset($_POST['password']) ? $_POST['password'] : false;
	$password2 = isset($_POST['password2']) ? $_POST['password2'] : false;
	$register = isset($_GET['register']) ? $_GET['register'] : false;
	$key = isset($_GET['key']) ? $_GET['key'] : false;

	switch($cmd) {
		case 'login':
			if ($email && $password) {
				$user = $db->query("SELECT * FROM Users WHERE Email='".$email."'")->fetchArray();
				if($user && password_verify($password, $user['Password'])) $_SESSION['login'] = $user['ID'];
				else $error = 'Sorry, wrong user id / password';
			}
			break;
		case 'logout':
			unset($_SESSION['login']);
			break;
		case 'new':
			$showRegistrationForm = true;
			break;
		case 'register':
			if ($password!==$password2) $error = 'Passwords don\'t match';
			else if ($DEBUG||$init) {
				$db->query("INSERT INTO Users (Email, Password, Status) VALUES ('".$email."', '".password_hash($password, PASSWORD_DEFAULT)."', '".($init?"admin":"user")."')");
				$id = $db->lastInsertRowID();
				$_SESSION['login'] = $id;
			}
			else if($db->querySingle("SELECT COUNT(*) as count FROM Users WHERE Email='".$email."'")) $error = 'User exists';
			else {
				$status = 'request_'.uniqid().
				$url = $_SERVER['SCRIPT_NAME'].'?register=allow&key='.$status;
				$db->query("INSERT INTO Users (Email, Password, Status) VALUES ('".$email."', '".password_hash($password, PASSWORD_DEFAULT)."', '".$status."')");
				$id = $db->lastInsertRowID();
				mail($admin_email, __('registration-request-subject'), $email.' wants to register. Click <a href="'.$url.'">'.$url.'</a> to allow');
				$content.= __('request-pending');
			}
			break;
		case 'renew':
			// create activation-link
			// send link to user email
			// return link sent message
			break;
	}
	switch($register) {
		case 'allow':
			$status = str_replace("request_", "confirm_", $key);
			$db->query("UPDATE Users SET Status='".$status."' WHERE Status='".$key."'");
			mail($admin_email, __('registration-request-subject'), $email.' wants to register. Click <a href="'.$url.'">'.$url.'</a> to allow');
			// send link to user email
			break;
		case 'confirm':
			$user = $db->query("SELECT * FROM Users WHERE Status='".$key."'")->fetchArray();
			$db->query("UPDATE Users SET Status='".$status."' WHERE Status='user'");
			$_SESSION['login'] = $user['ID'];
			break;
	}
	
	// === show login screen or logout button === //
	if (!isset($_SESSION['login'])) {
		$content.= '<form method="post">';
		$content.= '<div id="login">';
		$content.= __($init ? 'welcome-init' : 'welcome-login').'<br/>';
		$content.= __('email').' <input type="text" name="email" id="loginEmail" value="'.$email.'"><br/>';
		$content.= __('password').' <input type="password" name="password"><br/>';
		if ($showRegistrationForm) {
			$content.= __('password2').' <input type="password" name="password2"><br/>';
			$content.= '<button type="submit" name="cmd" value="register">'.($init?__('register'):__('request')).'</button>';
			$content.= '<input type="button" value="'.__('cancel').'" onclick="window.location.href=\''.$url.$lang.'\';"/>';
		}
		else {
			$content.= '<button type="submit" name="cmd" value="login">'.__('login').'</button><br/>';
			$content.= '<button type="submit" name="cmd" value="renew">'.__('renew').'</button><br/>';
			$content.= '<button type="submit" name="cmd" value="new">'.__('new').'</button>';
		}
		$content.= '</div>';
		$content.= '</form>';
		$content.= '<script>document.getElementById(\'loginEmail\').focus();</script>';
	}
	else {
		$user = $db->query("SELECT * FROM Users WHERE ID='".$_SESSION['login']."'")->fetchArray();
		$menu.= 'Logged in as '.$user['Email'].' <input type="button" value="'.__('logout').'" onclick="window.location.href=\''.$url.'?cmd=logout\';"/>';
	}
