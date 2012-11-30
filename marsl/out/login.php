<?php
include_once (dirname(__FILE__)."/includes/errorHandler.php");
include_once (dirname(__FILE__)."/includes/dbsocket.php");
include_once (dirname(__FILE__)."/user/user.php");
include_once (dirname(__FILE__)."/includes/config.inc.php");

class Login {
	
	/*
	 * Login a normal user.
	 */
	public function Login() {
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$db = new DB();
		$db->connect();
		$user = new User();
		if (isset($_SERVER['HTTP_REFERER'])) {
			$rightpw = $user->login($_POST['nickname'], $_POST['password']);
			$db->close();
			$referer = $_SERVER['HTTP_REFERER'];
			if ($rightpw) {
				header("Location: index.php");
			}
			else {
				header("Location: ".$referer."&wrongpw=1");
			}
		}
		else {
			header("Location: index.php");
		}
	}
}

$login = new Login();
?>