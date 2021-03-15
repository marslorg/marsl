<?php
include_once (dirname(__FILE__)."/includes/errorHandler.php");
include_once (dirname(__FILE__)."/includes/dbsocket.php");
include_once (dirname(__FILE__)."/user/user.php");
include_once (dirname(__FILE__)."/user/role.php");
include_once (dirname(__FILE__)."/includes/mailer.php");
include_once (dirname(__FILE__)."/includes/config.inc.php");

class Forgot {
	
	/*
	 * Initialize the mailer for the administrative password recovery function.
	 */
	public function __construct() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$db = new DB();
		$db->connect();
		$role = new Role($db);
		$user = new User($db, $role);
		$location = $_POST['location'];
		if ($user->isGuest()||$user->isAdmin()) {
			if (isset($_POST['action'])) {
				$mailer = new Mailer($db, $role);
				if ($_POST['action']=="password") {
					if (!empty($_POST['nickname'])) {
						if($mailer->sendPasswordMail($location, $_POST['nickname'])) {
							header("Location: index.php?id=".$location."&action=forgot&action2=success&topic=password");
						}
						else {
							header("Location: index.php?id=".$location."&action=forgot&action2=failed&topic=password");
						}
					}
					else {
						header("Location: index.php?id=".$location."&action=forgot&action2=empty");
					}
				}
				elseif ($_POST['action']=="nickname") {
					if (!empty($_POST['mail'])) {
						if($mailer->sendNicknameMail($_POST['mail'])) {
							header("Location: index.php?id=".$location."&action=forgot&action2=success&topic=nickname");
						}
						else {
							header("Location: index.php?id=".$location."&action=forgot&action2=failed&topic=nickname");
						}
					}
					else {
						header("Location: index.php?id=".$location."&action=forgot&action2=empty");
					}
				}
				else {
					header("Location: index.php?id=".$location."&action=forgot&action2=empty");
				}
			}
		}
		else {
			header("Location: index.php");
		}
		$db->close();
	}
}

$forgot = new Forgot();
?>