<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../user/auth.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");

class RoleAdmin {
	
	/*
	 * Set up and change roles.
	 */
	public function admin() {
		$db = new DB();
		$user = new User();
		$role = new Role();
		$auth = new Authentication();
		if ($user->isAdmin()) {
			if (isset($_POST['action'])) {
				$action = $_POST['action'];
				if ($action=="new") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						if ($role->createRole($_POST['role'])) {
							$slave = mysql_insert_id();
							$master = mysql_real_escape_string($role->getRole());
							$db->query("INSERT INTO `role_editor`(`master`,`slave`) VALUES('$master','$slave')");
						}
					}
				}
				else if ($action=="change") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$roleID = $_POST['role'];
						if ($roleID!=$role->getRole()) {
							if (in_array($roleID,$role->getPossibleRoles($role->getRole()))) {
								$roleID = mysql_real_escape_string($roleID);
								$name = mysql_real_escape_string($_POST['name']);
								$db->query("UPDATE `role` SET `name`='$name' WHERE `role` = '$roleID'");
							}
						}
					}
				}
			}
			else if (isset($_GET['action'])) {
				if ($_GET['action']=="del") {
					if ($auth->checkToken($_GET['time'], $_GET['token'])) {
						$roleID = $_GET['role'];
						if ($roleID!=$role->getRole()) {
							if (in_array($roleID,$role->getPossibleRoles($role->getRole()))) {
								$roleID = mysql_real_escape_string($roleID);
								$ownRole = mysql_real_escape_string($role->getRole());
								$db->query("UPDATE `user` SET `role`= (SELECT user FROM stdroles) WHERE `role`='$roleID'");
								$db->query("UPDATE `role_editor` SET `master`='$ownRole' WHERE `master`='$roleID'");
								$db->query("DELETE FROM `role_editor` WHERE `slave`='$roleID'");
								$db->query("DELETE FROM `role` WHERE `role`='$roleID'");
							}
						}
					}
				}
			}
			$possibleRoles = $role->getPossibleRoles($role->getRole());
			$roles = array();
			foreach ($possibleRoles as $possibleRole) {
				$possibleRole = mysql_real_escape_string($possibleRole);
				$result = $db->query("SELECT * FROM `role` WHERE `role` = '$possibleRole'");
				while ($row = mysql_fetch_array($result)) {
					if ($possibleRole!=$role->getRole()) {
						array_push($roles,array('role'=>htmlentities($row['role'], null, "ISO-8859-1"),'name'=>htmlentities($row['name'], null, "ISO-8859-1")));
					}
				}
			}
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			require_once("template/role.tpl.php");
		}
	}
}

?>