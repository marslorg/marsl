<?php
include_once (dirname(__FILE__)."/includes/errorHandler.php");
include_once (dirname(__FILE__)."/includes/dbsocket.php");
include_once (dirname(__FILE__)."/user/user.php");
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
		$user = new User($db);
		if ($user->isGuest()) {
			if (isset($_POST['action'])) {
				$mailer = new Mailer($db);
				if ($_POST['action']=="password") {
					if (!empty($_POST['nickname'])) {
						if($mailer->sendPasswordMail("admin", $_POST['nickname'])) {
							header("Location: admin/index.php?var=forgot&action=success&topic=password");
						}
						else {
							header("Location: admin/index.php?var=forgot&action=failed&topic=password");
						}
					}
					else {
						header("Location: admin/index.php?var=forgot&action=empty");
					}
				}
				elseif ($_POST['action']=="nickname") {
					if (!empty($_POST['mail'])) {
						if($mailer->sendNicknameMail($_POST['mail'])) {
							header("Location: admin/index.php?var=forgot&action=success&topic=nickname");
						}
						else {
							header("Location: admin/index.php?var=forgot&action=failed&topic=nickname");
						}
					}
					else {
						header("Location: admin/index.php?var=forgot&action=empty");
					}
				}
				else {
					header("Location: admin/index.php?var=forgot&action=empty");
				}
			}
		}
		else {
			if ($user->isAdmin()) {
				header("Location: admin/index.php");
			}
			else {
				header("Location: index.php");
			}
		}
		$db->close();
	}
}

$forgot = new Forgot();
?>