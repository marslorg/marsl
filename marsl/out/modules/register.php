<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/module.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../includes/recaptcha.php");
include_once(dirname(__FILE__)."/../includes/basic.php");

class Register implements Module {
	
	public function display() {
		$db = new DB();
		$auth = new Authentication();
		$role = new Role();
		$user = new User();
		$recaptcha = new Recaptcha();
		$basic = new Basic();
		$location = "";
		if (isset($_GET['id'])) {
			$location = $_GET['id'];
		}
		else {
			$location = $basic->getHomeLocation();
		}
		$nickname = "";
		$mail = "";
		$mail2 = "";
		$success = false;
		$captcha = false;
		$mailFailure = false;
		$passwordFailure = false;
		$nicknameFailure = false;
		if ($user->isGuest()||$user->isAdmin()) {
			if ($auth->moduleReadAllowed("register", $role->getRole())&&$auth->locationReadAllowed($location, $role->getRole())) {
				if ($auth->moduleWriteAllowed("register", $role->getRole())&&$auth->locationWriteAllowed($location, $role->getRole())) {
					if (isset($_POST['action'])) {
						if ($_POST['action']=="send") {
							if ($recaptcha->checkRecaptcha()) {
								$mail = mysql_real_escape_string($_POST['mail']);
								$mail2 = mysql_real_escape_string($_POST['mail2']);
								if (($mail==$mail2)&&($basic->checkMail($mail))) {
									$password = mysql_real_escape_string($_POST['password']);
									$password2 = mysql_real_escape_string($_POST['password2']);
									if ($password==$password2) {
										$nickname = mysql_real_escape_string($_POST['nickname']);
										if ($user->register($nickname, $password, $mail)) {
											$success = true;
										}
										else {
											$nicknameFailure = true;
										}
									}
									else {
										$passwordFailure = true;
									}
								}
								else {
									$mailFailure = true;
								}
							}
							else {
								$captcha = true;	
							}
							if (!$success) {
								$nickname = htmlentities($_POST['nickname'], null, "ISO-8859-1");
								$mail = htmlentities($_POST['mail'], null, "ISO-8859-1");
								$mail2 = htmlentities($_POST['mail2'], null, "ISO-8859-1");
							}
							else {
								$nickname = "";
								$mail = "";
								$mail2 = "";
							}
						}
					}
				}
				$recaptcha = $recaptcha->getRecaptcha();
				require_once("template/register.tpl.php");
			}
		}
	}
	
	public function admin() {
		$db = new DB();
		$auth = new Authentication();
		$role = new Role();
		if ($auth->moduleAdminAllowed("register", $role->getRole())) {
			if (isset($_POST['action'])) {
				if ($_POST['action']=="send"&&$auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					$newID = mysql_real_escape_string($_POST['location']);
					if ($db->isExisting("SELECT `id` FROM `registration_tos`")) {
						$db->query("UPDATE `registration_tos` SET `id`='$newID'");
					}
					else {
						$db->query("INSERT INTO `registration_tos`(`id`) VALUES('$newID')");
					}
				}
			}
			$id = "";
			$result = $db->query("SELECT `id` FROM `registration_tos`");
			while ($row = mysql_fetch_array($result)) {
				$id = $row['id'];
			}
			
			$links = array();
			
			$result = $db->query("SELECT `id`, `name` FROM `navigation` WHERE `type`='1' OR `type`='2'");
			while ($row = mysql_fetch_array($result)) {
				$guestRole = $role->getGuestRole();
				$location = $row['id'];
				if ($auth->locationReadAllowed($location, $guestRole)) {
					$name = htmlentities($row['name'], null, "ISO-8859-1");
					array_push($links, array('id'=>$location, 'name'=>$name));
				}
			}
		}
		$authTime = time();
		$authToken = $auth->getToken($authTime);
		require_once("template/register.tos.tpl.php");
	}
	
	public function isSearchable() {
		return false;
	}
	
	public function getSearchList() {
		return null;
	}
	
	public function search($query, $type) {
		return null;
	}
	
	public function isTaggable() {
		return false;
	}
	
	public function getTagList() {
		return null;
	}
	
	public function addTags($tagString, $type, $news) {
		
	}
	
	public function getTagString($type, $news) {
		return null;
	}
	public function getTags($type, $news) {
		return null;
	}
	
	public function displayTag($tagID, $type) {
	}
	
	public function getImage() {
		return null;
	}
}
?>