<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../includes/basic.php");
include_once (dirname(__FILE__)."/../user/auth.php");

class RegisterUser {
	
	/*
	 * Dialog for administrators to register a new user.
	 * No double-opt-in required.
	 */
	public function admin() {
		$user = new User();
		$auth = new Authentication();
		$basic = new Basic();
		if ($user->isAdmin()) {
			$db = new DB();
			$role = new Role();
			$possibleRoles = $role->getPossibleRoles($role->getRole());
			$userRole = true;
			if (!in_array($role->getUserRole(), $possibleRoles)) {
				$userRole = false;
			}
			$passwordProof = true;
			$emailProof = true;
			$registered = false;
			if (isset($_POST['action'])) {
				if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					if ($_POST['password']==$_POST['password2']) {
						if ($basic->checkMail($_POST['email'])) {
							if($user->register($_POST['nickname'], $_POST['password'], $_POST['email'])) {
								$email = mysql_real_escape_string($_POST['email']);
								$db->query("UPDATE `email` SET `confirmed`='1' WHERE `email`='$email'");
								$registered = true;
								$userID = $user->getIDbyName($_POST['nickname']);
								if (empty($_POST['role'])) {
									$user->changeRole($userID,$role->getUserRole());
								}
								else {
									if(($_POST['role']!=$role->getRole())&&(in_array($_POST['role'],$possibleRoles))) {
										$user->changeRole($userID,$_POST['role']);									
									}
									else {
										$user->changeRole($userID,$role->getUserRole());
									}
								}
							}
							else {
								$registered = false;
							}
						}
						else {
							$emailProof = false;
						}
					}
					else {
						$passwordProof = false;
					}
				}
			}
			$roles = array();
			foreach ($possibleRoles as $possibleRole) {
				if ($possibleRole!=$role->getRole()) {
					$possibleRole = mysql_real_escape_string($possibleRole);
					$result = $db->query("SELECT * FROM `role` WHERE `role`='$possibleRole'");
					while ($row = mysql_fetch_array($result)) {
						array_push($roles,array('role'=>$row['role'],'name'=>htmlentities($row['name'], null, "ISO-8859-1")));
					}
				}
			}
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			require_once("template/register.tpl.php");
		}
	}
}
?>