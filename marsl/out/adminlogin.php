<?php
include_once (dirname(__FILE__)."/includes/errorHandler.php");
include_once (dirname(__FILE__)."/includes/dbsocket.php");
include_once (dirname(__FILE__)."/user/user.php");
include_once (dirname(__FILE__)."/includes/config.inc.php");

class Login {
	/*
	 * Login an admin user and redirect to the admin panel.
	 */
	public function Login() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		if (isset($_POST['action'])) {
			$db = new DB();
			$db->connect();
			$user = new User();
			$rightpw = $user->login($_POST['nickname'], $_POST['password']);
			$db->close();
			if ($rightpw) {
				header("Location: admin/index.php");
			}
			else {
				header("Location: admin/index.php?wrongpw=1");
			}
		}
		else {
			header("Location: index.php");
		}
	}
}

$login = new Login();
?>